<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteBuilderTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Tests;

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
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class RouteBuilderTest extends TestCase {

    private $routeBuilder;

    protected function setUp() {
        parent::setUp();

        $nullLogger         = new NullLogger();
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
        $this->routeBuilder->group('/test', function(RouteBuilder $builder) {
           $builder->get('/abc', function() {});
        }, []);

        $container = $this->getPrivateValue($this->routeBuilder, 'actionContainer');
        $manager   = $this->getPrivateValue($this->routeBuilder, 'manager');

        $this->assertArrayHasKey('get::test/abc', $container);
        $node = $manager->createOrGetNode(
            $manager->createOrGetRootNode('test'), 'abc'
        );
        $this->assertCount(1, $this->getPrivateValue($node, 'references'));
        $this->assertTrue($this->getPrivateValue($node, 'references')->has('get'));
        $this->assertEquals('get::test/abc', $this->getPrivateValue($node, 'references')->get('get'));
    }

    public function testNamespace() {
        $this->routeBuilder->namespace('/test', function(RouteBuilder $builder) {
            $builder->get('/abc', function() {});
        }, []);

        $container = $this->getPrivateValue($this->routeBuilder, 'actionContainer');
        $manager   = $this->getPrivateValue($this->routeBuilder, 'manager');

        $this->assertArrayHasKey('get::test/abc', $container);
        $node = $manager->createOrGetNode(
            $manager->createOrGetRootNode('test'), 'abc'
        );
        $this->assertCount(1, $this->getPrivateValue($node, 'references'));
        $this->assertTrue($this->getPrivateValue($node, 'references')->has('get'));
        $this->assertEquals('get::test/abc', $this->getPrivateValue($node, 'references')->get('get'));
    }

    public function testActions() {

        foreach (Method::getConstantValues() as $method) {
            $this->routeBuilder->{$method}('/abc', function () {});
            $container = $this->getPrivateValue($this->routeBuilder, 'actionContainer');
            $this->assertTrue($container->has($method.'::/abc'));
        }
    }

}
