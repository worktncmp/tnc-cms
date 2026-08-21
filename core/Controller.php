<?php

declare(strict_types=1);

namespace Core;

abstract class Controller
{
    public function __construct(protected Application $app)
    {
    }

    /** @param array<string, mixed> $data */
    protected function view(string $name, array $data = [], string $layout = 'default'): Response
    {
        $data = $this->withSharedViewData($data);
        $html = $this->app->view()->render($name, $data);
        $layoutFile = Path::join($this->app->basePath('app/Views/layouts'), $layout . '.php');
        $page = $this->app->view()->layout($html, $layoutFile, $data);

        return Response::html($page);
    }

    protected function redirect(string $path, int $status = 302): Response
    {
        if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
            $path = url($path);
        }

        return Response::redirect($path, $status);
    }

    protected function request(): Request
    {
        return $this->app->request();
    }

    protected function session(): Session
    {
        return $this->app->session();
    }

    protected function auth(): Auth
    {
        return $this->app->auth();
    }

    protected function db(): Database
    {
        return $this->app->db();
    }

    /** @param array<string, mixed> $data */
    private function withSharedViewData(array $data): array
    {
        $data['appName'] ??= $this->app->config('app.name');
        $data['title'] ??= $this->app->config('app.name');
        $data['flashSuccess'] ??= $this->session()->getFlash('success');
        $data['flashError'] ??= $this->session()->getFlash('error');
        $data['currentUser'] ??= $this->app->auth()->user();
        $data['currentPath'] ??= $this->request()->path;

        return $data;
    }
}
