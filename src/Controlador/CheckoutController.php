<?php

declare(strict_types=1);

namespace App\Controlador;

use App\Database;
use App\Modelo\Order;
use App\Modelo\Product;
use App\Modelo\User;
use PDO;
use Throwable;

final class CheckoutController extends BaseController
{
    private $productModel;
    private $userModel;
    private $orderModel;

    public function __construct()
    {
        $this->productModel = null;
        $this->userModel    = null;
        $this->orderModel   = null;
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

    private function getOrderModel()
    {
        if ($this->orderModel === null) {
            $this->orderModel = new Order();
        }
        return $this->orderModel;
    }

    private function jsonResponse(array $payload, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }

    private function callStripePaymentIntent(int $amountCents, string $userId)
    {
        $secretKey = Database::env('STRIPE_SECRET_KEY', '');
        if ($secretKey === '') {
            error_log('Missing STRIPE_SECRET_KEY');
            return null;
        }

        $bodyParams = [];
        $bodyParams['amount']                             = $amountCents;
        $bodyParams['currency']                           = 'eur';
        $bodyParams['automatic_payment_methods[enabled]'] = 'true';
        $bodyParams['metadata[id_usuari]']                = $userId;

        $headers = [];
        $headers[] = 'Authorization: Bearer ' . $secretKey;
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';

        $httpOptions = [];
        $httpOptions['method']        = 'POST';
        $httpOptions['header']        = implode("\r\n", $headers);
        $httpOptions['content']       = http_build_query($bodyParams);
        $httpOptions['timeout']       = 20;
        $httpOptions['ignore_errors'] = true;

        $sslOptions = [];
        $sslOptions['verify_peer']      = true;
        $sslOptions['verify_peer_name'] = true;

        $contextOptions = [];
        $contextOptions['http'] = $httpOptions;
        $contextOptions['ssl']  = $sslOptions;

        $ctx = stream_context_create($contextOptions);
        $raw = @file_get_contents('https://api.stripe.com/v1/payment_intents', false, $ctx);
        if ($raw === false) {
            error_log('Stripe API network failure');
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || empty($decoded['id']) || empty($decoded['client_secret'])) {
            error_log('Stripe API error response: ' . $raw);
            return null;
        }

        return $decoded;
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

        $publishableKey = Database::env('STRIPE_PUBLISHABLE_KEY', '');

        $viewData = [];
        $viewData['pageTitle']            = 'Finalitzar comanda';
        $viewData['summary']              = $summary;
        $viewData['addresses']            = $addresses;
        $viewData['stripePublishableKey'] = $publishableKey;
        $viewData['amountCents']          = (int) round((float) $summary['import_total'] * 100);

        $this->render('checkout/index', $viewData);
    }

    public function createPayment()
    {
        if (empty($_SESSION['user']['id'])) {
            $err = [];
            $err['success'] = false;
            $err['error']   = 'Has d\'iniciar sessió per realitzar el pagament.';
            $this->jsonResponse($err, 401);
        }

        $sentCsrf = '';
        if (isset($_POST['csrf_token'])) {
            $sentCsrf = (string) $_POST['csrf_token'];
        }
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sentCsrf)) {
            $err = [];
            $err['success'] = false;
            $err['error']   = 'Token de seguretat CSRF no vàlid.';
            $this->jsonResponse($err, 403);
        }

        if (!isset($_POST['acceptar_condicions']) || (string) $_POST['acceptar_condicions'] !== '1') {
            $err = [];
            $err['success'] = false;
            $err['error']   = 'Has d\'acceptar les condicions de compra abans de continuar.';
            $this->jsonResponse($err, 400);
        }

        $userId = (string) $_SESSION['user']['id'];

        $addressId = '';
        if (isset($_POST['id_adreca_enviament'])) {
            $addressId = trim((string) $_POST['id_adreca_enviament']);
        }

        if ($addressId === '' || $addressId === 'nova') {
            $adreca = '';
            if (isset($_POST['nova_adreca'])) {
                $adreca = trim((string) $_POST['nova_adreca']);
            }
            $cp = '';
            if (isset($_POST['nova_codi_postal'])) {
                $cp = trim((string) $_POST['nova_codi_postal']);
            }
            $municipi = '';
            if (isset($_POST['nova_municipi'])) {
                $municipi = trim((string) $_POST['nova_municipi']);
            }
            $provincia = '';
            if (isset($_POST['nova_provincia'])) {
                $provincia = trim((string) $_POST['nova_provincia']);
            }

            if ($adreca === '' || $cp === '' || $municipi === '' || $provincia === '') {
                $err = [];
                $err['success'] = false;
                $err['error']   = 'Selecciona una adreça o omple tots els camps de la nova adreça d\'enviament.';
                $this->jsonResponse($err, 400);
            }

            try {
                $newAddr = [];
                $newAddr['adreca']       = $adreca;
                $newAddr['codi_postal']  = $cp;
                $newAddr['municipi']     = $municipi;
                $newAddr['provincia']    = $provincia;
                $newAddr['es_principal'] = true;
                $addressId = $this->getUserModel()->createAddress($userId, $newAddr);
            } catch (Throwable $e) {
                error_log('Checkout create address error: ' . $e->getMessage());
                $err = [];
                $err['success'] = false;
                $err['error']   = 'No s\'ha pogut desar l\'adreça d\'enviament.';
                $this->jsonResponse($err, 500);
            }
        } else {
            $verifiedAddress = $this->getUserModel()->getAddressByIdForUser($addressId, $userId);
            if ($verifiedAddress === null) {
                $err = [];
                $err['success'] = false;
                $err['error']   = 'L\'adreça seleccionada no és vàlida.';
                $this->jsonResponse($err, 403);
            }
        }

        $summary = CartController::buildCartSummary($this->getProductModel());
        if (empty($summary['groups'])) {
            $err = [];
            $err['success'] = false;
            $err['error']   = 'La cistella està buida o els productes ja no estan disponibles.';
            $this->jsonResponse($err, 400);
        }

        $amountCents = (int) round((float) $summary['import_total'] * 100);
        if ($amountCents <= 0) {
            $err = [];
            $err['success'] = false;
            $err['error']   = 'Import de comanda no vàlid.';
            $this->jsonResponse($err, 400);
        }

        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $db->exec('DELETE FROM reserves_temporals_estoc WHERE expira_el < NOW()');

            $stmtDelMine = $db->prepare('DELETE FROM reserves_temporals_estoc WHERE id_usuari = :id_usuari');
            $delMineParams = [];
            $delMineParams['id_usuari'] = $userId;
            $stmtDelMine->execute($delMineParams);

            $stmtLockProd = $db->prepare('SELECT estoc_unitats, nom FROM productes WHERE id = :id FOR UPDATE');
            $stmtOtherRes = $db->prepare('SELECT COALESCE(SUM(quantitat), 0) AS reservat FROM reserves_temporals_estoc WHERE id_producte = :id_producte AND id_usuari <> :id_usuari AND expira_el > NOW()');
            $stmtInsRes   = $db->prepare("INSERT INTO reserves_temporals_estoc (id_usuari, id_producte, quantitat, expira_el) VALUES (:id_usuari, :id_producte, :quantitat, NOW() + INTERVAL '15 minutes')");

            foreach ($summary['groups'] as $grp) {
                foreach ($grp['items'] as $item) {
                    $pid = (string) $item['id_producte'];
                    $qty = (int) $item['quantitat'];

                    $lockParams = [];
                    $lockParams['id'] = $pid;
                    $stmtLockProd->execute($lockParams);
                    $prodRow = $stmtLockProd->fetch();

                    if (!$prodRow) {
                        $db->rollBack();
                        $err = [];
                        $err['success'] = false;
                        $err['error']   = 'Un dels productes de la cistella ja no existeix.';
                        $this->jsonResponse($err, 400);
                    }

                    $realStock = (int) $prodRow['estoc_unitats'];

                    $resParams = [];
                    $resParams['id_producte'] = $pid;
                    $resParams['id_usuari']   = $userId;
                    $stmtOtherRes->execute($resParams);
                    $resRow = $stmtOtherRes->fetch();
                    $reservedByOthers = 0;
                    if ($resRow && isset($resRow['reservat'])) {
                        $reservedByOthers = (int) $resRow['reservat'];
                    }

                    $available = $realStock - $reservedByOthers;
                    if ($available < $qty) {
                        $db->rollBack();
                        $err = [];
                        $err['success'] = false;
                        $err['error']   = 'Estoc insuficient per a ' . (string) $prodRow['nom'] . ' (disponibles ara ' . max(0, $available) . ' u.).';
                        $this->jsonResponse($err, 409);
                    }

                    $stmtInsRes->bindValue(':id_usuari', $userId, PDO::PARAM_STR);
                    $stmtInsRes->bindValue(':id_producte', $pid, PDO::PARAM_STR);
                    $stmtInsRes->bindValue(':quantitat', $qty, PDO::PARAM_INT);
                    $stmtInsRes->execute();
                }
            }

            $stripePi = $this->callStripePaymentIntent($amountCents, $userId);
            if ($stripePi === null) {
                $db->rollBack();
                $err = [];
                $err['success'] = false;
                $err['error']   = 'No s\'ha pogut iniciar la passarel·la de pagament Stripe.';
                $this->jsonResponse($err, 502);
            }

            $piId         = (string) $stripePi['id'];
            $clientSecret = (string) $stripePi['client_secret'];

            $sqlOrder = "INSERT INTO comandes (id_comprador, id_adreca, stripe_payment_intent_id, subtotal_productes, cost_total_enviaments, total_iva, import_total, estat_pagament)
                         VALUES (:id_comprador, :id_adreca, :stripe_pi, :subtotal_productes, :cost_total_enviaments, :total_iva, :import_total, 'PENDENT')
                         RETURNING id";
            $stmtOrder = $db->prepare($sqlOrder);
            $stmtOrder->bindValue(':id_comprador', $userId, PDO::PARAM_STR);
            $stmtOrder->bindValue(':id_adreca', $addressId, PDO::PARAM_STR);
            $stmtOrder->bindValue(':stripe_pi', $piId, PDO::PARAM_STR);
            $stmtOrder->bindValue(':subtotal_productes', number_format((float) $summary['subtotal_productes'], 2, '.', ''), PDO::PARAM_STR);
            $stmtOrder->bindValue(':cost_total_enviaments', number_format((float) $summary['cost_enviament_total'], 2, '.', ''), PDO::PARAM_STR);
            $stmtOrder->bindValue(':total_iva', number_format((float) $summary['iva_total'], 2, '.', ''), PDO::PARAM_STR);
            $stmtOrder->bindValue(':import_total', number_format((float) $summary['import_total'], 2, '.', ''), PDO::PARAM_STR);
            $stmtOrder->execute();
            $orderRow = $stmtOrder->fetch();
            $orderId  = (string) $orderRow['id'];

            $sqlSub = "INSERT INTO subcomandes_ramader (id_comanda, id_ramader, cost_enviament_individual, estat_expedicio)
                       VALUES (:id_comanda, :id_ramader, :cost_enviament, 'PAGAT')
                       RETURNING id";
            $stmtSub = $db->prepare($sqlSub);

            $sqlLine = 'INSERT INTO linies_comanda (id_subcomanda, id_producte, quantitat, preu_unitari_historica, subtotal_linia)
                        VALUES (:id_subcomanda, :id_producte, :quantitat, :preu_unitari, :subtotal_linia)';
            $stmtLine = $db->prepare($sqlLine);

            foreach ($summary['groups'] as $grp) {
                $stmtSub->bindValue(':id_comanda', $orderId, PDO::PARAM_STR);
                $stmtSub->bindValue(':id_ramader', (string) $grp['id_ramader'], PDO::PARAM_STR);
                $stmtSub->bindValue(':cost_enviament', number_format((float) $grp['cost_enviament'], 2, '.', ''), PDO::PARAM_STR);
                $stmtSub->execute();
                $subRow = $stmtSub->fetch();
                $subId  = (string) $subRow['id'];

                foreach ($grp['items'] as $item) {
                    $stmtLine->bindValue(':id_subcomanda', $subId, PDO::PARAM_STR);
                    $stmtLine->bindValue(':id_producte', (string) $item['id_producte'], PDO::PARAM_STR);
                    $stmtLine->bindValue(':quantitat', (int) $item['quantitat'], PDO::PARAM_INT);
                    $stmtLine->bindValue(':preu_unitari', number_format((float) $item['preu_unitari'], 2, '.', ''), PDO::PARAM_STR);
                    $stmtLine->bindValue(':subtotal_linia', number_format((float) $item['subtotal_linia'], 2, '.', ''), PDO::PARAM_STR);
                    $stmtLine->execute();
                }
            }

            $db->commit();

            $_SESSION['last_order_id'] = $orderId;

            $ok = [];
            $ok['success']       = true;
            $ok['client_secret'] = $clientSecret;
            $ok['order_id']      = $orderId;
            $this->jsonResponse($ok, 200);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Create payment error: ' . $e->getMessage());
            $err = [];
            $err['success'] = false;
            $err['error']   = 'Error intern en processar la reserva i crear la comanda.';
            $this->jsonResponse($err, 500);
        }
    }

    public function stripeWebhook()
    {
        $payload = file_get_contents('php://input');
        if ($payload === false || $payload === '') {
            http_response_code(400);
            exit('Payload buit');
        }

        $sigHeader = '';
        if (isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
            $sigHeader = (string) $_SERVER['HTTP_STRIPE_SIGNATURE'];
        }

        $webhookSecret = Database::env('STRIPE_WEBHOOK_SECRET', '');
        if ($sigHeader === '' || $webhookSecret === '') {
            http_response_code(400);
            exit('Signatura o secret absent');
        }

        $timestamp = '';
        $v1Signatures = [];
        $parts = explode(',', $sigHeader);
        foreach ($parts as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) {
                if ($kv[0] === 't') {
                    $timestamp = $kv[1];
                } elseif ($kv[0] === 'v1') {
                    $v1Signatures[] = $kv[1];
                }
            }
        }

        if ($timestamp === '' || !ctype_digit($timestamp) || empty($v1Signatures)) {
            http_response_code(400);
            exit('Capçalera de signatura no vàlida');
        }

        if (abs(time() - (int) $timestamp) > 300) {
            http_response_code(400);
            exit('Signatura caducada');
        }

        $signedPayload = $timestamp . '.' . $payload;
        $expectedSig   = hash_hmac('sha256', $signedPayload, $webhookSecret);

        $sigValid = false;
        foreach ($v1Signatures as $candidateSig) {
            if (hash_equals($expectedSig, $candidateSig)) {
                $sigValid = true;
                break;
            }
        }

        if (!$sigValid) {
            http_response_code(400);
            exit('Signatura HMAC no vàlida');
        }

        $event = json_decode($payload, true);
        if (!is_array($event) || empty($event['type'])) {
            http_response_code(400);
            exit('Esdeveniment JSON no vàlid');
        }

        $eventType = (string) $event['type'];
        $paymentIntentId = '';
        if (isset($event['data']['object']['id'])) {
            $paymentIntentId = (string) $event['data']['object']['id'];
        }

        try {
            if ($eventType === 'payment_intent.succeeded' && $paymentIntentId !== '') {
                $this->getOrderModel()->markPaymentSucceeded($paymentIntentId);
            } elseif ($eventType === 'payment_intent.payment_failed' && $paymentIntentId !== '') {
                $this->getOrderModel()->markPaymentFailed($paymentIntentId);
            }
        } catch (Throwable $e) {
            error_log('Stripe webhook DB error: ' . $e->getMessage());
            http_response_code(500);
            exit('Error intern');
        }

        http_response_code(200);
        echo 'OK';
        exit;
    }

    public function confirmation()
    {
        $_SESSION['cart'] = [];

        $paymentIntentId = '';
        if (isset($_GET['payment_intent'])) {
            $paymentIntentId = trim((string) $_GET['payment_intent']);
        }

        $order = null;
        if ($paymentIntentId !== '') {
            try {
                $candidate = $this->getOrderModel()->getOrderByPaymentIntent($paymentIntentId);
                if ($candidate !== null && isset($_SESSION['user']['id']) && (string) $candidate['id_comprador'] === (string) $_SESSION['user']['id']) {
                    $order = $candidate;
                }
            } catch (Throwable $e) {
                error_log('Confirmation order lookup error: ' . $e->getMessage());
            }
        }

        $viewData = [];
        $viewData['pageTitle'] = 'Comanda confirmada';
        $viewData['order']     = $order;

        $this->render('checkout/confirmacio', $viewData);
    }
}
