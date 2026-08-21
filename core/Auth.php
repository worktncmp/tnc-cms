<?php

declare(strict_types=1);

namespace Core;

final class Auth
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EDITOR = 'editor';

    /** @var array<string, list<string>> */
    private const PERMISSIONS = [
        self::ROLE_ADMIN => [
            'admin.access',
            'pages.view',
            'pages.manage',
            'messages.view',
            'messages.manage',
            'products.view',
            'products.manage',
            'users.view',
            'users.manage',
        ],
        self::ROLE_EDITOR => [
            'admin.access',
            'pages.view',
            'pages.manage',
            'messages.view',
            'messages.manage',
        ],
    ];

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

        $user = $this->app->db()->fetch(
            'SELECT id, email, name, role, created_at FROM users WHERE id = ? LIMIT 1',
            [$id],
        );

        if ($user === null) {
            return null;
        }

        if (!isset($user['role']) || $user['role'] === '' || $user['role'] === null) {
            $user['role'] = self::ROLE_EDITOR;
        }

        return $user;
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

    public function role(): string
    {
        $user = $this->user();
        if ($user === null) {
            return '';
        }

        $role = (string) ($user['role'] ?? self::ROLE_EDITOR);

        return $role !== '' ? $role : self::ROLE_EDITOR;
    }

    public function isAdmin(): bool
    {
        return $this->role() === self::ROLE_ADMIN;
    }

    public function can(string $permission): bool
    {
        $role = $this->role();
        $allowed = self::PERMISSIONS[$role] ?? [];

        return in_array($permission, $allowed, true);
    }

    /** @return array<string, mixed> */
    public function requirePermission(string $permission): array
    {
        $user = $this->require();
        if (!$this->can($permission)) {
            throw HttpException::forbidden('You do not have permission to do that.');
        }

        return $user;
    }
}
