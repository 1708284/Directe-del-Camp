<?php

declare(strict_types=1);

namespace App\Controlador;

use App\Modelo\Product;
use App\Modelo\User;
use Throwable;

final class CheckoutController extends BaseController
{
    private $productModel;
    private $userModel;

    public function __construct()
    {
        $this->productModel = null;
        $this->userModel    = null;
    }

    private function getProductModel()
    {
        if ($this->productModel === null) {
            $this->productModel = new Product();
        }
        return $this->productModel;
    }

    private function getUserModel()
    {
        if ($this->userModel === null) {
            $this->userModel = new User();
        }
        return $this->userModel;
    }

    public function index()
    {
        if (empty($_SESSION['user']['id'])) {
            $_SESSION['flash_error'] = 'Has d\'iniciar sessió per finalitzar la comanda.';
            $this->redirect('/login');
        }

        $summary = CartController::buildCartSummary($this->getProductModel());
        if (empty($summary['groups'])) {
            $_SESSION['flash_error'] = 'La cistella està buida.';
            $this->redirect('/cistella');
        }

        $addresses = [];
        try {
            $addresses = $this->getUserModel()->getAddressesByUser($_SESSION['user']['id']);
        } catch (Throwable $e) {
            error_log('Checkout load addresses error: ' . $e->getMessage());
        }

        $viewData = [];
        $viewData['pageTitle'] = 'Finalitzar comanda';
        $viewData['summary']   = $summary;
        $viewData['addresses'] = $addresses;

        $this->render('checkout/index', $viewData);
    }
}
