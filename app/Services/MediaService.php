<?php

declare(strict_types=1);

namespace App\Services;

use Core\Path;

final class MediaService
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct(private readonly string $uploadsPath)
    {
    }

    /** @return list<array{name: string, url: string, size: int, modified: int}> */
    public function all(): array
    {
        $root = $this->root(true);
        $items = [];

        foreach (scandir($root) ?: [] as $name) {
            if ($name === '.' || $name === '..' || str_starts_with($name, '.')) {
                continue;
            }
            if (!$this->isSafeName($name)) {
                continue;
            }

            $full = $root . DIRECTORY_SEPARATOR . $name;
            if (!is_file($full)) {
                continue;
            }

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                continue;
            }

            $items[] = [
                'name' => $name,
                'url' => url('uploads/' . $name),
                'size' => (int) filesize($full),
                'modified' => (int) filemtime($full),
            ];
        }

        usort($items, static fn (array $a, array $b): int => $b['modified'] <=> $a['modified']);

        return $items;
    }

    public function delete(string $name): void
    {
        if (!$this->isSafeName($name)) {
            throw new \InvalidArgumentException('Invalid file name.');
        }

        $root = $this->root(false);
        $full = Path::join($root, $name);
        $real = realpath($full);
        if ($real === false || !is_file($real) || !Path::inside($real, $root)) {
            throw new \InvalidArgumentException('File not found.');
        }

        unlink($real);
    }

    public function directory(): string
    {
        return $this->root(true);
    }

    /** @return list<string> */
    public function allowedExtensions(): array
    {
        return self::IMAGE_EXTENSIONS;
    }

    private function isSafeName(string $name): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9._-]+$/', $name)
            && !str_contains($name, '..')
            && !str_contains($name, '/')
            && !str_contains($name, '\\');
    }

    private function root(bool $create): string
    {
        if ($create && !is_dir($this->uploadsPath)) {
            mkdir($this->uploadsPath, 0755, true);
        }

        $root = realpath($this->uploadsPath);
        if ($root === false) {
            throw new \RuntimeException('Upload directory is missing.');
        }

        return $root;
    }
}
