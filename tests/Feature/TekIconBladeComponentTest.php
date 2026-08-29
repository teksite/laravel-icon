<?php

declare(strict_types=1);

namespace Teksite\IconLaravel\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Teksite\IconLaravel\Tests\TestCase;

/**
 * Feature tests for the <x-tkicon> Blade component. Unlike <x-icon>, it
 * always uses its own inline renderer regardless of the configured view —
 * that is the documented difference between the two components.
 *
 * @covers \Teksite\IconLaravel\Component\TekIcon
 */
class TekIconBladeComponentTest extends TestCase
{
    public function test_it_renders_a_known_icon_as_a_full_svg_element(): void
    {
        $html = Blade::render('<x-tkicon icon="arrow-down" type="outline" />');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('data-icon="arrow-down"', $html);
        $this->assertStringContainsString('outline-icon', $html);
        $this->assertStringContainsString('<path', $html);
    }

    public function test_solid_type_uses_the_solid_default_class(): void
    {
        $html = Blade::render('<x-tkicon icon="arrow-down" type="solid" />');

        $this->assertStringContainsString('solid-icon', $html);
    }

    public function test_the_title_prop_renders_a_title_element_when_provided(): void
    {
        $html = Blade::render('<x-tkicon icon="arrow-down" title="Down arrow" />');

        $this->assertStringContainsString('<title>Down arrow</title>', $html);
    }

    public function test_unknown_icon_renders_without_crashing(): void
    {
        $html = Blade::render('<x-tkicon icon="does-not-exist" />');

        $this->assertStringContainsString('<svg', $html);
    }

    public function test_extra_html_attributes_are_merged_onto_the_root_svg_element(): void
    {
        $html = Blade::render('<x-tkicon icon="arrow-down" id="my-icon" data-test="1" />');

        $this->assertStringContainsString('id="my-icon"', $html);
        $this->assertStringContainsString('data-test="1"', $html);
    }
}
