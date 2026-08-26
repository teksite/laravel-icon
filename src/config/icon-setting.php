<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache'               => [
        'key'     => 'svg_icons.icons',
        'enabled' => env('SVG_ICONS_CACHE_ENABLED', false),
        'ttl'     => env('SVG_ICONS_CACHE_TTL', 86400), // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Path for Custom Icons
    |--------------------------------------------------------------------------
    */
    'path'=>[
        'solid'=>public_path('vendor/icons/solid.json'), // set it null to read from default icons
        'outline'=>public_path('vendor/icons/outline.json'),
    ],

    /*
    |--------------------------------------------------------------------------
    | component to load icons
    |--------------------------------------------------------------------------
    */
    'component'           => 'components.icon',
];
