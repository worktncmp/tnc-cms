<?php

declare(strict_types=1);

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
$router->get('/account', [AuthController::class, 'account']);
