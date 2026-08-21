<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use Core\Controller;
use Core\HttpException;
use Core\Response;

final class ProductController extends Controller
{
    public function index(): Response
    {
        $products = (new Product($this->db()))->all();

        return $this->view('products/index', [
            'title' => 'Work',
            'products' => $products,
        ]);
    }

    public function show(string $id): Response
    {
        if (!ctype_digit($id)) {
            throw HttpException::notFound();
        }

        $product = (new Product($this->db()))->find((int) $id);
        if ($product === null) {
            throw HttpException::notFound();
        }

        return $this->view('products/show', [
            'title' => (string) $product['title'],
            'product' => $product,
        ]);
    }
}
