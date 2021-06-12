<?php

namespace Hrafn\Router\Tests\Attributes;

use Hrafn\Router\Attributes\Action;
use Hrafn\Router\Attributes\ActionResolver;
use Hrafn\Router\Attributes\Controller;
use Hrafn\Router\Attributes\ControllerResolver;
use Hrafn\Router\Attributes\MiddlewareResolver;
use Hrafn\Router\Contracts\ActionResolverInterface;
use PHPUnit\Framework\TestCase;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;

class ActionTest extends TestCase {
    private ActionResolverInterface $actionResolver;


    protected function setUp(): void {
        $this->actionResolver = new ActionResolver(new MiddlewareResolver(), new ControllerResolver());
    }

    public function testGetActions(): void {
        $m1 = $this->actionResolver->getControllerActions(TestController::class);
        $m2 = $this->actionResolver->getControllerActions(new TestController());
        self::assertEquals($m1, $m2);
        self::assertCount(2, $m1);

        self::assertContains([
            'method' => 'GET',
            'path'   => '/',
            'handler' => TestController::class . '@' . 'getSomething',
            'middlewares' => []
        ], $m1);

        self::assertContains([
            'method' => 'POST',
            'path'   => '/a/b/c',
            'handler' => TestController::class . '@' . 'postSomething',
            'middlewares' => []
        ], $m2);

        self::assertEmpty($this->actionResolver->getControllerActions(TestControllerNoActions::class));
    }

    public function testActionMethodErrorClassName(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->actionResolver->getControllerActions(TestControllerWithInvalidAction::class);
    }

    public function testActionMethodErrorObject(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->actionResolver->getControllerActions(new TestControllerWithInvalidAction());
    }

    public function testGetFunctionActions(): void {
        $actions = $this->actionResolver->getFunctionActions();

        self::assertContains([
            'method' => 'POST',
            'path'   => '/a/path',
            'handler' => __NAMESPACE__ . '\\testActionFunc',
            'middlewares' => []
        ], $actions);

        self::assertContains([
            'method' => 'GET',
            'path'   => '/',
            'handler' => __NAMESPACE__ . '\\testActionFuncTwo',
            'middlewares' => []
        ], $actions);
    }
}

#[Action(path: '/a/path', method: 'POST')]
function testActionFunc(): void {}

#[Action]
function testActionFuncTwo(): void {}

function testActionFuncNoAction() {}

class TestControllerNoActions {}

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
    #[Action(path: '/a/b/c', method: 'POST')]
    public function postSomething(): void {}
    #[Action(method: 'GAHWAGAAA')]
    public function errorSomething(): void {}
}
