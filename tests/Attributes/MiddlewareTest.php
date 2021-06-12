<?php

namespace Hrafn\Router\Tests\Attributes;

use Hrafn\Router\Attributes\Controller;
use Hrafn\Router\Attributes\Middleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareTest extends TestCase {

    public function testHasMiddleware(): void {
        self::assertTrue(Middleware::hasMiddleware(ControllerWithMiddleware::class));
        self::assertTrue(Middleware::hasMiddleware(new ControllerWithMiddleware()));

        self::assertFalse(Middleware::hasMiddleware(ControllerWithoutMiddleware::class));
        self::assertFalse(Middleware::hasMiddleware(new ControllerWithoutMiddleware()));
    }

    public function testGetMiddleware(): void {
        self::assertEquals(TestMiddleware::class, Middleware::getMiddlewareName(ControllerWithMiddleware::class));
        self::assertEquals(TestMiddleware::class, Middleware::getMiddlewareName(new ControllerWithMiddleware()));
    }

}

#[Controller]
#[Middleware(fqn: TestMiddleware::class)]
class ControllerWithMiddleware {}

#[Controller]
class ControllerWithoutMiddleware {}

class TestMiddleware implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {}
}
