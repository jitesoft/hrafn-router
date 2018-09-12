<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  CallbackHandler.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\RequestHandler;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CallbackHandler
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class CallbackHandler implements RequestHandlerInterface {

    private $callback;
    private $container;

    public function __construct(callable $callback, ContainerInterface $container) {
        $this->callback  = $callback;
        $this->container = $container;
    }

    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        // TODO: Implement handle() method.
    }
}
