<?php

declare(strict_types=1);

namespace Core;

final class Autoloader
{
    public static function register(string $basePath): void
    {
        $basePath = rtrim(str_replace('\\', '/', $basePath), '/');

        spl_autoload_register(static function (string $class) use ($basePath): void {
            $map = [
                'Core\\' => $basePath . '/core/',
                'App\\' => $basePath . '/app/',
            ];

            foreach ($map as $prefix => $directory) {
                if (!str_starts_with($class, $prefix)) {
                    continue;
                }

                $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
                $file = $directory . $relative . '.php';

                if (is_file($file)) {
                    require $file;
                }

                return;
            }
        });
    }
}
