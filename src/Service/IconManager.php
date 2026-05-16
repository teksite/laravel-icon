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
        $this->config = config('icon' ,[]);
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
     * @throws FileNotFoundException
     */
    protected function loadDefaultIcons(): array
    {
        $defaultPath = app('icon.default.list');
        if (!File::exists($defaultPath)) {
            return [];
        }

        $content = File::get($defaultPath);
        return json_decode($content, true) ?? [];
    }

    /**
     * Load custom icons from storage path
     * @throws FileNotFoundException
     */
    protected function loadCustomIcons(): array
    {
        $customPath = $this->config['custom_icons_path'] ?? storage_path('app/svg-icons/custom.json');

        if (!File::exists($customPath)) {
            return [];
        }

        $content = File::get($customPath);
        return json_decode($content, true) ?? [];
    }

    /**
     * Get an icon by name
     */
    public function getIcon(string $name, array $attributes = [] ,$render =false): string
    {
        if (!isset($this->icons[$name])) {
            return $this->renderNotFoundIcon($name, $attributes);
        }


        $path = $this->icons[$name];

        unset($attributes['iconManager']);
        return $render
            ?$this->renderSvg($path,$attributes)
            : $path;
    }

    /**
     * Render SVG element
     */
    protected function renderSvg(string $path ,array $attributes): string
    {

        $attr = '';

        foreach ($attributes as $key => $value) {
            if (strlen(trim($value)) === 0) continue;
            $attr .= $key.'="'.trim($value).'" ';
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
