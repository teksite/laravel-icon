<?php

return [

        /*
        |--------------------------------------------------------------------------
        | Cache Settings
        |--------------------------------------------------------------------------
        */
        'cache' => [
            'enabled' => env('SVG_ICONS_CACHE_ENABLED', false),
            'ttl' => env('SVG_ICONS_CACHE_TTL', 86400), // 24 hours
        ],

        /*
        |--------------------------------------------------------------------------
        | Storage Path for Custom Icons
        |--------------------------------------------------------------------------
        */
        'custom_icons_path' => storage_path('app/svg-icons/custom.json'),

        'component' => 'components.icon',
];
