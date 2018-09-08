<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Action.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router;

use Hrafn\Router\Contracts\ActionInterface;
use Jitesoft\Utilities\DataStructures\Lists\IndexedListInterface;
use Jitesoft\Utilities\DataStructures\Lists\LinkedList;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Action
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class Action implements LoggerAwareInterface, ActionInterface {
    private const LOG_TAG                      = 'Hrafn\Router\Action:';
    private const HANDLER_SEPARATOR            = '@';
    private const PLACEHOLDER_PATTERN          = '\{(\w+?)\}';
    private const OPTIONAL_PLACEHOLDER_PATTERN = '\{\?(\w+)\}';
    private const REGEX_DELIMITER              = '~';

    private $logger          = null;
    private $method          = null;
    private $handlerClass    = null;
    private $handlerMethod   = null;
    private $handlerCallback = null;
    private $handlerType     = null;
    private $path            = null;
    private $pathRegex       = null;
    private $middlewares     = null;

    public function __construct(string $method, $handler, string $path, array $middlewares = [], LoggerInterface $logger = null) {
        $this->logger      = $logger ?? new NullLogger();
        $this->method      = $method;
        $this->handlerType = is_callable($handler) ? self::ACTION_TYPE_CALLBACK : self::ACTION_TYPE_INSTANCE_METHOD;
        $this->path        = mb_strrpos($path, '/') === mb_strlen($path) ? rtrim($path,'/') : $path;

        if ($this->handlerType === self::ACTION_TYPE_INSTANCE_METHOD) {
            $split               = explode(self::HANDLER_SEPARATOR, $handler);
            $this->handlerClass  = $split[0];
            $this->handlerMethod = $split[1];
        } else {
            $this->handlerCallback = $handler;
        }

        $this->middlewares = new LinkedList($middlewares);
    }


    /**
     * Get the request method, the method string will correspond to the Hrafn\Router\Method constants.
     *
     * @see Method
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }

    /**
     * Get the class that will handle the message after all middlewares have been invoked.
     *
     * @return string|null
     */
    public function getHandlerClass(): ?string {
        return $this->handlerClass;
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
    public function getHandlerFunction(): ?string {
        return $this->handlerMethod;
    }

    /**
     * Get the type of action handler. The types can be either 'callback' or 'instance_method' and should be
     * set from within the action depending on the values passed.
     *
     * @return string
     */
    public function getActionType(): string {
        return $this->handlerType;
    }

    /**
     * Get the path used by the action, the path should not contain any group path nor query parameters and such.
     * E.G., /path/to/action/{with}/params
     *
     * @return string
     */
    public function getActionPath(): string {
        return $this->path;
    }

    /**
     * Get the regular expression used by the router to parse the path for matching.
     * E.G., ^/path/to/action/(+d)/params$
     *
     * @return string
     */
    public function getActionPathRegex(): string {
        if ($this->pathRegex !== null) {
            return $this->pathRegex;
        }

        $replace   = [];
        $replace[] = sprintf('%s%s%s', self::REGEX_DELIMITER, self::PLACEHOLDER_PATTERN, self::REGEX_DELIMITER);
        $replace[] = sprintf('%s%s%s', self::REGEX_DELIMITER, self::OPTIONAL_PLACEHOLDER_PATTERN, self::REGEX_DELIMITER);
        $replace[] = sprintf('%s%s%s', self::REGEX_DELIMITER, '([/])', self::REGEX_DELIMITER);

        $with = [
            // named group.
            "(?'$1'\w+)",
            // None-capturing group with a named group inside which have optional slash at end and is optional!
            "(?:(?'$1'\w+))?",
            // Escaped slash.
            '\/'
        ];

        $regex           = preg_replace($replace, $with, $this->path);
        $regex           = preg_replace('~\\\/\(\?\:~', '[\/]?(?:', $regex);
        $this->pathRegex = sprintf('%s^%s[\/]?$%s', self::REGEX_DELIMITER, "{$regex}", self::REGEX_DELIMITER);

        return $this->pathRegex;
    }

    /**
     * Get the callback used as action handler. This will be null if a class is used as action handler instead of a
     * callback. The callable will receive the same data as a instance method handler would.
     *
     * @return callable|null
     */
    public function getCallback(): ?callable {
        return $this->handlerCallback;
    }

    /**
     * Get a indexed list of middlewares which the router should invoke before calling the action handler.
     * This list does not include the global or group middlewares, only the ones registered to the specific action.
     *
     * @return IndexedListInterface|MiddlewareInterface[]|array
     */
    public function getRouteMiddlewares(): IndexedListInterface {
        // TODO: Implement getRouteMiddlewares() method.
        // TODO: Needs tests, but requires middleware first.
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
}
