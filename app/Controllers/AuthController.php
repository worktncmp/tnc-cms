<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Response;

final class AuthController extends Controller
{
    public function show(): Response
    {
        if ($this->auth()->check()) {
            return $this->redirect('/account');
        }

        return $this->view('auth/login', [
            'title' => 'Sign in',
        ]);
    }

    public function login(): Response
    {
        $email = trim((string) $this->request()->input('email', ''));
        $password = (string) $this->request()->input('password', '');

        if (!$this->auth()->attempt($email, $password)) {
            $this->session()->flash('error', 'Those details do not match our records.');
            $this->session()->flash('old', ['email' => $email]);

            return $this->redirect('/login');
        }

        return $this->redirect('/account');
    }

    public function logout(): Response
    {
        $this->auth()->logout();

        return $this->redirect('/');
    }

    public function account(): Response
    {
        $user = $this->auth()->require();

        return $this->view('auth/account', [
            'title' => 'Account',
            'user' => $user,
        ]);
    }
}
