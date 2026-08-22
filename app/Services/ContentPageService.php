<?php

declare(strict_types=1);

namespace App\Services;

use Core\HttpException;
use Core\Path;

final class ContentPageService
{
    public function __construct(private readonly string $pagesPath)
    {
    }

    /** @return list<array{path: string, url: string, title: string, type: string, editable: bool}> */
    public function all(): array
    {
        $root = $this->root();
        $pages = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
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
            if ($real === false || !Path::inside($real, $root)) {
                continue;
            }

            $relative = ltrim(str_replace('\\', '/', substr(
                str_replace('\\', '/', $real),
                strlen(str_replace('\\', '/', $root)),
            )), '/');

            $dir = trim(dirname($relative), '/.');
            $path = $dir === '' || $dir === '.' ? '' : $dir;
            $type = $name === 'index.html' ? 'html' : 'php';

            if (isset($pages[$path]) && $type !== 'html') {
                continue;
            }

            $pages[$path] = [
                'path' => $path,
                'url' => $path === '' ? '/' : '/' . $path,
                'title' => $this->titleFor($path, dirname($real)),
                'type' => $type,
                'editable' => $type === 'html',
            ];
        }

        ksort($pages);

        return array_values($pages);
    }

    /** @return array{path: string, url: string, title: string, type: string, body: string, editable: bool} */
    public function find(string $path): array
    {
        $path = $this->normalizePath($path);
        $directory = $this->directoryFor($path);
        $html = $directory . DIRECTORY_SEPARATOR . 'index.html';
        $php = $directory . DIRECTORY_SEPARATOR . 'index.php';

        if (is_file($html)) {
            $body = (string) file_get_contents($html);

            return [
                'path' => $path,
                'url' => $path === '' ? '/' : '/' . $path,
                'title' => $this->titleFor($path, $directory),
                'type' => 'html',
                'body' => $body,
                'editable' => true,
            ];
        }

        if (is_file($php)) {
            return [
                'path' => $path,
                'url' => $path === '' ? '/' : '/' . $path,
                'title' => $this->titleFor($path, $directory),
                'type' => 'php',
                'body' => '',
                'editable' => false,
            ];
        }

        throw HttpException::notFound('Page not found.');
    }

    public function create(string $path, string $title, string $body): void
    {
        $path = $this->normalizePath($path);
        if ($path === '') {
            throw new \InvalidArgumentException('Use the home page editor to change the home page.');
        }

        $body = trim($body);
        if ($body === '' || $body === '<p></p>' || $body === '<p><br></p>') {
            throw new \InvalidArgumentException('Page content cannot be empty.');
        }

        $directory = $this->directoryFor($path);
        if (is_file($directory . '/index.html') || is_file($directory . '/index.php')) {
            throw new \InvalidArgumentException('A page already exists at that path.');
        }

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Could not create the page folder.');
        }

        $this->writeHtmlPage($directory, $title, $body);
    }

    public function update(string $path, string $title, ?string $body): void
    {
        $page = $this->find($path);
        $directory = $this->directoryFor($page['path']);

        if ($page['type'] === 'html') {
            if ($body === null) {
                throw new \InvalidArgumentException('Page body is required.');
            }
            $this->writeHtmlPage($directory, $title, $body);
            return;
        }

        $this->writeJsonTitle($directory, $title);
    }

    public function delete(string $path): void
    {
        $path = $this->normalizePath($path);
        if ($path === '') {
            throw new \InvalidArgumentException('The home page cannot be deleted.');
        }

        $directory = $this->directoryFor($path);
        foreach (['index.html', 'index.php', 'page.json'] as $file) {
            $full = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_file($full)) {
                unlink($full);
            }
        }

        $this->removeEmptyParents($directory);
    }

    public function convertToHtml(string $path): void
    {
        $page = $this->find($path);
        if ($page['type'] !== 'php') {
            throw new \InvalidArgumentException('This page is already editable HTML.');
        }

        $directory = $this->directoryFor($page['path']);
        $phpFile = $directory . DIRECTORY_SEPARATOR . 'index.php';
        $raw = (string) file_get_contents($phpFile);
        $body = $this->extractHtmlFromPhp($raw);
        if ($body === '') {
            $body = '<h1>' . htmlspecialchars($page['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h1>'
                . "\n<p>This page was converted from PHP. Replace this text in the admin editor.</p>";
        }

        $this->writeHtmlPage($directory, $page['title'], $body);
    }

    public function convertToPhp(string $path): void
    {
        $page = $this->find($path);
        if ($page['type'] !== 'html') {
            throw new \InvalidArgumentException('This page is already a PHP template.');
        }

        $directory = $this->directoryFor($page['path']);
        $htmlFile = $directory . DIRECTORY_SEPARATOR . 'index.html';
        $body = trim((string) file_get_contents($htmlFile));
        if ($body === '') {
            throw new \InvalidArgumentException('Page content is empty. Add content before converting to PHP.');
        }

        $this->writePhpPage($directory, $page['title'], $body);
    }

    public function count(): int
    {
        return count($this->all());
    }

    private function extractHtmlFromPhp(string $raw): string
    {
        if (preg_match('/\?>\s*(.*)\s*$/s', $raw, $matches) === 1) {
            return trim($matches[1]);
        }

        return '';
    }

    private function writeHtmlPage(string $directory, string $title, string $body): void
    {
        $this->assertInsidePages($directory);
        $this->writeJsonTitle($directory, $title);
        file_put_contents($directory . DIRECTORY_SEPARATOR . 'index.html', $this->normalizeBody($body));

        $php = $directory . DIRECTORY_SEPARATOR . 'index.php';
        if (is_file($php)) {
            unlink($php);
        }
    }

    private function writePhpPage(string $directory, string $title, string $body): void
    {
        $this->assertInsidePages($directory);
        $this->writeJsonTitle($directory, $title);

        $header = "<?php\n\$page = [\n    'title' => " . var_export(trim($title), true) . ",\n];\n?>\n";
        $body = str_replace("\r\n", "\n", trim($body));
        file_put_contents($directory . DIRECTORY_SEPARATOR . 'index.php', $header . $body . "\n");

        $html = $directory . DIRECTORY_SEPARATOR . 'index.html';
        if (is_file($html)) {
            unlink($html);
        }
    }

    private function writeJsonTitle(string $directory, string $title): void
    {
        $this->assertInsidePages($directory);
        $title = trim($title);
        if ($title === '') {
            throw new \InvalidArgumentException('Title is required.');
        }

        $meta = ['title' => $title];
        $json = json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('Could not encode page metadata.');
        }

        file_put_contents($directory . DIRECTORY_SEPARATOR . 'page.json', $json . "\n");
    }

    private function normalizeBody(string $body): string
    {
        $body = str_replace("\r\n", "\n", $body);
        $body = $this->sanitizeHtml($body);
        $body = trim($body);

        return $body . "\n";
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
        $html = preg_replace('#<iframe\b[^>]*>.*?</iframe>#is', '', $html) ?? $html;
        $html = preg_replace('#<object\b[^>]*>.*?</object>#is', '', $html) ?? $html;
        $html = preg_replace('#<embed\b[^>]*/?>#is', '', $html) ?? $html;
        $html = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? $html;
        $html = preg_replace('#javascript\s*:#i', '', $html) ?? $html;

        return $html;
    }

    private function titleFor(string $path, string $directory): string
    {
        $jsonFile = $directory . DIRECTORY_SEPARATOR . 'page.json';
        if (is_file($jsonFile)) {
            $raw = file_get_contents($jsonFile);
            $data = is_string($raw) ? json_decode($raw, true) : null;
            if (is_array($data) && isset($data['title']) && is_string($data['title']) && $data['title'] !== '') {
                return $data['title'];
            }
        }

        if ($path === '') {
            return 'Home';
        }

        $leaf = basename(str_replace('\\', '/', $path));

        return ucwords(str_replace(['-', '_'], ' ', $leaf));
    }

    private function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = trim($path, '/');

        if ($path === '' || $path === '.') {
            return '';
        }

        if (str_contains($path, '..') || str_contains($path, "\0")) {
            throw new \InvalidArgumentException('Invalid page path.');
        }

        if (!preg_match('#^[a-z0-9]+(?:-[a-z0-9]+)*(?:/[a-z0-9]+(?:-[a-z0-9]+)*)*$#', $path)) {
            throw new \InvalidArgumentException(
                'Path must use lowercase letters, numbers, and hyphens only. Example: our-team or services/pricing (not spaces or capitals).',
            );
        }

        return $path;
    }

    private function directoryFor(string $path): string
    {
        $root = $this->root();
        if ($path === '') {
            return $root;
        }

        $directory = Path::join($root, $path);
        $this->assertInsidePages($directory);

        return $directory;
    }

    private function root(): string
    {
        $root = realpath($this->pagesPath);
        if ($root === false) {
            throw new \RuntimeException('content/pages is missing.');
        }

        return $root;
    }

    private function assertInsidePages(string $directory): void
    {
        $root = $this->root();
        $real = realpath($directory);
        if ($real === false) {
            $parent = realpath(dirname($directory));
            if ($parent === false || !Path::inside($parent, $root)) {
                throw new \RuntimeException('Invalid page directory.');
            }
            return;
        }

        if (!Path::inside($real, $root)) {
            throw new \RuntimeException('Invalid page directory.');
        }
    }

    private function removeEmptyParents(string $directory): void
    {
        $root = $this->root();
        $current = realpath($directory) ?: $directory;

        while (Path::inside((string) realpath($current) ?: $current, $root)) {
            $real = realpath($current);
            if ($real === false || $real === $root) {
                break;
            }

            $items = scandir($real);
            if ($items === false) {
                break;
            }
            $items = array_diff($items, ['.', '..']);
            if ($items !== []) {
                break;
            }

            rmdir($real);
            $current = dirname($real);
        }
    }
}
