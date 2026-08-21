<?php

declare(strict_types=1);

namespace Core;

final class PageResolver
{
    public function __construct(
        private readonly string $pagesPath,
        private readonly string $layoutsPath,
        private readonly string $cachePath,
        private readonly bool $debug,
    ) {
    }

    /**
     * @return array{
     *     contentFile: string,
     *     type: string,
     *     directory: string,
     *     meta: array<string, mixed>
     * }|null
     */
    public function resolve(string $urlPath): ?array
    {
        $pagesRoot = realpath($this->pagesPath);
        if ($pagesRoot === false) {
            return null;
        }

        $relative = $this->relativeFromCache($urlPath);
        if ($relative === null) {
            $relative = $this->relativeFromFilesystem($urlPath, $pagesRoot);
        }
        if ($relative === null) {
            return null;
        }

        $contentFile = Path::join($pagesRoot, $relative);
        $real = realpath($contentFile);
        if ($real === false || !is_file($real) || !Path::inside($real, $pagesRoot)) {
            return null;
        }

        $type = str_ends_with(strtolower($real), '.html') ? 'html' : 'php';

        return [
            'contentFile' => $real,
            'type' => $type,
            'directory' => dirname($real),
            'meta' => $this->readJsonMeta(dirname($real)),
        ];
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function resolveLayout(string $pageDirectory, array $meta): string
    {
        $named = $meta['layout'] ?? null;
        if (is_string($named) && $named !== '') {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $named)) {
                throw HttpException::serverError('Invalid layout name.');
            }
            $file = Path::join($this->layoutsPath, $named . '.php');
            $real = realpath($file);
            $layoutsRoot = realpath($this->layoutsPath);
            if ($real === false || $layoutsRoot === false || !Path::inside($real, $layoutsRoot)) {
                throw HttpException::serverError('Layout not found.');
            }

            return $real;
        }

        $pagesRoot = realpath($this->pagesPath);
        $current = realpath($pageDirectory);
        if ($pagesRoot === false || $current === false) {
            return $this->defaultLayout();
        }

        while (Path::inside($current, $pagesRoot)) {
            $candidate = $current . DIRECTORY_SEPARATOR . 'layout.php';
            if (is_file($candidate)) {
                $real = realpath($candidate);
                if ($real !== false && Path::inside($real, $pagesRoot)) {
                    return $real;
                }
            }
            if ($current === $pagesRoot) {
                break;
            }
            $parent = dirname($current);
            if ($parent === $current) {
                break;
            }
            $current = $parent;
        }

        return $this->defaultLayout();
    }

    /** @return array<string, string> */
    public function pageMap(): array
    {
        $pagesRoot = realpath($this->pagesPath);
        if ($pagesRoot === false) {
            return [];
        }

        if (!$this->debug) {
            $cached = $this->readCache();
            if ($cached !== null) {
                return $cached;
            }
        }

        $map = $this->scan($pagesRoot);
        if (!$this->debug) {
            $this->writeCache($map);
        }

        return $map;
    }

    public function rebuildCache(): array
    {
        $pagesRoot = realpath($this->pagesPath);
        $map = $pagesRoot === false ? [] : $this->scan($pagesRoot);
        $this->writeCache($map);

        return $map;
    }

    private function relativeFromCache(string $urlPath): ?string
    {
        if ($this->debug) {
            return null;
        }

        $map = $this->readCache();
        if ($map === null) {
            $map = $this->pageMap();
        }

        return $map[$urlPath] ?? null;
    }

    private function relativeFromFilesystem(string $urlPath, string $pagesRoot): ?string
    {
        $relativeDir = trim($urlPath, '/');
        $candidates = $relativeDir === ''
            ? ['index.php', 'index.html']
            : [$relativeDir . '/index.php', $relativeDir . '/index.html'];

        foreach ($candidates as $relative) {
            if (str_contains($relative, '..') || str_contains($relative, "\0")) {
                return null;
            }
            $full = Path::join($pagesRoot, $relative);
            $real = realpath($full);
            if ($real !== false && is_file($real) && Path::inside($real, $pagesRoot)) {
                return $relative;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function readJsonMeta(string $directory): array
    {
        $file = $directory . DIRECTORY_SEPARATOR . 'page.json';
        if (!is_file($file)) {
            return [];
        }

        $raw = file_get_contents($file);
        if ($raw === false || $raw === '') {
            return [];
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    /** @return array<string, string>|null */
    private function readCache(): ?array
    {
        $file = Path::join($this->cachePath, 'pages.php');
        if (!is_file($file)) {
            return null;
        }

        $data = require $file;

        return is_array($data) ? $data : null;
    }

    /** @param array<string, string> $map */
    private function writeCache(array $map): void
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }

        $export = var_export($map, true);
        $code = "<?php\n\ndeclare(strict_types=1);\n\nreturn {$export};\n";
        file_put_contents(Path::join($this->cachePath, 'pages.php'), $code, LOCK_EX);
    }

    /** @return array<string, string> */
    private function scan(string $pagesRoot): array
    {
        $map = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $pagesRoot,
                \FilesystemIterator::SKIP_DOTS,
            ),
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $name = $file->getFilename();
            if ($name !== 'index.php' && $name !== 'index.html') {
                continue;
            }

            $real = $file->getRealPath();
            if ($real === false || !Path::inside($real, $pagesRoot)) {
                continue;
            }

            $relative = ltrim(substr(
                str_replace('\\', '/', $real),
                strlen(str_replace('\\', '/', $pagesRoot)),
            ), '/');

            $url = '/' . trim(dirname($relative), '/.');
            if ($url === '/' || $url === '/\\') {
                $url = '/';
            } else {
                $url = rtrim($url, '/');
            }

            if (!isset($map[$url]) || $name === 'index.php') {
                $map[$url] = $relative;
            }
        }

        return $map;
    }

    private function defaultLayout(): string
    {
        $file = Path::join($this->layoutsPath, 'default.php');
        $real = realpath($file);
        if ($real === false) {
            throw HttpException::serverError('Default layout is missing.');
        }

        return $real;
    }
}
