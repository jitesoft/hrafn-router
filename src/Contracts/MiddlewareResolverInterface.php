<?php
namespace Hrafn\Router\Contracts;

use ReflectionException;

interface MiddlewareResolverInterface {

    /**
     * Get a list of middlewares attached to a controller class.
     *
     * @param string|object $controller Controller to get middlewares from.
     * @return array
     */
    public function getControllerMiddlewares(string | object $controller): array;

    /**
     * Get a list of middlewares attached to an action.
     *
     * Valid $method and $controller values follows the following set of rules:
     *
     * * FQN of the controller as $controller and method name as $method.
     * * Method name as $method and the controller as an object.
     * * Concatenated string with FQN(sep)methodName as $method and no $controller.
     * * Callable of any type as $method and no controller.
     *
     * @param string|callable    $method     Method/Function to get middlewares from.
     * @param object|string|null $controller Optional controller, in case the action is bound to a class.
     * @return array
     */
    public function getActionMiddlewares(string | callable $method, object | string $controller = null): array;

}
