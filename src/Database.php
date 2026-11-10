<?php

declare(strict_types=1);

function db_env(string $key, ?string $default = null): string
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === '') {
        if ($default === null) {
            throw new RuntimeException("Falta la variable d'entorn {$key}.");
        }
        return $default;
    }
    return (string) $value;
}

function db_get_connection(): PDO
{
    static $instance = null;
    if ($instance !== null) {
        return $instance;
    }

    $host    = db_env('DB_HOST');
    $port    = db_env('DB_PORT', '5432');
    $dbname  = db_env('DB_NAME');
    $user    = db_env('DB_USER');
    $pass    = db_env('DB_PASS');
    $sslmode = db_env('DB_SSLMODE', 'require');

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";

    $options = [];
    $options[PDO::ATTR_ERRMODE]            = PDO::ERRMODE_EXCEPTION;
    $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;
    $options[PDO::ATTR_EMULATE_PREPARES]   = false;

    try {
        $instance = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        error_log('DB connection error: ' . $e->getMessage());
        throw new RuntimeException('No s\'ha pogut connectar a la base de dades.');
    }

    return $instance;
}

function db_prepare(PDO $db, string $sql)
{
    return call_user_func([$db, 'prepare'], $sql);
}

function db_execute($stmt, ?array $params = null): bool
{
    if ($params !== null) {
        return call_user_func([$stmt, 'execute'], $params);
    }
    return call_user_func([$stmt, 'execute']);
}

function db_bind_value($stmt, string $param, mixed $value, int $type): bool
{
    return call_user_func([$stmt, 'bindValue'], $param, $value, $type);
}

function db_fetch($stmt): mixed
{
    return call_user_func([$stmt, 'fetch']);
}
