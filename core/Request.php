<?php

declare(strict_types=1);

namespace Core;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $post
     * @param array<string, mixed> $server
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $files
     * @param array<string, string> $params
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array $post = [],
        public readonly array $server = [],
        public readonly array $cookies = [],
        public readonly array $files = [],
        public readonly bool $trailingSlash = false,
        public array $params = [],
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $post = $_POST;

        if ($method === 'POST' && isset($post['_method'])) {
            $spoof = strtoupper(trim((string) $post['_method']));
            if (in_array($spoof, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $spoof;
            }
        }

        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }
        $path = rawurldecode($path);

        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $scriptDir = rtrim(dirname($scriptName), '/');
        if ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '\\') {
            $scriptDir = '';
        }

        if ($scriptDir !== '' && ($path === $scriptDir || str_starts_with($path, $scriptDir . '/'))) {
            $path = substr($path, strlen($scriptDir)) ?: '/';
        }

        $trailingSlash = $path !== '/' && str_ends_with($path, '/');

        return new self(
            $method,
            self::normalizePath($path),
            $_GET,
            $post,
            $_SERVER,
            $_COOKIE,
            $_FILES,
            $trailingSlash,
        );
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $post
     * @param array<string, mixed> $server
     */
    public static function fake(
        string $method,
        string $path,
        array $query = [],
        array $post = [],
        array $server = [],
        bool $trailingSlash = false,
    ): self {
        return new self(
            strtoupper($method),
            self::normalizePath($path),
            $query,
            $post,
            $server,
            [],
            [],
            $trailingSlash,
        );
    }

    public static function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        if (str_contains($path, "\0")) {
            throw HttpException::notFound();
        }

        $parts = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                throw HttpException::notFound();
            }
            $parts[] = $segment;
        }

        return '/' . implode('/', $parts);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function param(string $key, ?string $default = null): ?string
    {
        return $this->params[$key] ?? $default;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $value = $this->server[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    public function isHttps(): bool
    {
        $https = $this->server['HTTPS'] ?? '';
        if ($https !== '' && $https !== 'off') {
            return true;
        }

        $forwarded = $this->server['HTTP_X_FORWARDED_PROTO'] ?? '';

        return $forwarded === 'https';
    }
}
