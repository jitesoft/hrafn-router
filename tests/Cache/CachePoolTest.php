<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  CachePoolTest.php - Part of the router project.

  © - Jitesoft 2019
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Tests\Cache;

use Hrafn\Router\Cache\CacheItem;
use Hrafn\Router\Cache\FileCache;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemInterface;

class CachePoolTest extends TestCase {

    /** @var vfsStreamDirectory */
    private $fs;

    protected function setUp() {
        parent::setUp();
        $this->fs = vfsStream::setup('root', 0777, [
            'cache' => [
                'test.txt' => 'just-a-file',
                'cc' => json_encode([
                    'valid_key' => [
                        'value'   => 'abc123',
                        'expires' => 9999999999
                    ],
                    'expired_key' => [
                        'value'   => 'efg123',
                        'expires' => 0
                    ],
                    'valid_key2' => [
                        'value'   => '321cba',
                        'expires' => 9999999999
                    ]
                ])
            ]
        ]);
    }

    public function testCreateWithoutFile() {
        $fc = new FileCache($this->fs->url() . '/cache', 'hrafn.cache');
        $this->assertTrue(file_exists($this->fs->url() . '/cache/hrafn.cache'));
    }

    public function testCreateWithFile() {
        file_put_contents($this->fs->url() . '/cache/hrafn.cache', json_encode([
            'key' => ['value' => 'value1', 'expires' => 999999999],
            'key2' => ['value' => 'value2', 'expires' => 999999999]
        ]));
        $fc = new FileCache($this->fs->url(). '/cache/', 'hrafn.cache');
        $this->assertTrue($fc->hasItem('key'));
        $this->assertTrue($fc->hasItem('key2'));
        $this->assertFalse($fc->hasItem('aaaa'));
    }

    public function testGetItem() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $item = $fc->getItem('valid_key');
        $this->assertInstanceOf(CacheItemInterface::class, $item);
        $this->assertEquals('abc123', $item->get());
    }

    public function testGetItemInvalidKey() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $this->expectException(CacheException::class);
        $fc->getItem(123);
    }

    public function testGetItemNotExist() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $item = $fc->getItem('aa');
        $this->assertInstanceOf(CacheItemInterface::class, $item);
        $this->assertFalse($item->isHit());
        $this->assertNull($item->get());
    }

    public function testGetItems() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $items = $fc->getItems(['valid_key', 'valid_key2']);
        $this->assertIsArray($items);

        $this->assertNotNull($items['valid_key']);
        $this->assertNotNull($items['valid_key2']);
        $this->assertInstanceOf(CacheItemInterface::class, $items['valid_key']);
        $this->assertInstanceOf(CacheItemInterface::class, $items['valid_key2']);
    }

    public function testGetItemsInvalidKey() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $this->expectException(CacheException::class);
        $fc->getItems(['valid_key', 123123, 'valid_key2']);
    }

    public function testGetItemsNotFound() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $items = $fc->getItems(['abc', 'efg']);
        $this->assertIsArray($items);
        $this->assertFalse($items['abc']->isHit());
        $this->assertFalse($items['efg']->isHit());
    }

    public function testHasItem() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $this->assertFalse($fc->hasItem('abc123'));
        $this->assertTrue($fc->hasItem('valid_key'));
    }

    public function testHasItemInvalidKey() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $this->expectException(CacheException::class);
        $fc->hasItem(123123);
    }

    public function testClear() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $fc->clear();
        $this->assertFalse($fc->hasItem('valid_key'));
    }

    public function testDeleteItem() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $this->assertTrue($fc->hasItem('valid_key'));
        $this->assertTrue($fc->deleteItem('valid_key'));
        $this->assertFalse($fc->hasItem('valid_key'));
    }

    public function testDeleteItemInvalidKey() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $this->expectException(CacheException::class);
        $fc->deleteItem(123);
    }

    public function testDeleteItemNotFound() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $this->assertFalse($fc->deleteItem('none'));
    }

    public function testDeleteItems() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $this->assertTrue($fc->hasItem('valid_key'));
        $this->assertTrue($fc->hasItem('valid_key2'));
        $this->assertTrue($fc->deleteItems(['valid_key', 'valid_key2']));
        $this->assertFalse($fc->hasItem('valid_key'));
        $this->assertFalse($fc->hasItem('valid_key2'));
    }

    public function testDeleteItemsInvalidKey() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $this->expectException(CacheException::class);
        $fc->deleteItems(['valid_key', 123]);
    }

    public function testSave() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $fc->save(new CacheItem('test', 'test'));
        $json = json_decode(file_get_contents($this->fs->url() . '/cache/cc'), true);
        $this->assertArrayHasKey('test', $json);
        $this->assertTrue($fc->hasItem('test'));
    }

    public function testSaveDeferred() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $fc->saveDeferred(new CacheItem('test', 'test'));
        $json = json_decode(file_get_contents($this->fs->url() . '/cache/cc'), true);
        $this->assertArrayNotHasKey('test', $json);
        $this->assertTrue($fc->hasItem('test'));
    }

    public function testSaveAndCommit() {
        $fc = new FileCache($this->fs->url() . '/cache', 'cc');
        $fc->saveDeferred(new CacheItem('test', 'test'));
        $json = json_decode(file_get_contents($this->fs->url() . '/cache/cc'), true);
        $this->assertArrayNotHasKey('test', $json);
        $this->assertTrue($fc->hasItem('test'));
        $this->assertTrue($fc->commit());
        $json = json_decode(file_get_contents($this->fs->url() . '/cache/cc'), true);
        $this->assertArrayHasKey('test', $json);
        $this->assertTrue($fc->hasItem('test'));
    }

}
