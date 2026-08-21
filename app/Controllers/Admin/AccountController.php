<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Response;

final class AccountController extends AdminController
{
    public function show(): Response
    {
        return $this->adminView('admin/account', [
            'title' => 'Your account',
            'user' => $this->auth()->user(),
        ]);
    }

    public function updatePassword(): Response
    {
        $user = $this->auth()->require();
        $current = (string) $this->request()->input('current_password', '');
        $password = (string) $this->request()->input('password', '');
        $confirm = (string) $this->request()->input('password_confirmation', '');

        $row = $this->db()->fetch('SELECT password_hash FROM users WHERE id = ?', [$user['id']]);
        if ($row === null || !password_verify($current, (string) $row['password_hash'])) {
            $this->session()->flash('error', 'Current password is incorrect.');

            return $this->redirect('/admin/account');
        }

        if (strlen($password) < 8) {
            $this->session()->flash('error', 'New password must be at least 8 characters.');

            return $this->redirect('/admin/account');
        }

        if ($password !== $confirm) {
            $this->session()->flash('error', 'New password and confirmation do not match.');

            return $this->redirect('/admin/account');
        }

        $this->db()->update(
            'users',
            ['password_hash' => $this->auth()->hash($password)],
            'id = ?',
            [$user['id']],
        );

        $this->session()->flash('success', 'Password updated.');

        return $this->redirect('/admin/account');
    }
}
