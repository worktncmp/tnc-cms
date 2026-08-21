<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\ContentPageService;
use Core\Application;
use Core\Controller;
use Core\Response;

abstract class AdminController extends Controller
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->auth()->requirePermission('admin.access');
    }

    /** @param array<string, mixed> $data */
    protected function adminView(string $name, array $data = []): Response
    {
        $data['canPages'] = $this->auth()->can('pages.view');
        $data['canMedia'] = $this->auth()->can('media.view');
        $data['canMessages'] = $this->auth()->can('messages.view');
        $data['canProducts'] = $this->auth()->can('products.view');
        $data['canUsers'] = $this->auth()->can('users.view');
        $data['isAdmin'] = $this->auth()->isAdmin();

        return $this->view($name, $data, 'admin');
    }

    protected function pages(): ContentPageService
    {
        return new ContentPageService($this->app->basePath('content/pages'));
    }
}
