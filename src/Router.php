<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Router.php - Part of the router project.

  © - Jitesoft 2018-2021
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router;

use Hrafn\Router\{Attributes\ActionResolver,
    Attributes\AttributeResolver,
    Attributes\Controller,
    Attributes\ControllerResolver,
    Attributes\MiddlewareResolver,
    Contracts\ActionResolverInterface,
    Contracts\ControllerResolverInterface,
    Contracts\DispatcherInterface,
    Contracts\MiddlewareResolverInterface,
    Contracts\ParameterExtractorInterface,
    Contracts\RouteBuilderInterface,
    Contracts\PathExtractorInterface,
    Dispatcher\DefaultDispatcher,
    Parser\RegexParameterExtractor as ParamExtractor,
    Parser\RegexPathExtractor as PathExtractor,
    RouteTree\Node,
    RouteTree\RouteTreeManager};
use Jitesoft\Exceptions\Http\Client\HttpMethodNotAllowedException;
use Jitesoft\Exceptions\Http\Client\HttpNotFoundException;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use Jitesoft\Utilities\DataStructures\Maps\ {
    MapInterface,
    SimpleMap
};
use Psr\ {
    Container\ContainerInterface,
    Log\LoggerAwareInterface,
    Log\LoggerInterface,
    Log\NullLogger
};
use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface,
    Server\RequestHandlerInterface
};

/**
 * Router
 *
 * @author  Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class Router implements LoggerAwareInterface, RequestHandlerInterface {
    public const LOG_TAG = 'Hrafn\Router:';

    private ContainerInterface | MapInterface $container;
    private LoggerInterface $logger;
    private RouteBuilderInterface $routeBuilder;
    private SimpleMap $actions;
    private RouteTreeManager $routeTreeManager;
    private PathExtractorInterface $pathExtractor;
    private ParameterExtractorInterface $paramExtractor;
    private Node $rootNode;

    /**
     * Middlewares marked as disabled.
     * This array should not be directly accessed, use the
     * Router::enable/disable - Middleware functions instead.
     * I.E., this array should be seen as internal, but needs to be reachable from friend classes.
     *
     * @var array|string[]
     */
    public static array $disabledMiddleware = [];

    /**
     * Mark middleware as enabled.
     *
     * @param string ...$middleware Middleware or middlewares to enable.
     * @return void
     */
    public static function enableMiddleware(string ...$middleware): void {
        self::$disabledMiddleware = array_filter(
            self::$disabledMiddleware,
            static function (string $m) use ($middleware) {
                return !in_array($m, $middleware, true);
            }
        );
    }

    /**
     * Mark a middleware as disabled.
     *
     * @param string ...$middleware Middleware or middlewares to disable.
     * @return void
     */
    public static function disableMiddleware(string ...$middleware): void {
        array_push(self::$disabledMiddleware, ...$middleware);
    }

    /**
     * Router constructor.
     *
     * @param ContainerInterface|null $container     Dependency container.
     * @param bool                    $useAttributes If the router should resolve attribute based routes or not.
     */
    public function __construct(?ContainerInterface $container = null, bool $useAttributes = false) {
        $this->container = $container ?? new SimpleMap();

        $get = function ($name, $default) {
            if (!$this->container->has($name)) {
                $this->container->set($name, $default);
            }
            /** @noinspection PhpUnhandledExceptionInspection */
            return $this->container->get($name);
        };

        $this->logger = $get(
            LoggerInterface::class,
            new NullLogger()
        );

        $this->pathExtractor = $get(
            PathExtractorInterface::class,
            new PathExtractor($this->logger)
        );

        $this->paramExtractor = $get(
            ParameterExtractorInterface::class,
            new ParamExtractor($this->logger)
        );

        $this->routeTreeManager = new RouteTreeManager($this->logger);
        $this->actions          = new SimpleMap();
        $this->rootNode         = new Node(null, '');

        $this->routeBuilder     = new RouteBuilder(
            [],
            $this->rootNode,
            $this->pathExtractor,
            $this->paramExtractor,
            $this->routeTreeManager,
            '',
            $this->logger,
            $this->actions,
            $this->container
        );

        if ($useAttributes) {
            $controllerResolver = $get(ControllerResolverInterface::class, new ControllerResolver());
            $actionResolver     = $get(
                ActionResolverInterface::class,
                new ActionResolver($get(MiddlewareResolverInterface::class, new MiddlewareResolver()))
            );

            $this->buildAttributeRoutes($actionResolver, $controllerResolver);
        }
    }

    public function buildAttributeRoutes(
        ActionResolverInterface $actionResolver,
        ControllerResolverInterface $controllerResolver): void {

        // Use cache!
        $actions = $actionResolver->getFunctionActions();
        foreach ($controllerResolver->getAllControllers() as $controller) {
            array_merge($actions, $actionResolver->getControllerActions($controller));
        }

        foreach ($actions as $action) {
            // Method must be one of the Method:: constants (which is checked in the attribute constructor).
            $method = strtolower($action['method']);
            $this->routeBuilder->{$method}(pattern: $action['path'], handler: $action['handler'], middlewares: $action['middlewares']);
        }
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger Logger to use.
     * @return void
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger): void {
        $this->logger = $logger;
        $this->routeBuilder->setLogger($logger);
        $this->routeTreeManager->setLogger($logger);
        $this->pathExtractor->setLogger($logger);
        $this->paramExtractor->setLogger($logger);
    }

    /**
     * @return RouteBuilderInterface
     */
    public function getBuilder(): RouteBuilderInterface {
        return $this->routeBuilder;
    }

    /**
     * Handle the request and return a response.
     *
     * @param ServerRequestInterface $request Request to handle.
     * @return ResponseInterface
     * @throws HttpMethodNotAllowedException On invalid http method.
     * @throws HttpNotFoundException         On invalid path.
     * @throws InvalidArgumentException      On invalid argument.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $dispatcher = $this->container->has(
            DispatcherInterface::class
        ) ? $this->container->get(
            DispatcherInterface::class
        ) : new DefaultDispatcher(
            $this->rootNode,
            $this->actions,
            $this->pathExtractor,
            $this->logger
        );

        $this->logger->debug(
            '{tag} Created dispatcher, calling dispatch with supplied request.',
            ['tag' => self::LOG_TAG]
        );

        return $dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        )->handle($request);
    }

}
