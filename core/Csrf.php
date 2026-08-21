<?php

declare(strict_types=1);

namespace Core;

final class Csrf
{
    public function token(Session $session): string
    {
        $token = $session->get('_csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $session->set('_csrf_token', $token);
        }

        return $token;
    }

    public function field(Session $session): string
    {
        $token = e($this->token($session));

        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    public function verify(Request $request, Session $session): void
    {
        $expected = $session->get('_csrf_token');
        $provided = $request->post['_csrf'] ?? $request->header('X-CSRF-Token');

        if (!is_string($expected) || $expected === '' || !is_string($provided) || $provided === '') {
            throw HttpException::forbidden('Invalid security token.');
        }

        if (!hash_equals($expected, $provided)) {
            throw HttpException::forbidden('Invalid security token.');
        }
    }
}
