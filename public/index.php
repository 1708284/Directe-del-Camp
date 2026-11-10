<?php

declare(strict_types=1);

if (php_sapi_name() === 'cli-server') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $filePath = __DIR__ . $uriPath;
    if (is_file($filePath)) {
        return false;
    }
}

$https = ($_SERVER['HTTPS'] ?? '') === 'on';

$cookieParams = [];
$cookieParams['lifetime'] = 0;
$cookieParams['path']     = '/';
$cookieParams['secure']   = $https;
$cookieParams['httponly'] = true;
$cookieParams['samesite'] = 'Lax';

session_set_cookie_params($cookieParams);
ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', '1800');
session_start();

if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 1800) {
    session_unset();
    session_regenerate_id(true);
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Modelo/User.php';
require_once __DIR__ . '/../src/Controlador/AuthController.php';

$dotenv = call_user_func(['Dotenv\Dotenv', 'createImmutable'], __DIR__ . '/..');
call_user_func([$dotenv, 'safeLoad']);

header("Content-Security-Policy: default-src 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self' https://accounts.google.com");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Cache-Control: no-store');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!$path) {
    $path = '/';
}

$ruta = $method . ' ' . $path;

if ($ruta === 'GET /') {
    $user = $_SESSION['user'] ?? null;
    echo '<!DOCTYPE html><html lang="ca"><head><meta charset="UTF-8"><title>Directe del Camp</title></head><body>';
    echo '<h1>Directe del Camp</h1>';
    if ($user) {
        $nom = htmlspecialchars($user['nom'], ENT_QUOTES, 'UTF-8');
        $rol = htmlspecialchars($user['rol'], ENT_QUOTES, 'UTF-8');
        echo "<p>Sessió activa: {$nom} ({$rol})</p><p><a href=\"/logout\">Tancar sessió</a></p>";
    } else {
        echo '<p>No hi ha cap sessió activa.</p><p><a href="/login">Iniciar sessió</a> | <a href="/register">Registrar-se</a></p>';
    }
    echo '</body></html>';
} elseif ($ruta === 'GET /login') {
    auth_show_login();
} elseif ($ruta === 'POST /login') {
    auth_process_login();
} elseif ($ruta === 'GET /register') {
    auth_show_register();
} elseif ($ruta === 'POST /register') {
    auth_process_register();
} elseif ($ruta === 'GET /logout') {
    auth_logout();
} elseif ($ruta === 'GET /auth/google') {
    auth_google_redirect();
} elseif ($ruta === 'GET /auth/google/callback') {
    auth_google_callback();
} else {
    http_response_code(404);
    echo '404 - Pàgina no trobada';
}
