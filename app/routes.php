<?php

declare(strict_types=1);

use App\Controllers\Admin\AccountController as AdminAccountController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\MediaController as AdminMediaController;
use App\Controllers\Admin\MessageController as AdminMessageController;
use App\Controllers\Admin\PageController as AdminPageController;
use App\Controllers\Admin\ProductController as AdminProductController;
use App\Controllers\Admin\UserController as AdminUserController;
use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\ProductController;
use Core\Router;

assert(isset($router) && $router instanceof Router);

$router->get('/products', [ProductController::class, 'index'], 'products.index');
$router->get('/products/{id}', [ProductController::class, 'show'], 'product.show');
$router->post('/contact', [ContactController::class, 'submit']);

$router->get('/login', [AuthController::class, 'show']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/admin', [DashboardController::class, 'index'], 'admin.dashboard');
$router->get('/admin/account', [AdminAccountController::class, 'show']);
$router->post('/admin/account/password', [AdminAccountController::class, 'updatePassword']);

$router->get('/admin/pages', [AdminPageController::class, 'index'], 'admin.pages');
$router->get('/admin/pages/create', [AdminPageController::class, 'create']);
$router->post('/admin/pages', [AdminPageController::class, 'store']);
$router->get('/admin/pages/edit', [AdminPageController::class, 'edit']);
$router->post('/admin/pages/save', [AdminPageController::class, 'update']);
$router->post('/admin/pages/delete', [AdminPageController::class, 'destroy']);
$router->post('/admin/pages/convert', [AdminPageController::class, 'convert']);

$router->get('/admin/media', [AdminMediaController::class, 'index'], 'admin.media');
$router->post('/admin/media', [AdminMediaController::class, 'store']);
$router->post('/admin/media/delete', [AdminMediaController::class, 'destroy']);

$router->get('/admin/messages', [AdminMessageController::class, 'index'], 'admin.messages');
$router->get('/admin/messages/{id}', [AdminMessageController::class, 'show']);
$router->post('/admin/messages/{id}/delete', [AdminMessageController::class, 'destroy']);

$router->get('/admin/products', [AdminProductController::class, 'index'], 'admin.products');
$router->get('/admin/products/create', [AdminProductController::class, 'create']);
$router->post('/admin/products', [AdminProductController::class, 'store']);
$router->get('/admin/products/{id}/edit', [AdminProductController::class, 'edit']);
$router->post('/admin/products/{id}', [AdminProductController::class, 'update']);
$router->post('/admin/products/{id}/delete', [AdminProductController::class, 'destroy']);

$router->get('/admin/users', [AdminUserController::class, 'index'], 'admin.users');
$router->get('/admin/users/create', [AdminUserController::class, 'create']);
$router->post('/admin/users', [AdminUserController::class, 'store']);
$router->post('/admin/users/{id}/role', [AdminUserController::class, 'updateRole']);
