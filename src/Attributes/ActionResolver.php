<?php
namespace Hrafn\Router\Attributes;

use Hrafn\Router\Contracts\ActionResolverInterface;
use Hrafn\Router\Contracts\ControllerResolverInterface;
use Hrafn\Router\Contracts\MiddlewareResolverInterface;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use Jitesoft\Utilities\DataStructures\Arrays;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use \Hrafn\Router\Action as RouterAction;

class ActionResolver implements ActionResolverInterface {
    private ControllerResolverInterface $controllerResolver;
    private MiddlewareResolverInterface $middlewareResolver;


    public function __construct(MiddlewareResolverInterface $middlewareResolver,
                                ControllerResolverInterface $controllerResolver) {
        $this->middlewareResolver = $middlewareResolver;
        $this->controllerResolver = $controllerResolver;
    }

    /**
     * Fetch an array with all defined actions from a specific controller.
     * Resulting array will contain assoc arrays like the following:
     * <pre>
     * [
     *   'method'      => '(Http method)',
     *   'path'        => '/path/to/resolve',
     *   'handler'     => 'method fqn',
     *   'middlewares' => ['array', 'of', 'middleware fqn']
     * ]
     * </pre>
     *
     *
     * @param string|object $controller Controller to fetch actions from.
     * @throws ReflectionException On reflection error.
     * @throws InvalidArgumentException On Invalid action method.
     * @return array
     */
    public function getControllerActions(string | object $controller): array {
        $refClass        = new ReflectionClass($controller);
        $methods         = $refClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $result          = [];
        $baseMiddlewares = $this->middlewareResolver->getControllerMiddlewares($controller);
        $basePath        = $this->controllerResolver->getPath($controller);

        foreach ($methods as $method) {
            if (count($method->getAttributes(Action::class)) > 0) {
                $r = $this->getAction($method->getName(), $controller);
                if ($r !== null) {
                    $r['middlewares'] = array_merge($baseMiddlewares, $r['middlewares']);
                    $r['path'] = $basePath . $r['path'];

                    $result[] = $r;
                }
            }
        }
        return $result;
    }

    /**
     * Fetch an array with all defined function actions.
     * Resulting array will contain assoc arrays like the following:
     * <pre>
     * [
     *   'method'      => '(Http method)',
     *   'path'        => '/path/to/resolve',
     *   'handler'     => 'method fqn',
     *   'middlewares' => ['array', 'of', 'middleware fqn']
     * ]
     * </pre>
     *
     * @throws InvalidArgumentException On Invalid action method.
     * @return array
     */
    public function getFunctionActions(): array {
        $funcs   = get_defined_functions();
        $actions = [];

        foreach ($funcs['user'] as $func) {
            $a = $this->getAction($func);
            if ($a !== null) {
                $actions[] = $a;
            }
        }

        return $actions;
    }

    private function getAction(string | object $action, string | object $class = null): ?array {
        if (!$class && is_string($action) && str_contains($action, RouterAction::$HANDLER_SEPARATOR)) {
            [$class, $action] = explode(RouterAction::$HANDLER_SEPARATOR, $action);
        }

        if (!$class && function_exists($action)) {
            $function = new ReflectionFunction($action);
            $attr     = Arrays::first($function->getAttributes(Action::class));

            if ($attr === null) {
                return null;
            }

            $actAttribute = $attr->newInstance();

            /** @var Action $actAttribute */
            return [
                'method'      => $actAttribute->method ?? 'GET',
                'path'        => $actAttribute->path ?? '/',
                'handler'     => $function->getName(),
                'middlewares' => $this->middlewareResolver->getActionMiddlewares($function->getName())
            ];
        }

        if ($class) {
            $refClass = new ReflectionClass($class);
            $method   = $refClass->getMethod($action);
            $attr     = Arrays::first($method->getAttributes(Action::class));

            if ($attr === null) {
                return null;
            }

            $actAttribute = $attr->newInstance();

            /** @var Action $actAttribute */
            if (count($method->getAttributes(Action::class)) > 0) {
                return [
                    'method'      => $actAttribute->method ?? 'GET',
                    'path'        => $actAttribute->path ?? '/',
                    'handler'     => $refClass->getName() . RouterAction::$HANDLER_SEPARATOR . $method->getName(),
                    'middlewares' => $this->middlewareResolver->getActionMiddlewares($method->getName(), $class)
                ];
            }
        }

        return null;
    }

}
