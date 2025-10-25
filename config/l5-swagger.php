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
                'docs_yaml' => 'api-docs.yaml',
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
                'annotations' => [
                    base_path('app'),
                ],
                'excludes' => [],
                'base' => env('L5_SWAGGER_BASE_PATH', null),
            ],
            'servers' => [
                [
                    'url' => env('APP_URL', 'https://monprojet-laravel-13.onrender.com'),
                    'description' => 'Render API Server',
                ],
            ],
            'proxy' => false,
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
            'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
            'annotations' => [
                base_path('app'),
            ],
            'excludes' => [],
            'base' => env('L5_SWAGGER_BASE_PATH', null),
        ],
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', true),
        'ui' => [
            'display' => [
                'doc_expansion' => 'none',
                'filter' => true,
            ],
            'authorization' => [
                'persist_authorization' => false,
                'oauth2' => [
                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],
        'proxy' => false,
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'https://monprojet-laravel-13.onrender.com'),
        ],
    ],
];