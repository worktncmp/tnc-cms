<?php

declare(strict_types=1);

namespace Core;

final class Config
{
    /** @param array<string, mixed> $items */
    public function __construct(private array $items)
    {
    }

    public static function load(string $directory): self
    {
        $items = [];
        $directory = rtrim(str_replace('\\', '/', $directory), '/');

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $name = basename($file, '.php');
            $data = require $file;
            if (is_array($data)) {
                $items[$name] = $data;
            }
        }

        return new self($items);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->items;

        foreach (explode('.', $key) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->items;
    }
}
