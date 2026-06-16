<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function set(string $key, $value)
    {
        Cache::set($key, $value);
    }

    public function setWithTimeOut(string $key, $value, int $timeOutInSeconds)
    {
        Cache::set($key, $value, $timeOutInSeconds);
    }

    public function get(string $key)
    {
        return Cache::get($key);
    }

    public function forget(string $key)
    {
        Cache::forget($key);
    }
}
