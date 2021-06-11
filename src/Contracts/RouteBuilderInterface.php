<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteBuilderInterface.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Contracts;

/**
 * Interface for classes which creates and contains routes for a set of actions.
 *
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
interface RouteBuilderInterface {

    /**
     * Create a get action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function get(string $pattern,
                        string | callable $handler,
                        array $middleWares = []): static;

    /**
     * Create a head action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function head(string $pattern,
                         string | callable $handler,
                         array $middleWares = []): static;

    /**
     * Create a post action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function post(string $pattern,
                         string | callable $handler,
                         array $middleWares = []): static;

    /**
     * Create a put action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function put(string $pattern,
                        string | callable $handler,
                        array $middleWares = []): static;

    /**
     * Create a delete action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function delete(string $pattern,
                           string | callable $handler,
                           array $middleWares = []): static;

    /**
     * Create a connect action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function connect(string $pattern,
                            string | callable $handler,
                            array $middleWares = []): static;

    /**
     * Create a options action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function options(string $pattern,
                            string | callable $handler,
                            array $middleWares = []): static;

    /**
     * Create a trace action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function trace(string $pattern,
                          string | callable $handler,
                          array $middleWares = []): static;

    /**
     * Create a patch action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function patch(string $pattern,
                          string | callable $handler,
                          array $middleWares = []): static;

    /**
     * Create a new namespace inside of current namespace.
     * A RouteBuilderInterface instance is passed as the single argument to the $closure callback.
     *
     * @param string   $pattern     Pattern for the namespace/group.
     * @param callable $closure     Closure which will be passed the route builder.
     * @param array    $middleWares Create a new route namespace/group.
     * @return RouteBuilderInterface
     */
    public function namespace(string $pattern,
                              callable $closure,
                              array $middleWares = []): static;

    /**
     * Create a new group inside of current group.
     * A RouteBuilderInterface instance is passed as the single argument to the $closure callback.
     * @alias namespace
     *
     * @param string   $pattern     Pattern for the namespace/group.
     * @param callable $closure     Closure which will be passed the route builder.
     * @param array    $middleWares Create a new route namespace/group.
     * @return RouteBuilderInterface
     */
    public function group(string $pattern,
                          callable $closure,
                          array $middleWares = []): static;

}
