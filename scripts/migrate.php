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

ensureDemoUser($db, $app, 'admin@example.com', 'admin1234', 'Admin', Auth::ROLE_ADMIN);
ensureDemoUser($db, $app, 'editor@example.com', 'editor1234', 'Editor', Auth::ROLE_EDITOR);

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
echo "Sample admin login: admin@example.com / admin1234\n";
echo "Sample editor login: editor@example.com / editor1234\n";

/**
 * @param \Core\Database $db
 */
function ensureDemoUser(
    \Core\Database $db,
    Application $app,
    string $email,
    string $password,
    string $name,
    string $role,
): void {
    $hash = $app->auth()->hash($password);
    $existing = $db->fetch('SELECT id FROM users WHERE email = ?', [$email]);

    if ($existing === null) {
        $db->insert('users', [
            'email' => $email,
            'password_hash' => $hash,
            'name' => $name,
            'role' => $role,
            'created_at' => date('c'),
        ]);

        return;
    }

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
}

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
