<?php

declare(strict_types=1);

namespace Teksite\IconLaravel\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Teksite\IconLaravel\Service\IconManager;
use Teksite\IconLaravel\Tests\TestCase;

/**
 * @covers \Teksite\IconLaravel\Service\IconManager
 */
class IconManagerLoadingTest extends TestCase
{
    public function test_it_falls_back_to_the_bundled_outline_icons_when_no_custom_path_exists(): void
    {
        Config::set('icon-setting.path.outline', '/this/path/does/not/exist.json');

        $manager = new IconManager();

        $names = $manager->getIconNames('outline');

        $this->assertNotEmpty($names);
        $this->assertContains('arrow-down', $names);
    }

    public function test_a_custom_configured_path_takes_priority_over_the_bundled_resource(): void
    {
        $customFile = $this->putJsonFixture('outline.json', ['only-mine' => '<path d="M0,0"/>']);
        Config::set('icon-setting.path.outline', $customFile);

        $manager = new IconManager();

        $this->assertTrue($manager->hasIcon('only-mine', 'outline'));
        $this->assertFalse(
            $manager->hasIcon('arrow-down', 'outline'),
            'bundled icons must not leak in once a custom path fully replaces the list'
        );
    }

    public function test_malformed_json_is_logged_and_that_icon_type_becomes_empty_without_throwing(): void
    {
        Log::spy();

        $badFile = $this->putJsonFixture('broken.json', '{ this is not valid json');
        Config::set('icon-setting.path.outline', $badFile);

        $manager = new IconManager();

        $this->assertSame([], $manager->getIconNames('outline'));
        Log::shouldHaveReceived('error')->once();
    }

    public function test_a_type_key_with_invalid_characters_is_ignored_entirely(): void
    {
        $file = $this->putJsonFixture('weird.json', ['x' => '<path/>']);
        Config::set('icon-setting.path', ['bad type!' => $file]);

        $manager = new IconManager();

        $this->assertArrayNotHasKey('bad type!', $manager->getIconNames());
    }

    public function test_entries_with_invalid_names_or_empty_paths_are_filtered_out(): void
    {
        $file = $this->putJsonFixture('mixed.json', [
            'good-one'   => '<path d="M1,1"/>',
            'bad name!'  => '<path d="M2,2"/>', // fails the identifier pattern
            'empty-path' => '',                  // empty string
            'array-path' => ['not', 'a', 'string'],
            'null-path'  => null,
        ]);
        Config::set('icon-setting.path.outline', $file);

        $manager = new IconManager();

        $this->assertSame(['good-one'], $manager->getIconNames('outline'));
    }

    public function test_an_empty_path_configuration_loads_no_icon_types_at_all(): void
    {
        Config::set('icon-setting.path', []);

        $manager = new IconManager();

        $this->assertSame([], $manager->getAll(render: false));
    }
}
