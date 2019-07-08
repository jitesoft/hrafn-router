<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  FileCache.php - Part of the router project.

  © - Jitesoft 2019
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Cache;

use Jitesoft\Exceptions\Psr\Cache\InvalidArgumentException as PsrInvalidArgument;
use Jitesoft\Utilities\DataStructures\Arrays;
use Jitesoft\Utilities\DataStructures\Maps;
use Jitesoft\Utilities\DataStructures\Maps\MapInterface;
use Jitesoft\Utilities\DataStructures\Maps\SimpleMap;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Jitesoft\Exceptions\Logic\InvalidArgumentException as InvalidArgument;

class FileCache implements CacheItemPoolInterface {
    /** @var string */
    private $filePath;
    /** @var MapInterface */
    private $cache;

    /**
     * FileCache constructor.
     *
     * @param string $cacheDir
     * @param string $fileName
     * @throws InvalidArgument
     */
    public function __construct(string $cacheDir, string $fileName) {
        $this->filePath = sprintf(
            '%s%s%s',
            rtrim($cacheDir, '/\\'),
            DIRECTORY_SEPARATOR,
            $fileName
        );

        $this->cache = new SimpleMap(
            file_exists($this->filePath)
                ? json_decode(file_get_contents($this->filePath), true)
                : []
        );

        $this->cache = Maps::map($this->cache, function ($data, $key) {
            return new CacheItem($key, $data['value'], $data['expires']);
        });

        $this->cache = Maps::filter($this->cache, function (CacheItem $item) {
            return $item->isHit();
        });

        $this->commit();
    }

    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key The key for which to return the corresponding Cache Item.
     * @throws InvalidArgumentException If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException MUST be thrown.
     * @return CacheItemInterface
     */
    public function getItem($key): CacheItemInterface {
        if (!is_string($key)) {
            throw new PsrInvalidArgument('Key was not a string.');
        }
        try {
            return $this->cache->get($key);
        } catch (InvalidArgument $e) {
            return new CacheItem($key, null, 0);
        }
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param string[]|array $keys An indexed array of keys of items to retrieve.
     * @throws InvalidArgumentException If any of the keys in $keys are not a legal value a
     *                                  \Psr\Cache\InvalidArgumentException MUST be thrown.
     * @throws InvalidArgument     Should not happen.
     * @return array|\Traversable
     *                             A traversable collection of Cache Items keyed by the cache keys of
     *                             each item. A Cache item will be returned for each key, even if that
     *                             key is not found. However, if no keys are specified then an empty
     *                             traversable MUST be returned instead.
     */
    public function getItems(array $keys = array()): array {
        if (count($keys) === 0) {
            return $this->cache->values();
        }

        $items = [];
        $out = Arrays::first($keys, function($v) {
            return !is_string($v);
        });

        if ($out !== null) {
            throw new PsrInvalidArgument('Key was not a string.');
        }

        foreach ($keys as $key) {
            $items[$key] = $this->cache->has($key)
                ? $this->cache->get($key)
                : new CacheItem($key, null, 0);
        }

        return $items;
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key The key for which to check existence.
     * @throws InvalidArgumentException If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException MUST be thrown.
     * @return boolean
     */
    public function hasItem($key): bool {
        if (!is_string($key)) {
            throw new PsrInvalidArgument('Key was not a string.');
        }

        return $this->cache->has($key);
    }

    /**
     * Deletes all items in the pool.
     *
     * @return boolean
     */
    public function clear(): bool {
        return $this->cache->clear();
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key The key to delete.
     * @throws InvalidArgumentException If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException MUST be thrown.
     * @return boolean
     */
    public function deleteItem($key): bool {
        return $this->hasItem($key) && $this->cache->unset($key);
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param string[]|array $keys An array of keys that should be removed from the pool.
     * @throws InvalidArgumentException If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException MUST be thrown.
     *
     * @return boolean
     */
    public function deleteItems(array $keys): bool {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }


    /**
     * Persists a cache item immediately.
     *
     * @param CacheItemInterface $item The cache item to save.
     * @return boolean
     */
    public function save(CacheItemInterface $item): bool {
        /** @var CacheItem $item */
        if (!file_exists($this->filePath)) {
            $currentCache = [];
        } else {
            $currentCache = json_decode(file_get_contents($this->filePath), true);
        }

        $currentCache[$item->getKey()] = [
            'value'  => $item->get(),
            'expiry' => $item->getExpiry()
        ];

        file_put_contents($this->filePath, json_encode($currentCache));
        return $this->cache->set($item->getKey(), $item);
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param CacheItemInterface $item The cache item to save.
     *
     * @return boolean
     */
    public function saveDeferred(CacheItemInterface $item): bool {
        return $this->cache->set($item->getKey(), $item);
    }

    /**
     * Persists any deferred cache items.
     *
     * @return boolean
     */
    public function commit(): bool {
        $curr  = file_exists($this->filePath)
            ? json_decode(file_get_contents($this->filePath), true)
            : [];

        foreach ($this->cache as $key => $item) {
            $curr[$key] = [
                'value'  => $item->get(),
                'expiry' => $item->getExpiry()
            ];
        }

        return file_put_contents($this->filePath, json_encode($curr));
    }

}
