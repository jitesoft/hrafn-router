<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  RouteTreeManagerTest.php - Part of the router project.

  © - Jitesoft 2018-2021
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Tests\RouteTree;

use Hrafn\Router\RouteTree\Node;
use Hrafn\Router\RouteTree\RouteTreeManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;

/**
 * RouteTreeManagerTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class RouteTreeManagerTest extends TestCase {

    public function testCreateRootNode() {
        $manager    = new RouteTreeManager(new NullLogger());
        $reflection = new ReflectionClass($manager);
        $property   = $reflection->getProperty('rootNodes');

        $property->setAccessible(true);
        $this->assertEmpty($property->getValue($manager));

        $node = $manager->createOrGetRootNode('test');
        $this->assertEquals('test', $node->getPart());

        $this->assertCount(1, $property->getValue($manager));
        $this->assertEquals($node, $property->getValue($manager)[$node->getPart()]);

        $property->setAccessible(false);
    }

    public function testGetRootNode() {
        $manager    = new RouteTreeManager(new NullLogger());
        $reflection = new ReflectionClass($manager);
        $property   = $reflection->getProperty('rootNodes');
        $manager->createOrGetRootNode('root');

        $property->setAccessible(true);
        $node = $manager->createOrGetRootNode('root');

        $this->assertEquals('root', $node->getPart());
        $this->assertCount(1, $property->getValue($manager));
        $node = $manager->createOrGetRootNode('root');
        $this->assertEquals('root', $node->getPart());
        $this->assertCount(1, $property->getValue($manager));
        $property->setAccessible(false);
    }

    public function testCreateNode() {
        $parent  = new Node(null, 'root');
        $manager = new RouteTreeManager(new NullLogger());
        $child   = $manager->createOrGetNode($parent, 'part');

        $this->assertEquals($child, $parent->getChild('part'));
        $this->assertEquals($parent, $child->getParent());
        $this->assertEquals('part', $child->getPart());
    }

    public function testGetNode() {
        $parent  = new Node(null, 'root');
        $manager = new RouteTreeManager(new NullLogger());
        $child   = $manager->createOrGetNode($parent, 'part');

        $reflection = new ReflectionClass($parent);
        $property   = $reflection->getProperty('children');
        $property->setAccessible(true);
        $this->assertCount(1, $property->getValue($parent));

        $child2 = $manager->createOrGetNode($parent, 'part');

        $this->assertCount(1, $property->getValue($parent));
        $this->assertSame($child, $child2);
        $this->assertEquals($child, $parent->getChild('part'));
        $this->assertEquals($parent, $child->getParent());
        $this->assertEquals('part', $child->getPart());

        $property->setAccessible(false);
    }

}
