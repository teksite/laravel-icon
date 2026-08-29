<?php

declare(strict_types=1);

namespace Teksite\IconLaravel\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Teksite\IconLaravel\Service\IconManager;
use Teksite\IconLaravel\Tests\TestCase;

/**
 * Feature tests exercising the package end-to-end against its real,
 * as-shipped resource files (src/resources/outline.json, solid.json) —
 * the exact files a `composer require` install ships with.
 */
class PackageResourcesTest extends TestCase
{
    private const OUTLINE_JSON = __DIR__ . '/../../src/resources/outline.json';
    private const SOLID_JSON   = __DIR__ . '/../../src/resources/solid.json';

    public function test_the_bundled_outline_json_is_valid_and_every_entry_is_a_valid_identifier(): void
    {
        $data = json_decode(file_get_contents(self::OUTLINE_JSON), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        foreach ($data as $name => $path) {
            $this->assertIsString($path, "icon '{$name}' must have a string path");
            $this->assertNotSame('', trim($path), "icon '{$name}' must not have an empty path");
            $this->assertMatchesRegularExpression(
                '/^[a-zA-Z0-9_-]+$/',
                (string) $name,
                "icon name '{$name}' must satisfy the package's own identifier pattern"
            );
        }
    }

    public function test_the_bundled_solid_json_is_valid_though_it_currently_ships_no_icons(): void
    {
        $data = json_decode(file_get_contents(self::SOLID_JSON), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data);

        if ($data === []) {
            $this->markTestIncomplete(
                'solid.json currently ships with 0 icons — type="solid" has nothing to render yet. ' .
                'This is a real, current gap in the package, not a test failure.'
            );
        }
    }

    public function test_every_bundled_outline_icon_can_be_resolved_and_rendered(): void
    {
        $manager = new IconManager();
        $names   = $manager->getIconNames('outline');

        $this->assertNotEmpty($names);

        foreach ($names as $name) {
            $this->assertTrue($manager->hasIcon($name, 'outline'), "expected hasIcon() to confirm '{$name}'");
            $this->assertStringContainsString(
                '<svg',
                $manager->getIcon($name, type: 'outline'),
                "expected '{$name}' to render as a full <svg> element"
            );
        }
    }

    public function test_a_real_bundled_icon_renders_end_to_end_through_the_tkicon_component(): void
    {
        $html = Blade::render('<x-tkicon icon="arrow-down" type="outline" title="Down arrow" />');

        $this->assertStringContainsString('<path', $html);
        $this->assertStringContainsString('<title>Down arrow</title>', $html);
    }
}
