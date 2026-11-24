<?php

declare(strict_types=1);

namespace App\Controlador;

use App\Modelo\User;
use Throwable;

final class AdminController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    private function requireAdmin()
    {
        $userRole = '';
        if (isset($_SESSION['user']['rol'])) {
            $userRole = (string) $_SESSION['user']['rol'];
        }

        if (empty($_SESSION['user']) || $userRole !== 'ADMIN') {
            http_response_code(403);
            exit('Accés denegat');
        }
    }

    public function pendingRanchers()
    {
        $this->requireAdmin();
        $pending = $this->userModel->getPendingRanchers();

        $viewData = [];
        $viewData['pending'] = $pending;
        $this->render('admin/ramaders', $viewData);
    }

    public function processValidation()
    {
        $this->requireAdmin();
        $this->verifyCsrf();

        $rancherId = '';
        if (isset($_POST['id_ramader'])) {
            $rancherId = trim((string) $_POST['id_ramader']);
        }

        $action = '';
        if (isset($_POST['accio'])) {
            $action = trim((string) $_POST['accio']);
        }

        if ($rancherId === '' || ($action !== 'aprovar' && $action !== 'rebutjar')) {
            $_SESSION['flash_error'] = 'Paràmetres de validació incorrectes.';
            $this->redirect('/admin/ramaders');
        }

        $newStatus = 'REBUTJAT';
        if ($action === 'aprovar') {
            $newStatus = 'ACTIU';
        }

        try {
            $this->userModel->updateRancherStatus($rancherId, $newStatus);
            if ($action === 'aprovar') {
                $_SESSION['flash_success'] = 'Explotació ramadera aprovada amb èxit. L\'usuari ara té el rol RAMADER.';
            } else {
                $_SESSION['flash_success'] = 'Sol·licitud de ramader rebutjada.';
            }
        } catch (Throwable $e) {
            error_log('Admin validation error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Error en actualitzar l\'estat de la sol·licitud.';
        }

        $this->redirect('/admin/ramaders');
    }
}
