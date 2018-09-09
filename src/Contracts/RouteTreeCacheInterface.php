<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteTreeCacheInterface.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Contracts;

use Hrafn\Router\RouteBuilder\RouteNode;

/**
 * RouteTreeCacheInterface
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 * @state Stable
 */
interface RouteTreeCacheInterface {

    /**
     * Check if the cache have a RouteNode tree structure in the cache.
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Save a RouteNode tree to cache.
     *
     * @param RouteNode $root
     * @return bool
     */
    public function store(RouteNode $root): bool;


    /**
     * Load a full RouteNode tree structure from cache.
     *
     * @return RouteNode
     */
    public function load(): RouteNode;

}
