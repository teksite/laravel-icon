<?php

declare(strict_types=1);

namespace Teksite\IconLaravel\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Teksite\IconLaravel\Service\IconManager;
use Teksite\IconLaravel\Tests\TestCase;

/**
 * @covers \Teksite\IconLaravel\Service\IconManager
 */
class IconManagerRenderingTest extends TestCase
{
    public function test_render_false_returns_the_raw_path_only(): void
    {
        $manager = new IconManager();

        $raw = $manager->getIcon('arrow-down', type: 'outline', render: false);

        $this->assertStringContainsString('<path', $raw);
        $this->assertStringNotContainsString('<svg', $raw);
    }

    public function test_render_true_wraps_the_path_in_an_svg_element(): void
    {
        $manager = new IconManager();

        $svg = $manager->getIcon('arrow-down', attributes: ['class' => 'w-6 h-6'], type: 'outline');

        $this->assertStringContainsString('<svg xmlns="http://www.w3.org/2000/svg"', $svg);
        $this->assertStringContainsString('class="w-6 h-6 outline-icon"', $svg);
        $this->assertStringContainsString('<path', $svg);
    }

    public function test_unknown_icon_returns_an_empty_string_when_debug_is_off(): void
    {
        Config::set('app.debug', false);
        $manager = new IconManager();

        $this->assertSame('', $manager->getIcon('does-not-exist'));
    }

    public function test_unknown_icon_returns_a_visible_warning_when_debug_is_on(): void
    {
        Config::set('app.debug', true);
        $manager = new IconManager();

        $result = $manager->getIcon('does-not-exist');

        $this->assertStringContainsString('tkicon-not-found', $result);
        $this->assertStringContainsString('does-not-exist', $result);
    }

    public function test_a_path_traversal_shaped_name_is_treated_as_not_found_not_resolved(): void
    {
        Config::set('app.debug', true);
        $manager = new IconManager();

        $this->assertStringContainsString('tkicon-not-found', $manager->getIcon('../../etc/passwd'));
    }

    public function test_an_invalid_type_argument_is_rejected_safely(): void
    {
        $manager = new IconManager();

        $this->assertSame('', $manager->getIcon('arrow-down', type: 'not a valid type!!'));
    }

    public function test_has_icon_reports_existence_correctly_and_never_throws_on_garbage_input(): void
    {
        $manager = new IconManager();

        $this->assertTrue($manager->hasIcon('arrow-down', 'outline'));
        $this->assertFalse($manager->hasIcon('totally-made-up', 'outline'));
        $this->assertFalse($manager->hasIcon('bad name!', 'outline'));
    }

    public function test_get_all_without_render_returns_the_raw_grouped_map(): void
    {
        $manager = new IconManager();

        $raw = $manager->getAll(render: false);

        $this->assertArrayHasKey('arrow-down', $raw['outline']);
        $this->assertStringNotContainsString('<svg', $raw['outline']['arrow-down']);
    }

    public function test_get_all_with_render_wraps_every_icon_in_svg(): void
    {
        $manager = new IconManager();

        $rendered = $manager->getAll(render: true);

        $this->assertStringContainsString('<svg', $rendered['outline']['arrow-down']);
    }

    public function test_attribute_values_are_html_escaped(): void
    {
        $manager = new IconManager();

        $svg = $manager->getIcon('arrow-down', attributes: [
            'data-x' => '"><script>alert(1)</script>',
        ]);

        $this->assertStringNotContainsString('<script>', $svg);
        $this->assertStringContainsString('&lt;script&gt;', $svg);
    }

    public function test_boolean_true_renders_bare_and_false_or_null_attributes_are_omitted(): void
    {
        $manager = new IconManager();

        $svg = $manager->getIcon('arrow-down', attributes: [
            'aria-hidden' => true,
            'data-skip'   => false,
            'data-null'   => null,
        ]);

        $this->assertStringContainsString(' aria-hidden', $svg);
        $this->assertStringNotContainsString('data-skip', $svg);
        $this->assertStringNotContainsString('data-null', $svg);
    }

    public function test_get_icon_names_is_grouped_by_type_when_no_type_is_given(): void
    {
        $manager = new IconManager();

        $grouped = $manager->getIconNames();

        $this->assertArrayHasKey('outline', $grouped);
        $this->assertContains('arrow-down', $grouped['outline']);
    }
}
