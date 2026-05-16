# Extra laravel Package

## About

A simple and lightweight SVG icon package for Laravel.

### Author

Sina Zangiband

### Contact

- [teksite.net](https://teksite.net)
- [laratek.ir](https://laratek.ir)
- [teksite.net](https://teksite.net)

---

### Step 1: Install via Composer

Run the following command in your terminal:

```bash
composer require teksite/icon-laravel
```

#### Laravel 5.x and earlier

If you are using Laravel 5.x or earlier, register the service provider in the `config/app.php` file under the
`providers` array:

```php
'providers' => [
    // Other Service Providers
    Teksite\IconLaravel\IconLaravelServiceProvider::class,
];
```

> **Note:** This step is not required for newer versions of Laravel (5.x and above).

---

## Usage

### Step 2: Create Blade Component

Create a blade component file named icon.blade.php in resources/views/components/ directory.

Add the following content:

@php
$attributes = array_filter([
'class' => $class ?? config('icon-laravel.default_class'),
'viewbox' => $viewbox ?? config('icon-laravel.default_viewbox'),
'fill' => $fill ?? config('icon-laravel.default_fill'),
]);
@endphp

{!! app(Teksite\IconLaravel\IconManager::class)->getIcon($icon, $attributes) !!}

### Step 3: Use in Blade Template

<x-icon icon="example" class="stroke-red-600 fill-none" />

---

## Custom Icons

You can add your own SVG icons by creating a JSON file at:

storage_path('app/svg-icons/outline.json') --> for outline svg icons
storage_path('app/svg-icons/solid.json')  --> for solid svg icons

Tip: Only save the path tags inside the JSON file.

Example custom.json:

[
{"name": "<path d='M12 2L2 7l10 5 10-5-10-5z'/>"},
{"name": "<path d='M5 3l14 9-14 9V3z'/>"}
]

---

## Configuration

You can change the default paths and settings by publishing the config file:

php artisan vendor:publish --provider="Teksite\IconLaravel\IconLaravelServiceProvider"

Then edit config/icon-laravel.php.

---

## Support

Feel free to reach out if you have any questions or need assistance with this package.
