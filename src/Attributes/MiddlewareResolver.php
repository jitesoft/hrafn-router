<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Action.php - Part of the router project.

  © - Jitesoft 2018-2021
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Attributes;

use Hrafn\Router\Action as RouterAction;
use Hrafn\Router\Contracts\MiddlewareResolverInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

class MiddlewareResolver implements MiddlewareResolverInterface {

    /**
     * Get a list of middlewares attached to a controller class.
     *
     * @param string|object $controller Controller to get middlewares from.
     * @return array
     * @throws ReflectionException On reflection error.
     */
    public function getControllerMiddlewares(string | object $controller): array {
        $refClass   = new ReflectionClass($controller);
        $attributes = $refClass->getAttributes(Middleware::class);
        $result     = [];

        foreach ($attributes as $attribute) {
            /** @var Middleware $instance */
            $instance = $attribute->newInstance();
            $result[] = $instance->fqn;
        }

        return $result;
    }

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
     * @throws ReflectionException On reflection error.
     */
    public function getActionMiddlewares(string | callable $method,
                                         object | string $controller = null): array {
        if (!$controller && is_string($method) && str_contains($method, '@')) {
            [$controller, $method] = explode(RouterAction::$HANDLER_SEPARATOR, $method);
        }

        $attributes = [];
        if ($controller) {
            $refMethod  = new ReflectionMethod($controller, $method);
            $attributes = $refMethod->getAttributes(Middleware::class);
        } else {
            $refFunc    = new ReflectionFunction($method);
            $attributes = $refFunc->getAttributes(Middleware::class);
        }

        $result = [];
        foreach ($attributes as $attribute) {
            /** @var Middleware $instance */
            $instance = $attribute->newInstance();
            $result[] = $instance->fqn;
        }

        return $result;
    }

}
