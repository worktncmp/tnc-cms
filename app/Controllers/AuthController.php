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
            return $this->redirect($this->intendedPath() ?? '/admin');
        }

        $redirect = safe_internal_path((string) $this->request()->input('redirect', ''));
        if ($redirect !== null) {
            $this->session()->set('intended_url', $redirect);
        }

        return $this->view('auth/login', [
            'title' => 'Sign in',
            'redirect' => $this->intendedPath() ?? '',
        ]);
    }

    public function login(): Response
    {
        $email = trim((string) $this->request()->input('email', ''));
        $password = (string) $this->request()->input('password', '');
        $redirect = safe_internal_path((string) $this->request()->input('redirect', ''));
        if ($redirect !== null) {
            $this->session()->set('intended_url', $redirect);
        }

        if (!$this->auth()->attempt($email, $password)) {
            $this->session()->flash('error', 'Those details do not match our records.');
            $this->session()->flash('old', ['email' => $email]);

            return $this->redirect('/login');
        }

        $intended = $this->consumeIntendedPath();

        return $this->redirect($intended ?? '/admin');
    }

    public function logout(): Response
    {
        $this->auth()->logout();
        $this->session()->remove('intended_url');

        return $this->redirect('/');
    }

    private function intendedPath(): ?string
    {
        return safe_internal_path((string) $this->session()->get('intended_url', ''));
    }

    private function consumeIntendedPath(): ?string
    {
        $path = $this->intendedPath();
        $this->session()->remove('intended_url');

        return $path;
    }
}
