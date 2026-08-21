<?php

declare(strict_types=1);

use Core\Application;

$basePath = dirname(__DIR__);
require $basePath . '/core/Autoloader.php';
Core\Autoloader::register($basePath);
require $basePath . '/core/helpers.php';

$email = $argv[1] ?? '';
$password = $argv[2] ?? '';
$name = $argv[3] ?? 'Editor';

if ($email === '' || $password === '') {
    fwrite(STDERR, "Usage: php scripts/create-user.php email password [name]\n");
    exit(1);
}

/** @var Application $app */
$app = require $basePath . '/bootstrap/app.php';
$app->db()->insert('users', [
    'email' => $email,
    'password_hash' => $app->auth()->hash($password),
    'name' => $name,
    'created_at' => date('c'),
]);

echo "Created user {$email}\n";
