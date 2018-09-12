<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  DispatcherTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Tests\Dispatcher;

use Hrafn\Router\Action;
use Hrafn\Router\Dispatcher\DefaultDispatcher;
use Hrafn\Router\RequestHandler\CallbackHandler;
use Hrafn\Router\RouteTree\Node;
use Jitesoft\Exceptions\Http\Client\HttpMethodNotAllowedException;
use Jitesoft\Exceptions\Http\Client\HttpNotFoundException;
use Jitesoft\Utilities\DataStructures\Maps\SimpleMap;
use PHPUnit\Framework\TestCase;

/**
 * DefaultDispatcherTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class DispatcherTest extends TestCase {

    public function testDispatchNotFoundNoRef() {
        $root = new Node(null, '');

        $dispatcher = new DefaultDispatcher($root, new SimpleMap());
        $this->expectException(HttpNotFoundException::class);
        $dispatcher->dispatch('post', '/a/b/c');
    }

    public function testDispatchNotFoundNotInMap() {
        $root = new Node(null, '/');
        $c    = $root->createChild('test');
        $c->addReference('post', '/test');
        $dispatcher = new DefaultDispatcher($root, new SimpleMap());

        $this->expectException(HttpNotFoundException::class);
        $dispatcher->dispatch('post', '/test');
    }

    public function testDispatchMethodNotAllowed() {
        $root = new Node(null, '/');
        $root->createChild('test');
        $dispatcher = new DefaultDispatcher($root, new SimpleMap());

        $this->expectException(HttpMethodNotAllowedException::class);
        $dispatcher->dispatch('post', '/test');
    }

    public function testDispatchSuccess() {
        $root = new Node(null, '/');
        $c    = $root->createChild('test');
        $c->addReference('post', 'post::/test');

        $dispatcher = new DefaultDispatcher($root, new SimpleMap([
            sprintf('%s::%s/%s', 'post', '', 'test') => new Action('post', function() {}, '/test', [])
        ]));

        $handler = $dispatcher->dispatch('post', '/test');
        $this->assertInstanceOf(CallbackHandler::class, $handler);
    }


}
