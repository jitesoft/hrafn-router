<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ActionTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Tests;

use Hrafn\Router\Action;
use Hrafn\Router\Method;
use Hrafn\Router\Middleware\AnonymousMiddleware;
use Hrafn\Router\RequestHandler\CallbackHandler;
use Hrafn\Router\RequestHandler\ClassHandler;
use Jitesoft\Container\Container;
use Jitesoft\Utilities\DataStructures\Arrays;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ActionTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ActionTest extends TestCase {

    public function testGetMethod() {
        Arrays::forEach(Method::getConstantValues(), function($method) {
            $action = new Action($method, 'Handler@method', '/test', [], new Container());
            $this->assertEquals($method, $action->getMethod());
        });
    }

    public function testGetMiddlewares() {
        $action = new Action(Method::POST, 'handler@method', '/test', [
            new ActionTestTestMiddleware(),
            new AnonymousMiddleware(function($request, $handler) {

            }),
            new ActionTestTestMiddleware()
        ], new Container());

        $queue = $action->getMiddlewares();
        $this->assertCount(3, $queue);
        $this->assertInstanceOf(ActionTestTestMiddleware::class, $queue->dequeue());
        $this->assertInstanceOf(AnonymousMiddleware::class, $queue->dequeue());
        $this->assertInstanceOf(ActionTestTestMiddleware::class, $queue->dequeue());
    }

    public function testGetPattern() {

        $action = new Action('get', 'Handler@method', '/test', [], new Container());
        $this->assertEquals('/test', $action->getPattern());
        $action = new Action('get', 'Handler@method', '/test/{with}/{?params}', [], new Container());
        $this->assertEquals('/test/{with}/{?params}', $action->getPattern());
    }

    public function testGetHandlerCallback() {
        $handler = function($r) {

        };
        $action = new Action('get', $handler, '/test', [], new Container());
        $this->assertInstanceOf(CallbackHandler::class, $action->getHandler());
    }

    public function testGetHandlerClass() {
        $action = new Action('get', 'Handler@method', '/test', [], new Container());
        $this->assertInstanceOf(ClassHandler::class, $action->getHandler());
    }

}

class ActionTestTestMiddleware implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return null;
    }
}
