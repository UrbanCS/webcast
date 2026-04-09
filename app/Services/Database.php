<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = (string) \config('database.host', 'localhost');
        $port = (int) \config('database.port', 3306);
        $name = (string) \config('database.name', '');
        $username = (string) \config('database.username', '');
        $password = (string) \config('database.password', '');
        $charset = (string) \config('database.charset', 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);

        try {
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            \log_message('Database connection failed: ' . $exception->getMessage());

            if (\is_production()) {
                throw new RuntimeException('Connexion à la base de données impossible.');
            }

            throw $exception;
        }

        return self::$connection;
    }
}
