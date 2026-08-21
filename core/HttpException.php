<?php

declare(strict_types=1);

namespace Core;

final class HttpException extends \RuntimeException
{
    public function __construct(
        public readonly int $status,
        string $message = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    public static function notFound(string $message = 'Page not found'): self
    {
        return new self(404, $message);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self(403, $message);
    }

    public static function methodNotAllowed(string $message = 'Method not allowed'): self
    {
        return new self(405, $message);
    }

    public static function serverError(string $message = 'An unexpected error occurred.'): self
    {
        return new self(500, $message);
    }
}
