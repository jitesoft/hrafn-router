<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Group.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router;

use Hrafn\Router\Contracts\ActionInterface;
use Hrafn\Router\Contracts\ActionNamespaceBuilderInterface;
use Hrafn\Router\Contracts\ActionNamespaceHandlerInterface;
use Hrafn\Router\Traits\MethodToActionTrait;
use Jitesoft\Utilities\DataStructures\Lists\IndexedListInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * A class which handles containment of actions and sub-namespaces.
 *
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ActionNamespace implements ActionNamespaceBuilderInterface, ActionNamespaceHandlerInterface, LoggerAwareInterface {
    use MethodToActionTrait;

    /**
     * Create a new namespace inside of current namespace.
     * The new GroupInterface instance is passed as the single argument to the $closure callback.
     *
     * @param string     $pattern
     * @param array|null $middleWares
     * @param callable   $closure
     * @return ActionNamespaceBuilderInterface
     */
    public function namespace(string $pattern, ?array $middleWares, callable $closure): ActionNamespaceBuilderInterface {
        // TODO: Implement namespace() method.
    }

    /**
     * Get a list of actions in the namespace handler.
     *
     * @param string|null $method Method to filter.
     * @return IndexedListInterface|array|ActionInterface[]
     */
    public function getActions(?string $method = null): IndexedListInterface {
        // TODO: Implement getActions() method.
    }

    /**
     * Get a list of namespace handlers inside the namespace handler.
     *
     * @return IndexedListInterface|array|ActionNamespaceHandlerInterface[]
     */
    public function getNamespaces(): IndexedListInterface {
        // TODO: Implement getNamespaces() method.
    }

    /**
     * Get a list of middlewares used for the actions in the namespace and all its sub namespaces.
     *
     * @return IndexedListInterface|array|MiddlewareInterface[]
     */
    public function getMiddlewares(): IndexedListInterface {
        // TODO: Implement getMiddlewares() method.
    }

    /**
     * Get the pattern used to access the given namespace.
     *
     * @return string
     */
    public function getPattern(): string {
        // TODO: Implement getPattern() method.
    }

    /**
     * Get the pattern as a regular expression.
     *
     * @return string
     */
    public function getPatternRegex(): string {
        // TODO: Implement getPatternRegex() method.
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger) {
        // TODO: Implement setLogger() method.
    }

    /**
     * Method which all the http-method specific methods forward data to.
     *
     * @param string $method
     * @param string $pattern
     * @param        $handler
     * @param array  $middleWares
     * @return ActionNamespaceBuilderInterface
     */
    protected function action(string $method,
                              string $pattern,
                              $handler,
                              $middleWares = []): ActionNamespaceBuilderInterface {
        // TODO: Implement action() method.
    }
}
