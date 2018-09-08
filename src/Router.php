<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  Router.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router;

use Hrafn\Router\Contracts\ActionNamespaceBuilderInterface;
use Hrafn\Router\Traits\MethodToActionTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Router
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class Router implements LoggerAwareInterface, RequestHandlerInterface, ActionNamespaceBuilderInterface {
    use MethodToActionTrait;

    private const LOG_TAG = 'Hrafn\Router:';

    private $logger;
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->logger    = $this->container->has(LoggerInterface::class)
            ? $container->get(LoggerInterface::class)
            : new NullLogger();
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        // TODO: Implement handle() method.
    }

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
