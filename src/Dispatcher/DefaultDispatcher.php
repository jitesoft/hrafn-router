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
use Hrafn\Router\Router;
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
    /** @var PathExtractorInterface|RegexPathExtractor|null */
    private $pathExtractor = null;
    /** @var LoggerInterface|NullLogger|null */
    private $logger = null;
    /** @var Node|null */
    private $root = null;
    /** @var MapInterface|null */
    private $actions = null;

    /**
     * DefaultDispatcher constructor.
     * @param Node                        $rootNode      Initial node to traverse from.
     * @param MapInterface                $actions       Actions map.
     * @param PathExtractorInterface|null $pathExtractor Extractor for paths.
     * @param null|LoggerInterface        $logger        Logger to use.
     */
    public function __construct(Node $rootNode,
                                MapInterface $actions,
                                ?PathExtractorInterface $pathExtractor = null,
                                ?LoggerInterface $logger = null) {
        $this->logger        = $logger ?? new NullLogger();
        $this->root          = $rootNode;
        $this->actions       = $actions;
        $this->pathExtractor = $pathExtractor ?? new RegexPathExtractor(
            $this->logger
        );
    }

    /**
     * @param Node           $parent Parent node.
     * @param QueueInterface $parts  Queue to traverse.
     * @return Node
     * @throws HttpNotFoundException On path not found.
     */
    private function getNode(Node $parent, QueueInterface $parts): Node {
        if (!$parent->hasChild($parts->peek())
            && !$parent->hasChild('%PARAM%')
        ) {
            $this->logger->error(
                '{tag} Tried to fetch a resource that did not exist.',
                [ 'tag' => Router::LOG_TAG ]
            );

            throw new HttpNotFoundException(
                'Could not locate the requested resource.'
            );
        }

        $node = $parent->getChild($parts->dequeue());
        return ($parts->count() === 0) ? $node : $this->getNode(
            $node,
            $parts
        );
    }

    /**
     * @param string $method Method that is used in request.
     * @param string $target Target path.
     * @return RequestHandlerInterface
     * @throws HttpNotFoundException         On path not found.
     * @throws HttpMethodNotAllowedException On method not existing.
     */
    public function dispatch(string $method,
                             string $target): RequestHandlerInterface {
        $parts     = $this->pathExtractor->getUriParts($target);
        $node      = $this->getNode($this->root, $parts);
        $reference = $node->getReference(mb_strtolower($method));

        if (!$reference) {
            throw new HttpMethodNotAllowedException();
        }

        if (!$this->actions->has($reference)) {
            throw new HttpNotFoundException();
        }

        $this->logger->debug(
            '{tag} Handler found with default dispatcher.',
            [ 'tag' => Router::LOG_TAG ]
        );
        return $this->actions[$reference]->getHandler();
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger Logger to use.
     * @return void
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

}
