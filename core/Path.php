<?php

declare(strict_types=1);

namespace Core;

final class Path
{
    public static function join(string $base, string $append = ''): string
    {
        $base = rtrim(str_replace('\\', '/', $base), '/');
        if ($append === '') {
            return $base;
        }

        return $base . '/' . ltrim(str_replace('\\', '/', $append), '/');
    }

    public static function inside(string $path, string $root): bool
    {
        $path = str_replace('\\', '/', $path);
        $root = rtrim(str_replace('\\', '/', $root), '/');

        if (PHP_OS_FAMILY === 'Windows') {
            $path = strtolower($path);
            $root = strtolower($root);
        }

        return $path === $root || str_starts_with($path, $root . '/');
    }
}
