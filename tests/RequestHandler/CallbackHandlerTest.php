<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  CallbackHandlerTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Tests\RequestHandler;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Hrafn\Router\Action;
use Hrafn\Router\Parser\RegexParameterExtractor;
use Hrafn\Router\RequestHandler\ReflectionCallbackHandler;
use Jitesoft\Exceptions\Http\Client\HttpBadRequestException;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;

/**
 * CallbackHandlerTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class CallbackHandlerTest extends TestCase {

    private $nullLogger;

    protected function setUp(): void {
        parent::setUp();

        $this->nullLogger = new NullLogger();
    }

    public function testHandleNoParams() {
        $called   = false;
        $callback = function() use(&$called) {
            $called = true;
            return new Response(123);
        };

        $action  = new Action('get', $callback, '/test', [], new RegexParameterExtractor($this->nullLogger));
        $handler = new ReflectionCallbackHandler(
            $callback,
            new RegexParameterExtractor(new NullLogger()),
            $action
        );

        $result = $handler->handle(new ServerRequest('get', '/test'));
        $this->assertEquals(123, $result->getStatusCode());
        $this->assertTrue($called);
    }

    public function testHandleOneParam() {
        $called   = false;
        $callback = function(ServerRequestInterface $request, $id) use(&$called) {
            $called = true;
            return new Response($id);
        };

        $action  = new Action('get', $callback, '/test/{id}', [], new RegexParameterExtractor($this->nullLogger));
        $handler = new ReflectionCallbackHandler(
            $callback,
            new RegexParameterExtractor(new NullLogger()),
            $action
        );

        $result = $handler->handle(new ServerRequest('get', '/test/123'));
        $this->assertEquals(123, $result->getStatusCode());
        $this->assertTrue($called);
    }

    public function testHandleMultiRequiredParams() {
        $called   = false;
        $self     = $this;
        $callback = function(ServerRequestInterface $request, int $id, string $name, $something) use(&$called, &$self) {
            $called = true;
            $self->assertEquals(123, $id);
            $self->assertEquals('test', $name);
            $self->assertEquals('whatever', $something);
            return new Response(567);
        };

        $action  = new Action('get', $callback, '/test/{id}/{name}/{something}', [], new RegexParameterExtractor($this->nullLogger));
        $handler = new ReflectionCallbackHandler(
            $callback,
            new RegexParameterExtractor(new NullLogger()),
            $action
        );

        $result = $handler->handle(new ServerRequest('get', '/test/123/test/whatever'));
        $this->assertEquals(567, $result->getStatusCode());
        $this->assertTrue($called);
    }

    public function testHandleMultiOptionalParams() {
        $called   = false;
        $self     = $this;
        $callback = function(int $id, string $name, $something) use(&$called, &$self) {
            $called = true;
            $self->assertEquals(123, $id);
            $self->assertEquals('test', $name);
            $self->assertNull($something);
            return new Response(567);
        };

        $action  = new Action('get', $callback, '/test/{?id}/{?name}/{?something}', [], new RegexParameterExtractor($this->nullLogger));
        $handler = new ReflectionCallbackHandler(
            $callback,
            new RegexParameterExtractor(new NullLogger()),
            $action
        );

        $result = $handler->handle(new ServerRequest('get', '/test/123/test'));
        $this->assertEquals(567, $result->getStatusCode());
        $this->assertTrue($called);
    }

    public function testHandleMixedParams() {
        $called   = false;
        $self     = $this;
        $callback = function(int $id, string $name, $something) use(&$called, &$self) {
            $called = true;
            $self->assertEquals(123, $id);
            $self->assertEquals('test', $name);
            $self->assertNull($something);
            return new Response(567);
        };

        $action  = new Action('get', $callback, '/test/{id}/{name}/{?something}', [], new RegexParameterExtractor($this->nullLogger));
        $handler = new ReflectionCallbackHandler(
            $callback,
            new RegexParameterExtractor(new NullLogger()),
            $action
        );

        $result = $handler->handle(new ServerRequest('get', '/test/123/test'));
        $this->assertEquals(567, $result->getStatusCode());
        $this->assertTrue($called);
    }

    public function testHandleInvalidArgumentException() {

        $callback = function(int $id, string $name, $something) {
            return new Response(567);
        };

        $action  = new Action('get', $callback, '/test/{id}/{name}/{something}', [], new RegexParameterExtractor($this->nullLogger));
        $handler = new ReflectionCallbackHandler(
            $callback,
            new RegexParameterExtractor(new NullLogger()),
            $action
        );
        $this->expectException(InvalidArgumentException::class);

        $handler->handle(new ServerRequest('get', '/test/123/test'));
    }

    public function testHandleBadRequest() {

        $callback = function(int $id, string $name, $something, $andAnother) {
            return new Response(567);
        };

        $action  = new Action('get', $callback, '/test/{id}/{name}', [], new RegexParameterExtractor($this->nullLogger));
        $handler = new ReflectionCallbackHandler(
            $callback,
            new RegexParameterExtractor(new NullLogger()),
            $action
        );
        $this->expectException(HttpBadRequestException::class);
        $handler->handle(new ServerRequest('get', '/test/123/test'));
    }

}
