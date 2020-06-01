<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ClassHandlerTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Tests\RequestHandler;

use Exception;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Hrafn\Router\Action;
use Hrafn\Router\Parser\RegexParameterExtractor;
use Hrafn\Router\RequestHandler\ReflectionClassHandler;
use Jitesoft\Container\Container;
use Jitesoft\Exceptions\Http\Client\HttpBadRequestException;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;

/**
 * ClassHandlerTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ClassHandlerTest extends TestCase {

    private $logger;
    private $container;

    protected function setUp(): void {
        parent::setUp();

        ClassHandlerTest_Handler::reset();

        $value = new ClassHandlerTest_BoundObject('abc123');
        $this->logger = new NullLogger();
        $this->container = new Container([
            ClassHandlerTest_BoundObject::class => $value
        ]);
    }

    public function testHandleNoParams() {
        $callee = ClassHandlerTest_Handler::class . '@handlerWithNoParam';

        $action = new Action('get', $callee, '/test', [], new RegexParameterExtractor($this->logger), $this->container);
        $handler = new ReflectionClassHandler(
            ClassHandlerTest_Handler::class,
            'handlerWithNoParam',
            new RegexParameterExtractor(new NullLogger()),
            $action,
            $this->container
        );

        $result = $handler->handle(new ServerRequest('get', '/test'));
        $this->assertEquals(123, $result->getStatusCode());
        $this->assertTrue(ClassHandlerTest_Handler::$called);
        $this->assertEquals('abc123', ClassHandlerTest_Handler::$value->value);
    }

    public function testHandleOneParam() {
        $callee = ClassHandlerTest_Handler::class . '@handlerWithOneParam';

        $action = new Action('get', $callee, '/test/{id}', [], new RegexParameterExtractor($this->logger), $this->container);
        $handler = new ReflectionClassHandler(
            ClassHandlerTest_Handler::class,
            'handlerWithOneParam',
            new RegexParameterExtractor(new NullLogger()),
            $action,
            $this->container
        );

        $result = $handler->handle(new ServerRequest('get', '/test/123'));
        $this->assertEquals(123, $result->getStatusCode());

        $this->assertTrue(ClassHandlerTest_Handler::$called);
        $this->assertEquals('abc123', ClassHandlerTest_Handler::$value->value);
    }

    public function testHandleMultipleRequiredParams() {
        $callee = ClassHandlerTest_Handler::class . '@handleWithMultipleRequiredParams';

        $action = new Action('get', $callee, '/test/{id}/{name}/{something}', [], new RegexParameterExtractor($this->logger), $this->container);
        $handler = new ReflectionClassHandler(
            ClassHandlerTest_Handler::class,
            'handleWithMultipleRequiredParams',
            new RegexParameterExtractor(new NullLogger()),
            $action,
            $this->container
        );

        $result = $handler->handle(new ServerRequest('get', '/test/123/abc123/123abc'));
        $this->assertEquals(567, $result->getStatusCode());

        $this->assertTrue(ClassHandlerTest_Handler::$called);
        $this->assertEquals('abc123', ClassHandlerTest_Handler::$value->value);
    }

    public function testHandleMultipleOptionalParams() {
        $callee = ClassHandlerTest_Handler::class . '@handleWithMultipleOptionalParams';

        $action = new Action('get', $callee, '/test/{?id}/{?name}/{?something}', [], new RegexParameterExtractor($this->logger), $this->container);
        $handler = new ReflectionClassHandler(
            ClassHandlerTest_Handler::class,
            'handleWithMultipleOptionalParams',
            new RegexParameterExtractor(new NullLogger()),
            $action,
            $this->container
        );

        $result = $handler->handle(new ServerRequest('get', '/test/123/abc123'));
        $this->assertEquals(567, $result->getStatusCode());

        $this->assertTrue(ClassHandlerTest_Handler::$called);
        $this->assertEquals('abc123', ClassHandlerTest_Handler::$value->value);
    }

    public function testHandleMultipleMixedParams() {
        $callee = ClassHandlerTest_Handler::class . '@handleWithMixedParams';

        $action = new Action('get', $callee, '/test/{id}/{name}/{?something}', [], new RegexParameterExtractor($this->logger), $this->container);
        $handler = new ReflectionClassHandler(
            ClassHandlerTest_Handler::class,
            'handleWithMixedParams',
            new RegexParameterExtractor(new NullLogger()),
            $action,
            $this->container
        );

        $result = $handler->handle(new ServerRequest('get', '/test/123/abc123'));
        $this->assertEquals(567, $result->getStatusCode());

        $this->assertTrue(ClassHandlerTest_Handler::$called);
        $this->assertEquals('abc123', ClassHandlerTest_Handler::$value->value);
    }

    public function testHandleInvalidArgumentException() {
        $callee = ClassHandlerTest_Handler::class . '@handleInvalidException';

        $action = new Action('get', $callee, '/test/{id}/{name}/{something}', [], new RegexParameterExtractor($this->logger), $this->container);
        $handler = new ReflectionClassHandler(
            ClassHandlerTest_Handler::class,
            'handleInvalidException',
            new RegexParameterExtractor(new NullLogger()),
            $action,
            $this->container
        );

        $this->expectException(InvalidArgumentException::class);
        $handler->handle(new ServerRequest('get', '/test/123/test'));


        $this->assertTrue(ClassHandlerTest_Handler::$called);
        $this->assertEquals('abc123', ClassHandlerTest_Handler::$value->value);
    }

    public function testHandleBadRequestException() {
        $callee = ClassHandlerTest_Handler::class . '@handleBadRequest';

        $action = new Action('get', $callee, '/test/{id}/{name}', [], new RegexParameterExtractor($this->logger), $this->container);
        $handler = new ReflectionClassHandler(
            ClassHandlerTest_Handler::class,
            'handleBadRequest',
            new RegexParameterExtractor(new NullLogger()),
            $action,
            $this->container
        );

        $this->expectException(HttpBadRequestException::class);
        $handler->handle(new ServerRequest('get', '/test/123/test'));

        $this->assertTrue(ClassHandlerTest_Handler::$called);
        $this->assertEquals('abc123', ClassHandlerTest_Handler::$value->value);
    }
}

class ClassHandlerTest_BoundObject {
    public $value;

    public function __construct($value) {
        $this->value = $value;
    }
}

class ClassHandlerTest_Handler {
    public static $called = false;
    public static $value  = null;

    public static function reset() {
        self::$value = null;
        self::$called = false;
    }

    public function __construct(ClassHandlerTest_BoundObject $object) {
        self::$called = true;
        self::$value  = $object;
    }

    public function handlerWithNoParam() {
        return new Response(123);
    }

    public function handlerWithOneParam(int $id) {
        if ($id !== 123) {
            throw new Exception('No!');
        }
        return new Response($id);
    }

    public function handleWithMultipleRequiredParams(ServerRequestInterface $request, int $id, string $name, $something) {
        if ($id !== 123 || $name !== 'abc123' || $something !== '123abc') {
            throw new Exception('No!');
        }
        return new Response(567);
    }

    public function handleWithMultipleOptionalParams(?int $id, ?string $name, $something) {
        if ($id !== 123 || $name !== 'abc123' || $something !== null) {
            throw new Exception('No!');
        }

        return new Response(567);
    }

    public function handleWithMixedParams(int $id, string $name, $something) {
        if ($id !== 123 || $name !== 'abc123' || $something !== null) {
            throw new Exception('No!');
        }

        return new Response(567);
    }

    public function handleInvalidException(int $id, string $name, $something) {
        return new Response(567);
    }

    public function handleBadRequest(int $id, string $name, $something, $andAnother) {
        return new Response(567);
    }
}
