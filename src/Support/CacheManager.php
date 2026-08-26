<?php

namespace Teksite\IconLaravel\Support;

use Illuminate\Support\Facades\Cache;

class CacheManager {

    public static function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? true;
    }

    public static function cacheKey(): string
    {
        return $this->config['cache']['key'] ?? 'svg_icons.icons';
    }

    public static function getCacheTTL(): int
    {
        return $this->config['cache']['ttl'] ?? 86400;
    }

    public static function clearCache(): void
    {
        Cache::forget($this->cacheKey());
        $this->loadIcons();
    }

    public static function reload(): void
    {
        $this->clearCache();
    }

}
