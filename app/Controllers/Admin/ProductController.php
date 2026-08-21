<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Product;
use Core\HttpException;
use Core\Response;

final class ProductController extends AdminController
{
    public function index(): Response
    {
        $this->auth()->requirePermission('products.view');
        $products = (new Product($this->db()))->all();

        return $this->adminView('admin/products/index', [
            'title' => 'Products',
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        $this->auth()->requirePermission('products.manage');
        return $this->adminView('admin/products/form', [
            'title' => 'New product',
            'product' => null,
            'action' => url('/admin/products'),
        ]);
    }

    public function store(): Response
    {
        $this->auth()->requirePermission('products.manage');
        [$title, $summary, $body, $error] = $this->validated();
        if ($error !== null) {
            $this->session()->flash('error', $error);
            $this->session()->flash('old', [
                'title' => $title,
                'summary' => $summary,
                'body' => $body,
            ]);

            return $this->redirect('/admin/products/create');
        }

        $this->db()->insert('products', [
            'title' => $title,
            'summary' => $summary,
            'body' => $body,
        ]);

        $this->session()->flash('success', 'Product created.');

        return $this->redirect('/admin/products');
    }

    public function edit(string $id): Response
    {
        $this->auth()->requirePermission('products.manage');
        $product = $this->findProduct($id);

        return $this->adminView('admin/products/form', [
            'title' => 'Edit product',
            'product' => $product,
            'action' => url('/admin/products/' . $product['id']),
        ]);
    }

    public function update(string $id): Response
    {
        $this->auth()->requirePermission('products.manage');
        $product = $this->findProduct($id);
        [$title, $summary, $body, $error] = $this->validated();

        if ($error !== null) {
            $this->session()->flash('error', $error);
            $this->session()->flash('old', [
                'title' => $title,
                'summary' => $summary,
                'body' => $body,
            ]);

            return $this->redirect('/admin/products/' . $product['id'] . '/edit');
        }

        $this->db()->update(
            'products',
            [
                'title' => $title,
                'summary' => $summary,
                'body' => $body,
            ],
            'id = ?',
            [(int) $product['id']],
        );

        $this->session()->flash('success', 'Product updated.');

        return $this->redirect('/admin/products');
    }

    public function destroy(string $id): Response
    {
        $this->auth()->requirePermission('products.manage');
        $product = $this->findProduct($id);
        $this->db()->delete('products', 'id = ?', [(int) $product['id']]);
        $this->session()->flash('success', 'Product deleted.');

        return $this->redirect('/admin/products');
    }

    /** @return array{0: string, 1: string, 2: string, 3: ?string} */
    private function validated(): array
    {
        $title = trim((string) $this->request()->input('title', ''));
        $summary = trim((string) $this->request()->input('summary', ''));
        $body = trim((string) $this->request()->input('body', ''));

        if ($title === '' || $summary === '' || $body === '') {
            return [$title, $summary, $body, 'Please fill in title, summary, and body.'];
        }

        return [$title, $summary, $body, null];
    }

    /** @return array<string, mixed> */
    private function findProduct(string $id): array
    {
        if (!ctype_digit($id)) {
            throw HttpException::notFound();
        }

        $product = (new Product($this->db()))->find((int) $id);
        if ($product === null) {
            throw HttpException::notFound();
        }

        return $product;
    }
}
