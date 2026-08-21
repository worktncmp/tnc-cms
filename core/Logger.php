<?php

declare(strict_types=1);

namespace Core;

final class Logger
{
    public function __construct(private readonly string $directory)
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
    }

    public function error(\Throwable $exception): void
    {
        $this->write('ERROR', sprintf(
            "%s: %s in %s:%d\n%s",
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString(),
        ));
    }

    public function info(string $message): void
    {
        $this->write('INFO', $message);
    }

    private function write(string $level, string $message): void
    {
        $line = sprintf("[%s] %s %s\n", date('c'), $level, $message);
        $file = $this->directory . '/app-' . date('Y-m-d') . '.log';
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
