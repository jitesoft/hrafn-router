<?php

namespace Hrafn\Router\Tests\Attributes;

use Hrafn\Router\Attributes\Action;
use Hrafn\Router\Attributes\Controller;
use PHPUnit\Framework\TestCase;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;

class ActionTest extends TestCase {

    public function testIsAction(): void {
        self::assertTrue(Action::isAction(TestController::class . '@getSomething'));
        self::assertTrue(Action::isAction('getSomething', TestController::class));
        self::assertTrue(Action::isAction('getSomething', new TestController()));
    }

    public function testIsActionFunction(): void {
        self::assertTrue(Action::isAction('Hrafn\Router\Tests\Attributes\testActionFunc'));
        self::assertTrue(Action::isAction(#[Action]static fn () => null));
        self::assertTrue(Action::isAction('Hrafn\Router\Tests\Attributes\testActionFuncTwo'));
        self::assertFalse(Action::isAction('Hrafn\Router\Tests\Attributes\testActionFuncNoAction'));
    }

    public function testGetActions(): void {
        $m1 = Action::getActions(TestController::class);
        $m2 = Action::getActions(new TestController());
        self::assertEquals($m1, $m2);

        self::assertCount(2, $m1);

        self::assertContains([
            'method' => 'GET',
            'path'   => '/'
        ], $m1);

        self::assertContains([
            'method' => 'POST',
            'path'   => '/a/b/c'
        ], $m2);
    }

    public function testActionMethodErrorClassName(): void {
        $this->expectException(InvalidArgumentException::class);
        Action::getActions(TestControllerWithInvalidAction::class);
    }

    public function testActionMethodErrorObject(): void {
        $this->expectException(InvalidArgumentException::class);
        Action::getActions(new TestControllerWithInvalidAction());
    }
}

#[Action(path: '/a/path', method: 'POST')]
function testActionFunc(): void {}

#[Action]
function testActionFuncTwo(): void {}

function testActionFuncNoAction() {}

#[Controller]
class TestController {
    #[Action]
    public function getSomething(): void {}
    #[Action(path: '/a/b/c', method: 'POST')]
    public function postSomething(): void {}
    public function nonAction(): void {}
}

#[Controller]
class TestControllerWithInvalidAction {
    #[Action]
    public function getSomething(): void {}
    #[Action(method: 'POST', path: '/a/b/c')]
    public function postSomething(): void {}
    #[Action(method: 'GAHWAGAAA')]
    public function errorSomething(): void {}
}
