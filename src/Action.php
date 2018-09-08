<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Action.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router;

use Hrafn\Router\Contracts\ActionInterface;
use Jitesoft\Utilities\DataStructures\Lists\IndexedListInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Action
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class Action implements LoggerAwareInterface, ActionInterface {

    /**
     * Get the request method, the method string will correspond to the Hrafn\Router\Method constants.
     *
     * @see Method
     * @return string
     */
    public function getMethod(): string {
        // TODO: Implement getMethod() method.
    }

    /**
     * Get the class that will handle the message after all middlewares have been invoked.
     *
     * @return string
     */
    public function getHandlerClass(): string {
        // TODO: Implement getHandlerClass() method.
    }

    /**
     * Get the name of the function (case sensitive) of the class that will handle the the message after
     * all middlewares have been invoked.
     *
     * Observe.
     * The method have to be publicly reachable from the router, or it will not be possible to invoke.
     *
     * @return string
     */
    public function getHandlerFunction(): string {
        // TODO: Implement getHandlerFunction() method.
    }

    /**
     * Get the type of action handler. The types can be either 'callback' or 'instance_method' and should be
     * set from within the action depending on the values passed.
     *
     * @return string
     */
    public function getActionType(): string {
        // TODO: Implement getActionType() method.
    }

    /**
     * Get the path used by the action, the path should not contain any group path nor query parameters and such.
     * E.G., /path/to/action/{with}/params
     *
     * @return string
     */
    public function getActionPath(): string {
        // TODO: Implement getActionPath() method.
    }

    /**
     * Get the regular expression used by the router to parse the path for matching.
     * E.G., ^/path/to/action/(+d)/params$
     *
     * @return string
     */
    public function getActionPathRegex(): string {
        // TODO: Implement getActionPathRegex() method.
    }

    /**
     * Get the callback used as action handler. This will be null if a class is used as action handler instead of a
     * callback. The callable will receive the same data as a instance method handler would.
     *
     * @return callable|null
     */
    public function getCallback(): ?callable {
        // TODO: Implement getCallback() method.
    }

    /**
     * Get a indexed list of middlewares which the router should invoke before calling the action handler.
     * This list does not include the global or group middlewares, only the ones registered to the specific action.
     *
     * @return IndexedListInterface|MiddlewareInterface[]|array
     */
    public function getRouteMiddlewares(): IndexedListInterface {
        // TODO: Implement getRouteMiddlewares() method.
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
}
