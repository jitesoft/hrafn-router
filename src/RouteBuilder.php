<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteBuilder.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router;

use Hrafn\Router\Contracts\PathExtractorInterface;
use Hrafn\Router\Contracts\RouteBuilderInterface;
use Hrafn\Router\RouteTree\Node;
use Hrafn\Router\RouteTree\RouteTreeManager;
use Hrafn\Router\Traits\MethodToActionTrait;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use Jitesoft\Utilities\DataStructures\Maps\MapInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * RouteBuilder
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class RouteBuilder implements RouteBuilderInterface, LoggerAwareInterface {
    use MethodToActionTrait;

    private $root;
    private $middlewares;
    private $extractor;
    private $manager;
    private $basePattern;
    private $logger;
    private $actionContainer;

    /**
     * RouteBuilder constructor.
     * @param array                  $middlewares
     * @param Node                   $node
     * @param PathExtractorInterface $extractor
     * @param RouteTreeManager       $manager
     * @param string                 $basePattern
     * @param LoggerInterface        $logger
     * @param MapInterface           $actionContainer
     */
    public function __construct(array $middlewares,
                                Node $node,
                                PathExtractorInterface $extractor,
                                RouteTreeManager $manager,
                                string $basePattern,
                                LoggerInterface $logger,
                                MapInterface $actionContainer) {

        $this->root            = $node;
        $this->middlewares     = $middlewares;
        $this->extractor       = $extractor;
        $this->manager         = $manager;
        $this->basePattern     = $this->cleanupPattern($basePattern);
        $this->actionContainer = $actionContainer;
        $this->logger          = $logger;
    }

    private function cleanupPattern(string $pattern): string {
        // Remove slash from first and last part of pattern.
        $pattern = substr($pattern, -1) === '/' ? substr($pattern, -1) : $pattern;
        return substr($pattern, 0, 1) === '/' ? substr($pattern, 1) : $pattern;
    }

    private function getOrCreateNode(string $pattern): Node {
        $parts = $this->extractor->getUriParts($pattern);
        $node  = $this->manager->createOrGetNode($this->root, $parts->dequeue());

        do {
            $node = $this->manager->createOrGetNode($node, $parts->dequeue());
        } while ($node !== null);

        return $node;
    }

    /**
     * Method which all the http-method specific methods forward data to.
     *
     * @param string $method
     * @param string $pattern
     * @param        $handler
     * @param array  $middleWares
     * @return RouteBuilderInterface
     */
    protected function action(string $method,
                              string $pattern,
                              $handler,
                              $middleWares = []): RouteBuilderInterface {
        $pattern   = $this->cleanupPattern($pattern);
        $reference = sprintf('%s::%s/%s', $method, $this->basePattern, $pattern);
        $node      = $this->getOrCreateNode($pattern);

        $node->addReference($method, $reference);
        $this->actionContainer->set($reference, new Action($method, $handler, $pattern, $middleWares));
        return $this;
    }

    /**
     * Create a new namespace inside of current namespace.
     * A RouteBuilderInterface instance is passed as the single argument to the $closure callback.
     *
     * @param string     $pattern
     * @param array|null $middleWares
     * @param callable   $closure
     * @return RouteBuilderInterface
     */
    public function namespace(string $pattern, callable $closure, ?array $middleWares): RouteBuilderInterface {
        $pattern = $this->cleanupPattern($pattern);

        $pathParts = $this->extractor->getUriParts($pattern);
        $node      = $this->manager->createOrGetRootNode($pathParts->dequeue());
        $part      = $pathParts->dequeue();

        while ($part !== null) {
            $node = $this->manager->createOrGetNode($node, $part);
            $part = $pathParts->dequeue();
        }

        $builder = new RouteBuilder(
            $middleWares ?? [],
            $node,
            $this->extractor,
            $this->manager,
            sprintf('%s/%s', $this->basePattern, $pattern),
            $this->logger,
            $this->actionContainer
        );

        $closure($builder);
        return $this;
    }

    /**
     * Create a new group inside of current group.
     * A RouteBuilderInterface instance is passed as the single argument to the $closure callback.
     * @alias namespace
     *
     * @param string     $pattern
     * @param callable   $closure
     * @param array|null $middleWares
     * @return RouteBuilderInterface
     */
    public function group(string $pattern, callable $closure, ?array $middleWares): RouteBuilderInterface {
        return $this->namespace($pattern, $closure, $middleWares);
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
}
