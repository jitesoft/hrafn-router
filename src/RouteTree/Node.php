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
    private $reference;

    /**
     * RouteNode constructor.
     * @param Node|null   $parent
     * @param string      $part
     * @param null|string $reference
     */
    public function __construct(?Node $parent, string $part, ?string $reference = null) {
        $this->part      = $part;
        $this->parent    = $parent;
        $this->children  = new SimpleMap();
        $this->reference = $reference;
    }

    /**
     * @param Node $node
     * @return bool
     * @throws InvalidArgumentException
     */
    public function addChild(Node $node): bool {
        if ($this->children->has($node->getPart())) {
            return $this->children[$node->getPart()]->addChild($node);
        }

        $node->setParent($this);
        return $this->children->add($node->getPart(), $node);
    }

    private function setParent(Node $parent) {
        $this->parent = $parent;
    }

    /**
     * @return null|string
     */
    public function getReference(): ?string {
        return $this->reference;
    }

    /**
     * Get child with given path.
     *
     * @param string $part
     * @return Node|null
     */
    public function getChild(string $part): ?Node {
        if ($this->children->has($part)) {
            return $this->children[$part];
        }
        return null;
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
     * @return Node|null
     */
    public function getParent(): ?Node {
        return $this->parent;
    }

}
