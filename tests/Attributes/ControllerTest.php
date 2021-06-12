<?php

namespace Hrafn\Router\Tests\Attributes;

use Hrafn\Router\Attributes\Controller;
use ReflectionException;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase {

    public function testIsController(): void {
        self::assertTrue(Controller::classIsController(ControllerWithAttribute::class));
        self::assertTrue(Controller::classIsController(new ControllerWithAttribute()));
        self::assertFalse(Controller::classIsController(NotAController::class));
        self::assertFalse(Controller::classIsController(new NotAController()));

        $this->expectException(ReflectionException::class);
        Controller::classIsController('Asadkjldsajkldsalkjdsa');
    }

    public function testRouteIsDefault(): void {
        self::assertEquals('/', Controller::getRoute(ControllerWithAttribute::class));
        self::assertEquals('/', Controller::getRoute(new ControllerWithAttribute()));
    }

    public function testRouteIsNotDefault(): void {
        self::assertEquals('/is/not/default', Controller::getPath(ControllerWithAttributeTwo::class));
        self::assertEquals('/is/not/default', Controller::getPath(new ControllerWithAttributeTwo()));
    }

}

#[Controller]
class ControllerWithAttribute {}

#[Controller(path: '/is/not/default')]
class ControllerWithAttributeTwo {}

class NotAController {}
