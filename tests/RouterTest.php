<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouterTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Hrafn\Router\Router;
use PHPUnit\Framework\TestCase;

/**
 * RouterTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
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

}
