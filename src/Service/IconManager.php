<?php

namespace Teksite\IconLaravel\Service;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

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
     *
     * @throws FileNotFoundException
     */
    protected function loadIcons(): void
    {
        $cacheKey = $this->cacheKey();

        if ($this->isCacheEnabled() && Cache::has($cacheKey)) {
            $this->iconsByType = Cache::get($cacheKey);
            return;
        }

        $this->loadDefaultIcons();

        $this->loadCustomIcons();

        if ($this->isCacheEnabled()) {
            Cache::put($cacheKey, $this->iconsByType, $this->getCacheTTL());
        }
    }

    /**
     * Load default icons from package
     *
     * @throws FileNotFoundException
     */
    protected function loadDefaultIcons(): void
    {
        $defaultPath = app('icon.default.path');

        foreach (['solid', 'outline'] as $type) {

            $filePath = $defaultPath . "$type.json";

            if (File::exists($filePath)) {
                $fileContent = File::get($filePath);
                $icons = json_decode($fileContent, true);
                $arrayIcon = is_array($icons) ? $icons : [];

                if (isset($arrayIcon[$type])) {
                    $this->iconsByType[$type][] = $arrayIcon;
                } else {
                    $this->iconsByType[$type] = $arrayIcon;
                }
            }
        }
    }

    /**
     * Load custom icons from storage path
     *
     * @throws FileNotFoundException
     */
    protected function loadCustomIcons(): void
    {
        $customs = $this->config['custom_icon'] ?? [];

        foreach ($customs as $type => $path) {
            if (File::exists($path)) {
                $fileContent = File::get($path);
                $icons = json_decode($fileContent, true);
                $arrayIcon = is_array($icons) ? $icons : [];
                if ($this->iconsByType[$type]) {

                    $this->iconsByType[$type][] = $arrayIcon;
                } else {

                    $this->iconsByType[$type] = $arrayIcon;
                }
            }
        }
    }

    /**
     * Get an icon by name
     */
    public function getIcon(string $name, array $attributes = [], string $type = 'outline', $render = false): string
    {

        $iconPath = $this->iconsByType[$type][$name] ?? null;

        if (!$iconPath) return $this->renderNotFoundIcon($name, $attributes);

        return $render
            ? $this->renderSvg($iconPath, $attributes, $type)
            : $iconPath;
    }

    /**
     * Render SVG element
     */
    protected function renderSvg(string $path, array $attributes, string $type = 'outline'): string
    {
        $attr = '';

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
    public function getIconNames(string $type): array
    {
        if (!isset($this->iconsByType[$type])) {
            return [];
        }

        return array_keys($this->iconsByType[$type]);
    }

    /**
     * Check if an icon exists for a specific type
     */
    public function hasIcon(string $name, string $type): bool
    {
        return isset(($this->iconsByType)[$type][$name]);
    }





    protected function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? true;
    }

    protected function cacheKey(): bool
    {
        return $this->config['cache']['key'] ?? 'svg_icons.icons';
    }

    protected function getCacheTTL(): int
    {
        return $this->config['cache']['ttl'] ?? 86400;
    }

    public function clearCache(): void
    {
        Cache::forget($this->cacheKey());
        $this->loadIcons();
    }

    public function reload(): void
    {
        $this->clearCache();
    }

}
