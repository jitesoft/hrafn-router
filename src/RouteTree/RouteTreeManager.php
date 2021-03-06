<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteTreeManager.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\RouteTree;

use Hrafn\Router\Router;
use Jitesoft\Utilities\DataStructures\Maps\SimpleMap;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * RouteTreeManager
 *
 * @author  Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class RouteTreeManager implements LoggerAwareInterface {
    private SimpleMap $rootNodes;
    private LoggerInterface $logger;

    /**
     * RouteTreeManager constructor.
     *
     * @param LoggerInterface $logger Logger to use.
     */
    public function __construct(LoggerInterface $logger) {
        $this->logger    = $logger;
        $this->rootNodes = new SimpleMap();
    }

    /**
     * Create a new or return existing root node.
     *
     * @param string $part Node path part.
     * @return Node
     */
    public function createOrGetRootNode(string $part): Node {
        $this->logger->debug(
            '{tag} Fetching or creating root node.',
            ['tag' => Router::LOG_TAG]
        );
        if ($this->rootNodes->has($part)) {
            $this->logger->debug(
                '{tag} Node existed, returning route node with part {part}.', [
                    'tag'  => Router::LOG_TAG,
                    'part' => $part
                ]
            );
        } else {
            $this->logger->debug(
                '{tag} Node did not exist, creating node with part {part}.', [
                    'tag'  => Router::LOG_TAG,
                    'part' => $part
                ]
            );

            $this->rootNodes[$part] = new Node(null, $part);
        }

        return $this->rootNodes[$part];
    }

    /**
     * Create or get an existing node from a given node.
     *
     * @param Node   $parent Parent node.
     * @param string $part   Path part.
     * @return Node
     */
    public function createOrGetNode(Node $parent, string $part): Node {
        if ($parent->hasChild($part)) {
            $this->logger->debug(
                '{tag} Node had child with part {part} returning node.', [
                    'tag'  => Router::LOG_TAG,
                    'part' => $part
                ]
            );
        } else {

            $this->logger->debug(
                '{tag} No child with part {part} existed. Creating new node.', [
                    'tag'  => Router::LOG_TAG,
                    'part' => $part
                ]
            );
            $parent->createChild($part);
        }
        return $parent->getChild($part);
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger Logger to use.
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function setLogger(LoggerInterface $logger): void {
        $this->logger = $logger;
    }

}
