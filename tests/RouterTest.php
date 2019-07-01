<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouterTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Hrafn\Router\RouteBuilder;
use Hrafn\Router\Router;
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
            new Test_Middleware($callOrder)
        ]);

        $result = $router->handle(new ServerRequest('get', '/test/test'));
        $result->getBody()->rewind();
        $this->assertEquals('1234', $result->getBody()->read(4));
        $this->assertEquals(200, $result->getStatusCode());
    }

}

class Test_Middleware implements MiddlewareInterface {

    private $callOrder = null;
    public function __construct(&$callOrder) { $callOrder = $callOrder; }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $this->callOrder .= '2';
        $request->getBody()->write('2');
        return $handler->handle($request);
    }
}
