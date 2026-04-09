<?php
declare(strict_types=1);

namespace App\Services;

class Csrf
{
    public static function token(string $form = 'default'): string
    {
        $ttl = (int) \config('security.csrf_ttl', 7200);
        $_SESSION['lsb_csrf'] ??= [];

        if (
            empty($_SESSION['lsb_csrf'][$form]['token']) ||
            empty($_SESSION['lsb_csrf'][$form]['expires_at']) ||
            $_SESSION['lsb_csrf'][$form]['expires_at'] < time()
        ) {
            $_SESSION['lsb_csrf'][$form] = [
                'token' => bin2hex(random_bytes(32)),
                'expires_at' => time() + $ttl,
            ];
        }

        return $_SESSION['lsb_csrf'][$form]['token'];
    }

    public static function validate(string $form, ?string $token): bool
    {
        if ($token === null || empty($_SESSION['lsb_csrf'][$form])) {
            return false;
        }

        $record = $_SESSION['lsb_csrf'][$form];
        $valid = !empty($record['expires_at'])
            && $record['expires_at'] >= time()
            && !empty($record['token'])
            && hash_equals((string) $record['token'], $token);

        if ($valid) {
            unset($_SESSION['lsb_csrf'][$form]);
        }

        return $valid;
    }
}
