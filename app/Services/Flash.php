<?php
declare(strict_types=1);

namespace App\Services;

class Flash
{
    public static function add(string $type, string $message): void
    {
        $_SESSION['lsb_flash'] ??= [];
        $_SESSION['lsb_flash'][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function pullAll(): array
    {
        $messages = $_SESSION['lsb_flash'] ?? [];
        unset($_SESSION['lsb_flash']);
        return $messages;
    }
}
