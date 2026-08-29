<?php

declare(strict_types=1);

namespace Teksite\IconLaravel\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Teksite\IconLaravel\IconLaravelServiceProvider;
use Teksite\IconLaravel\Service\IconManager;
use Teksite\IconLaravel\Tests\TestCase;

/**
 * Feature tests exercising IconLaravelServiceProvider exactly the way
 * Laravel drives it: container binding resolution, Blade component
 * registration, and the standard `Illuminate\Support\ServiceProvider::
 * pathsToPublish()` API for publishable assets — plus an actual
 * `vendor:publish` artisan run.
 *
 * @covers \Teksite\IconLaravel\IconLaravelServiceProvider
 */
class ServiceProviderTest extends TestCase
{
    public function test_the_package_default_config_is_merged_when_nothing_is_published(): void
    {
        $this->assertSame('components.icon', config('icon-setting.component'));
        $this->assertFalse(config('icon-setting.cache.enabled'));
        $this->assertSame(2592000, config('icon-setting.cache.ttl'));
        $this->assertSame('svg_icons.icons', config('icon-setting.cache.key'));
    }

    public function test_icon_manager_is_registered_as_a_shared_singleton(): void
    {
        $first  = $this->app->make(IconManager::class);
        $second = $this->app->make(IconManager::class);

        $this->assertInstanceOf(IconManager::class, $first);
        $this->assertSame($first, $second);
    }

    public function test_the_default_icon_resource_path_binding_points_at_a_real_directory(): void
    {
        $path = $this->app->make('icon.default.path');

        $this->assertDirectoryExists(rtrim($path, DIRECTORY_SEPARATOR));
        $this->assertFileExists($path . 'outline.json');
    }

    public function test_the_icon_and_tkicon_tags_are_both_usable_after_boot(): void
    {
        $iconHtml   = Blade::render('<x-icon icon="arrow-down" />');
        $tkiconHtml = Blade::render('<x-tkicon icon="arrow-down" />');

        $this->assertStringContainsString('<path', $iconHtml);
        $this->assertStringContainsString('<path', $tkiconHtml);
    }

    public function test_the_config_file_is_publishable_under_the_icon_setting_tag(): void
    {
        $paths = ServiceProvider::pathsToPublish(IconLaravelServiceProvider::class, 'icon-setting');

        $this->assertContains(config_path('icon-setting.php'), array_values($paths));
    }

    public function test_the_outline_and_solid_json_files_are_publishable_under_their_own_tags(): void
    {
        $picker  = ServiceProvider::pathsToPublish(IconLaravelServiceProvider::class, 'icons-picker');

        $this->assertContains(public_path('vendor/icons/icon-picker.js'), array_values($picker));
    }

    public function test_every_publishable_asset_is_also_tagged_under_the_shared_icon_group(): void
    {
        $umbrella = ServiceProvider::pathsToPublish(IconLaravelServiceProvider::class, 'icon');

        $this->assertCount(4, $umbrella, 'expected config + outline + solid + picker under the shared "icon" tag');
    }

    public function test_vendor_publish_with_the_icon_setting_tag_actually_copies_the_config_file(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'icon-setting', '--force' => true])
            ->assertExitCode(0);

        $this->assertFileExists(config_path('icon-setting.php'));

        $published = require config_path('icon-setting.php');

        $this->assertSame('components.icon', $published['component']);
    }
}
