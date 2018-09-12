<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  MethodToActionTraitTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Traits;

use Hrafn\Router\Contracts\RouteBuilderInterface;
use Hrafn\Router\Method;
use PHPUnit\Framework\TestCase;

/**
 * MethodToActionTraitTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 *
 * Test for Hrafn\Router\Traits\MethodToActionTrait.
 */
class MethodToActionTraitTest extends TestCase {

    public function testPatch() {
        $action              = new MethodToActionTraitTestImplementation();
        $action->handler     = 'class@method';
        $action->pattern     = '/test/patch';
        $action->method      = Method::PATCH;
        $action->middlewares = [];
        $action->patch('/test/patch', 'class@method');
        $this->assertTrue($action->called);
        if (count($action->getErrors()) > 0) {
            $this->fail($action->getErrors()[0]);
        }
    }

    public function testPut() {
        $action              = new MethodToActionTraitTestImplementation();
        $action->handler     = 'class@method';
        $action->pattern     = '/test/put';
        $action->method      = Method::PUT;
        $action->middlewares = [];
        $action->put('/test/put', 'class@method');
        $this->assertTrue($action->called);
        if (count($action->getErrors()) > 0) {
            $this->fail($action->getErrors()[0]);
        }
    }

    public function testHead() {
        $action              = new MethodToActionTraitTestImplementation();
        $action->handler     = 'class@method';
        $action->pattern     = '/test/head';
        $action->method      = Method::HEAD;
        $action->middlewares = [];
        $action->head('/test/head', 'class@method');
        $this->assertTrue($action->called);
        if (count($action->getErrors()) > 0) {
            $this->fail($action->getErrors()[0]);
        }
    }

    public function testConnect() {
        $action              = new MethodToActionTraitTestImplementation();
        $action->handler     = 'class@method';
        $action->pattern     = '/test/connect';
        $action->method      = Method::CONNECT;
        $action->middlewares = [];
        $action->connect('/test/connect', 'class@method');
        $this->assertTrue($action->called);
        if (count($action->getErrors()) > 0) {
            $this->fail($action->getErrors()[0]);
        }
    }

    public function testOptions() {
        $action              = new MethodToActionTraitTestImplementation();
        $action->handler     = 'class@method';
        $action->pattern     = '/test/options';
        $action->method      = Method::OPTIONS;
        $action->middlewares = [];
        $action->options('/test/options', 'class@method');
        $this->assertTrue($action->called);
        if (count($action->getErrors()) > 0) {
            $this->fail($action->getErrors()[0]);
        }
    }

    public function testGet() {
        $action              = new MethodToActionTraitTestImplementation();
        $action->handler     = 'class@method';
        $action->pattern     = '/test/get';
        $action->method      = Method::GET;
        $action->middlewares = [];
        $action->get('/test/get', 'class@method');
        $this->assertTrue($action->called);
        if (count($action->getErrors()) > 0) {
            $this->fail($action->getErrors()[0]);
        }
    }

    public function testDelete() {
        $action              = new MethodToActionTraitTestImplementation();
        $action->handler     = 'class@method';
        $action->pattern     = '/test/delete';
        $action->method      = Method::DELETE;
        $action->middlewares = [];
        $action->delete('/test/delete', 'class@method');
        $this->assertTrue($action->called);
        if (count($action->getErrors()) > 0) {
            $this->fail($action->getErrors()[0]);
        }
    }

    public function testPost() {
        $action              = new MethodToActionTraitTestImplementation();
        $action->handler     = 'class@method';
        $action->pattern     = '/test/post';
        $action->method      = Method::POST;
        $action->middlewares = [];
        $action->post('/test/post', 'class@method');
        $this->assertTrue($action->called);
        if (count($action->getErrors()) > 0) {
            $this->fail($action->getErrors()[0]);
        }
    }

    public function testTrace() {
        $action              = new MethodToActionTraitTestImplementation();
        $action->handler     = 'class@method';
        $action->pattern     = '/test/trace';
        $action->method      = Method::TRACE;
        $action->middlewares = [];
        $action->trace('/test/trace', 'class@method');
        $this->assertTrue($action->called);
        if (count($action->getErrors()) > 0) {
            $this->fail($action->getErrors()[0]);
        }
    }

}


/**
 * @property $method
 * @property $pattern
 * @property $handler
 * @property $middlewares
 */
class MethodToActionTraitTestImplementation implements RouteBuilderInterface {
    use MethodToActionTrait;
    /** @var bool */
    public $called = false;

    /** @var array */
    private $expectation;

    /** @var array */
    private $errors;

    public function __set($name, $value) {
        $this->expectation[$name] = $value;
    }

    protected function action(string $method,
                              string $pattern,
                              $handler,
                              $middleWares = []): RouteBuilderInterface {
        $this->errors = [];
        $this->called = true;

        foreach (['method' => $method, 'pattern' => $pattern, 'handler' => $handler, 'middlewares' => $middleWares] as $name => $value) {
            if ($value !== $this->expectation[$name]) {
                $this->addError(json_encode($this->expectation[$name]), json_encode($value));
            }
        }

        return $this;
    }

    private function addError($expected, $actual) {
        $this->errors[] = sprintf(
            'Method was not the expected value. Expected: [%s] - Actual: [%s]',
            $expected,
            $actual
        );
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function namespace(string $pattern, callable $closure,  ?array $middleWares): RouteBuilderInterface { }

    /**
     * Create a new group inside of current group.
     * A RouteBuilderInterface instance is passed as the single argument to the $closure callback.
     * @alias namespace
     *
     * @param string     $pattern
     * @param callable   $closure
     * @param array|null $middleWares
     * @return RouteBuilderInterface
     */
    public function group(string $pattern, callable $closure, ?array $middleWares): RouteBuilderInterface {
        $this->namespace($pattern, $closure, $middleWares);
    }
}
