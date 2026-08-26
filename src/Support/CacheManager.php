<?php

namespace Teksite\IconLaravel\Support;

use Illuminate\Support\Facades\Cache;

class CacheManager
{

    public static array $cacheSetting = [];


    public static function isCacheEnabled(): bool
    {
        return self::getSetting('enabled', false);
    }

    public static function cacheKey(): string
    {
        return self::getSetting('key' ,'svg_icons.icons');
    }

    public static function getCacheTTL(): int
    {
        return self::getSetting('ttl' ,86400);
    }


    private static function getSetting(string|null $key, mixed $default = null) :mixed
    {
        $config = config('icon-setting.cache', []);
        if (is_null($key)) return $config;
        return isset($config[$key]) ? $config[$key] : $default;

    }

    public static function clearCache(): void
    {
        Cache::forget(self::cacheKey());
    }
}
