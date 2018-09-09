<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteNode.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router;

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
class RouteNode {

    /** @var RouteNode */
    private $parent;

    /** @var MapInterface */
    private $children;

    /** @var string */
    private $part;

    /** @var string|null */
    private $reference;

    /**
     * RouteNode constructor.
     * @param RouteNode|null $parent
     * @param string         $part
     * @param null|string    $reference
     */
    public function __construct(?RouteNode $parent, string $part, ?string $reference = null) {
        $this->part      = $part;
        $this->parent    = $parent;
        $this->children  = new SimpleMap();
        $this->reference = $reference;
    }

    /**
     * @param RouteNode $node
     * @return bool
     * @throws InvalidArgumentException
     */
    public function addChild(RouteNode $node): bool {
        if ($this->children->has($node->getPart())) {
            return $this->children[$node->getPart()]->addChild($node);
        }

        return $this->children->add($node->getPart(), $node);
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
     * @return RouteNode|null
     */
    public function getChild(string $part): ?RouteNode {
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
     * @return RouteNode|null
     */
    public function getParent(): ?RouteNode {
        return $this->parent;
    }

}
