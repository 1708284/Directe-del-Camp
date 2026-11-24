<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host    = self::env('DB_HOST');
        $port    = self::env('DB_PORT', '5432');
        $dbname  = self::env('DB_NAME');
        $user    = self::env('DB_USER');
        $pass    = self::env('DB_PASS');
        $sslmode = self::env('DB_SSLMODE', 'require');

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";

        $options = [];
        $options[PDO::ATTR_ERRMODE]            = PDO::ERRMODE_EXCEPTION;
        $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;
        $options[PDO::ATTR_EMULATE_PREPARES]   = false;

        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log('DB connection error: ' . $e->getMessage());
            throw new RuntimeException('No s\'ha pogut connectar a la base de dades.');
        }

        return self::$instance;
    }

    public static function env(string $key, $default = null)
    {
        $value = null;
        if (isset($_ENV[$key])) {
            $value = $_ENV[$key];
        } else {
            $value = getenv($key);
        }

        if ($value === false || $value === '') {
            if ($default === null) {
                throw new RuntimeException("Falta la variable d'entorn {$key}.");
            }
            return $default;
        }
        return (string) $value;
    }
}
