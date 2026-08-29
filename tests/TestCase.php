<?php

declare(strict_types=1);

namespace Teksite\IconLaravel\Tests;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Teksite\IconLaravel\IconLaravelServiceProvider;

/**
 * Base test case for the whole suite. This boots a real, minimal Laravel
 * application (via Orchestra Testbench) for every single test — including
 * the ones under Unit/ — because the package's classes rely on Laravel
 * facades and the `config()`/`app()`/`view()` helpers, which only work
 * inside a booted application container. This is the standard way Laravel
 * packages (e.g. Spatie's) are tested.
 */
abstract class TestCase extends OrchestraTestCase
{
    /**
     * Absolute path to a scratch directory created fresh for each test.
     * Use {@see putJsonFixture()} to write files into it. It is removed
     * automatically in tearDown().
     */
    protected string $fixturePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturePath = sys_get_temp_dir() . '/icon-laravel-tests/' . uniqid('', true);
        File::ensureDirectoryExists($this->fixturePath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->fixturePath);

        parent::tearDown();
    }

    /**
     * Register the package's own service provider, exactly like a consuming
     * application's config/app.php (or auto-discovery) would.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            IconLaravelServiceProvider::class,
        ];
    }

    /**
     * Fast, isolated defaults applied before every test.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('app.debug', false);
    }

    /**
     * Write a JSON icon fixture inside this test's scratch directory and
     * return its absolute path. Pass a raw string instead of an array to
     * write deliberately malformed content.
     */
    protected function putJsonFixture(string $filename, array|string $contents): string
    {
        $path = $this->fixturePath . '/' . ltrim($filename, '/');

        File::put($path, is_string($contents) ? $contents : json_encode($contents));

        return $path;
    }
}
