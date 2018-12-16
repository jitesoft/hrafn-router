<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Router.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router;

use Hrafn\Router\{Contracts\ActionInterface,
    Contracts\DispatcherInterface,
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
use Jitesoft\Utilities\DataStructures\Queues\QueueInterface;
use Psr\ {
    Container\ContainerInterface,
    Http\Message\ResponseInterface,
    Http\Message\ServerRequestInterface,
    Http\Server\RequestHandlerInterface,
    Log\LoggerAwareInterface,
    Log\LoggerInterface,
    Log\NullLogger
};


/**
 * Router
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class Router implements LoggerAwareInterface, RequestHandlerInterface {

    /** @var string */
    public const LOG_TAG = 'Hrafn\Router:';
    /** @var LoggerInterface */
    private $logger;
    /** @var MapInterface|ContainerInterface */
    private $container;
    /** @var RouteBuilderInterface  */
    private $routeBuilder;
    /** @var SimpleMap */
    private $actions;
    /** @var RouteTreeManager */
    private $routeTreeManager;
    /** @var PathExtractorInterface */
    private $pathExtractor;
    /** @var ParameterExtractorInterface */
    private $paramExtractor;
    /** @var Node */
    private $rootNode;

    public function __construct(?ContainerInterface $container = null) {
        $this->container = $container ?? new SimpleMap();

        $getOrNull = function($name) use($container) {
            return $this->container->has($name) ? $this->container->get($name) : null;
        };

        $this->logger         = $getOrNull(LoggerInterface::class) ?? new NullLogger();
        $this->pathExtractor  = $getOrNull(PathExtractorInterface::class) ?? new PathExtractor($this->logger);
        $this->paramExtractor = $getOrNull(ParameterExtractorInterface::class) ?? new ParamExtractor($this->logger);

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
            $this->actions
        );
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     * @return void
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger) {
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
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws HttpMethodNotAllowedException
     * @throws HttpNotFoundException
     * @throws InvalidArgumentException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $dispatcher = null;
        if ($this->container->has(DispatcherInterface::class)) {
            $this->container->get(DispatcherInterface::class);
        } else {
            $dispatcher = new DefaultDispatcher(
                $this->rootNode,
                $this->actions,
                $this->pathExtractor,
                $this->logger
            );
        }

        return $dispatcher
            ->dispatch(
                $request->getMethod(),
                $request->getRequestTarget())
            ->handle($request);
    }

}
