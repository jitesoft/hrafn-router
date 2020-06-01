<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  AnonymousMiddleware.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Middleware;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * AnonymousMiddleware
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class AnonymousMiddleware implements MiddlewareInterface {
    private Closure $handler;

    /**
     * AnonymousMiddleware constructor.
     *
     * @param callable $handler Handler to invoke.
     */
    public function __construct(callable $handler) {
        $this->handler = $handler;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface  $request Request to handle.
     * @param RequestHandlerInterface $handler Handler to pass to the callback.
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request,
                            RequestHandlerInterface $handler
    ): ResponseInterface {
        $method = $this->handler;
        return $method($request, $handler);
    }

}
