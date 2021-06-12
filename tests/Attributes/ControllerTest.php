<?php

namespace Hrafn\Router\Tests\Attributes;

use Hrafn\Router\Attributes\Controller;
use Hrafn\Router\Attributes\ControllerResolver;
use Hrafn\Router\Contracts\ControllerResolverInterface;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase {

    private ControllerResolverInterface $controllerResolver;

    public function setUp(): void {
        $this->controllerResolver = new ControllerResolver();
    }

    public function testRouteIsDefault(): void {
        self::assertEquals('/', $this->controllerResolver->getPath(ControllerWithAttribute::class));
        self::assertEquals('/', $this->controllerResolver->getPath(new ControllerWithAttribute()));
    }

    public function testRouteIsNotDefault(): void {
        self::assertEquals('/is/not/default', $this->controllerResolver->getPath(ControllerWithAttributeTwo::class));
        self::assertEquals('/is/not/default', $this->controllerResolver->getPath(new ControllerWithAttributeTwo()));
    }

    public function testGetAllControllers(): void {
        $result = $this->controllerResolver->getAllControllers();

        self::assertContains(
            ControllerWithAttribute::class,
            $result
        );

        self::assertContains(
            ControllerWithAttributeTwo::class,
            $result
        );

        self::assertNotContains([
            NotAController::class
        ], $result);
    }

}

#[Controller]
class ControllerWithAttribute {}

#[Controller(path: '/is/not/default')]
class ControllerWithAttributeTwo {}

class NotAController {}
