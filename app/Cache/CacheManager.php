<?php

namespace App\Cache;
use Illuminate\Cache\CacheManager as LaravelCacheManager;
use Illuminate\Contracts\Cache\Repository;

class CacheManager extends LaravelCacheManager
{
    protected function createRedisDriver(array $config): Repository
    {
        try {
            return parent::createRedisDriver($config);
        } catch (\Exception) {
            return $this->resolve('file');
        }
    }
}