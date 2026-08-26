<?php

namespace Teksite\IconLaravel\Service;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Teksite\IconLaravel\Support\CacheManager;

class IconManager
{
    private const string IDENTIFIER_PATTERN = '/^[a-zA-Z0-9_-]+$/';

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
     * Load icons from configured paths or package defaults.
     *
     * @throws FileNotFoundException
     */
    protected function loadIcons(): void
    {
        if (!CacheManager::isCacheEnabled()) {
            $this->getIconList();
            return;
        }

        $this->iconsByType = Cache::remember(CacheManager::cacheKey(), CacheManager::getCacheTTL(), function (): array {
            $this->getIconList();
            return $this->iconsByType;
        });
    }

    /**
     * Load icon lists from configured paths.
     *
     * Custom paths have priority over package default resources.
     *
     * @throws FileNotFoundException
     */
    protected function getIconList(): void
    {
        $paths = $this->config['path'] ?? [];

        if (empty($paths)) return;

        foreach ($paths as $type => $path) {
            if (!$this->isValidIdentifier($type)) continue;

            if (!is_string($path) || trim($path) === '') continue;

            $filePath = $this->resolveIconFilePath($type, $path);

            if ($filePath === null) continue;

            $icons = $this->loadJsonFile($filePath);

            if ($icons === null) continue;

            $this->iconsByType[$type] = $this->filterIcons($icons);
        }
    }

    /**
     * Resolve the icon JSON file.
     *
     * Custom configured paths take priority over package resources.
     */
    protected function resolveIconFilePath(string $type, string $customPath): ?string
    {
        $customPath = trim($customPath);

        if (File::exists($customPath)) return $customPath;

        $defaultPath = __DIR__ . "/../resources/{$type}.json";

        if (File::exists($defaultPath)) return $defaultPath;

        return null;
    }

    /**
     * Load and decode an icon JSON file.
     *
     * @throws FileNotFoundException
     */
    protected function loadJsonFile(string $filePath): ?array
    {
        try {
            $content = File::get($filePath);
            $icons = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        } catch (\JsonException $exception) {
            Log::error('Failed to decode icon JSON.', [
                'path'  => $filePath,
                'error' => $exception->getMessage(),
            ]);
            return null;
        }

        if (!is_array($icons)) {
            Log::warning('Icon JSON must contain an object/array.', [
                'path' => $filePath,
            ]);
            return null;
        }
        return $icons;
    }

    /**
     * Keep only valid icon definitions.
     */
    protected function filterIcons(array $icons): array
    {
        $filtered = [];

        foreach ($icons as $name => $path) {
            if (!is_string($name) || !$this->isValidIdentifier($name) || !is_string($path) || trim($path) === '') {
                continue;
            }
            $filtered[$name] = $path;
        }
        return $filtered;
    }

    /**
     * Get an icon by name.
     */
    public function getIcon(string $name, array $attributes = [], string $type = 'outline', bool $render = true): string
    {
        if (!$this->isValidIdentifier($name) || !$this->isValidIdentifier($type)) {
            return $this->renderNotFoundIcon($name);
        }

        $iconPath = $this->iconsByType[$type][$name] ?? null;

        if ($iconPath === null) return $this->renderNotFoundIcon($name);


        return $render
            ? $this->renderSvg($iconPath, $attributes, $type)
            : $iconPath;
    }

    /**
     * Get all available icons.
     */
    public function getAll(bool $render = true): array
    {
        if (!$render) return $this->iconsByType;

        $icons = [];

        foreach ($this->iconsByType as $type => $iconGroup) {
            foreach ($iconGroup as $name => $path) {
                $icons[$type][$name] = $this->renderSvg($path, [], $type);
            }
        }
        return $icons;
    }

    /**
     * Render an SVG element.
     */
    protected function renderSvg(string $path, array $attributes, string $type): string
    {
        $defaultClass = "{$type}-icon";

        $attributes['class'] = trim(($attributes['class'] ?? '') . ' ' . $defaultClass);

        $attributeString = $this->renderAttributes($attributes);

        return "<svg xmlns=\"http://www.w3.org/2000/svg\" {$attributeString}>{$path}</svg>";
    }

    /**
     * Render HTML attributes safely.
     */
    protected function renderAttributes(array $attributes): string
    {
        $output = '';

        foreach ($attributes as $key => $value) {
            if (!is_string($key) || trim($key) === '') continue;

            if ($value === null || $value === false) continue;

            $key = htmlspecialchars(trim($key), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            if ($value === true) {
                $output .= " {$key}";
                continue;
            }

            if (!is_scalar($value)) continue;

            $value = trim((string)$value);

            if ($value === '') continue;

            $value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $output .= " {$key}=\"{$value}\"";
        }

        return $output;
    }

    /**
     * Render a not-found icon for debugging.
     */
    protected function renderNotFoundIcon(string $name): string
    {
        if (!config('app.debug', false)) return '';

        $name = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf('<span class="tkicon-not-found" title="Icon \'%s\' not found">⚠️</span>', $name);
    }

    /**
     * Get all available icon names.
     */
    public function getIconNames(?string $type = null): array
    {
        if ($type !== null)  return array_keys($this->iconsByType[$type] ?? []);

        return array_map(
            static fn(array $icons): array => array_keys($icons),
            $this->iconsByType
        );
    }

    /**
     * Check whether an icon exists.
     */
    public function hasIcon(string $name, string $type = 'outline'): bool
    {
        if (!$this->isValidIdentifier($name) || !$this->isValidIdentifier($type))   return false;

        return isset($this->iconsByType[$type][$name]);
    }

    /**
     * Check whether a value is a valid icon identifier.
     *
     * This does not whitelist icon types.
     * Users can still define custom types dynamically.
     */
    protected function isValidIdentifier(string $value): bool
    {
        return preg_match(self::IDENTIFIER_PATTERN, $value) === 1;
    }
}
