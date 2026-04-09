<?php
declare(strict_types=1);

namespace App\Services;

class RateLimiter
{
    private string $directory;

    public function __construct()
    {
        $this->directory = APP_ROOT . '/storage/cache';

        if (!is_dir($this->directory)) {
            @mkdir($this->directory, 0755, true);
        }
    }

    public function tooManyAttempts(string $key, int $maxAttempts, int $windowMinutes): bool
    {
        $record = $this->read($key);
        $windowStart = time() - ($windowMinutes * 60);
        $attempts = array_values(array_filter($record['attempts'], static fn (int $timestamp): bool => $timestamp >= $windowStart));

        return count($attempts) >= $maxAttempts;
    }

    public function hit(string $key): void
    {
        $record = $this->read($key);
        $record['attempts'][] = time();
        $this->write($key, $record);
    }

    public function clear(string $key): void
    {
        $file = $this->filePath($key);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private function read(string $key): array
    {
        $file = $this->filePath($key);

        if (!is_file($file)) {
            return ['attempts' => []];
        }

        $decoded = json_decode((string) file_get_contents($file), true);

        return is_array($decoded) ? $decoded : ['attempts' => []];
    }

    private function write(string $key, array $record): void
    {
        file_put_contents($this->filePath($key), json_encode($record, JSON_THROW_ON_ERROR));
    }

    private function filePath(string $key): string
    {
        return $this->directory . '/ratelimit_' . sha1($key) . '.json';
    }
}
