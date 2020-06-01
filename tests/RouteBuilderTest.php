<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteBuilderTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Tests;

use Hrafn\Router\Contracts\RouteBuilderInterface;
use Hrafn\Router\Method;
use Hrafn\Router\Parser\RegexParameterExtractor;
use Hrafn\Router\Parser\RegexPathExtractor;
use Hrafn\Router\RouteBuilder;
use Hrafn\Router\RouteTree\Node;
use Hrafn\Router\RouteTree\RouteTreeManager;
use Jitesoft\Utilities\DataStructures\Maps\SimpleMap;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;

/**
 * RouteBuilderTest
 *
 * @author  Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class RouteBuilderTest extends TestCase {
    private RouteBuilderInterface $routeBuilder;

    protected function setUp(): void {
        parent::setUp();

        $nullLogger = new NullLogger();
        $this->routeBuilder = new RouteBuilder(
            [],
            new Node(null, '/'),
            new RegexPathExtractor($nullLogger),
            new RegexParameterExtractor($nullLogger),
            new RouteTreeManager($nullLogger),
            '/',
            $nullLogger,
            new SimpleMap()
        );
    }

    private function getPrivateValue($obj, $name) {
        $ref = new ReflectionClass($obj);
        $prop = $ref->getProperty($name);
        $prop->setAccessible(true);
        $val = $prop->getValue($obj);
        $prop->setAccessible(false);
        return $val;
    }

    public function testGroup() {
        $this->routeBuilder->group('test', function (RouteBuilder $builder) {
            $builder->get('abc', function () {
            });
        }, []);

        $container = $this->getPrivateValue($this->routeBuilder, 'actionContainer');
        /** @var Node $root */
        $root = $this->getPrivateValue($this->routeBuilder, 'root');

        $this->assertTrue($root->hasChild('test'));
        $child = $root->getChild('test');
        $this->assertTrue($child->hasChild('abc'));
        $this->assertEquals('get::test/abc', $child->getChild('abc')->getReference('get'));
        $this->assertTrue($container->has('get::test/abc'));
    }

    public function testNamespace() {
        $this->routeBuilder->namespace('/test', function (RouteBuilder $builder) {
            $builder->get('/abc', function () {
            });
        }, []);

        $container = $this->getPrivateValue($this->routeBuilder, 'actionContainer');
        /** @var Node $root */
        $root = $this->getPrivateValue($this->routeBuilder, 'root');

        $this->assertTrue($root->hasChild('test'));
        $child = $root->getChild('test');
        $this->assertTrue($child->hasChild('abc'));
        $this->assertEquals('get::test/abc', $child->getChild('abc')->getReference('get'));
        $this->assertTrue($container->has('get::test/abc'));
    }

    public function testActions() {

        foreach (Method::getConstantValues() as $method) {
            $this->routeBuilder->{$method}('/abc', function () {
            });
            $container = $this->getPrivateValue($this->routeBuilder, 'actionContainer');
            $this->assertTrue($container->has($method . '::/abc'));
        }
    }

}
