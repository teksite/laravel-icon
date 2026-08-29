<?php

namespace Teksite\IconLaravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Teksite\IconLaravel\Component\Icon;
use Teksite\IconLaravel\Component\TekIcon;
use Teksite\IconLaravel\Service\IconManager;


class IconLaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();
        $this->registerStubPath();

        $this->app->singleton(IconManager::class, function ($app) {
            return new IconManager();
        });
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->publish();

        Blade::component('icon', Icon::class);
        Blade::component('tkicon', TekIcon::class);
    }

    public function registerConfig(): void
    {
        $configPath = config_path('icon-setting.php');
        $this->mergeConfigFrom(file_exists($configPath) ? $configPath : __DIR__ . '/config/icon-setting.php', 'icon-setting');
    }

    public function registerStubPath(): void
    {
        $this->app->bind('icon.default.path', function () {
            return __DIR__ . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR;
        });
    }

    public function publish(): void
    {
        $this->publishes([
            __DIR__ . '/config/icon-setting.php' => config_path('icon-setting.php'),
        ], ['icon','icon-setting']);


        $this->publishes([
            __DIR__ . '/resources/outline.json' => public_path('vendor/icons/outline.json'),
            __DIR__ . '/resources/solid.json' => public_path('vendor/icons/solid.json'),
        ], ['icon','icon-assets','icons-list']);



        $this->publishes([
            __DIR__ . '/resources/icon-picker.js' => public_path('vendor/icons/icon-picker.js'),
            __DIR__ . '/resources/outline.json' => public_path('vendor/icons/outline.json'),
            __DIR__ . '/resources/solid.json' => public_path('vendor/icons/solid.json'),
        ], ['icon','icon-assets' ,'icons-picker']);
    }
}
