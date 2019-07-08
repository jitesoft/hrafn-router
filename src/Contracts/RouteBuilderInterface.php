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
                        $handler,
                        array $middleWares = []): self;

    /**
     * Create a head action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function head(string $pattern,
                         $handler,
                         array $middleWares = []): self;

    /**
     * Create a post action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function post(string $pattern,
                         $handler,
                         array $middleWares = []): self;

    /**
     * Create a put action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function put(string $pattern,
                        $handler,
                        array $middleWares = []): self;

    /**
     * Create a delete action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function delete(string $pattern,
                           $handler,
                           array $middleWares = []): self;

    /**
     * Create a connect action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function connect(string $pattern,
                            $handler,
                            array $middleWares = []): self;

    /**
     * Create a options action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function options(string $pattern,
                            $handler,
                            array $middleWares = []): self;

    /**
     * Create a trace action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function trace(string $pattern,
                          $handler,
                          array $middleWares = []): self;

    /**
     * Create a patch action.
     *
     * @param string          $pattern     Pattern for the specific action.
     * @param string|callable $handler     Handler to handle the action.
     * @param array           $middleWares Middlewares to use for the action.
     * @return RouteBuilderInterface
     */
    public function patch(string $pattern,
                          $handler,
                          array $middleWares = []): self;

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
                              array $middleWares = []): self;

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
                          array $middleWares = []): self;

}
