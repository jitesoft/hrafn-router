<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  GroupInterface.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Contracts;

/**
 * Interface for classes which creates and contains namespace for a set of actions.
 *
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
interface ActionNamespaceBuilderInterface {

    /**
     * Create a get action.
     *
     * @param string          $pattern
     * @param string|callable $handler
     * @param array           $middleWares
     * @return ActionNamespaceBuilderInterface
     */
    public function get(string $pattern, $handler, $middleWares = []): self;

    /**
     * Create a head action.
     *
     * @param string          $pattern
     * @param string|callable $handler
     * @param array           $middleWares
     * @return ActionNamespaceBuilderInterface
     */
    public function head(string $pattern, $handler, $middleWares = []): self;

    /**
     * Create a post action.
     *
     * @param string          $pattern
     * @param string|callable $handler
     * @param array           $middleWares
     * @return ActionNamespaceBuilderInterface
     */
    public function post(string $pattern, $handler, $middleWares = []): self;

    /**
     * Create a put action.
     *
     * @param string          $pattern
     * @param string|callable $handler
     * @param array           $middleWares
     * @return ActionNamespaceBuilderInterface
     */
    public function put(string $pattern, $handler, $middleWares = []): self;

    /**
     * Create a delete action.
     *
     * @param string          $pattern
     * @param string|callable $handler
     * @param array           $middleWares
     * @return ActionNamespaceBuilderInterface
     */
    public function delete(string $pattern, $handler, $middleWares = []): self;

    /**
     * Create a connect action.
     *
     * @param string          $pattern
     * @param string|callable $handler
     * @param array           $middleWares
     * @return ActionNamespaceBuilderInterface
     */
    public function connect(string $pattern, $handler, $middleWares = []): self;

    /**
     * Create a options action.
     *
     * @param string          $pattern
     * @param string|callable $handler
     * @param array           $middleWares
     * @return ActionNamespaceBuilderInterface
     */
    public function options(string $pattern, $handler, $middleWares = []): self;

    /**
     * Create a trace action.
     *
     * @param string          $pattern
     * @param string|callable $handler
     * @param array           $middleWares
     * @return ActionNamespaceBuilderInterface
     */
    public function trace(string $pattern, $handler, $middleWares = []): self;

    /**
     * Create a patch action.
     *
     * @param string          $pattern
     * @param string|callable $handler
     * @param array           $middleWares
     * @return ActionNamespaceBuilderInterface
     */
    public function patch(string $pattern, $handler, $middleWares = []): self;

    /**
     * Create a new namespace inside of current namespace.
     * The new GroupInterface instance is passed as the single argument to the $closure callback.
     *
     * @param string     $pattern
     * @param array|null $middleWares
     * @param callable   $closure
     * @return ActionNamespaceBuilderInterface
     */
    public function namespace(string $pattern, ?array $middleWares, callable $closure): self;

}
