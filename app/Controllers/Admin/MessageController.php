<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\HttpException;
use Core\Response;

final class MessageController extends AdminController
{
    public function index(): Response
    {
        $this->auth()->requirePermission('messages.view');
        $messages = $this->db()->fetchAll(
            'SELECT id, name, email, body, created_at FROM messages ORDER BY id DESC',
        );

        return $this->adminView('admin/messages/index', [
            'title' => 'Messages',
            'messages' => $messages,
        ]);
    }

    public function show(string $id): Response
    {
        $this->auth()->requirePermission('messages.view');
        if (!ctype_digit($id)) {
            throw HttpException::notFound();
        }

        $message = $this->db()->fetch(
            'SELECT id, name, email, body, created_at FROM messages WHERE id = ?',
            [(int) $id],
        );

        if ($message === null) {
            throw HttpException::notFound();
        }

        return $this->adminView('admin/messages/show', [
            'title' => 'Message',
            'message' => $message,
        ]);
    }

    public function destroy(string $id): Response
    {
        $this->auth()->requirePermission('messages.manage');
        if (!ctype_digit($id)) {
            throw HttpException::notFound();
        }

        $deleted = $this->db()->delete('messages', 'id = ?', [(int) $id]);
        if ($deleted === 0) {
            throw HttpException::notFound();
        }

        $this->session()->flash('success', 'Message deleted.');

        return $this->redirect('/admin/messages');
    }
}
