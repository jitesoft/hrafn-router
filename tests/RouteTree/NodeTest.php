<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  NodeTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Hrafn\Router\Tests\RouteTree;

use Hrafn\Router\RouteTree\Node;
use PHPUnit\Framework\TestCase;

/**
 * NodeTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class NodeTest extends TestCase {

    public function testGetReference() {
        $node = new Node(null, 'test', 'abc123');
        $this->assertEquals('abc123', $node->getReference());

        $node = new Node(null, 'test');
        $this->assertNull($node->getReference());
    }

    public function testGetPart() {
        $node = new Node(null, 'test', 'abc123');
        $this->assertEquals('test', $node->getPart());
    }

    public function testGetParent() {
        $node1 = new Node(null, 'test');
        $node2 = new Node($node1, 'test2');
        $this->assertSame($node1, $node2->getParent());
    }

    public function testAddAndGetChild() {
        $node1 = new Node(null, 'test');
        $node2 = new Node(null, 'test2');
        $this->assertNull($node2->getParent());
        $this->assertEmpty($node1->getChild('test2'));

        $node1->addChild($node2);
        $this->assertSame($node1, $node2->getParent());
        $this->assertSame($node2, $node1->getChild('test2'));
        $this->assertNull($node1->getChild('nottest2'));
    }

}
