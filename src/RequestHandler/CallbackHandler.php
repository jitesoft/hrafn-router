<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  CallbackHandler.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\RequestHandler;

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

    public function __construct(callable $callback) {
        $this->callback = $callback;
    }

    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        // TODO: Implement handle() method.
    }

}
