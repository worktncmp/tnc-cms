<?php

declare(strict_types=1);

namespace Core;

final class Session
{
    /** @var array<string, mixed> */
    private $store;

    /** @param array<string, mixed> $store */
    private function __construct(&$store)
    {
        $this->store =& $store;
        $this->ageFlash();
    }

    /** @param array<string, mixed> $config */
    public static function start(array $config, bool $secure = false): self
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $savePath = $config['path'] ?? null;
            if (is_string($savePath) && $savePath !== '') {
                if (!is_dir($savePath)) {
                    mkdir($savePath, 0700, true);
                }
                session_save_path($savePath);
            }

            session_name((string) ($config['name'] ?? 'cms_session'));
            session_set_cookie_params([
                'lifetime' => (int) ($config['lifetime'] ?? 0),
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        return new self($_SESSION);
    }

    /** @param array<string, mixed> $store */
    public static function fake(array &$store): self
    {
        return new self($store);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->store[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->store[$key]);
    }

    public function flash(string $key, mixed $value): void
    {
        $flash = $this->store['_flash_new'] ?? [];
        $flash[$key] = $value;
        $this->store['_flash_new'] = $flash;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $old = $this->store['_flash_old'] ?? [];

        return is_array($old) ? ($old[$key] ?? $default) : $default;
    }

    public function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function destroy(): void
    {
        $this->store = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    private function ageFlash(): void
    {
        $this->store['_flash_old'] = $this->store['_flash_new'] ?? [];
        $this->store['_flash_new'] = [];
    }
}
