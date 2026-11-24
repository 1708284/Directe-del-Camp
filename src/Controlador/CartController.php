<?php

declare(strict_types=1);

namespace App\Controlador;

use App\Modelo\Product;
use Throwable;

final class CartController extends BaseController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public static function buildCartSummary(Product $productModel)
    {
        $summary = [];
        $summary['groups']               = [];
        $summary['subtotal_productes']   = 0.0;
        $summary['cost_enviament_total'] = 0.0;
        $summary['base_productes']       = 0.0;
        $summary['iva_productes']        = 0.0;
        $summary['base_enviament']       = 0.0;
        $summary['iva_enviament']        = 0.0;
        $summary['base_total']           = 0.0;
        $summary['iva_total']            = 0.0;
        $summary['import_total']         = 0.0;
        $summary['total_units']          = 0;

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return $summary;
        }

        $ids = [];
        foreach ($_SESSION['cart'] as $cartItem) {
            if (isset($cartItem['id_producte'])) {
                $ids[] = (string) $cartItem['id_producte'];
            }
        }

        if (empty($ids)) {
            return $summary;
        }

        $dbProducts = $productModel->getPublicProductsByIds($ids);
        $productsMap = [];
        foreach ($dbProducts as $prod) {
            $pid = (string) $prod['id'];
            $productsMap[$pid] = $prod;
        }

        $groupsByRancher = [];
        $subtotalProductes = 0.0;
        $totalUnits = 0;

        foreach ($_SESSION['cart'] as $cartItem) {
            $pid = '';
            if (isset($cartItem['id_producte'])) {
                $pid = (string) $cartItem['id_producte'];
            }
            $qty = 0;
            if (isset($cartItem['quantitat'])) {
                $qty = (int) $cartItem['quantitat'];
            }

            if ($pid === '' || $qty <= 0 || !isset($productsMap[$pid])) {
                continue;
            }

            $prod = $productsMap[$pid];
            $rancherId = (string) $prod['id_ramader'];
            $unitPrice = (float) $prod['preu_pack'];
            $lineSubtotal = round($unitPrice * $qty, 2);

            if (!isset($groupsByRancher[$rancherId])) {
                $group = [];
                $group['id_ramader']         = $rancherId;
                $group['nom_explotacio']     = (string) $prod['nom_explotacio'];
                $group['comarca']            = (string) $prod['comarca'];
                $group['cost_enviament']     = 8.00;
                $group['subtotal_productes'] = 0.0;
                $group['items']              = [];
                $groupsByRancher[$rancherId] = $group;
            }

            $line = [];
            $line['product']        = $prod;
            $line['id_producte']    = $pid;
            $line['quantitat']      = $qty;
            $line['preu_unitari']   = $unitPrice;
            $line['subtotal_linia'] = $lineSubtotal;

            $groupsByRancher[$rancherId]['items'][] = $line;
            $groupsByRancher[$rancherId]['subtotal_productes'] = round($groupsByRancher[$rancherId]['subtotal_productes'] + $lineSubtotal, 2);

            $subtotalProductes = round($subtotalProductes + $lineSubtotal, 2);
            $totalUnits = $totalUnits + $qty;
        }

        $groupsList = [];
        foreach ($groupsByRancher as $grp) {
            $groupsList[] = $grp;
        }

        $costEnviamentTotal = round(8.00 * count($groupsList), 2);
        $baseProductes      = round($subtotalProductes / 1.10, 2);
        $ivaProductes       = round($subtotalProductes - $baseProductes, 2);
        $baseEnviament      = round($costEnviamentTotal / 1.21, 2);
        $ivaEnviament       = round($costEnviamentTotal - $baseEnviament, 2);
        $baseTotal          = round($baseProductes + $baseEnviament, 2);
        $ivaTotal           = round($ivaProductes + $ivaEnviament, 2);
        $importTotal        = round($subtotalProductes + $costEnviamentTotal, 2);

        $summary['groups']               = $groupsList;
        $summary['subtotal_productes']   = $subtotalProductes;
        $summary['cost_enviament_total'] = $costEnviamentTotal;
        $summary['base_productes']       = $baseProductes;
        $summary['iva_productes']        = $ivaProductes;
        $summary['base_enviament']       = $baseEnviament;
        $summary['iva_enviament']        = $ivaEnviament;
        $summary['base_total']           = $baseTotal;
        $summary['iva_total']            = $ivaTotal;
        $summary['import_total']         = $importTotal;
        $summary['total_units']          = $totalUnits;

        return $summary;
    }

    public function index()
    {
        $summary = [];
        try {
            $summary = self::buildCartSummary($this->productModel);
        } catch (Throwable $e) {
            error_log('Cart summary error: ' . $e->getMessage());
            $summary = [];
            $summary['groups'] = [];
        }

        $viewData = [];
        $viewData['pageTitle'] = 'La meva cistella';
        $viewData['summary']   = $summary;

        $this->render('cistella/index', $viewData);
    }

    public function add()
    {
        $this->verifyCsrf();

        $productId = '';
        if (isset($_POST['id_producte'])) {
            $productId = trim((string) $_POST['id_producte']);
        }

        $qty = 1;
        if (isset($_POST['quantitat'])) {
            $qty = (int) $_POST['quantitat'];
        }

        if ($productId === '' || $qty <= 0) {
            $_SESSION['flash_error'] = 'Quantitat o producte no vàlids.';
            $this->redirect('/cistella');
        }

        try {
            $product = $this->productModel->getPublicProductById($productId);
        } catch (Throwable $e) {
            error_log('Cart add fetch error: ' . $e->getMessage());
            $product = null;
        }

        if ($product === null) {
            $_SESSION['flash_error'] = 'Aquest producte ja no està disponible.';
            $this->redirect('/productes');
        }

        $stock = (int) $product['estoc_unitats'];
        if ($stock <= 0) {
            $_SESSION['flash_error'] = 'Aquest producte està temporalment esgotat.';
            $this->redirect('/producte?id=' . urlencode($productId));
        }

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $existingQty = 0;
        if (isset($_SESSION['cart'][$productId]['quantitat'])) {
            $existingQty = (int) $_SESSION['cart'][$productId]['quantitat'];
        }

        $newQty = $existingQty + $qty;
        if ($newQty > $stock) {
            $newQty = $stock;
            $_SESSION['flash_error'] = 'S\'ha ajustat la quantitat al màxim d\'estoc disponible (' . $stock . ' u.).';
        } else {
            $_SESSION['flash_success'] = 'Producte afegit a la cistella.';
        }

        $entry = [];
        $entry['id_producte'] = $productId;
        $entry['quantitat']   = $newQty;
        $_SESSION['cart'][$productId] = $entry;

        $this->redirect('/cistella');
    }

    public function update()
    {
        $this->verifyCsrf();

        $productId = '';
        if (isset($_POST['id_producte'])) {
            $productId = trim((string) $_POST['id_producte']);
        }

        $qty = 0;
        if (isset($_POST['quantitat'])) {
            $qty = (int) $_POST['quantitat'];
        }

        if ($productId === '') {
            $this->redirect('/cistella');
        }

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if ($qty <= 0) {
            unset($_SESSION['cart'][$productId]);
            $_SESSION['flash_success'] = 'Producte eliminat de la cistella.';
            $this->redirect('/cistella');
        }

        try {
            $product = $this->productModel->getPublicProductById($productId);
        } catch (Throwable $e) {
            $product = null;
        }

        if ($product === null) {
            unset($_SESSION['cart'][$productId]);
            $_SESSION['flash_error'] = 'El producte ja no està disponible i s\'ha retirat de la cistella.';
            $this->redirect('/cistella');
        }

        $stock = (int) $product['estoc_unitats'];
        if ($stock <= 0) {
            unset($_SESSION['cart'][$productId]);
            $_SESSION['flash_error'] = 'El producte s\'ha esgotat.';
            $this->redirect('/cistella');
        }

        if ($qty > $stock) {
            $qty = $stock;
            $_SESSION['flash_error'] = 'Estoc màxim disponible per a aquest producte ' . $stock . ' unitats.';
        } else {
            $_SESSION['flash_success'] = 'Cistella actualitzada.';
        }

        $entry = [];
        $entry['id_producte'] = $productId;
        $entry['quantitat']   = $qty;
        $_SESSION['cart'][$productId] = $entry;

        $this->redirect('/cistella');
    }

    public function remove()
    {
        $this->verifyCsrf();

        $productId = '';
        if (isset($_POST['id_producte'])) {
            $productId = trim((string) $_POST['id_producte']);
        }

        if ($productId !== '' && isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            $_SESSION['flash_success'] = 'Producte retirat de la cistella.';
        }

        $this->redirect('/cistella');
    }
}
