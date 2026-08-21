<?php

declare(strict_types=1);

namespace Core;

final class Auth
{
    public function __construct(private readonly Application $app)
    {
    }

    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->app->db()->fetch('SELECT * FROM users WHERE email = ? LIMIT 1', [$email]);
        if ($user === null) {
            return false;
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            return false;
        }

        $this->app->session()->regenerate();
        $this->app->session()->set('user_id', $user['id']);

        return true;
    }

    public function logout(): void
    {
        $this->app->session()->remove('user_id');
        $this->app->session()->regenerate();
    }

    public function id(): int|string|null
    {
        $id = $this->app->session()->get('user_id');
        if ($id === null || $id === '') {
            return null;
        }

        return is_int($id) || is_string($id) ? $id : null;
    }

    public function check(): bool
    {
        return $this->id() !== null;
    }

    /** @return array<string, mixed>|null */
    public function user(): ?array
    {
        $id = $this->id();
        if ($id === null) {
            return null;
        }

        return $this->app->db()->fetch(
            'SELECT id, email, name, created_at FROM users WHERE id = ? LIMIT 1',
            [$id],
        );
    }

    /** @return array<string, mixed> */
    public function require(): array
    {
        $user = $this->user();
        if ($user === null) {
            throw HttpException::forbidden('Authentication required.');
        }

        return $user;
    }
}
