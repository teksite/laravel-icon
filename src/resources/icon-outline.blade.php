@php
    $attributes = array_filter([
        'class' => $class ?? config('svg-icons.default_class'),
        'viewbox' => $viewbox ?? config('svg-icons.default_viewbox'),
        'fill' => $fill ?? config('svg-icons.default_fill'),
    ]);
@endphp

{!! app(\YourVendor\SvgIcons\Services\IconManager::class)->getIcon($icon, $attributes) !!}
