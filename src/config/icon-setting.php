<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache'               => [
        'enabled' => env('SVG_ICONS_CACHE_ENABLED', false),
        'ttl'     => env('SVG_ICONS_CACHE_TTL', 86400), // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Path for Custom Icons
    |--------------------------------------------------------------------------
    */
    'custom_solid_icon'   => storage_path('app/svg-icons/solid.json'),
    'custom_outline_icon' => storage_path('app/svg-icons/outline.json'),
    /*
    |--------------------------------------------------------------------------
    | component to load icons
    |--------------------------------------------------------------------------
    */
    'component'           => 'components.icon',
];
