<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Action.php - Part of the router project.

  © - Jitesoft 2018-2021
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Attributes;

use Hrafn\Router\Contracts\ControllerResolverInterface;
use Jitesoft\Utilities\DataStructures\Arrays;
use ReflectionClass;
use ReflectionException;

class ControllerResolver implements ControllerResolverInterface {

    /**
     * Fetch all controllers declared in the code.
     *
     * @return array Controller FQN's as an array.
     * @throws ReflectionException On reflection error.
     */
    public function getAllControllers(): array {
        $classes     = get_declared_classes();
        $controllers = [];

        foreach ($classes as $class) {
            $refClass   = new ReflectionClass($class);
            $attributes = $refClass->getAttributes(Controller::class);

            if (empty($attributes)) {
                continue;
            }

            $controllers[] = $class;
        }

        return $controllers;
    }

    /**
     * Get the path of the Controller.
     * The controller path will be prepended to the actions in the controller.
     *
     * @param string|object $class Class or Object which the path should be fetched from.
     * @return string
     * @throws ReflectionException On reflection error.
     */
    public function getPath(string | object $class): string {
        $refClass   = new ReflectionClass($class);
        $attributes = $refClass->getAttributes(Controller::class);
        $att        = Arrays::first($attributes);

        if ($att === null) {
            return '/';
        }

        /** @var Controller $attribute */
        $attribute = $att->newInstance();
        return $attribute->path;
    }

}
