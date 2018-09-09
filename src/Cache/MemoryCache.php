<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  MemoryCache.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Cache;

use Hrafn\Router\Contracts\RouteTreeCacheInterface;
use Hrafn\Router\RouteBuilder\RouteNode;

/**
 * MemoryCache
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 * @state Stable
 * @codeCoverageIgnore
 */
class MemoryCache implements RouteTreeCacheInterface {
    /** @var RouteNode */
    private $cache = null;

    /**
     * Check if the cache have a RouteNode tree structure in the cache.
     *
     * @return bool
     */
    public function exists(): bool {
        return $this->cache !== null;
    }

    /**
     * Save a RouteNode tree to cache.
     *
     * @param RouteNode $root
     * @return bool
     */
    public function store(RouteNode $root): bool {
        $this->cache = $root;
        return true;
    }

    /**
     * Load a full RouteNode tree structure from cache.
     *
     * @return RouteNode
     */
    public function load(): RouteNode {
        return $this->cache;
    }

}
