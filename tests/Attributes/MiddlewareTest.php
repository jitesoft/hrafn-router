<?php

namespace Hrafn\Router\Tests\Attributes;

use Hrafn\Router\Attributes\Action;
use Hrafn\Router\Attributes\Controller;
use Hrafn\Router\Attributes\Middleware;
use Hrafn\Router\Attributes\MiddlewareResolver;
use Hrafn\Router\Contracts\MiddlewareResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareTest extends TestCase {

    private MiddlewareResolverInterface $middlewareResolver;


    public function setUp(): void {
        $this->middlewareResolver = new MiddlewareResolver();
    }

    public function testGetControllerMiddleware(): void {
        self::assertEquals($this->middlewareResolver->getControllerMiddlewares(ControllerWithMiddleware::class), [
            TestMiddleware::class
        ]);

        self::assertEquals($this->middlewareResolver->getControllerMiddlewares(new ControllerWithManyMiddleware()), [
            TestMiddleware::class,
            TestMiddlewareTwo::class,
            TestMiddlewareThree::class
        ]);

        self::assertEmpty($this->middlewareResolver->getControllerMiddlewares(ControllerWithoutMiddleware::class));
    }

    public function testGetActionMiddleware(): void {
        self::assertEmpty($this->middlewareResolver->getActionMiddlewares('getSomething', ControllerWithMiddleware::class));
        self::assertEquals([
            TestMiddlewareTwo::class,
            TestMiddlewareThree::class
        ], $this->middlewareResolver->getActionMiddlewares('postSomething', new ControllerWithMiddleware()));
        self::assertEquals([
            TestMiddlewareTwo::class,
            TestMiddlewareThree::class
        ], $this->middlewareResolver->getActionMiddlewares(ControllerWithMiddleware::class . '@postSomething'));

        $func = #[Action]
                #[Middleware(fqn: TestMiddlewareThree::class)]
                #[Middleware(fqn: TestMiddlewareTwo::class)]
                static fn() => null;
        self::assertEquals([
            TestMiddlewareThree::class,
            TestMiddlewareTwo::class
        ], $this->middlewareResolver->getActionMiddlewares($func));
    }

}

#[Controller]
#[Middleware(fqn: TestMiddleware::class)]
class ControllerWithMiddleware {
    #[Middleware(fqn: TestMiddlewareTwo::class)]
    #[Middleware(fqn: TestMiddlewareThree::class)]
    #[Action(method: 'POST')]
    public function postSomething() {}

    #[Action]
    public function getSomething() {}
}


#[Controller]
#[Middleware(fqn: TestMiddleware::class)]
#[Middleware(fqn: TestMiddlewareTwo::class)]
#[Middleware(fqn: TestMiddlewareThree::class)]
class ControllerWithManyMiddleware {}

#[Controller]
class ControllerWithoutMiddleware {}

class TestMiddleware implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {}
}
class TestMiddlewareTwo implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {}
}
class TestMiddlewareThree implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {}
}
