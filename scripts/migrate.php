<?php

declare(strict_types=1);

use Core\Application;

$basePath = dirname(__DIR__);
require $basePath . '/core/Autoloader.php';
Core\Autoloader::register($basePath);
require $basePath . '/core/helpers.php';

/** @var Application $app */
$app = require $basePath . '/bootstrap/app.php';
$db = $app->db();
$driver = (string) $app->config('database.driver', 'sqlite');
$file = $driver === 'mysql'
    ? $basePath . '/database/schema.mysql.sql'
    : $basePath . '/database/schema.sqlite.sql';

$sql = file_get_contents($file);
if ($sql === false) {
    fwrite(STDERR, "Could not read {$file}\n");
    exit(1);
}

foreach (explode(';', $sql) as $chunk) {
    $lines = [];
    foreach (explode("\n", $chunk) as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '--')) {
            continue;
        }
        $lines[] = $line;
    }
    $statement = trim(implode("\n", $lines));
    if ($statement !== '') {
        $db->pdo()->exec($statement);
    }
}

$users = $db->fetch('SELECT COUNT(*) AS total FROM users');
if ((int) ($users['total'] ?? 0) === 0) {
    $db->insert('users', [
        'email' => 'editor@example.com',
        'password_hash' => $app->auth()->hash('TNC-demo-1'),
        'name' => 'Editor',
        'created_at' => date('c'),
    ]);
} else {
    // Keep existing users, but ensure the sample account password matches the docs.
    $demo = $db->fetch('SELECT id FROM users WHERE email = ?', ['editor@example.com']);
    if ($demo !== null) {
        $db->update(
            'users',
            ['password_hash' => $app->auth()->hash('TNC-demo-1')],
            'email = ?',
            ['editor@example.com'],
        );
    }
}

$products = $db->fetch('SELECT COUNT(*) AS total FROM products');
if ((int) ($products['total'] ?? 0) === 0) {
    $db->insert('products', [
        'title' => 'Brochure site',
        'summary' => 'Pages from folders, shared layouts, and a contact form.',
        'body' => 'Built with TNC-CMS: content pages for the story, controllers only where needed.',
    ]);
    $db->insert('products', [
        'title' => 'Catalogue site',
        'summary' => 'Public pages plus database-backed product listings.',
        'body' => 'Convention pages for marketing. Explicit routes for catalogue detail pages.',
    ]);
}

echo "Database ready.\n";
echo "Sample login: editor@example.com / TNC-demo-1\n";
