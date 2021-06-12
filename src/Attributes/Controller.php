<?php
namespace Hrafn\Router\Attributes;

use Attribute;
use Jitesoft\Utilities\DataStructures\Arrays;
use ReflectionClass;
use ReflectionException;

/**
 * Class Controller
 *
 * Attribute used to auto resolve classes as controllers in the Hrafn router.
 *
 * @package Hrafn\Router\Attributes
 *
 * @property {string} $path Path to prepend to actions in the class.
 *
 * @see Action
 * @see Middleware
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Controller {

    /**
     * @var string Path to prepend to all actions in the class.
     */
    public string $path;

    /**
     * Controller constructor.
     *
     * @param string $path Optional path to prepend to all actions.
     */
    public function __construct(string $path = '/') {
        $this->path = $path;
    }

    /**
     * Check if a given class is using the controller attribute.
     *
     * @param string|object $class Class to check.
     * @return bool
     * @throws ReflectionException On reflection error.
     */
    public static function classIsController(string | object $class): bool {
        $refClass = new ReflectionClass($class);
        $attributes = $refClass->getAttributes(static::class);

        return !(count($attributes) === 0);
    }

    /**
     * Get the route/path of the Controller.
     *
     * @param string|object $class Class or Object which should be tested.
     * @return string
     * @throws ReflectionException
     */
    public static function getRoute(string | object $class): string {
        $refClass = new ReflectionClass($class);
        $attributes = $refClass->getAttributes(static::class);
        $att = Arrays::first($attributes);
        return $att->getArguments()['path'] ?? '/';
    }

    /**
     * Get the route/path of the Controller.
     *
     * @alias getRoute
     * @param string|object $class Class or Object which should be tested.
     * @return string
     * @throws ReflectionException
     */
    public static function getPath(string | object $class): string {
        return self::getRoute($class);
    }

}
