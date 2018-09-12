<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Router.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router;

use Hrafn\Router\{
    Contracts\ActionInterface,
    Contracts\RouteBuilderInterface,
    Contracts\PathExtractorInterface,
    Parser\RegularExpressionExtractor,
    RouteTree\Node,
    RouteTree\RouteTreeManager
};
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
    /** @var Node */
    private $rootNode;

    public function __construct(?ContainerInterface $container = null) {
        $this->container = $container ?? new SimpleMap();
        $this->logger    = $this->container->has(LoggerInterface::class)
            ? $container->get(LoggerInterface::class)
            : new NullLogger();

        if (!$this->container->has(PathExtractorInterface::class)) {
            $this->pathExtractor = new RegularExpressionExtractor('\{(\w+?)\}', '\{\?(\w+)\}', '~', $this->logger);
        } else {
            $this->pathExtractor = $container->get(PathExtractorInterface::class);
        }

        $this->routeTreeManager = new RouteTreeManager($this->logger);
        $this->actions          = new SimpleMap();
        $this->rootNode         = new Node(null, '/');
        $this->routeBuilder     = new RouteBuilder(
            [],
            $this->rootNode,
            $this->pathExtractor,
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
        $uri   = $request->getUri()->getPath();
        $parts = $this->pathExtractor->getUriParts($uri);
        $node  = $this->getNode($this->rootNode, $parts);

        $reference = $node->getReference(mb_strtolower($request->getMethod()));

        if (!$reference) {
            throw new HttpMethodNotAllowedException();
        }

        if (!$this->actions->has($reference)) {
            throw new HttpNotFoundException();
        }

        /** @var ActionInterface $action */
        $action = $this->actions->get($reference);
    }

    /**
     * @param Node           $parent
     * @param QueueInterface $parts
     * @return Node
     * @throws HttpNotFoundException
     */
    private function getNode(Node $parent, QueueInterface $parts): Node {
        $part = $parts->dequeue();
        if ($parts === null || !$parent->hasChild($part)) {
            throw new HttpNotFoundException();
        }

        $node = $parent->getChild($parent);
        return ($parts->peek() === null) ? $node : $this->getNode($node, $parts);
    }

}
