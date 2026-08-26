<?php

namespace Teksite\IconLaravel\Service;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Teksite\IconLaravel\Support\CacheManager;

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
        if (!CacheManager::isCacheEnabled()) {
            $this->getIconList();
            return;
        }

        $cacheKey = CacheManager::cacheKey();

        Cache::remember($cacheKey , CacheManager::getCacheTTL(), function () {
            $this->getIconList();
            return $this->iconsByType;
        });
    }

    /**
     * Load default icons from package
     *
     * @throws FileNotFoundException
     */
    protected function getIconList(): void
    {
        $paths = $this->config['path'] ?? [];

        foreach ($paths ?? [] as $type => $path) {
            if (!File::exists($path)) {
                $path = __DIR__ . "/../resources/$type.json";
                if (!File::exists($path)) continue;
            }
            $fileContent = File::get($path);
            $icons = json_decode($fileContent, true);
            $arrayIcon = is_array($icons) ? $icons : [];

            $this->iconsByType[$type] = $arrayIcon;

        }
    }


    /**
     * Get an icon by name
     */
    public function getIcon(string $name, array $attributes = [], string $type = 'outline', bool $render = true): string
    {

        $iconPath = $this->iconsByType[$type][$name] ?? null;

        if (is_null($iconPath)) return $this->renderNotFoundIcon($name, $attributes);

        return $render
            ? $this->renderSvg($iconPath, $attributes, $type)
            : $iconPath;
    }

    public function getAll(bool $render = true): array
    {
        $iconsGroup = $this->iconsByType;

        $icon = [];
        foreach ($iconsGroup as $iconType => $icons) {
            foreach ($icons as $name => $path) {
                $icon[$iconType][$name] = $render ? $this->renderSvg($path, [], $iconType) : $path;
            }
        }
        return $icon;
    }

    /**
     * Render SVG element
     */
    protected function renderSvg(string $path, array $attributes, string $type): string
    {
        $attr = '';
        $defaultClass = "$type-icon";
        $attributes['class'] = ($attributes['class'] ?? '') . ' ' . $defaultClass;


        foreach ($attributes as $key => $value) {
            if (!is_string($value) || strlen(trim($value)) === 0) continue;
            $value = htmlspecialchars(trim($value));
            $key = htmlspecialchars(trim($key));
            $attr .= " $key='" . $value . "'";
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
    public function getIconNames(?string $type = null): array
    {
        $groups = $this->iconsByType;

        if ($type) return array_keys($this->iconsByType[$type] ?? []);

        return array_map(function ($icons) {
            return array_keys($icons);
        }, $groups);
    }

    /**
     * Check if an icon exists for a specific type
     */
    public function hasIcon(string $name, string $type): bool
    {
        return isset(($this->iconsByType)[$type][$name]);
    }


}
