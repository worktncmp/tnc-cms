<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'TNC-CMS'),
    'env' => env('APP_ENV', 'local'),
    'debug' => filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim((string) env('APP_URL', 'http://127.0.0.1:8080'), '/'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
];
