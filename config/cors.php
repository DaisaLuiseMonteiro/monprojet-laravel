<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'docs', 'docs/*', 'monteiro.daisa/v1/*'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => ['*'], // Pour production, vous pouvez restreindre plus tard
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => false,
];