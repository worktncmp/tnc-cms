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
$db = $app->db();

$demos = [
    ['admin@example.com', 'admin1234', 'Admin', Auth::ROLE_ADMIN],
    ['editor@example.com', 'editor1234', 'Editor', Auth::ROLE_EDITOR],
];

foreach ($demos as [$email, $password, $name, $role]) {
    $hash = $app->auth()->hash($password);
    $user = $db->fetch('SELECT id FROM users WHERE email = ?', [$email]);
    if ($user === null) {
        $db->insert('users', [
            'email' => $email,
            'password_hash' => $hash,
            'name' => $name,
            'role' => $role,
            'created_at' => date('c'),
        ]);
        echo "Created {$email}\n";
    } else {
        $db->update(
            'users',
            [
                'password_hash' => $hash,
                'name' => $name,
                'role' => $role,
            ],
            'email = ?',
            [$email],
        );
        echo "Updated {$email}\n";
    }
}

echo "Admin: admin@example.com / admin1234\n";
echo "Editor: editor@example.com / editor1234\n";
