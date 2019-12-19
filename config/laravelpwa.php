<?php

return [
    'name' => 'FRACCSOFT',
    'manifest' => [
        'name' => env('APP_NAME', 'FRACCSOFT'),
        'short_name' => 'FSOFT',
        'start_url' => '/',
        'background_color' => '#ffffff',
        'theme_color' => '#000000',
        'display' => 'standalone',
        'orientation'=> 'any',
        'icons' => [
            '72x72' => '/images/icons/72.png',
            '96x96' => '/images/icons/96.png',
            '128x128' => '/images/icons/128.png',
            '144x144' => '/images/icons/144.png',
            '152x152' => '/images/icons/152.png',
            '192x192' => '/images/icons/192.png',
            '384x384' => '/images/icons/384.png',
            '512x512' => '/images/icons/512.png',
        ],
        'splash' => [
           '640x1136' => '/images/icons/640x1136.png',
            '750x1334' => '/images/icons/750x1334.png',
            '828x1792' => '/images/icons/828x1792.png',
            '1125x2436' => '/images/icons/1125x2436.png',
            '1242x2208' => '/images/icons/1242x2208.png',
            '1242x2688' => '/images/icons/1242x2688.png',
            '1536x2048' => '/images/icons/1536x2048.png',
            '1668x2224' => '/images/icons/1668x2224.png',
            '1668x2388' => '/images/icons/1668x2388.png',
            '2048x2732' => '/images/icons/2048x2732.png',
        ],
        'custom' => []
    ]
];
