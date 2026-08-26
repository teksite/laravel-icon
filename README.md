# Teksite Icon Laravel

A simple, lightweight Laravel package for using SVG icons directly in Blade components.

## Requirements

- PHP 8.3+
- Laravel 13+

## Installation

Install the package with Composer:

```bash
composer require teksite/icon-laravel
```

The service provider is automatically discovered by Laravel.

## Publishing Assets

Publish the package configuration:

```bash
php artisan vendor:publish --tag=icon-setting
```

Publish the icon assets:

```bash
php artisan vendor:publish --tag=icon-assets
```

You can also publish each asset group separately:

```bash
php artisan vendor:publish --tag=icons-outline
php artisan vendor:publish --tag=icons-solid
php artisan vendor:publish --tag=icons-picker
```

The published files are placed under:

```text
{public_path}/vendor/icons/
```

The configuration file is:

```text
config/icon-setting.php
```

## Configuration

The default configuration looks like this:

```php
<?php

return [

    'cache' => [
        'key' => 'svg_icons.icons',
        'enabled' => env('SVG_ICONS_CACHE_ENABLED', false),
        'ttl' => env('SVG_ICONS_CACHE_TTL', 86400),
    ],

    'path' => [
        'solid' => public_path('vendor/icons/solid.json'),
        'outline' => public_path('vendor/icons/outline.json'),
    ],

    'component' => 'components.icon',

];
```

### Icon Sources

The package supports multiple icon types.

By default:

```php
'path' => [
    'outline' => public_path('vendor/icons/outline.json'),
],
```

Custom icon JSON files take priority when they exist.

If a configured file does not exist, the package automatically falls back to the package's bundled resource:

```text
src/resources/{type}.json
```

For example:

```text
src/resources/outline.json
src/resources/solid.json
```

You can define additional custom icon types by adding them to the configuration:

```php
'path' => [
    'outline' => public_path('vendor/icons/outline.json'),
    'solid' => public_path('vendor/icons/solid.json'),
    'custom' => public_path('vendor/icons/custom.json'),
],
```

The icon type must contain only letters, numbers, underscores, or hyphens.

## Using Icons

The package provides two Blade components:

```blade
<x-icon />
```

and:

```blade
<x-tkicon />
```

Both components use the same icon manager and support the same main properties.

### Basic Usage

```blade
<x-icon icon="home" />
```

or:

```blade
<x-tkicon icon="home" />
```

The default icon type is:

```text
outline
```

To use a solid icon:

```blade
<x-icon icon="home" type="solid" />
```

## Component Attributes

The component supports the following properties:

```text
icon
title
type
viewbox
x
y
width
height
strokeWidth
strokeLinecap
strokeLinejoin
```

Example:

```blade
<x-icon
    icon="home"
    type="outline"
    title="Home"
    width="32"
    height="32"
    strokeWidth="1.5"
/>
```

Additional HTML attributes can also be passed to the component:

```blade
<x-icon
    icon="home"
    class="text-blue-500"
    id="home-icon"
/>
```

The component merges the provided class with its default classes.

## Generated SVG

The component renders an SVG element similar to:

```html
<svg
    x="0"
    y="0"
    width="24"
    height="24"
    viewBox="0 0 24 24"
    class="tkicon home outline-icon"
    data-icon="home"
    stroke-width="1"
    stroke-linecap="round"
    stroke-linejoin="round"
    xmlns="http://www.w3.org/2000/svg"
>
    ...
</svg>
```

## Custom Component View

The default component view is configured as:

```php
'component' => 'components.icon',
```

You can change it in:

```text
config/icon-setting.php
```

For example:

```php
'component' => 'icons.svg',
```

The configured view receives:

```php
[
    'path' => $path,
]
```

The icon component also exposes its normal Blade component properties and attributes.

If the configured view does not exist, the `Icon` component logs an error and falls back to an inline SVG renderer.

## Difference Between `icon` and `tkicon`

Both components provide the same basic functionality.

### `<x-icon>`

`<x-icon>` supports a configurable Blade view through:

```php
'component' => 'components.icon',
```

If the configured view exists, it is used for rendering.

If it does not exist, the component uses its built-in fallback renderer.

### `<x-tkicon>`

`<x-tkicon>` always uses its built-in inline renderer.

Example:

```blade
<x-tkicon icon="home" />
```

## IconManager

The main service responsible for loading and rendering icons is:

```php
Teksite\IconLaravel\Service\IconManager
```

It is registered as a Laravel singleton.

You can resolve it from the container:

```php
$iconManager = app(\Teksite\IconLaravel\Service\IconManager::class);
```

## Get an Icon

You can retrieve an icon as a complete SVG:

```php
$icon = app(\Teksite\IconLaravel\Service\IconManager::class)
    ->getIcon('home');
```

By default, the icon type is:

```text
outline
```

To specify another type:

```php
$icon = app(\Teksite\IconLaravel\Service\IconManager::class)
    ->getIcon('home', type: 'solid');
```

## Get the Raw SVG Path

The `render` argument can be disabled to retrieve only the SVG path/content:

```php
$path = app(\Teksite\IconLaravel\Service\IconManager::class)
    ->getIcon(
        'home',
        type: 'outline',
        render: false
    );
```

This is useful when the SVG element itself needs to be controlled by a Blade component or custom renderer.

## SVG Attributes

When rendering an icon directly through `IconManager`, attributes can be passed as an array:

```php
$icon = app(\Teksite\IconLaravel\Service\IconManager::class)
    ->getIcon(
        'home',
        attributes: [
            'class' => 'w-6 h-6',
            'width' => 24,
            'height' => 24,
            'aria-hidden' => true,
        ]
    );
```

The package escapes attribute names and values before rendering them.

## Check Whether an Icon Exists

```php
$exists = app(\Teksite\IconLaravel\Service\IconManager::class)
    ->hasIcon('home');
```

For a specific type:

```php
$exists = app(\Teksite\IconLaravel\Service\IconManager::class)
    ->hasIcon('home', 'solid');
```

The result is a boolean.

## Get Icon Names

Get all icon names for a specific type:

```php
$names = app(\Teksite\IconLaravel\Service\IconManager::class)
    ->getIconNames('outline');
```

Get icon names grouped by type:

```php
$names = app(\Teksite\IconLaravel\Service\IconManager::class)
    ->getIconNames();
```

## Get All Icons

Get all icons as raw path definitions:

```php
$icons = app(\Teksite\IconLaravel\Service\IconManager::class)
    ->getAll(render: false);
```

Get all icons as rendered SVG elements:

```php
$icons = app(\Teksite\IconLaravel\Service\IconManager::class)
    ->getAll();
```

## Icon JSON Format

Icons are stored as JSON objects where the key is the icon name and the value is the SVG path/content.

Example:

```json
{
    "home": "<path d=\"...\" />",
    "user": "<path d=\"...\" />"
}
```

Icon names must match:

```text
[a-zA-Z0-9_-]+
```

Invalid icon names are ignored while loading the JSON file.

Empty or invalid path values are also ignored.

## Cache

Icon loading can be cached.

Caching is disabled by default:

```php
'cache' => [
    'enabled' => false,
],
```

Enable it through the environment:

```env
SVG_ICONS_CACHE_ENABLED=true
```

The default cache TTL is 24 hours:

```env
SVG_ICONS_CACHE_TTL=86400
```

You can customize the cache key:

```env
SVG_ICONS_CACHE_ENABLED=true
SVG_ICONS_CACHE_TTL=86400
```

Or configure it directly in:

```text
config/icon-setting.php
```

Example:

```php
'cache' => [
    'key' => 'svg_icons.icons',
    'enabled' => true,
    'ttl' => 86400,
],
```

## Clearing the Icon Cache

The package provides:

```php
Teksite\IconLaravel\Support\CacheManager
```

To clear the icon cache:

```php
\Teksite\IconLaravel\Support\CacheManager::clearCache();
```

For example:

```php
use Teksite\IconLaravel\Support\CacheManager;

CacheManager::clearCache();
```

This is useful after changing or replacing icon JSON files while caching is enabled.

## Debugging Missing Icons

If an icon does not exist and Laravel is running with:

```env
APP_DEBUG=true
```

the package renders a small warning indicator:

```text
⚠️
```

with the missing icon name.

When debug mode is disabled, missing icons return an empty string instead.

## Creating Custom Icons

You can add your own icon collection.

For example, create:

```text
public/vendor/icons/custom.json
```

with:

```json
{
    "my-icon": "<path d=\"...\" />"
}
```

Then configure it:

```php
'path' => [
    'outline' => public_path('vendor/icons/outline.json'),
    'solid' => public_path('vendor/icons/solid.json'),
    'custom' => public_path('vendor/icons/custom.json'),
],
```

You can then use:

```blade
<x-icon icon="my-icon" type="custom" />
```

## Blade Registration

The package registers the following Blade components:

```blade
<x-icon />
<x-tkicon />
```

Their aliases are registered by the service provider:

```php
Blade::component('icon', Icon::class);
Blade::component('tkicon', TekIcon::class);
```

## Security

Icon names and icon types are validated before lookup.

Only identifiers matching:

```text
[a-zA-Z0-9_-]+
```

are accepted.

HTML attributes generated by `IconManager` are escaped using:

```php
htmlspecialchars(
    $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
```

Icon path content is treated as SVG content and is inserted directly into the generated SVG. Therefore, custom icon JSON files should only contain trusted SVG definitions.

## License

This package is open-sourced software licensed under the MIT license.

## Author

Sina Zangiband

Email:

```text
sina.zangiband@gmail.com
```
### Contact

- [teksite.net](https://teksite.net)
- [laratek.ir](https://laratek.ir)
- [teksite.net](https://teksite.net)


## GitHub

Repository:

```text
https://github.com/teksite/icon-laravel
```
