<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  CacheItemTest.php - Part of the router project.

  © - Jitesoft 2019
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Tests\Cache;

use DateInterval;
use DateTime;
use Hrafn\Router\Cache\CacheItem;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;

class CacheItemTest extends TestCase {

    public function testCreate() {
        // Mainly sanity check (and to check so that no exceptions are thrown).
        $item = new CacheItem('abc', 'abc', 0);
        $this->assertInstanceOf(CacheItemInterface::class, $item);
    }

    public function testGetKey() {
        $item = new CacheItem('abc', [], 10);
        $this->assertEquals('abc', $item->getKey());
    }

    public function testGet() {
        $item = new CacheItem('abc', 'stringstring', 100);
        $this->assertEquals('stringstring', $item->get());
    }

    public function testGetNoHit() {
        $item = new CacheItem('abc', 'abc', 0);
        $item->expiresAt((new DateTime())->setTimestamp(0));

        $this->assertNull($item->get());
    }

    public function testIsHit() {
        $item = new CacheItem('abc', 'abc', 1000);
        $this->assertTrue($item->isHit());
    }

    public function testGetTtl() {
        $item = new CacheItem('abc', 'abc', 10000);
        $s = (new DateTime())->add(new DateInterval('PT10000S'));
        $this->assertEquals($s->getTimestamp(), $item->getExpiry());
    }

    public function testSet() {
        $item = new CacheItem('abc', 'abc');
        $item->set('cba');
        $this->assertEquals('cba', $item->get());
    }

    public function testExpiresAt() {
        $dt = new DateTime('tomorrow');
        $item = new CacheItem('abc', 'efg');
        $item->expiresAt($dt);

        $this->assertEquals(
            $dt->getTimestamp(),
            $item->getExpiry(),
            ''
        );
    }

    public function testExpiresAtWithNull() {
        $dt = new DateTime('next week');
        $item = new CacheItem('abc', 'efg');
        $item->expiresAt($dt);
        $this->assertEquals(
            $dt->getTimestamp(),
            $item->getExpiry(),
            ''
        );
    }

    public function testExpiresAfterInt() {
        $item = new CacheItem('abc', 'abc');
        $item->expiresAfter(3600 * 24);
        $dt = (new DateTime('1 day'));
        $this->assertEquals(
          $dt->getTimestamp(),
          $item->getExpiry()
        );
    }

    public function testExpiresAfterNull() {
        $item = new CacheItem('abc', 'abc');
        $item->expiresAfter(null);
        $dt = (new DateTime('1 week'));
        $this->assertEquals(
            $dt->getTimestamp(),
            $item->getExpiry()
        );
    }

    public function testExpiresAfterInterval() {
        $interval = new DateInterval('P3D');
        $item = new CacheItem('abc', '123');
        $item->expiresAfter($interval);
        $this->assertEquals(
            (new DateTime('3 days'))->getTimestamp(),
            $item->getExpiry()
        );
    }
}
