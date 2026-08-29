<?php

declare(strict_types=1);

namespace Teksite\IconLaravel\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Teksite\IconLaravel\Service\IconManager;
use Teksite\IconLaravel\Support\CacheManager;
use Teksite\IconLaravel\Tests\TestCase;

/**
 * These tests deliberately avoid mocking the File facade. Instead they
 * observe real, black-box behaviour: does a changed icon file get picked
 * up immediately (cache disabled) or only after the cache is cleared
 * (cache enabled)? That is what actually matters to a consumer of this
 * package, and it's far less brittle than asserting internal call counts.
 *
 * @covers \Teksite\IconLaravel\Service\IconManager
 * @covers \Teksite\IconLaravel\Support\CacheManager
 */
class IconManagerCachingTest extends TestCase
{
    public function test_with_caching_disabled_a_changed_icon_file_is_picked_up_immediately(): void
    {
        Config::set('icon-setting.cache.enabled', false);

        $file = $this->putJsonFixture('outline.json', ['v1' => '<path d="M1,1"/>']);
        Config::set('icon-setting.path.outline', $file);

        $first = new IconManager();
        $this->assertTrue($first->hasIcon('v1', 'outline'));

        File::put($file, json_encode(['v2' => '<path d="M2,2"/>']));

        $second = new IconManager();
        $this->assertTrue($second->hasIcon('v2', 'outline'), 'expected the updated file to be picked up when caching is disabled');
        $this->assertFalse($second->hasIcon('v1', 'outline'));
    }

    public function test_with_caching_enabled_a_changed_icon_file_is_ignored_until_the_cache_is_cleared(): void
    {
        Config::set('icon-setting.cache.enabled', true);
        Config::set('icon-setting.cache.key', 'svg_icons.icons');
        Config::set('icon-setting.cache.ttl', 3600);

        $file = $this->putJsonFixture('outline.json', ['v1' => '<path d="M1,1"/>']);
        Config::set('icon-setting.path.outline', $file);

        $first = new IconManager();
        $this->assertTrue($first->hasIcon('v1', 'outline'));

        File::put($file, json_encode(['v2' => '<path d="M2,2"/>']));

        $stillCached = new IconManager();
        $this->assertTrue($stillCached->hasIcon('v1', 'outline'), 'expected the stale cached list to still be served');
        $this->assertFalse($stillCached->hasIcon('v2', 'outline'));

        CacheManager::clearCache();

        $afterClear = new IconManager();
        $this->assertTrue($afterClear->hasIcon('v2', 'outline'), 'expected a fresh load after the cache was cleared');
    }
}
