<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ActionTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Tests;

use Hrafn\Router\Action;
use Hrafn\Router\Method;
use function is_callable;
use PHPUnit\Framework\TestCase;
use function preg_match;
use function preg_match_all;
use function var_dump;

/**
 * ActionTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ActionTest extends TestCase {

    private const METHODS = [
        Method::GET,
        Method::HEAD,
        Method::POST,
        Method::PUT,
        Method::DELETE,
        Method::CONNECT,
        Method::OPTIONS,
        Method::TRACE,
        Method::PATCH
    ];

    public function testGetMethod() {
        foreach (self::METHODS as $method) {
            $action = new Action($method, 'Handler@method', '/test');
            $this->assertEquals($method, $action->getMethod());
        }
    }

    public function testGetHandlerClass() {
        $action = new Action('get', 'className@classMethod', '/test');
        $this->assertEquals('className', $action->getHandlerClass());
    }

    public function testGetHandlerClassFailure() {
        $action = new Action('get', function() {}, '/test');
        $this->assertNull($action->getHandlerClass());
    }

    public function testGetHandlerFunction() {
        $action = new Action('get', 'className@classMethod', '/test');
        $this->assertEquals('classMethod', $action->getHandlerFunction());
    }

    public function testGetHandlerFunctionFailure() {
        $action = new Action('get', function() {}, '/test');
        $this->assertNull($action->getHandlerFunction());
    }

    public function testGetCallback() {
        $action   = new Action('get', function() { return 15; }, '/test');
        $callback = $action->getCallback();

        $this->assertTrue(is_callable($callback));
        $this->assertEquals(15, $callback());
    }

    public function testGetCallbackFailure() {
        $action = new Action('get', 'className@classMethod', '/test');
        $this->assertNull($action->getCallback());
    }

    public function testGetActionType() {
        $action = new Action('get', 'className@classMethod', '/test');
        $this->assertEquals('instance_method', $action->getActionType());
        $action = new Action('get', function() { return 15; }, '/test');
        $this->assertEquals('callback', $action->getActionType());
    }

    public function testGetActionPath() {
        $action = new Action('get', 'className@classMethod', '/test');
        $this->assertEquals('/test', $action->getActionPath());
        $action = new Action('get', 'className@classMethod', '/test/{with}/param');
        $this->assertEquals('/test/{with}/param', $action->getActionPath());
    }

    public function testGetActionPathRegex() {
        $action = new Action('get', 'className@classMethod', '/test');
        $this->assertEquals('~^\/test[\/]?$~', $action->getActionPathRegex());
        $action = new Action('get', 'className@classMethod', '/test/{with}/param/{?optional}');
        $this->assertEquals("~^\/test\/(?'with'\w+)\/param[\/]?(?:(?'optional'\w+))?[\/]?$~", $action->getActionPathRegex());

        $this->assertNotRegExp($action->getActionPathRegex(), '/test/abc123/');
        $this->assertRegExp($action->getActionPathRegex(), '/test/abc123/param');
        $this->assertRegExp($action->getActionPathRegex(), '/test/abc123/param/');
        $this->assertRegExp($action->getActionPathRegex(), '/test/abc123/param/something');
        $this->assertRegExp($action->getActionPathRegex(), '/test/abc123/param/something/');
        $this->assertNotRegExp($action->getActionPathRegex(), '/test/abc123/param/something/abc');

        $action = new Action('get', 'className@classMethod', '/test/{with}/param/{?optional}/{?again}');
        $this->assertEquals("~^\/test\/(?'with'\w+)\/param[\/]?(?:(?'optional'\w+))?[\/]?(?:(?'again'\w+))?[\/]?$~", $action->getActionPathRegex());

        $this->assertNotRegExp($action->getActionPathRegex(), '/test/abc123/');
        $this->assertRegExp($action->getActionPathRegex(), '/test/abc123/param');
        $this->assertRegExp($action->getActionPathRegex(), '/test/abc123/param/');
        $this->assertRegExp($action->getActionPathRegex(), '/test/abc123/param/something');
        $this->assertRegExp($action->getActionPathRegex(), '/test/abc123/param/something/');
        $this->assertRegExp($action->getActionPathRegex(), '/test/abc123/param/something/abc');
        $this->assertRegExp($action->getActionPathRegex(), '/test/abc123/param/something/abc/');
        $this->assertNotRegExp($action->getActionPathRegex(), '/test/abc123/param/something/abc/abc');
    }

}
