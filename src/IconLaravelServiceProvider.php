<?php

namespace Teksite\IconLaravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Teksite\IconLaravel\Component\Icon;
use Teksite\IconLaravel\Service\IconManager;


class IconLaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfig();
        $this->registerStubPath();

        $this->app->singleton(IconManager::class, function ($app) {
            return new IconManager($app['config']['svg-setting']);
        });

    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->publish();

        Blade::component('icon', Icon::class);


    }

    public function registerConfig(): void
    {
        $configPath = config_path('icon-setting.php');
        $this->mergeConfigFrom(file_exists($configPath) ? $configPath : __DIR__ . '/config/icon-setting.php', '/icon-setting');

    }

    public function registerStubPath(): void
    {
        $this->app->bind('icon.default.list', function () {
            return __DIR__ . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . 'icons.json';
        });

    }

    public function publish(): void
    {
        $this->publishes([
            __DIR__ . '/config/icon-setting.php' => config_path('icon-setting.php'),
        ], '/icon-setting');
    }
}
