<?php

declare(strict_types=1);

use Core\Application;
use Core\Auth;

$basePath = dirname(__DIR__);
require $basePath . '/core/Autoloader.php';
Core\Autoloader::register($basePath);
require $basePath . '/core/helpers.php';

$email = $argv[1] ?? '';
$password = $argv[2] ?? '';
$name = $argv[3] ?? 'Editor';
$role = $argv[4] ?? Auth::ROLE_EDITOR;

if ($email === '' || $password === '') {
    fwrite(STDERR, "Usage: php scripts/create-user.php email password [name] [admin|editor]\n");
    exit(1);
}

if (!in_array($role, [Auth::ROLE_ADMIN, Auth::ROLE_EDITOR], true)) {
    fwrite(STDERR, "Role must be admin or editor.\n");
    exit(1);
}

/** @var Application $app */
$app = require $basePath . '/bootstrap/app.php';
$app->db()->insert('users', [
    'email' => $email,
    'password_hash' => $app->auth()->hash($password),
    'name' => $name,
    'role' => $role,
    'created_at' => date('c'),
]);

echo "Created {$role} user {$email}\n";
