<?php

namespace Modules\ABase\Contracts;

use Psr\Cache\CacheItemInterface;

interface ItemInterface
{
    public function get();

    public function update($data, $ttl = 60): CacheItemInterface;
}
