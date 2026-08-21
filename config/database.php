<?php

declare(strict_types=1);

$driver = env('DB_DRIVER', 'sqlite');
$path = env('DB_PATH', 'storage/database.sqlite');

if ($driver === 'sqlite' && $path !== ':memory:' && !str_contains((string) $path, ':')) {
    $root = dirname(__DIR__);
    $path = $root . '/' . ltrim(str_replace('\\', '/', (string) $path), '/');
}

return [
    'driver' => $driver,
    'path' => $path,
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'name' => env('DB_NAME', 'cms'),
    'user' => env('DB_USER', 'root'),
    'pass' => env('DB_PASS', ''),
];
