<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Banking API',
                'version' => '1.0.0',
            ],
            'routes' => [
                'api' => 'api/documentation', // C'est l'URL de l'interface Swagger UI
                'docs' => 'docs', // C'est l'URL pour le JSON
            ],
            'paths' => [
                'use_absolute_path' => true,
                'docs_json' => 'api-docs.json',
                'annotations' => [
                    base_path('app'),
                ],
            ],
            'servers' => [
                [
                    'url' => env('APP_URL', 'https://monprojet-laravel-13.onrender.com'),
                    'description' => 'Render API Server',
                ],
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            'docs' => 'docs', // Route pour le JSON
            'api' => 'api/documentation', // Route pour l'interface UI
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
        ],
        'paths' => [
            'docs' => storage_path('api-docs'),
        ],
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', true),
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'https://monprojet-laravel-13.onrender.com'),
        ],
    ],
];