<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Action.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router;

use function explode;
use Hrafn\Router\Contracts\ActionInterface;
use Hrafn\Router\Contracts\ParameterExtractorInterface;
use Hrafn\Router\RequestHandler\ReflectionCallbackHandler;
use Hrafn\Router\RequestHandler\ReflectionClassHandler;
use function is_callable;
use Jitesoft\Container\Container;
use Jitesoft\Utilities\DataStructures\Queues\LinkedQueue;
use Jitesoft\Utilities\DataStructures\Queues\QueueInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Action
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class Action implements ActionInterface {
    private const HANDLER_SEPARATOR = '@';

    private $method      = null;
    private $handler     = null;
    private $pattern     = null;
    private $middlewares = null;

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * Action constructor.
     * @param string                      $method
     * @param string|callable             $handler
     * @param string                      $pattern
     * @param array                       $middlewares
     * @param ParameterExtractorInterface $parameterExtractor
     * @param ContainerInterface|null     $container
     *
     * @internal
     */
    public function __construct(string $method,
                                $handler,
                                string $pattern,
                                array $middlewares,
                                ParameterExtractorInterface $parameterExtractor,
                                ContainerInterface $container = null) {

        $this->method  = $method;
        $this->pattern = $pattern;

        if (is_callable($handler)) {
            $this->handler = new ReflectionCallbackHandler($handler, $parameterExtractor, $this);
        } else {
            $handlerSplit = explode(self::HANDLER_SEPARATOR, $handler);
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->handler = new ReflectionClassHandler(
                $handlerSplit[0],
                $handlerSplit[1],
                $parameterExtractor,
                $this,
                $container ?? new Container([])
            );
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
     * Get the request handler instance.
     *
     * @return RequestHandlerInterface
     */
    public function getHandler(): RequestHandlerInterface {
        return $this->handler;
    }

}
