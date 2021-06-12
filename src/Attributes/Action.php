<?php

namespace Hrafn\Router\Attributes;

use Attribute;
use Hrafn\Router\Method;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use Jitesoft\Utilities\DataStructures\Arrays;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Class Action
 *
 * Attribute used on methods in classes that uses the Controller attribute.
 * In case a Action is defined, the Harfn router will automatically add it to the route tree.
 *
 * @package Hrafn\Router\Attributes
 *
 * @property {string} $path Optional path, which will be appended to the controller base path.
 * @property {string} $method Optional method, defaults to 'GET', which the action will be invoked on.
 *
 * @see Controller
 * @see Middleware
 */
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
class Action {
    public static string $separator = '@';

    /**
     * Path/pattern to use to resolve the action.
     *
     * @var string Action path.
     */
    public string $path;

    /**
     * Action request method.
     * @see Method
     * @var string Method the route uses.
     */
    public string $method;

    public function __construct(string $path = '', string $method = "GET") {
        $this->path = $path;
        $this->method = $method;

        if (!in_array(strtolower($this->method), Method::getConstantValues())) {
            throw new InvalidArgumentException('Invalid route method. See Hrafn\Router\Method for allowed values.');
        }
    }

    private static function isActionString(string $action): bool {
        $sp = explode(self::$separator, $action);
        $method = new ReflectionMethod($sp[0], $sp[1]);

        return count($method->getAttributes(static::class)) > 0;
    }

    public static function isAction(string | callable $action, string | object $class = null): bool {
        if (is_string($action) && !$class && str_contains($action, self::$separator)) {
            return self::isActionString($action);
        }

        if ($class) {
            $method = new ReflectionMethod($class, $action);
            return count($method->getAttributes(static::class)) > 0;
        }

        $refFunc = new ReflectionFunction($action);

        return count($refFunc->getAttributes(static::class)) > 0;
    }

    public static function getActions(string | object $controller): array {
        $refClass = new ReflectionClass($controller);
        $methods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $result = [];
        foreach ($methods as $method) {
            if (count($method->getAttributes(self::class)) > 0) {
                $attr = Arrays::first($method->getAttributes(self::class));
                $actionArgs = $attr->getArguments();

                if (isset($actionArgs['method']) && !in_array(strtolower($actionArgs['method']), Method::getConstantValues(), true)) {
                    throw new InvalidArgumentException(sprintf(
                        'Invalid route method (%s). See Hrafn\Router\Method for allowed values.',
                        $actionArgs['method']
                    ));
                }

                $result[$method->getName()] = [
                    'method' => $actionArgs['method'] ?? 'GET',
                    'path'   => $actionArgs['path'] ?? '/'
                ];
            }
        }
        return $result;
    }

}
