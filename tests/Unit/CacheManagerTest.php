<?php

declare(strict_types=1);

namespace Teksite\IconLaravel\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Teksite\IconLaravel\Support\CacheManager;
use Teksite\IconLaravel\Tests\TestCase;

/**
 * @covers \Teksite\IconLaravel\Support\CacheManager
 */
class CacheManagerTest extends TestCase
{
    public function test_is_cache_enabled_reflects_the_config_value(): void
    {
        Config::set('icon-setting.cache.enabled', true);
        $this->assertTrue(CacheManager::isCacheEnabled());

        Config::set('icon-setting.cache.enabled', false);
        $this->assertFalse(CacheManager::isCacheEnabled());
    }

    public function test_is_cache_enabled_defaults_to_false_when_unset(): void
    {
        Config::set('icon-setting.cache', []);

        $this->assertFalse(CacheManager::isCacheEnabled());
    }

    public function test_cache_key_reflects_config_or_falls_back_to_the_documented_default(): void
    {
        Config::set('icon-setting.cache.key', 'my.custom.key');
        $this->assertSame('my.custom.key', CacheManager::cacheKey());

        Config::set('icon-setting.cache', []);
        $this->assertSame('svg_icons.icons', CacheManager::cacheKey());
    }

    public function test_cache_ttl_reflects_config_or_falls_back_to_the_documented_default(): void
    {
        Config::set('icon-setting.cache.ttl', 123);
        $this->assertSame(123, CacheManager::getCacheTTL());

        Config::set('icon-setting.cache', []);
        $this->assertSame(2592000, CacheManager::getCacheTTL());
    }

    public function test_clear_cache_forgets_only_the_configured_key(): void
    {
        Config::set('icon-setting.cache.key', 'svg_icons.icons');

        Cache::put('svg_icons.icons', ['fake' => 'data'], 60);
        Cache::put('some.other.key', ['untouched' => true], 60);

        CacheManager::clearCache();

        $this->assertFalse(Cache::has('svg_icons.icons'));
        $this->assertTrue(Cache::has('some.other.key'));
    }
}
