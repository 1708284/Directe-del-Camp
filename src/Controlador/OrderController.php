<?php

declare(strict_types=1);

namespace App\Controlador;

use App\Modelo\Order;
use Throwable;

final class OrderController extends BaseController
{
    private $orderModel;

    public function __construct()
    {
        $this->orderModel = new Order();
    }

    private function requireAuth()
    {
        if (empty($_SESSION['user']['id'])) {
            http_response_code(403);
            exit('Accés denegat');
        }
    }

    private function requireRancher()
    {
        $userRole = '';
        if (isset($_SESSION['user']['rol'])) {
            $userRole = (string) $_SESSION['user']['rol'];
        }

        if (empty($_SESSION['user']) || $userRole !== 'RAMADER') {
            http_response_code(403);
            exit('Accés denegat');
        }
    }

    public function rancherOrders()
    {
        $this->requireRancher();

        $suborders = [];
        try {
            $suborders = $this->orderModel->getRancherSuborders($_SESSION['user']['id']);
        } catch (Throwable $e) {
            error_log('Load rancher orders error: ' . $e->getMessage());
        }

        $pendingOrders = [];
        $historyOrders = [];
        $kpiPending    = 0;
        $kpiShipped    = 0;
        $kpiDelivered  = 0;

        foreach ($suborders as $sub) {
            $st = '';
            if (isset($sub['estat_expedicio'])) {
                $st = (string) $sub['estat_expedicio'];
            }

            if ($st === 'LLIURAT') {
                $historyOrders[] = $sub;
                $kpiDelivered = $kpiDelivered + 1;
            } else {
                $pendingOrders[] = $sub;
                if ($st === 'ENVIAT') {
                    $kpiShipped = $kpiShipped + 1;
                } else {
                    $kpiPending = $kpiPending + 1;
                }
            }
        }

        $viewData = [];
        $viewData['pageTitle']     = 'Comandes rebudes';
        $viewData['sidebarActive'] = 'comandes';
        $viewData['pendingOrders'] = $pendingOrders;
        $viewData['historyOrders'] = $historyOrders;
        $viewData['kpiPending']    = $kpiPending;
        $viewData['kpiShipped']    = $kpiShipped;
        $viewData['kpiDelivered']  = $kpiDelivered;

        $this->render('ramader/comandes', $viewData);
    }

    public function updateRancherOrderStatus()
    {
        $this->requireRancher();
        $this->verifyCsrf();

        $suborderId = '';
        if (isset($_POST['id_subcomanda'])) {
            $suborderId = trim((string) $_POST['id_subcomanda']);
        }

        $newStatus = '';
        if (isset($_POST['nou_estat'])) {
            $newStatus = trim((string) $_POST['nou_estat']);
        }

        $trackingCode = '';
        if (isset($_POST['codi_seguiment_transport'])) {
            $trackingCode = trim((string) $_POST['codi_seguiment_transport']);
        }

        if ($suborderId === '' || ($newStatus !== 'EN_PREPARACIO' && $newStatus !== 'ENVIAT' && $newStatus !== 'LLIURAT')) {
            $_SESSION['flash_error'] = 'Paràmetres d\'actualització de comanda no vàlids.';
            $this->redirect('/ramader/comandes');
        }

        if ($newStatus === 'ENVIAT' && ($trackingCode === '' || mb_strlen($trackingCode) > 100)) {
            $_SESSION['flash_error'] = 'Has d\'indicar un codi de seguiment de transport vàlid per marcar la comanda com a enviada.';
            $this->redirect('/ramader/comandes');
        }

        try {
            $ok = $this->orderModel->updateSuborderStatus($suborderId, $_SESSION['user']['id'], $newStatus, $trackingCode);
            if ($ok) {
                $_SESSION['flash_success'] = 'Estat de la comanda actualitzat correctament.';
            } else {
                $_SESSION['flash_error'] = 'No s\'ha pogut canviar l\'estat (transició no permesa o comanda aliena).';
            }
        } catch (Throwable $e) {
            error_log('Update suborder status error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Error intern en actualitzar l\'estat de la comanda.';
        }

        $this->redirect('/ramader/comandes');
    }

    public function consumerOrders()
    {
        $this->requireAuth();

        $orders = [];
        try {
            $orders = $this->orderModel->getConsumerOrders($_SESSION['user']['id']);
        } catch (Throwable $e) {
            error_log('Load consumer orders error: ' . $e->getMessage());
        }

        $viewData = [];
        $viewData['pageTitle'] = 'Les meves comandes';
        $viewData['orders']    = $orders;

        $this->render('usuaris/comandes', $viewData);
    }
}
