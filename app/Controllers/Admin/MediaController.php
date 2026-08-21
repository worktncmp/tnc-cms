<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\MediaService;
use Core\Response;

final class MediaController extends AdminController
{
    public function index(): Response
    {
        $this->auth()->requirePermission('media.view');
        $media = $this->media();

        return $this->adminView('admin/media/index', [
            'title' => 'Media',
            'files' => $media->all(),
            'allowed' => $media->allowedExtensions(),
        ]);
    }

    public function store(): Response
    {
        $this->auth()->requirePermission('media.manage');
        $media = $this->media();
        $file = $this->request()->files['image'] ?? null;

        if (!is_array($file)) {
            $this->session()->flash('error', 'Choose an image to upload.');

            return $this->redirect('/admin/media');
        }

        try {
            $stored = $this->app->upload()->store(
                $file,
                $media->directory(),
                $media->allowedExtensions(),
                2_000_000,
            );
        } catch (\Throwable $e) {
            $this->session()->flash('error', $e->getMessage());

            return $this->redirect('/admin/media');
        }

        $this->session()->flash('success', 'Uploaded ' . $stored . '. Copy the URL into your page HTML.');

        return $this->redirect('/admin/media');
    }

    public function destroy(): Response
    {
        $this->auth()->requirePermission('media.manage');
        $name = (string) $this->request()->input('name', '');

        try {
            $this->media()->delete($name);
        } catch (\Throwable $e) {
            $this->session()->flash('error', $e->getMessage());

            return $this->redirect('/admin/media');
        }

        $this->session()->flash('success', 'File deleted.');

        return $this->redirect('/admin/media');
    }

    private function media(): MediaService
    {
        return new MediaService($this->app->basePath('public/uploads'));
    }
}
