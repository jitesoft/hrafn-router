<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Action.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router;

use function explode;
use Hrafn\Router\Contracts\ActionInterface;
use Hrafn\Router\RequestHandler\CallbackHandler;
use Hrafn\Router\RequestHandler\ClassHandler;
use function is_callable;
use Jitesoft\Utilities\DataStructures\Queues\LinkedQueue;
use Jitesoft\Utilities\DataStructures\Queues\QueueInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Action
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class Action implements ActionInterface {
    private const HANDLER_SEPARATOR = '@';

    private $logger      = null;
    private $method      = null;
    private $handler     = null;
    private $pattern     = null;
    private $middlewares = null;
    private $container   = null;

    /**
     * Action constructor.
     * @param string             $method
     * @param string|callable    $handler
     * @param string             $pattern
     * @param array              $middlewares
     * @param ContainerInterface $container
     * @internal
     */
    public function __construct(string $method,
                                $handler,
                                string $pattern,
                                array $middlewares,
                                ContainerInterface $container) {

        $this->logger = $container->has(LoggerInterface::class)
            ? $container->get(LoggerInterface::class)
            : new NullLogger();

        $this->container = $container;
        $this->method    = $method;
        $this->pattern   = $pattern;

        if (is_callable($handler)) {
            $this->handler = new CallbackHandler($handler, $container);
        } else {
            $handlerSplit  = explode(self::HANDLER_SEPARATOR, $handler);
            $this->handler = new ClassHandler($handlerSplit[0], $handlerSplit[1], $container);
        }

        $this->middlewares = new LinkedQueue();

        if (count($middlewares) > 0) {
            $this->middlewares->enqueue(...$middlewares);
        }
    }

    /**
     * Get list of middlewares as a queue.
     *
     * @return QueueInterface
     */
    public function getMiddlewares(): QueueInterface {
        return $this->middlewares;
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
     * Get the pattern used by the action, the pattern should not contain any group path nor query parameters and such.
     * E.G., /path/to/action/{with}/params
     *
     * @return string
     */
    public function getPattern(): string {
        return $this->pattern;
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

    /**
     * Get the request handler instance.
     *
     * @return RequestHandlerInterface
     */
    public function getHandler(): RequestHandlerInterface {
        return $this->handler;
    }

}
