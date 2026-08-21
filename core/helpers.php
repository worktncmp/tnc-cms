<?php

declare(strict_types=1);

use Core\Application;

function env(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return (string) $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    $base = rtrim((string) Application::getInstance()->config('app.url', ''), '/');
    $path = '/' . ltrim($path, '/');
    if ($path === '/') {
        return $base === '' ? '/' : $base;
    }

    return $base . $path;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** @param array<string, string> $params */
function route(string $name, array $params = []): string
{
    return url(Application::getInstance()->router()->url($name, $params));
}

/** @param array<string, mixed> $data */
function partial(string $name, array $data = []): string
{
    return Application::getInstance()->view()->partial($name, $data);
}

/** @param array<string, mixed> $data */
function component(string $name, array $data = []): string
{
    return Application::getInstance()->view()->component($name, $data);
}

function csrf_field(): string
{
    $app = Application::getInstance();

    return $app->csrf()->field($app->session());
}

function csrf_token(): string
{
    $app = Application::getInstance();

    return $app->csrf()->token($app->session());
}

function old(string $key, string $default = ''): string
{
    $flash = Application::getInstance()->session()->getFlash('old');
    if (is_array($flash) && isset($flash[$key]) && is_scalar($flash[$key])) {
        return (string) $flash[$key];
    }

    return $default;
}

function safe_internal_path(string $path): ?string
{
    $path = trim($path);
    if ($path === '' || !str_starts_with($path, '/') || str_starts_with($path, '//')) {
        return null;
    }
    if (str_contains($path, "\0") || str_contains($path, '\\')) {
        return null;
    }
    if (preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*:#', $path) === 1) {
        return null;
    }

    return $path;
}
