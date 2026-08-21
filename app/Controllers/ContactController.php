<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Response;

final class ContactController extends Controller
{
    public function submit(): Response
    {
        $name = trim((string) $this->request()->input('name', ''));
        $email = trim((string) $this->request()->input('email', ''));
        $message = trim((string) $this->request()->input('message', ''));

        if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session()->flash('error', 'Please fill in every field with a valid email address.');
            $this->session()->flash('old', [
                'name' => $name,
                'email' => $email,
                'message' => $message,
            ]);

            return $this->redirect('/contact');
        }

        $this->db()->insert('messages', [
            'name' => $name,
            'email' => $email,
            'body' => $message,
            'created_at' => date('c'),
        ]);

        $this->session()->flash('success', 'Thanks. We received your message and will reply soon.');

        return $this->redirect('/contact');
    }
}
