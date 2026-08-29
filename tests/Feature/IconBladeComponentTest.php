<?php

declare(strict_types=1);

namespace Teksite\IconLaravel\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Teksite\IconLaravel\Tests\TestCase;

/**
 * Feature tests for the <x-icon> Blade component, rendered end-to-end
 * through the real Blade compiler via Blade::render() — no fake templates,
 * no mocked component internals.
 *
 * @covers \Teksite\IconLaravel\Component\Icon
 */
class IconBladeComponentTest extends TestCase
{
    public function test_it_renders_a_known_icon_as_a_full_svg_element(): void
    {
        $html = Blade::render('<x-icon icon="arrow-down" type="outline" class="w-6 h-6" />');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('data-icon="arrow-down"', $html);
        $this->assertStringContainsString('outline-icon', $html);
        $this->assertStringContainsString('w-6 h-6', $html);
        $this->assertStringContainsString('<path', $html);
    }

    public function test_it_falls_back_to_the_inline_template_and_logs_when_the_configured_view_is_missing(): void
    {
        Log::spy();

        $html = Blade::render('<x-icon icon="arrow-down" />');

        Log::shouldHaveReceived('error')->once();
        $this->assertStringContainsString('<svg', $html);
    }

    public function test_it_uses_a_configured_custom_component_view_when_it_exists(): void
    {
        $this->app['view']->addLocation($this->fixturePath);
        File::put($this->fixturePath . '/custom-icon.blade.php', '<i class="my-custom-icon">{!! $path !!}</i>');

        Config::set('icon-setting.component', 'custom-icon');

        $html = Blade::render('<x-icon icon="arrow-down" />');

        $this->assertStringContainsString('my-custom-icon', $html);
        $this->assertStringContainsString('<path', $html);
    }

    public function test_no_error_is_logged_when_the_custom_view_exists(): void
    {
        $this->app['view']->addLocation($this->fixturePath);
        File::put($this->fixturePath . '/custom-icon.blade.php', '{!! $path !!}');
        Config::set('icon-setting.component', 'custom-icon');

        Log::spy();

        Blade::render('<x-icon icon="arrow-down" />');

        Log::shouldNotHaveReceived('error');
    }

    public function test_unknown_icon_renders_nothing_visible_when_debug_is_off(): void
    {
        Config::set('app.debug', false);

        $html = Blade::render('<x-icon icon="does-not-exist" />');

        $this->assertStringNotContainsString('tkicon-not-found', $html);
    }

    public function test_unknown_icon_shows_a_warning_when_debug_is_on(): void
    {
        Config::set('app.debug', true);

        $html = Blade::render('<x-icon icon="does-not-exist" />');

        $this->assertStringContainsString('tkicon-not-found', $html);
    }

    public function test_the_title_prop_renders_a_title_element_when_provided(): void
    {
        $html = Blade::render('<x-icon icon="arrow-down" title="Down arrow" />');

        $this->assertStringContainsString('<title>Down arrow</title>', $html);
    }

    public function test_the_title_element_is_absent_when_no_title_is_given(): void
    {
        $html = Blade::render('<x-icon icon="arrow-down" />');

        $this->assertStringNotContainsString('<title>', $html);
    }
}
