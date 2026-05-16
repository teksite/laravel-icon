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

    /**
     * @throws FileNotFoundException
     */
    public function __construct()
    {
        $this->config = config('icon', []);
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
            $this->icons = Cache::get($cacheKey);
            return;
        }

        $defaultIcons = $this->loadDefaultIcons();
        $customIcons = $this->loadCustomIcons();

        // Merge icons (custom icons override default icons with same name)
        $this->icons = array_merge($defaultIcons, $customIcons);
        if ($this->isCacheEnabled()) {
            Cache::put($cacheKey, $this->icons, $this->getCacheTTL());
        }
    }

    /**
     * Load default icons from package
     */
    protected function loadDefaultIcons(): array
    {
        $defaults = [];
        $defaultPath = app('icon.default.path');
        $outlineFile= $defaultPath."outline.json";
        $solidFile= $defaultPath."solid.json";
        if (!File::exists($outlineFile)) {
            $solidList=  json_decode($outlineFile, true);
            if (is_array($solidList)) {
                $defaults = array_merge($defaults, $solidList);
            }
        }

        if (!File::exists($solidFile)) {
            $solidList=  json_decode($solidFile, true);
            if (is_array($solidList)) {
                $defaults = array_merge($defaults, $solidList);
            }
        }

        return $defaults;
    }

    /**
     * Load custom icons from storage path
     * @throws FileNotFoundException
     */
    protected function loadCustomIcons(): array
    {
        $customIcons = [];
        $customSolidPath = $this->config['custom_solid_icon'] ?? storage_path('app/svg-icons/solid.json');
        $customOutlinePath = $this->config['custom_outline_icon'] ?? storage_path('app/svg-icons/outline.json');

        if (File::exists($customSolidPath)) {
            $solidContent = File::get($customSolidPath);
            $solidData = json_decode($solidContent, true) ?? [];
            if (is_array($solidData)) {
                $customIcons = array_merge($customIcons, $solidData);
            }
        }
        if (File::exists($customOutlinePath)) {
            $outlineContent[] = File::get($customOutlinePath);
            $outlineData = json_decode($customOutlinePath, true);
            if (is_array($outlineData)) {
                $customIcons = array_merge($customIcons, $outlineData);
            }
        }
        return $customIcons;
    }

    /**
     * Get an icon by name
     */
    public function getIcon(string $name, array $attributes = [], $render = false): string
    {
        if (!isset($this->icons[$name])) {
            return $this->renderNotFoundIcon($name, $attributes);
        }


        $path = $this->icons[$name];

        unset($attributes['iconManager']);
        return $render
            ? $this->renderSvg($path, $attributes)
            : $path;
    }

    /**
     * Render SVG element
     */
    protected function renderSvg(string $path, array $attributes): string
    {

        $attr = '';

        foreach ($attributes as $key => $value) {
            if (strlen(trim($value)) === 0) continue;
            $attr .= $key . '="' . trim($value) . '" ';
        }
        // Add any additional attributes (except special ones we already handled)


        return
            "<svg xmlns='http://www.w3.org/2000/svg' $attr>$path</svg>";

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
     * Get all available icon names
     */
    public function getIconNames(): array
    {
        return array_keys($this->icons);
    }

    /**
     * Check if an icon exists
     */
    public function hasIcon(string $name): bool
    {
        return isset($this->icons[$name]);
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
