<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ActionNamespaceHandlerInterface.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Contracts;

use Jitesoft\Utilities\DataStructures\Lists\IndexedListInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * ActionNamespaceHandlerInterface
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
interface ActionNamespaceHandlerInterface {

    /**
     * Get a list of actions in the namespace handler.
     *
     * @param string|null $method Method to filter.
     * @return IndexedListInterface|array|ActionInterface[]
     */
    public function getActions(?string $method = null): IndexedListInterface;

    /**
     * Get a list of namespace handlers inside the namespace handler.
     *
     * @return IndexedListInterface|array|ActionNamespaceHandlerInterface[]
     */
    public function getNamespaces(): IndexedListInterface;

    /**
     * Get a list of middlewares used for the actions in the namespace and all its sub namespaces.
     *
     * @return IndexedListInterface|array|MiddlewareInterface[]
     */
    public function getMiddlewares(): IndexedListInterface;

    /**
     * Get the pattern used to access the given namespace.
     *
     * @return string
     */
    public function getPattern(): string;

    /**
     * Get the pattern as a regular expression.
     *
     * @return string
     */
    public function getPatternRegex(): string;

}
