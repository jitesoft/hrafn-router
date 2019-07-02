<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Action.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router;

use function explode;
use Hrafn\Router\Contracts\ActionInterface;
use Hrafn\Router\Contracts\ParameterExtractorInterface;
use Hrafn\Router\Middleware\AnonymousMiddleware;
use Hrafn\Router\RequestHandler\ReflectionCallbackHandler;
use Hrafn\Router\RequestHandler\ReflectionClassHandler;
use function is_callable;
use Jitesoft\Container\Container;
use Jitesoft\Exceptions\Psr\Container\ContainerException;
use Jitesoft\Utilities\DataStructures\Arrays;
use Jitesoft\Utilities\DataStructures\Maps\MapInterface;
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

    /** @var string|null */
    private $method = null;
    /** @var ReflectionClassHandler|null */
    private $handler = null;
    /** @var string|null */
    private $pattern = null;
    /** @var LinkedQueue|null */
    private $middlewares = null;

    /**
     * Action constructor.
     * @param string                          $method             Method of the given action.
     * @param string|callable                 $handler            Action callback handler.
     * @param string                          $pattern            Pattern the action uses.
     * @param array                           $middlewares        Middlewares to use.
     * @param ParameterExtractorInterface     $parameterExtractor Parameter extractor object.
     * @param ContainerInterface|MapInterface $container          Dependency container.
     *
     * @internal
     */
    public function __construct(string $method,
                                $handler,
                                string $pattern,
                                array $middlewares,
                                ParameterExtractorInterface $parameterExtractor,
                                $container = null) {
        $this->method  = $method;
        $this->pattern = $pattern;

        if (is_callable($handler)) {
            $this->handler = new ReflectionCallbackHandler(
                $handler,
                $parameterExtractor,
                $this
            );
        } else {
            $handlerSplit = explode(self::HANDLER_SEPARATOR, $handler);
            try {
                $this->handler = new ReflectionClassHandler(
                    $handlerSplit[0],
                    $handlerSplit[1],
                    $parameterExtractor,
                    $this,
                    $container ?? new Container([])
                );
            } catch (ContainerException $e) {
                die('This should never happen as the container is empty...');
            }
        }

        $middlewares = Arrays::map(
            $middlewares,
            function ($middleware) use($container) {
                if (is_callable($middleware)) {
                    return new AnonymousMiddleware($middleware);
                } if (is_string($middleware)
                    && $container
                    && $container->has($middleware)
                ) {
                    return $container[$middleware];
                }

                return $middleware;
            }
        );

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
