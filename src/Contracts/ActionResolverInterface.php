<?php
namespace Hrafn\Router\Contracts;

use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use ReflectionException;

interface ActionResolverInterface {
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
     * @param string|object $controller Controller to fetch actions from.
     * @return array
     * @throws InvalidArgumentException On Invalid action method.
     * @throws ReflectionException On reflection error.
     */
    public function getControllerActions(string|object $controller): array;

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
     * @return array
     * @throws InvalidArgumentException On Invalid action method.
     */
    public function getFunctionActions(): array;
}
