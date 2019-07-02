<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteNode.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\RouteTree;

use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use Jitesoft\Utilities\DataStructures\Maps\MapInterface;
use Jitesoft\Utilities\DataStructures\Maps\SimpleMap;

/**
 * RouteNode
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 * @internal
 * @state Unstable
 */
class Node {

    /** @var Node */
    private $parent;

    /** @var MapInterface */
    private $children;

    /** @var string */
    private $part;

    /** @var string|null */
    private $references;

    /**
     * RouteNode constructor.
     * @param Node|null $parent Parent node.
     * @param string    $part   Part of path this node uses.
     * @internal
     */
    public function __construct(?Node $parent, string $part) {
        $this->part       = $part;
        $this->parent     = $parent;
        $this->children   = new SimpleMap();
        $this->references = new SimpleMap();
    }

    /**
     * @param string $part Part of path that the child node should use.
     * @return Node
     */
    public function createChild(string $part): Node {
        $this->children[$part] = new Node($this, $part);
        return $this->children[$part];
    }

    /**
     * @param string $part Part of path to check for.
     * @return boolean
     */
    public function hasChild(string $part) {
        return $this->children->has($part);
    }

    /**
     * Get an action reference based on method.
     *
     * @param string $method Method to fetch.
     * @return null|string
     */
    public function getReference(string $method): ?string {
        if ($this->references->has($method)) {
            try {
                return $this->references->get($method);
            } catch (InvalidArgumentException $e) {
                die('This should never happen.');
            }
        }
        return null;
    }

    /**
     * Add an action reference.
     *
     * @param string $method    HTTP Method.
     * @param string $reference Reference as string to the action.
     * @return void
     */
    public function addReference(string $method, string $reference): void {
        $this->references[$method] = $reference;
    }

    /**
     * Get child with given path.
     * If child does not exist, null will be returned.
     *
     * @param string $part Part of the path to fetch child for.
     * @return Node|null
     */
    public function getChild(string $part): ?Node {
        if (!$this->children->has($part)) {
            $part = '%PARAM%';
        }

        return $this->children->has($part) ? $this->children[$part] : null;
    }

    /**
     * Get path part as a string.
     *
     * @return string
     */
    public function getPart(): string {
        return $this->part;
    }

    /**
     * Get parent node of the given node.
     * If the node is a root node, null will be returned.
     *
     * @return Node|null
     */
    public function getParent(): ?Node {
        return $this->parent;
    }

}
