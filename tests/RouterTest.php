<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouterTest.php - Part of the router project.

  © - Jitesoft 2018-2021
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Tests;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Hrafn\Router\RouteBuilder;
use Hrafn\Router\Router;
use Jitesoft\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * RouterTest
 *
 * @author  Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class RouterTest extends TestCase {

    public function testIndex() {
        $router = new Router();
        $wasCalled = false;
        $router->getBuilder()->get('/', function() use (&$wasCalled) {
            $wasCalled = true;
            return new Response(200);
        });

        $result = $router->handle(new ServerRequest('get', ''));
        $this->assertTrue($wasCalled);
        $this->assertEquals(200, $result->getStatusCode());

        $wasCalled = false;
        $result = $router->handle(new ServerRequest('get', '/'));
        $this->assertTrue($wasCalled);
        $this->assertEquals(200, $result->getStatusCode());

    }

    public function testHandle() {
        $router = new Router();

        $wasCalled = falsE;
        $router->getBuilder()->get('/test', function() use(&$wasCalled) {
            $wasCalled = true;
            return new Response(200);

        });

        $result = $router->handle(new ServerRequest('get', '/test'));
        $this->assertTrue($wasCalled);
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testHandleWithMiddlewares() {
        $router = new Router();

        $router->getBuilder()->namespace('/test', function (RouteBuilder $builder) {
            $builder->get('/test', function(RequestInterface $r) {
                $r->getBody()->rewind();
                $body = $r->getBody()->read(3);
                return new Response(200, [], $body . '4');
            }, [ function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
                $request->getBody()->write('3');
                return $handler->handle($request);
            }]);
        }, [
            function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
                $request->getBody()->write('1');
                return $handler->handle($request);
            },
            new Test_Middleware()
        ]);

        $result = $router->handle(new ServerRequest('get', '/test/test'));
        $result->getBody()->rewind();
        $this->assertEquals('1234', $result->getBody()->read(4));
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function testPutWithParams() {
        $router = new Router();
        $called1 = false;
        $called2 = false;

        $router->getBuilder()->namespace('/api/v1', function (RouteBuilder $b) use(&$called1, &$called2) {
           $b->put('/test/{user}', function(RequestInterface $r, $user) use (&$called1) {
               $called1 = true;
               return new Response(200);
           });
           $b->post('/test/{user}', function (RequestInterface $r, $user) use(&$called2) {
               $called2 = true;
               return new Response(200);
           });
        });

        $result = $router->handle(new ServerRequest('put', '/api/v1/test/abc'));
        $this->assertTrue($called1);
        $this->assertFalse($called2);
    }

    public function testMiddlewareToggle() {
        $router = new Router(new Container([
            Test_Middleware::class => Test_Middleware::class
        ]));

        $router->getBuilder()->get(
            'test',
            function (Request $request) {
                $request->getBody()->rewind();
                $content = $request->getBody()->getContents();
                $content .= '1';
                return new Response(123, [], $content);
            },
            [ Test_Middleware::class ]
        );

        Router::disableMiddleware(Test_Middleware::class);
        $result = $router->handle(new ServerRequest('GET', 'test/'));
        $this->assertEquals(123, $result->getStatusCode());
        $result->getBody()->rewind();
        $this->assertEquals('1', $result->getBody()->read(16));

        $router->getBuilder()->post(
            'test2',
            function (Request $request) {
                $request->getBody()->rewind();
                $res = $request->getBody()->getContents();
                $res .= '1';
                return new Response(123, [], $res);
            },
            [ Test_Middleware::class ]
        );

        Router::enableMiddleware(Test_Middleware::class);
        $result = $router->handle(new ServerRequest('POST', 'test2'));
        $result->getBody()->rewind();
        $this->assertEquals('21', $result->getBody()->read(16));
    }

}

class Test_Middleware implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $request->getBody()->write('2');
        return $handler->handle($request);
    }
}
