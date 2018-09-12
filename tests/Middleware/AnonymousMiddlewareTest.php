<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  AnonymousMiddlewareTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Tests\Middleware;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Hrafn\Router\Middleware\AnonymousMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * AnonymousMiddlewareTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class AnonymousMiddlewareTest extends TestCase {

    public function testAll() {

        $called     = false;
        $middleware = new AnonymousMiddleware(
            function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$called) {
                $called = true;
                return $handler->handle($request);
            }
        );

        $middleware->process(new ServerRequest('GET', 'https://example.com'), new RequestHandlerTestClass());
        $this->assertTrue($called);
    }

}

class RequestHandlerTestClass implements RequestHandlerInterface {

    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        return new Response();
    }
}

