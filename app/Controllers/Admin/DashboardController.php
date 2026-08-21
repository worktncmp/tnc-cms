<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Response;

final class DashboardController extends AdminController
{
    public function index(): Response
    {
        $messageCount = 0;
        $productCount = 0;
        $userCount = 0;
        $pageCount = 0;
        $recent = [];

        if ($this->auth()->can('messages.view')) {
            $messages = $this->db()->fetch('SELECT COUNT(*) AS total FROM messages');
            $messageCount = (int) ($messages['total'] ?? 0);
            $recent = $this->db()->fetchAll(
                'SELECT id, name, email, created_at FROM messages ORDER BY id DESC LIMIT 5',
            );
        }

        if ($this->auth()->can('products.view')) {
            $products = $this->db()->fetch('SELECT COUNT(*) AS total FROM products');
            $productCount = (int) ($products['total'] ?? 0);
        }

        if ($this->auth()->can('users.view')) {
            $users = $this->db()->fetch('SELECT COUNT(*) AS total FROM users');
            $userCount = (int) ($users['total'] ?? 0);
        }

        if ($this->auth()->can('pages.view')) {
            $pageCount = $this->pages()->count();
        }

        $mediaCount = 0;
        if ($this->auth()->can('media.view')) {
            $mediaCount = count((new \App\Services\MediaService($this->app->basePath('public/uploads')))->all());
        }

        return $this->adminView('admin/dashboard', [
            'title' => 'Admin',
            'messageCount' => $messageCount,
            'productCount' => $productCount,
            'userCount' => $userCount,
            'pageCount' => $pageCount,
            'mediaCount' => $mediaCount,
            'recentMessages' => $recent,
            'role' => $this->auth()->role(),
        ]);
    }
}
