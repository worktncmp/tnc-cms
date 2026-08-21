<?php

declare(strict_types=1);

namespace Core;

final class Upload
{
    private const DANGEROUS = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8',
        'cgi', 'exe', 'htaccess', 'html', 'htm', 'js', 'svg',
    ];

    private const MIME_MAP = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'txt' => ['text/plain'],
    ];

    public function __construct(private readonly bool $strict = true)
    {
    }

    /**
     * @param array<string, mixed> $file  A $_FILES entry
     * @param list<string> $allowedExtensions
     */
    public function store(array $file, string $destinationDir, array $allowedExtensions, int $maxBytes): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('The file could not be uploaded.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        $original = (string) ($file['name'] ?? '');
        $size = (int) ($file['size'] ?? 0);

        if ($tmp === '' || !is_file($tmp)) {
            throw new \RuntimeException('The file could not be uploaded.');
        }

        if ($this->strict && !is_uploaded_file($tmp)) {
            throw new \RuntimeException('The file could not be uploaded.');
        }

        if ($size <= 0 || $size > $maxBytes) {
            throw new \RuntimeException('The file is too large.');
        }

        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowedExtensions = array_map(strtolower(...), $allowedExtensions);

        if ($extension === '' || in_array($extension, self::DANGEROUS, true)) {
            throw new \RuntimeException('This file type is not allowed.');
        }

        if (!in_array($extension, $allowedExtensions, true) || !isset(self::MIME_MAP[$extension])) {
            throw new \RuntimeException('This file type is not allowed.');
        }

        $mime = $this->detectMime($tmp);
        if (!is_string($mime) || !in_array($mime, self::MIME_MAP[$extension], true)) {
            throw new \RuntimeException('This file type is not allowed.');
        }

        if (!is_dir($destinationDir) && !mkdir($destinationDir, 0755, true) && !is_dir($destinationDir)) {
            throw new \RuntimeException('Upload directory is not writable.');
        }

        $stored = bin2hex(random_bytes(16)) . '.' . $extension;
        $destinationRealDir = realpath($destinationDir);
        if ($destinationRealDir === false) {
            throw new \RuntimeException('Invalid upload destination.');
        }
        $destination = Path::join($destinationRealDir, $stored);
        if (!Path::inside($destination, $destinationRealDir)) {
            throw new \RuntimeException('Invalid upload destination.');
        }

        $moved = $this->strict ? move_uploaded_file($tmp, $destination) : copy($tmp, $destination);
        if (!$moved) {
            throw new \RuntimeException('The file could not be saved.');
        }

        return $stored;
    }

    private function detectMime(string $path): ?string
    {
        if (class_exists(\finfo::class)) {
            $detected = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
            return is_string($detected) ? $detected : null;
        }

        if (function_exists('mime_content_type')) {
            $detected = mime_content_type($path);
            return is_string($detected) ? $detected : null;
        }

        $image = @getimagesize($path);
        if (is_array($image) && isset($image['mime']) && is_string($image['mime'])) {
            return $image['mime'];
        }

        throw new \RuntimeException('Cannot inspect the uploaded file type.');
    }
}
