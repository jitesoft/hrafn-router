<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Router.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router;

use Hrafn\Router\ {
    Contracts\DispatcherInterface,
    Contracts\ParameterExtractorInterface,
    Contracts\RouteBuilderInterface,
    Contracts\PathExtractorInterface,
    Dispatcher\DefaultDispatcher,
    Parser\RegexParameterExtractor as ParamExtractor,
    Parser\RegexPathExtractor as PathExtractor,
    RouteTree\Node,
    RouteTree\RouteTreeManager
};
use Jitesoft\Exceptions\Http\Client\HttpMethodNotAllowedException;
use Jitesoft\Exceptions\Http\Client\HttpNotFoundException;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use Jitesoft\Exceptions\Logic\InvalidKeyException;
use Jitesoft\Utilities\DataStructures\Maps\ {
    MapInterface,
    SimpleMap
};
use Jitesoft\Utilities\DataStructures\Queues\QueueInterface;
use Psr\ {
    Container\ContainerInterface,
    Log\LoggerAwareInterface,
    Log\LoggerInterface,
    Log\NullLogger
};
use Psr\Http\{Message\RequestInterface,
    Message\ResponseInterface,
    Message\ServerRequestInterface,
    Server\MiddlewareInterface,
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

    /** @var MapInterface|ContainerInterface */
    private                             $container;
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
     * @param ContainerInterface|null $container Dependency container.
     */
    public function __construct(?ContainerInterface $container = null) {
        $this->container = $container ?? new SimpleMap();

        $get = function ($name, $default) {
            if (!$this->container->has($name)) {
                $this->container->set($name, $default);
            }
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
    }

    /**
     * @return RouteBuilderInterface
     */
    public function getBuilder() {
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
        $dispatcher = null;
        if ($this->container->has(DispatcherInterface::class)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $dispatcher = $this->container->get(DispatcherInterface::class);
        } else {
            $dispatcher = new DefaultDispatcher(
                $this->rootNode,
                $this->actions,
                $this->pathExtractor,
                $this->logger
            );
        }

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
