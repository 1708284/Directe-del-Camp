<?php

declare(strict_types=1);

namespace App\Controlador;

use App\Modelo\User;
use Throwable;

final class UserController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    private function requireAuth()
    {
        if (empty($_SESSION['user']['id'])) {
            http_response_code(403);
            exit('Accés denegat');
        }
    }

    public function showProfile()
    {
        $this->requireAuth();
        $user = $this->userModel->findById($_SESSION['user']['id']);
        if (!$user) {
            http_response_code(404);
            exit('Usuari no trobat');
        }

        $addresses = [];
        try {
            $addresses = $this->userModel->getAddressesByUser($_SESSION['user']['id']);
        } catch (Throwable $e) {
            error_log('Load addresses error: ' . $e->getMessage());
        }

        $viewData = [];
        $viewData['user']      = $user;
        $viewData['addresses'] = $addresses;
        $this->render('usuaris/perfil', $viewData);
    }

    public function updateProfile()
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $nom = '';
        if (isset($_POST['nom'])) {
            $nom = trim((string) $_POST['nom']);
        }

        $cognoms = '';
        if (isset($_POST['cognoms'])) {
            $cognoms = trim((string) $_POST['cognoms']);
        }

        $telefon = '';
        if (isset($_POST['telefon'])) {
            $telefon = trim((string) $_POST['telefon']);
        }

        $error = null;
        if ($nom === '' || mb_strlen($nom) > 100) {
            $error = 'El nom és obligatori (màx. 100 caràcters).';
        } elseif ($cognoms === '' || mb_strlen($cognoms) > 150) {
            $error = 'Els cognoms són obligatoris (màx. 150 caràcters).';
        } elseif ($telefon !== '' && mb_strlen($telefon) > 20) {
            $error = 'El telèfon és massa llarg (màx. 20 caràcters).';
        }

        if ($error !== null) {
            $_SESSION['flash_error'] = $error;
            $this->redirect('/perfil');
        }

        try {
            $updateData = [];
            $updateData['nom']     = $nom;
            $updateData['cognoms'] = $cognoms;
            $updateData['telefon'] = $telefon;

            $this->userModel->updateProfile($_SESSION['user']['id'], $updateData);

            $_SESSION['user']['nom'] = $nom;
            $_SESSION['flash_success'] = 'Dades del perfil actualitzades correctament.';
        } catch (Throwable $e) {
            error_log('Update profile error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'No s\'han pogut actualitzar les dades del perfil.';
        }

        $this->redirect('/perfil');
    }

    public function storeAddress()
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $adreca = '';
        if (isset($_POST['adreca'])) {
            $adreca = trim((string) $_POST['adreca']);
        }

        $codiPostal = '';
        if (isset($_POST['codi_postal'])) {
            $codiPostal = trim((string) $_POST['codi_postal']);
        }

        $municipi = '';
        if (isset($_POST['municipi'])) {
            $municipi = trim((string) $_POST['municipi']);
        }

        $provincia = '';
        if (isset($_POST['provincia'])) {
            $provincia = trim((string) $_POST['provincia']);
        }

        $esPrincipal = false;
        if (isset($_POST['es_principal']) && (string) $_POST['es_principal'] === '1') {
            $esPrincipal = true;
        }

        $error = null;
        if ($adreca === '' || mb_strlen($adreca) > 255) {
            $error = 'L\'adreça és obligatòria (màx. 255 caràcters).';
        } elseif ($codiPostal === '' || mb_strlen($codiPostal) > 10) {
            $error = 'El codi postal és obligatori (màx. 10 caràcters).';
        } elseif ($municipi === '' || mb_strlen($municipi) > 100) {
            $error = 'El municipi és obligatori (màx. 100 caràcters).';
        } elseif ($provincia === '' || mb_strlen($provincia) > 100) {
            $error = 'La província és obligatòria (màx. 100 caràcters).';
        }

        if ($error !== null) {
            $_SESSION['flash_error'] = $error;
            $this->redirect('/perfil');
        }

        try {
            $addrData = [];
            $addrData['adreca']       = $adreca;
            $addrData['codi_postal']  = $codiPostal;
            $addrData['municipi']     = $municipi;
            $addrData['provincia']    = $provincia;
            $addrData['es_principal'] = $esPrincipal;

            $this->userModel->createAddress($_SESSION['user']['id'], $addrData);
            $_SESSION['flash_success'] = 'Adreça d\'enviament afegida correctament.';
        } catch (Throwable $e) {
            error_log('Store address error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'No s\'ha pogut desar l\'adreça d\'enviament.';
        }

        $this->redirect('/perfil');
    }

    public function deleteAddress()
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $id = '';
        if (isset($_POST['id'])) {
            $id = trim((string) $_POST['id']);
        }

        if ($id === '') {
            $_SESSION['flash_error'] = 'Adreça no especificada.';
            $this->redirect('/perfil');
        }

        try {
            $this->userModel->deleteAddress($id, $_SESSION['user']['id']);
            $_SESSION['flash_success'] = 'Adreça eliminada correctament.';
        } catch (Throwable $e) {
            error_log('Delete address error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'No s\'ha pogut eliminar l\'adreça.';
        }

        $this->redirect('/perfil');
    }
}
