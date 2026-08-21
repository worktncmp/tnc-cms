<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Auth;
use Core\HttpException;
use Core\Response;

final class UserController extends AdminController
{
    public function index(): Response
    {
        $this->auth()->requirePermission('users.view');
        $users = $this->db()->fetchAll(
            'SELECT id, email, name, role, created_at FROM users ORDER BY id ASC',
        );

        return $this->adminView('admin/users/index', [
            'title' => 'Users',
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        $this->auth()->requirePermission('users.manage');

        return $this->adminView('admin/users/form', [
            'title' => 'New user',
        ]);
    }

    public function store(): Response
    {
        $this->auth()->requirePermission('users.manage');

        $name = trim((string) $this->request()->input('name', ''));
        $email = trim((string) $this->request()->input('email', ''));
        $password = (string) $this->request()->input('password', '');
        $role = (string) $this->request()->input('role', Auth::ROLE_EDITOR);

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session()->flash('error', 'Name and a valid email are required.');
            $this->session()->flash('old', compact('name', 'email', 'role'));

            return $this->redirect('/admin/users/create');
        }

        if (strlen($password) < 8) {
            $this->session()->flash('error', 'Password must be at least 8 characters.');
            $this->session()->flash('old', compact('name', 'email', 'role'));

            return $this->redirect('/admin/users/create');
        }

        if (!in_array($role, [Auth::ROLE_ADMIN, Auth::ROLE_EDITOR], true)) {
            $role = Auth::ROLE_EDITOR;
        }

        $exists = $this->db()->fetch('SELECT id FROM users WHERE email = ?', [$email]);
        if ($exists !== null) {
            $this->session()->flash('error', 'That email is already registered.');
            $this->session()->flash('old', compact('name', 'email', 'role'));

            return $this->redirect('/admin/users/create');
        }

        $this->db()->insert('users', [
            'email' => $email,
            'password_hash' => $this->auth()->hash($password),
            'name' => $name,
            'role' => $role,
            'created_at' => date('c'),
        ]);

        $this->session()->flash('success', 'User created.');

        return $this->redirect('/admin/users');
    }

    public function updateRole(string $id): Response
    {
        $this->auth()->requirePermission('users.manage');
        if (!ctype_digit($id)) {
            throw HttpException::notFound();
        }

        $role = (string) $this->request()->input('role', Auth::ROLE_EDITOR);
        if (!in_array($role, [Auth::ROLE_ADMIN, Auth::ROLE_EDITOR], true)) {
            $this->session()->flash('error', 'Invalid role.');

            return $this->redirect('/admin/users');
        }

        $user = $this->db()->fetch('SELECT id FROM users WHERE id = ?', [(int) $id]);
        if ($user === null) {
            throw HttpException::notFound();
        }

        if ((int) $user['id'] === (int) $this->auth()->id() && $role !== Auth::ROLE_ADMIN) {
            $this->session()->flash('error', 'You cannot remove your own admin role.');

            return $this->redirect('/admin/users');
        }

        $this->db()->update('users', ['role' => $role], 'id = ?', [(int) $id]);
        $this->session()->flash('success', 'Role updated.');

        return $this->redirect('/admin/users');
    }
}
