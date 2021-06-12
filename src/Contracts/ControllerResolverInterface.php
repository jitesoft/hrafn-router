<?php
namespace Hrafn\Router\Contracts;

use ReflectionException;

interface ControllerResolverInterface {
    /**
     * Fetch all controllers declared in the code.
     *
     * @return array Controller FQN's as an array.
     * @throws ReflectionException
     */
    public function getAllControllers(): array;

    /**
     * Get the path of the Controller.
     * The controller path will be prepended to the actions in the controller.
     *
     * @param string|object $class Class or Object which the path should be fetched from.
     * @return string
     * @throws ReflectionException On reflection error.
     */
    public function getPath(string|object $class): string;
}
