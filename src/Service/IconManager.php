<?php

namespace Teksite\IconLaravel\Service;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class IconManager
{
    protected array $icons = [];
    protected array $config;
    protected array $iconsByType = [];

    /**
     * @throws FileNotFoundException
     */
    public function __construct()
    {
        $this->config = config('icon-setting', []);
        $this->loadIcons();
    }

    /**
     * Load and merge icons from default and custom paths
     * @throws FileNotFoundException
     */
    protected function loadIcons(): void
    {
        $cacheKey = 'svg_icons.icons';

        if ($this->isCacheEnabled() && Cache::has($cacheKey)) {
            $this->iconsByType = Cache::get($cacheKey);
            return;
        }

        $defaultOutlineIcons = $this->loadDefaultIcons('outline');
        $customOutlineIcons = $this->loadCustomIcons('outline');
        $outlineIcons = array_merge($defaultOutlineIcons, $customOutlineIcons);

        $defaultSolidIcons = $this->loadDefaultIcons('solid');
        $customSolidIcons = $this->loadCustomIcons('solid');
        $solidIcons = array_merge($defaultSolidIcons, $customSolidIcons);

        $this->iconsByType = [
            'outline' => $outlineIcons,
            'solid' => $solidIcons
        ];

        if ($this->isCacheEnabled()) {
            Cache::put($cacheKey, $this->iconsByType, $this->getCacheTTL());
        }
    }

    /**
     * Load default icons from package
     * @throws FileNotFoundException
     */
    protected function loadDefaultIcons(string $type = 'outline'): array
    {
        $defaultPath = app('icon.default.path');

        if ($type === 'solid') {
            $solidFile = $defaultPath . "solid.json";
            if (File::exists($solidFile)) {
                $solidContent = File::get($solidFile);
                $icons = json_decode($solidContent, true);
                return is_array($icons) ? $icons : [];
            }
        }

        if ($type === 'outline') {
            $outlineFile = $defaultPath . "outline.json";
            if (File::exists($outlineFile)) {
                $outlineContent = File::get($outlineFile);
                $icons = json_decode($outlineContent, true);
                return is_array($icons) ? $icons : [];
            }
        }

        return [];
    }

    /**
     * Load custom icons from storage path
     * @throws FileNotFoundException
     */
    protected function loadCustomIcons(string $type = 'outline'): array
    {
        if ($type === 'solid') {
            $customSolidPath = $this->config['custom_solid_icon'] ?? storage_path('app/svg-icons/solid.json');
            if (File::exists($customSolidPath)) {
                $solidContent = File::get($customSolidPath);
                $icons = json_decode($solidContent, true);
                return is_array($icons) ? $icons : [];
            }
        }

        if ($type === 'outline') {
            $customOutlinePath = $this->config['custom_outline_icon'] ?? storage_path('app/svg-icons/outline.json');
            if (File::exists($customOutlinePath)) {
                $outlineContent = File::get($customOutlinePath);
                $icons = json_decode($outlineContent, true);
                return is_array($icons) ? $icons : [];
            }
        }

        return [];
    }

    /**
     * Get an icon by name
     */
    public function getIcon(string $name, array $attributes = [], string $type = 'outline', $render = false): string
    {
        if (!isset($this->iconsByType[$type])) {
            return $this->renderNotFoundIcon($name, $attributes);
        }

        $icons = $this->iconsByType[$type];

        if (!isset($icons[$name])) {
            return $this->renderNotFoundIcon($name, $attributes);
        }

        $path = $icons[$name];
        unset($attributes['iconManager']);

        return $render
            ? $this->renderSvg($path, $attributes, $type)
            : $path;
    }

    /**
     * Render SVG element
     */
    protected function renderSvg(string $path, array $attributes, string $type = 'outline'): string
    {
        $attr = '';

        // اضافه کردن کلاس پیش‌فرض براساس type
        if (!isset($attributes['class'])) {
            $defaultClass = $type === 'solid' ? 'icon-solid' : 'icon-outline';
            $attributes['class'] = $defaultClass;
        }

        foreach ($attributes as $key => $value) {
            if (strlen(trim($value)) === 0) continue;
            $attr .= $key . '="' . trim($value) . '" ';
        }

        return "<svg xmlns='http://www.w3.org/2000/svg' $attr>$path</svg>";
    }

    /**
     * Render a "not found" icon for debugging
     */
    protected function renderNotFoundIcon(string $name, array $attributes): string
    {
        if (config('app.debug', false)) {
            return sprintf(
                '<span class="text-red-500" title="Icon \'%s\' not found">⚠️</span>',
                htmlspecialchars($name)
            );
        }

        return '';
    }

    /**
     * Get all available icon names for a specific type
     */
    public function getIconNames(string $type = 'outline'): array
    {
        if (!isset($this->iconsByType[$type])) {
            return [];
        }

        return array_keys($this->iconsByType[$type]);
    }

    /**
     * Check if an icon exists for a specific type
     */
    public function hasIcon(string $name, string $type = 'outline'): bool
    {
        if (!isset($this->iconsByType[$type])) {
            return false;
        }

        return isset($this->iconsByType[$type][$name]);
    }

    /**
     * Clear the icon cache
     * @throws FileNotFoundException
     */
    public function clearCache(): void
    {
        Cache::forget('svg_icons.icons');
        $this->loadIcons();
    }

    /**
     * Reload icons from files
     * @throws FileNotFoundException
     */
    public function reload(): void
    {
        $this->clearCache();
    }

    protected function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? true;
    }

    protected function getCacheTTL(): int
    {
        return $this->config['cache']['ttl'] ?? 86400;
    }
}
