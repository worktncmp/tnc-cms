<?php

declare(strict_types=1);

namespace Core;

final class View
{
    public function __construct(
        private readonly string $viewsPath,
        private readonly string $basePath,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function render(string $name, array $data = []): string
    {
        $this->assertName($name);
        $file = Path::join($this->viewsPath, $name . '.php');
        ['html' => $html] = $this->includeFile($file, $data, [$this->viewsPath]);

        return $html;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $allowedRoots
     * @return array{html: string, vars: array<string, mixed>}
     */
    public function includeFile(string $file, array $data = [], array $allowedRoots = []): array
    {
        if ($allowedRoots === []) {
            $allowedRoots = [$this->viewsPath];
        }

        $this->assertInside($file, $allowedRoots);

        $render = static function (string $__file, array $__data): array {
            extract($__data, EXTR_SKIP);
            ob_start();
            include $__file;
            $html = (string) ob_get_clean();
            $vars = get_defined_vars();
            unset($vars['__file'], $vars['__data'], $vars['html']);

            return ['html' => $html, 'vars' => $vars];
        };

        return $render($file, $data);
    }

    /** @param array<string, mixed> $data */
    public function partial(string $name, array $data = []): string
    {
        return $this->render('partials/' . $name, $data);
    }

    /** @param array<string, mixed> $data */
    public function component(string $name, array $data = []): string
    {
        return $this->render('components/' . $name, $data);
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $allowedRoots
     */
    public function layout(string $content, string $layoutFile, array $data, array $allowedRoots = []): string
    {
        $data['content'] = $content;
        if ($allowedRoots === []) {
            $allowedRoots = [$this->viewsPath, Path::join($this->basePath, 'content/pages')];
        }

        ['html' => $html] = $this->includeFile($layoutFile, $data, $allowedRoots);

        return $html;
    }

    public function exists(string $name): bool
    {
        if (!preg_match('#^[a-zA-Z0-9/_-]+$#', $name)) {
            return false;
        }

        $file = Path::join($this->viewsPath, $name . '.php');
        $real = realpath($file);
        $root = realpath($this->viewsPath);

        return $real !== false && $root !== false && Path::inside($real, $root);
    }

    private function assertName(string $name): void
    {
        if (!preg_match('#^[a-zA-Z0-9/_-]+$#', $name)) {
            throw new \InvalidArgumentException('Invalid view name.');
        }
    }

    /** @param list<string> $roots */
    private function assertInside(string $file, array $roots): void
    {
        $real = realpath($file);
        if ($real === false || !is_file($real)) {
            throw new \RuntimeException('View not found: ' . $file);
        }

        foreach ($roots as $root) {
            $rootReal = realpath($root);
            if ($rootReal !== false && Path::inside($real, $rootReal)) {
                return;
            }
        }

        throw new \RuntimeException('View path is not allowed.');
    }
}
