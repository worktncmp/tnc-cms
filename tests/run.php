<?php

declare(strict_types=1);

use Core\Application;
use Core\Csrf;
use Core\Database;
use Core\HttpException;
use Core\Request;
use Core\Session;
use Core\Upload;

$basePath = dirname(__DIR__);
require $basePath . '/core/Autoloader.php';
Core\Autoloader::register($basePath);
require $basePath . '/core/helpers.php';

$passed = 0;
$failed = 0;

function test(string $name, callable $fn): void
{
    global $passed, $failed;
    try {
        $fn();
        $passed++;
        echo "PASS  {$name}\n";
    } catch (Throwable $e) {
        $failed++;
        echo "FAIL  {$name}\n      {$e->getMessage()} ({$e->getFile()}:{$e->getLine()})\n";
    }
}

function assertTrue(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException($message);
    }
}

$_ENV['APP_DEBUG'] = 'true';
$_ENV['APP_URL'] = 'http://127.0.0.1:8080';
$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_PATH'] = ':memory:';

/** @var Application $app */
$app = require $basePath . '/bootstrap/app.php';

$schema = file_get_contents($basePath . '/database/schema.sqlite.sql') ?: '';
foreach (explode(';', $schema) as $chunk) {
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
        $app->db()->pdo()->exec($statement);
    }
}

$app->db()->insert('users', [
    'email' => 'tester@example.com',
    'password_hash' => $app->auth()->hash('secret-pass'),
    'name' => 'Tester',
    'role' => 'admin',
    'created_at' => date('c'),
]);
$app->db()->insert('users', [
    'email' => 'writer@example.com',
    'password_hash' => $app->auth()->hash('secret-pass'),
    'name' => 'Writer',
    'role' => 'editor',
    'created_at' => date('c'),
]);
$productId = $app->db()->insert('products', [
    'title' => 'Sample product',
    'summary' => 'A test product',
    'body' => 'Body copy for tests.',
]);

function handle(Application $app, Request $request, array &$store): \Core\Response
{
    return $app->withSession(Session::fake($store))->handle($request);
}

test('home page renders through the default layout', function () use ($app): void {
    $store = [];
    $response = handle($app, Request::fake('GET', '/'), $store);
    assertTrue($response->status() === 200, 'Expected 200, got ' . $response->status());
    assertTrue(str_contains($response->body(), 'small PHP foundation'), 'Home copy missing');
    assertTrue(str_contains($response->body(), 'site-header'), 'Layout header missing');
});

test('about page is discovered from the filesystem', function () use ($app): void {
    $store = [];
    $response = handle($app, Request::fake('GET', '/about'), $store);
    assertTrue($response->status() === 200, 'Expected 200, got ' . $response->status());
    assertTrue(str_contains($response->body(), 'What TNC-CMS is'), 'About copy missing');
});

test('nested services page inherits the section layout', function () use ($app): void {
    $store = [];
    $response = handle($app, Request::fake('GET', '/services/web-development'), $store);
    assertTrue($response->status() === 200, 'Expected 200, got ' . $response->status());
    assertTrue(str_contains($response->body(), 'side-nav'), 'Services layout missing');
    assertTrue(str_contains($response->body(), 'Web development'), 'Page copy missing');
});

test('html convention page is not executed as PHP', function () use ($app): void {
    $store = [];
    $response = handle($app, Request::fake('GET', '/services'), $store);
    assertTrue($response->status() === 200, 'Expected 200');
    assertTrue(str_contains($response->body(), 'What we build'), 'HTML page copy missing');
    assertTrue(str_contains($response->body(), 'Services'), 'page.json title missing');
});

test('unknown page returns 404', function () use ($app): void {
    $store = [];
    $response = handle($app, Request::fake('GET', '/missing-page'), $store);
    assertTrue($response->status() === 404, 'Expected 404, got ' . $response->status());
    assertTrue(str_contains($response->body(), 'not here'), '404 view missing');
});

test('path traversal is rejected', function (): void {
    try {
        Request::normalizePath('/about/../../core/Application.php');
        throw new RuntimeException('Traversal was not rejected');
    } catch (HttpException $e) {
        assertTrue($e->status === 404, 'Traversal should 404');
    }
});

test('trailing slash redirects', function () use ($app): void {
    $store = [];
    $response = handle($app, Request::fake('GET', '/about', trailingSlash: true), $store);
    assertTrue($response->status() === 301, 'Expected 301, got ' . $response->status());
    assertTrue(($response->headers()['Location'] ?? '') === 'http://127.0.0.1:8080/about', 'Redirect target was wrong');
});

test('explicit route parameters work', function () use ($app, $productId): void {
    $store = [];
    $response = handle($app, Request::fake('GET', '/products/' . $productId), $store);
    assertTrue($response->status() === 200, 'Expected 200, got ' . $response->status());
    assertTrue(str_contains($response->body(), 'Sample product'), 'Product title missing');
});

test('named route helper builds the correct URL', function () use ($app, $productId): void {
    $store = [];
    handle($app, Request::fake('GET', '/'), $store);
    $generated = route('product.show', ['id' => (string) $productId]);
    assertTrue($generated === 'http://127.0.0.1:8080/products/' . $productId, $generated);
});

test('url and asset helpers respect APP_URL', function () use ($app): void {
    $store = [];
    handle($app, Request::fake('GET', '/'), $store);
    assertTrue(url('/about') === 'http://127.0.0.1:8080/about', url('/about'));
    assertTrue(asset('css/app.css') === 'http://127.0.0.1:8080/assets/css/app.css', asset('css/app.css'));
});

test('convention pages reject POST', function () use ($app): void {
    $store = [];
    $app->csrf()->token(Session::fake($store));
    $store['_csrf_token'] = $store['_csrf_token'] ?? bin2hex(random_bytes(8));
    // Ensure token exists then POST without matching an explicit route
    $session = Session::fake($store);
    $store['_csrf_token'] = (new Csrf())->token($session);
    $response = $app->withSession($session)->handle(Request::fake('POST', '/about', [], ['_csrf' => $store['_csrf_token']]));
    assertTrue($response->status() === 405, 'Expected 405, got ' . $response->status());
});

test('CSRF rejects a missing token', function () use ($app): void {
    $store = [];
    $response = handle($app, Request::fake('POST', '/contact', [], [
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'message' => 'Hello there',
    ]), $store);
    assertTrue($response->status() === 403, 'Expected 403, got ' . $response->status());
});

test('CSRF accepts a valid token and contact POST succeeds', function () use ($app): void {
    $store = [];
    $session = Session::fake($store);
    $app->withSession($session);
    $app->handle(Request::fake('GET', '/contact'));
    $token = $session->get('_csrf_token');
    assertTrue(is_string($token) && $token !== '', 'Token was not issued');
    $response = $app->handle(Request::fake('POST', '/contact', [], [
        '_csrf' => $token,
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'message' => 'A longer test message.',
    ]));
    assertTrue($response->status() === 302, 'Expected redirect, got ' . $response->status());
    $row = $app->db()->fetch('SELECT * FROM messages WHERE email = ?', ['ada@example.com']);
    assertTrue($row !== null, 'Message was not stored');
});

test('database queries are parameterized', function () use ($app): void {
    $injected = "x' OR 1=1 --";
    $row = $app->db()->fetch('SELECT * FROM users WHERE email = ?', [$injected]);
    assertTrue($row === null, 'Parameter binding failed');
});

test('authentication hashes and verifies passwords', function () use ($app): void {
    $store = [];
    $app->withSession(Session::fake($store));
    assertTrue($app->auth()->attempt('tester@example.com', 'secret-pass'), 'Valid login failed');
    assertTrue($app->auth()->check(), 'User should be signed in');
    assertTrue($app->auth()->attempt('tester@example.com', 'wrong') === false, 'Invalid login succeeded');
    $app->auth()->logout();
    $app->withSession(Session::fake($store));
    assertTrue($app->auth()->check() === false, 'User should be signed out');
});

test('admin area is forbidden when logged out', function () use ($app): void {
    $store = [];
    $response = handle($app, Request::fake('GET', '/admin'), $store);
    assertTrue($response->status() === 403, 'Expected 403, got ' . $response->status());
});

test('admin dashboard works when logged in', function () use ($app): void {
    $store = [];
    $app->withSession(Session::fake($store));
    assertTrue($app->auth()->attempt('tester@example.com', 'secret-pass'), 'login failed');
    $response = $app->handle(Request::fake('GET', '/admin'));
    assertTrue($response->status() === 200, 'Expected 200, got ' . $response->status());
    assertTrue(str_contains($response->body(), 'Dashboard'), 'Dashboard missing');
});

test('admin can create a product', function () use ($app): void {
    $store = [];
    $session = Session::fake($store);
    $app->withSession($session);
    assertTrue($app->auth()->attempt('tester@example.com', 'secret-pass'), 'login failed');
    $token = $app->csrf()->token($session);
    $response = $app->handle(Request::fake('POST', '/admin/products', [], [
        '_csrf' => $token,
        'title' => 'Admin created',
        'summary' => 'From admin test',
        'body' => 'Body created in the admin area.',
    ]));
    assertTrue($response->status() === 302, 'Expected redirect, got ' . $response->status());
    $row = $app->db()->fetch('SELECT * FROM products WHERE title = ?', ['Admin created']);
    assertTrue($row !== null, 'Product was not created');
});

test('editor cannot manage products', function () use ($app): void {
    $store = [];
    $app->withSession(Session::fake($store));
    assertTrue($app->auth()->attempt('writer@example.com', 'secret-pass'), 'editor login failed');
    $response = $app->handle(Request::fake('GET', '/admin/products'));
    assertTrue($response->status() === 403, 'Expected 403, got ' . $response->status());
});

test('admin can create and edit an HTML content page', function () use ($app, $basePath): void {
    $store = [];
    $session = Session::fake($store);
    $app->withSession($session);
    assertTrue($app->auth()->attempt('tester@example.com', 'secret-pass'), 'login failed');
    $token = $app->csrf()->token($session);

    $response = $app->handle(Request::fake('POST', '/admin/pages', [], [
        '_csrf' => $token,
        'path' => 'practice-lab',
        'title' => 'Practice Lab',
        'body' => '<h1>Practice Lab</h1><p>Created from admin.</p>',
    ]));
    assertTrue($response->status() === 302, 'Expected redirect, got ' . $response->status());

    $file = $basePath . '/content/pages/practice-lab/index.html';
    assertTrue(is_file($file), 'Page file missing');

    $pageResponse = $app->handle(Request::fake('GET', '/practice-lab'));
    assertTrue($pageResponse->status() === 200, 'Public page missing');
    assertTrue(str_contains($pageResponse->body(), 'Created from admin'), 'Page body missing');

    // cleanup
    @unlink($file);
    @unlink($basePath . '/content/pages/practice-lab/page.json');
    @rmdir($basePath . '/content/pages/practice-lab');
});

test('view rendering escapes untrusted text', function () use ($app): void {
    $store = [];
    handle($app, Request::fake('GET', '/'), $store);
    $html = e('<script>alert(1)</script>');
    assertTrue($html === '&lt;script&gt;alert(1)&lt;/script&gt;', $html);
});

test('upload validation rejects php files', function () use ($basePath): void {
    $tmp = tempnam(sys_get_temp_dir(), 'upl');
    assertTrue($tmp !== false, 'temp file');
    file_put_contents($tmp, '<?php echo 1;');
    $upload = new Upload(false);
    $dest = $basePath . '/storage/uploads';
    try {
        $upload->store([
            'name' => 'shell.php',
            'type' => 'application/x-php',
            'tmp_name' => $tmp,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmp),
        ], $dest, ['png', 'jpg'], 1_000_000);
        throw new RuntimeException('PHP upload was accepted');
    } catch (RuntimeException $e) {
        assertTrue($e->getMessage() === 'This file type is not allowed.', $e->getMessage());
    } finally {
        @unlink($tmp);
    }
});

test('upload validation accepts a png with matching mime', function () use ($basePath): void {
    $tmp = tempnam(sys_get_temp_dir(), 'upl') . '.png';
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
    assertTrue($png !== false, 'png bytes');
    file_put_contents($tmp, $png);
    $upload = new Upload(false);
    $dest = $basePath . '/storage/uploads';
    $stored = $upload->store([
        'name' => 'pixel.png',
        'type' => 'image/png',
        'tmp_name' => $tmp,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($tmp),
    ], $dest, ['png'], 1_000_000);
    assertTrue(str_ends_with($stored, '.png'), $stored);
    assertTrue(is_file($dest . '/' . $stored), 'Stored file missing');
    unlink($dest . '/' . $stored);
    unlink($tmp);
});

test('security headers are present', function () use ($app): void {
    $store = [];
    $response = handle($app, Request::fake('GET', '/'), $store);
    $headers = $response->headers();
    assertTrue(($headers['X-Content-Type-Options'] ?? '') === 'nosniff', 'nosniff missing');
    assertTrue(($headers['X-Frame-Options'] ?? '') === 'SAMEORIGIN', 'frame options missing');
});

echo "\n{$passed} passed, {$failed} failed\n";
exit($failed === 0 ? 0 : 1);
