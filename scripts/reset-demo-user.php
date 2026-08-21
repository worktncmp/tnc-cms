<?php

declare(strict_types=1);

use Core\Application;
use Core\Auth;

$basePath = dirname(__DIR__);
require $basePath . '/core/Autoloader.php';
Core\Autoloader::register($basePath);
require $basePath . '/core/helpers.php';

/** @var Application $app */
$app = require $basePath . '/bootstrap/app.php';

$email = 'editor@example.com';
$password = 'TNC-demo-1';
$hash = $app->auth()->hash($password);

$user = $app->db()->fetch('SELECT id FROM users WHERE email = ?', [$email]);
if ($user === null) {
    $app->db()->insert('users', [
        'email' => $email,
        'password_hash' => $hash,
        'name' => 'Admin',
        'role' => Auth::ROLE_ADMIN,
        'created_at' => date('c'),
    ]);
    echo "Created {$email}\n";
} else {
    $app->db()->update(
        'users',
        [
            'password_hash' => $hash,
            'role' => Auth::ROLE_ADMIN,
            'name' => 'Admin',
        ],
        'email = ?',
        [$email],
    );
    echo "Updated password and role for {$email}\n";
}

echo "Login with: {$email} / {$password} (admin)\n";
