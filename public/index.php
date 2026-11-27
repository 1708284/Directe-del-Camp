<?php

declare(strict_types=1);

use App\Controlador\AdminController;
use App\Controlador\AuthController;
use App\Controlador\CartController;
use App\Controlador\CatalogController;
use App\Controlador\CheckoutController;
use App\Controlador\OrderController;
use App\Controlador\ProductController;
use App\Controlador\UserController;

if (php_sapi_name() === 'cli-server') {
    $reqUri = '';
    if (isset($_SERVER['REQUEST_URI'])) {
        $reqUri = $_SERVER['REQUEST_URI'];
    }
    $filePath = __DIR__ . parse_url($reqUri, PHP_URL_PATH);
    if (is_file($filePath)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$method = 'GET';
if (isset($_SERVER['REQUEST_METHOD'])) {
    $method = $_SERVER['REQUEST_METHOD'];
}

$rawUri = '/';
if (isset($_SERVER['REQUEST_URI'])) {
    $rawUri = $_SERVER['REQUEST_URI'];
}

$path = parse_url($rawUri, PHP_URL_PATH);
if (!$path) {
    $path = '/';
}

$route = $method . ' ' . $path;

if ($route === 'POST /api/webhooks/stripe') {
    $checkout = new CheckoutController();
    $checkout->stripeWebhook();
    exit;
}

$https = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $https = true;
}

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

header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://js.stripe.com https://m.stripe.network; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; frame-src 'self' https://js.stripe.com https://hooks.stripe.com https://m.stripe.network; connect-src 'self' https://api.stripe.com https://m.stripe.network; object-src 'none'; frame-ancestors 'none'; form-action 'self' https://accounts.google.com");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header('Cache-Control: no-store');

if ($route === 'GET /') {
    $catalog = new CatalogController();
    $catalog->home();
} elseif ($route === 'GET /productes') {
    $catalog = new CatalogController();
    $catalog->index();
} elseif ($route === 'GET /producte') {
    $catalog = new CatalogController();
    $catalog->show();
} elseif ($route === 'GET /granges') {
    $catalog = new CatalogController();
    $catalog->farms();
} elseif ($route === 'GET /login') {
    $auth = new AuthController();
    $auth->showLogin();
} elseif ($route === 'POST /login') {
    $auth = new AuthController();
    $auth->processLogin();
} elseif ($route === 'GET /register') {
    $auth = new AuthController();
    $auth->showRegister();
} elseif ($route === 'POST /register') {
    $auth = new AuthController();
    $auth->processRegister();
} elseif ($route === 'GET /register-ramader') {
    $auth = new AuthController();
    $auth->showRegisterRamader();
} elseif ($route === 'POST /register-ramader') {
    $auth = new AuthController();
    $auth->processRegisterRamader();
} elseif ($route === 'GET /logout') {
    $auth = new AuthController();
    $auth->logout();
} elseif ($route === 'GET /auth/google') {
    $auth = new AuthController();
    $auth->googleRedirect();
} elseif ($route === 'GET /auth/google/callback') {
    $auth = new AuthController();
    $auth->googleCallback();
} elseif ($route === 'GET /cistella') {
    $cart = new CartController();
    $cart->index();
} elseif ($route === 'POST /cistella/afegir') {
    $cart = new CartController();
    $cart->add();
} elseif ($route === 'POST /cistella/actualitzar') {
    $cart = new CartController();
    $cart->update();
} elseif ($route === 'POST /cistella/eliminar') {
    $cart = new CartController();
    $cart->remove();
} elseif ($route === 'GET /checkout') {
    $checkout = new CheckoutController();
    $checkout->index();
} elseif ($route === 'POST /checkout/crear-pagament') {
    $checkout = new CheckoutController();
    $checkout->createPayment();
} elseif ($route === 'GET /checkout/confirmacio') {
    $checkout = new CheckoutController();
    $checkout->confirmation();
} elseif ($route === 'GET /ramader/productes') {
    $product = new ProductController();
    $product->index();
} elseif ($route === 'GET /ramader/productes/crear') {
    $product = new ProductController();
    $product->create();
} elseif ($route === 'POST /ramader/productes/crear') {
    $product = new ProductController();
    $product->store();
} elseif ($route === 'GET /ramader/productes/editar') {
    $product = new ProductController();
    $product->edit();
} elseif ($route === 'POST /ramader/productes/editar') {
    $product = new ProductController();
    $product->update();
} elseif ($route === 'POST /ramader/productes/toggle') {
    $product = new ProductController();
    $product->toggle();
} elseif ($route === 'POST /ramader/productes/eliminar') {
    $product = new ProductController();
    $product->delete();
} elseif ($route === 'POST /ramader/categories/crear') {
    $product = new ProductController();
    $product->storeCategoryAjax();
} elseif ($route === 'POST /ramader/races/crear') {
    $product = new ProductController();
    $product->storeRaceAjax();
} elseif ($route === 'GET /ramader/comandes') {
    $order = new OrderController();
    $order->rancherOrders();
} elseif ($route === 'POST /ramader/comandes/estat') {
    $order = new OrderController();
    $order->updateRancherOrderStatus();
} elseif ($route === 'GET /admin/ramaders') {
    $admin = new AdminController();
    $admin->pendingRanchers();
} elseif ($route === 'POST /admin/ramaders/validar') {
    $admin = new AdminController();
    $admin->processValidation();
} elseif ($route === 'GET /perfil') {
    $user = new UserController();
    $user->showProfile();
} elseif ($route === 'POST /perfil') {
    $user = new UserController();
    $user->updateProfile();
} elseif ($route === 'POST /perfil/adreces/crear') {
    $user = new UserController();
    $user->storeAddress();
} elseif ($route === 'POST /perfil/adreces/eliminar') {
    $user = new UserController();
    $user->deleteAddress();
} elseif ($route === 'GET /perfil/comandes') {
    $order = new OrderController();
    $order->consumerOrders();
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="ca"><head><meta charset="UTF-8"><title>404 - Directe del Camp</title><link rel="stylesheet" href="/css/styles.css"></head><body><main class="main-content"><div class="container"><div class="card"><h1>404 - Pàgina no trobada</h1><p class="mt-2 text-muted">La ruta sol·licitada no existeix.</p><p class="mt-4"><a href="/" class="btn btn-primary btn-sm">Tornar a l\'inici</a></p></div></div></main></body></html>';
}
