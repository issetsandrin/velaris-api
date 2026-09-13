<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_filter(array_map('trim', explode(',', env('FRONTEND_URLS', 'http://localhost:3000')))),
    // Libera qualquer porta em localhost e em IPs de rede privada (uso em desenvolvimento).
    'allowed_origins_patterns' => env('CORS_ALLOW_LOCAL_NETWORK', true)
        ? ['#^https?://(localhost|127\.0\.0\.1|192\.168\.\d{1,3}\.\d{1,3}|10\.\d{1,3}\.\d{1,3}\.\d{1,3})(:\d+)?$#']
        : [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['X-Cart-Token'],
    'max_age' => 0,
    'supports_credentials' => false,
];
