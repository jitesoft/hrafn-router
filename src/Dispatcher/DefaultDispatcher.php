<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  DefaultDispatcher.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Dispatcher;

use Hrafn\Router\Contracts\ActionInterface;
use Hrafn\Router\Contracts\DispatcherInterface;
use Hrafn\Router\Contracts\PathExtractorInterface;
use Hrafn\Router\Parser\RegexPathExtractor;
use Hrafn\Router\RouteTree\Node;
use Jitesoft\Exceptions\Http\Client\HttpMethodNotAllowedException;
use Jitesoft\Exceptions\Http\Client\HttpNotFoundException;
use Jitesoft\Utilities\DataStructures\Maps\MapInterface;
use Jitesoft\Utilities\DataStructures\Queues\QueueInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * DefaultDispatcher
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class DefaultDispatcher implements DispatcherInterface, LoggerAwareInterface {

    private $pathExtractor = null;
    private $logger        = null;
    private $root          = null;
    private $actions       = null;

    /**
     * DefaultDispatcher constructor.
     * @param Node                        $rootNode
     * @param MapInterface                $actions
     * @param PathExtractorInterface|null $pathExtractor
     * @param null|LoggerInterface        $logger
     */
    public function __construct(Node $rootNode,
                                MapInterface $actions,
                                ?PathExtractorInterface $pathExtractor = null,
                                ?LoggerInterface $logger = null) {

        $this->logger        = $logger ?? new NullLogger();
        $this->pathExtractor = $pathExtractor ?? new RegexPathExtractor($this->logger);
        $this->root          = $rootNode;
        $this->actions       = $actions;
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

        $node = $parent->getChild($part);
        return ($parts->peek() === null) ? $node : $this->getNode($node, $parts);
    }

    /**
     * @param string $method
     * @param string $target
     * @return RequestHandlerInterface
     * @throws HttpNotFoundException
     * @throws HttpMethodNotAllowedException
     */
    public function dispatch(string $method, string $target): RequestHandlerInterface {
        $parts     = $this->pathExtractor->getUriParts($target);
        $node      = $this->getNode($this->root, $parts);
        $reference = $node->getReference(mb_strtolower($method));

        if (!$reference) {
            throw new HttpMethodNotAllowedException();
        }

        if (!$this->actions->has($reference)) {
            throw new HttpNotFoundException();
        }

        /** @var ActionInterface $action */
        return $this->actions[$reference]->getHandler();
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
}
