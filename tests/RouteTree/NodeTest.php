<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  NodeTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Tests\RouteTree;

use Hrafn\Router\RouteTree\Node;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * NodeTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class NodeTest extends TestCase {

    public function testCreateChild() {
        $node  = new Node(null, 'root');
        $child = $node->createChild('child');

        $this->assertNotNull($child);
    }

    public function testHasChild() {
        $node = new Node(null, 'root');
        $node->createChild('child');
        $this->assertTrue($node->hasChild('child'));
        $this->assertFalse($node->hasChild('another'));
    }

    public function testAddReference() {
        $node       = new Node(null, 'root');
        $reflection = new ReflectionClass($node);
        $property   = $reflection->getProperty('references');
        $property->setAccessible(true);
        $this->assertEmpty($property->getValue($node));

        $node->addReference('POST', 'abc123');
        $this->assertCount(1, $property->getValue($node));
        $this->assertEquals('POST', $property->getValue($node)->keys()[0]);
        $property->setAccessible(false);
    }

    public function testGetReference() {
        $node = new Node(null, 'root');
        $node->addReference('GET', 'abc123');
        $this->assertEquals('abc123', $node->getReference('GET'));
        $this->assertNull($node->getReference('POST'));
    }


    public function testGetChild() {
        $node   = new Node(null, 'root');
        $child  = $node->createChild('child');
        $child2 = $node->createChild('child2');
        $child3 = $node->createChild('child3');

        $this->assertSame($child, $node->getChild('child'));
        $this->assertSame($child2, $node->getChild('child2'));
        $this->assertSame($child3, $node->getChild('child3'));
    }

    public function testGetPart() {
        $node = new Node(null, 'part');
        $this->assertEquals('part', $node->getPart());
    }

    public function testGetParent() {
        $node   = new Node(null, 'root');
        $child  = $node->createChild('child');
        $child2 = $node->createChild('child2');
        $child3 = $node->createChild('child3');

        $this->assertSame($node, $child->getParent());
        $this->assertSame($node, $child2->getParent());
        $this->assertSame($node, $child3->getParent());
    }

}
