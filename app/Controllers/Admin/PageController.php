<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Response;

final class PageController extends AdminController
{
    public function index(): Response
    {
        $this->auth()->requirePermission('pages.view');

        return $this->adminView('admin/pages/index', [
            'title' => 'Pages',
            'pages' => $this->pages()->all(),
        ]);
    }

    public function create(): Response
    {
        $this->auth()->requirePermission('pages.manage');

        return $this->adminView('admin/pages/form', [
            'title' => 'New page',
            'page' => null,
            'action' => url('/admin/pages'),
        ]);
    }

    public function store(): Response
    {
        $this->auth()->requirePermission('pages.manage');

        $path = trim((string) $this->request()->input('path', ''));
        $title = trim((string) $this->request()->input('title', ''));
        $body = (string) $this->request()->input('body', '');

        try {
            $this->pages()->create($path, $title, $body);
        } catch (\InvalidArgumentException $e) {
            $this->session()->flash('error', $e->getMessage());
            $this->session()->flash('old', [
                'path' => $path,
                'title' => $title,
                'body' => $body,
            ]);

            return $this->redirect('/admin/pages/create');
        }

        $this->clearPageCache();
        $this->session()->flash('success', 'Page created.');

        return $this->redirect('/admin/pages');
    }

    public function edit(): Response
    {
        $this->auth()->requirePermission('pages.manage');
        $path = $this->requestPath();

        try {
            $page = $this->pages()->find($path);
        } catch (\Throwable $e) {
            $this->session()->flash('error', $e->getMessage());

            return $this->redirect('/admin/pages');
        }

        return $this->adminView('admin/pages/form', [
            'title' => 'Edit page',
            'page' => $page,
            'action' => url('/admin/pages/save') . ($path === '' ? '' : '?path=' . rawurlencode($path)),
        ]);
    }

    public function update(): Response
    {
        $this->auth()->requirePermission('pages.manage');
        $path = $this->requestPath();
        $title = trim((string) $this->request()->input('title', ''));
        $body = $this->request()->input('body');
        $body = is_string($body) ? $body : null;

        try {
            $page = $this->pages()->find($path);
            $this->pages()->update($path, $title, $page['editable'] ? $body : null);
        } catch (\Throwable $e) {
            $this->session()->flash('error', $e->getMessage());
            $query = $path === '' ? '' : ('?path=' . rawurlencode($path));

            return $this->redirect('/admin/pages/edit' . $query);
        }

        $this->clearPageCache();
        $this->session()->flash('success', 'Page saved.');

        return $this->redirect('/admin/pages');
    }

    public function destroy(): Response
    {
        $this->auth()->requirePermission('pages.manage');
        $path = $this->requestPath();

        try {
            $this->pages()->delete($path);
        } catch (\Throwable $e) {
            $this->session()->flash('error', $e->getMessage());

            return $this->redirect('/admin/pages');
        }

        $this->clearPageCache();
        $this->session()->flash('success', 'Page deleted.');

        return $this->redirect('/admin/pages');
    }

    public function convert(): Response
    {
        $this->auth()->requirePermission('pages.manage');
        if (!$this->auth()->isAdmin()) {
            $this->session()->flash('error', 'Only admins can convert PHP pages to HTML.');

            return $this->redirect('/admin/pages');
        }

        $path = $this->requestPath();

        try {
            $this->pages()->convertToHtml($path);
        } catch (\Throwable $e) {
            $this->session()->flash('error', $e->getMessage());
            $query = $path === '' ? '' : ('?path=' . rawurlencode($path));

            return $this->redirect('/admin/pages/edit' . $query);
        }

        $this->clearPageCache();
        $this->session()->flash('success', 'Page converted to editable HTML. Review the content before publishing.');
        $query = $path === '' ? '' : ('?path=' . rawurlencode($path));

        return $this->redirect('/admin/pages/edit' . $query);
    }

    private function requestPath(): string
    {
        $path = (string) $this->request()->input('path', '');
        $path = str_replace('\\', '/', trim($path));
        $path = trim($path, '/');

        return $path;
    }

    private function clearPageCache(): void
    {
        $cache = $this->app->basePath('storage/cache/pages.php');
        if (is_file($cache)) {
            unlink($cache);
        }
    }
}
