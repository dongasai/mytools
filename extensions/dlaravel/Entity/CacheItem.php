<?php

namespace DLaravel\Entity;

use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

/**
 * CacheItem - PSR-6 标准缓存项实现
 *
 * 用于封装缓存数据和元数据
 */
class CacheItem implements CacheItemInterface
{
    public int $ttl;

    public int $create_ts;

    public int $expire_ts = 0;

    public string $key;

    public mixed $value;

    private bool $isHit;

    private ?DateTimeInterface $expires = null;

    public function __construct(string $key, mixed $value = null, int $create_ts = 0, int $ttl = 0)
    {
        $this->key = $key;
        $this->value = $value;
        if ($create_ts) {
            $this->create_ts = time();
        } else {
            $this->create_ts = $create_ts;
        }

        if ($ttl) {
            $this->expire_ts = $this->create_ts + $ttl;
            $this->ttl = $ttl;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): mixed
    {
        if (! $this->isHit()) {
            return null;
        }

        return $this->value;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * A cache hit occurs when a Calling Library requests an Item by key
     * and a matching value is found for that key, and that value has
     * not expired, and the value is not invalid for some other reason.
     *
     * Calling Libraries SHOULD make sure to verify isHit() on all get() calls.
     *
     * {@inheritdoc}
     */
    public function isHit($force = false): bool
    {
        if (isset($this->isHit) && $force === false) {
            return $this->isHit;
        }
        if ($this->expire_ts) {
            if ($this->expire_ts < time()) {
                $this->isHit = false;

                return false;
            }
        }
        $this->isHit = true;

        return true;
    }

    public function setHit($hit)
    {
        $this->isHit = $hit;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function set(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt(?\DateTimeInterface $expires): static
    {
        if ($expires instanceof DateTimeInterface && ! $expires instanceof \DateTimeImmutable) {
            $timezone = $expires->getTimezone();
            $expires = \DateTimeImmutable::createFromFormat('U', (string) $expires->getTimestamp(), $timezone);
            if ($expires) {
                $expires = $expires->setTimezone($timezone);
            }
        }

        if ($expires instanceof DateTimeInterface) {
            $this->expires = $expires;
            $this->expire_ts = $expires->getTimestamp();
        } else {
            $this->expires = null;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter(int|\DateInterval|null $time): static
    {
        if ($time === null) {
            $this->expires = null;

            return $this;
        }

        $this->expires = new \DateTimeImmutable;

        if (! $time instanceof \DateInterval) {
            $time2 = new \DateInterval(sprintf('PT%sS', abs($time)));
        }
        if ($time > 0) {
            $this->expires = $this->expires->add($time2);
        } else {
            $this->expires = $this->expires->sub($time2);
        }

        $this->expire_ts = $this->expires->getTimestamp();

        return $this;
    }

    public function getExpiresAt(): DateTimeInterface
    {
        if (! $this->expires) {
            $this->expires = new \DateTimeImmutable;
            $time = new \DateInterval(sprintf('PT%sS', $this->ttl));

            $this->expires->add($time);
        }

        return $this->expires;
    }
}