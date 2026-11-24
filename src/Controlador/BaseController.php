<?php

declare(strict_types=1);

namespace App\Controlador;

abstract class BaseController
{
    protected function render(string $view, array $data = [])
    {
        $csrf = '';
        if (isset($_SESSION['csrf_token'])) {
            $csrf = $_SESSION['csrf_token'];
        }
        $data['csrf'] = $csrf;

        $flashSuccess = null;
        if (isset($_SESSION['flash_success'])) {
            $flashSuccess = $_SESSION['flash_success'];
        }
        $data['flash_success'] = $flashSuccess;

        $flashError = null;
        if (isset($_SESSION['flash_error'])) {
            $flashError = $_SESSION['flash_error'];
        }
        $data['flash_error'] = $flashError;

        $currentUser = null;
        if (isset($_SESSION['user'])) {
            $currentUser = $_SESSION['user'];
        }
        $data['currentUser'] = $currentUser;

        $cartCount = 0;
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $cartItem) {
                if (isset($cartItem['quantitat'])) {
                    $cartCount = $cartCount + (int) $cartItem['quantitat'];
                }
            }
        }
        $data['cartCount'] = $cartCount;

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        extract($data, EXTR_SKIP);
        require __DIR__ . '/../Vista/' . $view . '.php';
    }

    protected function redirect(string $url)
    {
        header('Location: ' . $url);
        exit;
    }

    protected function verifyCsrf()
    {
        $sent = '';
        if (isset($_POST['csrf_token'])) {
            $sent = (string) $_POST['csrf_token'];
        }

        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
            http_response_code(403);
            exit('Petició no vàlida (CSRF).');
        }
    }
}
