<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  CacheItem.php - Part of the router project.

  © - Jitesoft 2019
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Cache;

use DateInterval;
use DateTime;
use Exception;
use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface {
    private $key;
    private $data;
    private $expires;

    /**
     * CacheItem constructor.
     *
     * @param string  $key    Key the cache item uses.
     * @param mixed   $data   Data to store in the cache item.
     * @param integer $expiry Unix timestamp of expiry.
     * @throws Exception On date interval errors.
     */
    public function __construct(string $key, $data, int $expiry = 3600 * 24 * 7) {
        $this->key   = $key;
        $this->data  = $data;

        $this->expires = (new DateTime())->add(new DateInterval('PT' . $expiry . 'S'));
    }

    /**
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     */
    public function getKey(): string {
        return $this->key;
    }

    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     *
     * The value returned must be identical to the value originally stored by set().
     *
     * If isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return mixed
     */
    public function get() {
        return $this->isHit() ? $this->data : null;
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return boolean
     */
    public function isHit(): bool {
        return $this->expires->getTimestamp() > (new DateTime())->getTimestamp();
    }

    /**
     * Get time to live.
     *
     * @return integer
     */
    public function getExpiry(): int {
        return $this->expires->getTimestamp();
    }

    /**
     * Sets the value represented by this cache item.
     *
     * The $value argument may be any item that can be serialized by PHP,
     * although the method of serialization is left up to the Implementing
     * Library.
     *
     * @param mixed $value The serializable value to be stored.
     * @return static
     */
    public function set($value): CacheItemInterface {
        $this->data = $value;
        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param \DateTimeInterface|null $expiration
     *   The point in time after which the item MUST be considered expired.
     *   If null is passed explicitly, a default value MAY be used. If none is set,
     *   the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     */
    public function expiresAt($expiration): self {
        $this->expires = $expiration ?? new DateTime('next week');
        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param int|DateInterval|null $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     * @throws Exception On DateInterval failure.
     */
    public function expiresAfter($time): self {
        if ($time === null) {
            $time = new DateInterval('P7D');
        } else if (is_int($time)) {
            $time = new DateInterval(sprintf('PT%dS', $time));
        }
        $this->expires = (new DateTime())->add($time);

        return $this;
    }

}
