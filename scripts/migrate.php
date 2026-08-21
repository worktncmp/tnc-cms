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

ensureRoleColumn($db, $driver);

$users = $db->fetch('SELECT COUNT(*) AS total FROM users');
if ((int) ($users['total'] ?? 0) === 0) {
    $db->insert('users', [
        'email' => 'editor@example.com',
        'password_hash' => $app->auth()->hash('TNC-demo-1'),
        'name' => 'Admin',
        'role' => Auth::ROLE_ADMIN,
        'created_at' => date('c'),
    ]);
} else {
    $demo = $db->fetch('SELECT id FROM users WHERE email = ?', ['editor@example.com']);
    if ($demo !== null) {
        $db->update(
            'users',
            [
                'password_hash' => $app->auth()->hash('TNC-demo-1'),
                'role' => Auth::ROLE_ADMIN,
                'name' => 'Admin',
            ],
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
echo "Sample admin login: editor@example.com / TNC-demo-1\n";

/**
 * @param \Core\Database $db
 */
function ensureRoleColumn(\Core\Database $db, string $driver): void
{
    if ($driver === 'sqlite') {
        $columns = $db->fetchAll('PRAGMA table_info(users)');
        foreach ($columns as $column) {
            if (($column['name'] ?? '') === 'role') {
                return;
            }
        }
        $db->pdo()->exec("ALTER TABLE users ADD COLUMN role TEXT NOT NULL DEFAULT 'editor'");
        return;
    }

    $row = $db->fetch(
        'SELECT COUNT(*) AS total FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
        ['users', 'role'],
    );
    if ((int) ($row['total'] ?? 0) === 0) {
        $db->pdo()->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'editor'");
    }
}
