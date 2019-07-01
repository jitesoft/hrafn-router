<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ActionTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Tests;

use Hrafn\Router\Action;
use Hrafn\Router\Method;
use Hrafn\Router\Middleware\AnonymousMiddleware;
use Hrafn\Router\Parser\RegexParameterExtractor;
use Hrafn\Router\RequestHandler\ReflectionCallbackHandler;
use Hrafn\Router\RequestHandler\ReflectionClassHandler;
use Jitesoft\Utilities\DataStructures\Arrays;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\NullLogger;

/**
 * ActionTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ActionTest extends TestCase {

    public function testGetMethod() {
        Arrays::forEach(Method::getConstantValues(), function($method) {
            $action = new Action($method, 'Handler@method', '/test', [], new RegexParameterExtractor(new NullLogger()));
            $this->assertEquals($method, $action->getMethod());
        });
    }

    public function testGetMiddlewares() {
        $action = new Action(Method::POST, 'handler@method', '/test', [
            new ActionTestTestMiddleware(),
            new AnonymousMiddleware(function($request, $handler) {

            }),
            new ActionTestTestMiddleware()
        ], new RegexParameterExtractor(new NullLogger()));

        $queue = $action->getMiddlewares();
        $this->assertCount(3, $queue);
        $this->assertInstanceOf(ActionTestTestMiddleware::class, $queue->dequeue());
        $this->assertInstanceOf(AnonymousMiddleware::class, $queue->dequeue());
        $this->assertInstanceOf(ActionTestTestMiddleware::class, $queue->dequeue());
    }

    public function testGetPattern() {
        $action = new Action('get', 'Handler@method', '/test', [], new RegexParameterExtractor(new NullLogger()));
        $this->assertEquals('/test', $action->getPattern());
        $action = new Action('get', 'Handler@method', '/test/{with}/{?params}', [], new RegexParameterExtractor(new NullLogger()));
        $this->assertEquals('/test/{with}/{?params}', $action->getPattern());
    }

    public function testGetHandlerCallback() {
        $handler = function($r) {

        };
        $action = new Action('get', $handler, '/test', [], new RegexParameterExtractor(new NullLogger()));
        $this->assertInstanceOf(ReflectionCallbackHandler::class, $action->getHandler());
    }

    public function testGetHandlerClass() {
        $action = new Action('get', 'Handler@method', '/test', [], new RegexParameterExtractor(new NullLogger()));
        $this->assertInstanceOf(ReflectionClassHandler::class, $action->getHandler());
    }

}

class ActionTestTestMiddleware implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        return null;
    }
}
