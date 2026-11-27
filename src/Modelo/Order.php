<?php

declare(strict_types=1);

namespace App\Modelo;

use App\Database;
use PDO;
use Throwable;

final class Order
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getRancherSuborders(string $rancherId)
    {
        $sql = 'SELECT sr.*,
                       c.estat_pagament,
                       c.id_comprador,
                       c.id_adreca,
                       c.creat_el,
                       u.nom AS comprador_nom,
                       u.cognoms AS comprador_cognoms,
                       u.email AS comprador_email,
                       u.telefon AS comprador_telefon,
                       ae.adreca,
                       ae.codi_postal,
                       ae.municipi,
                       ae.provincia
                FROM subcomandes_ramader sr
                INNER JOIN comandes c ON sr.id_comanda = c.id
                INNER JOIN usuaris u ON c.id_comprador = u.id
                LEFT JOIN adreces_enviament ae ON c.id_adreca = ae.id
                WHERE sr.id_ramader = :id_ramader
                  AND c.estat_pagament = \'PAGAT\'
                ORDER BY c.creat_el DESC, sr.id DESC';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['id_ramader'] = $rancherId;
        $stmt->execute($params);
        $suborders = $stmt->fetchAll();

        $result = [];
        foreach ($suborders as $sub) {
            $lines = $this->getLinesBySuborder((string) $sub['id']);
            $subtotalProd = 0.0;
            foreach ($lines as $lin) {
                $subtotalProd = $subtotalProd + (float) $lin['subtotal_linia'];
            }
            $sub['linies'] = $lines;
            $sub['subtotal_productes'] = round($subtotalProd, 2);
            $result[] = $sub;
        }
        return $result;
    }

    public function getLinesBySuborder(string $suborderId)
    {
        $sql = 'SELECT lc.*,
                       p.nom AS producte_nom,
                       p.format_envasat,
                       p.codi_lot_tracabilitat
                FROM linies_comanda lc
                LEFT JOIN productes p ON lc.id_producte = p.id
                WHERE lc.id_subcomanda = :id_subcomanda
                ORDER BY lc.id ASC';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['id_subcomanda'] = $suborderId;
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateSuborderStatus(string $suborderId, string $rancherId, string $newStatus, string $trackingCode = '')
    {
        $sqlCheck = 'SELECT id, estat_expedicio
                     FROM subcomandes_ramader
                     WHERE id = :id AND id_ramader = :id_ramader
                     LIMIT 1';

        $stmtCheck = $this->db->prepare($sqlCheck);
        $checkParams = [];
        $checkParams['id']         = $suborderId;
        $checkParams['id_ramader'] = $rancherId;
        $stmtCheck->execute($checkParams);
        $current = $stmtCheck->fetch();

        if (!$current) {
            return false;
        }

        $currentStatus = '';
        if (isset($current['estat_expedicio'])) {
            $currentStatus = (string) $current['estat_expedicio'];
        }

        $validTransition = false;
        if ($currentStatus === 'PAGAT' && $newStatus === 'EN_PREPARACIO') {
            $validTransition = true;
        } elseif ($currentStatus === 'EN_PREPARACIO' && $newStatus === 'ENVIAT') {
            $validTransition = true;
        } elseif ($currentStatus === 'ENVIAT' && $newStatus === 'LLIURAT') {
            $validTransition = true;
        }

        if (!$validTransition) {
            return false;
        }

        if ($newStatus === 'ENVIAT') {
            $sqlUpd = 'UPDATE subcomandes_ramader
                       SET estat_expedicio = :estat,
                           codi_seguiment_transport = :tracking,
                           recollit_el = NOW()
                       WHERE id = :id AND id_ramader = :id_ramader';
            $stmtUpd = $this->db->prepare($sqlUpd);
            $updParams = [];
            $updParams['estat']      = $newStatus;
            $updParams['tracking']   = trim($trackingCode);
            $updParams['id']         = $suborderId;
            $updParams['id_ramader'] = $rancherId;
            return $stmtUpd->execute($updParams);
        }

        $sqlUpd = 'UPDATE subcomandes_ramader
                   SET estat_expedicio = :estat
                   WHERE id = :id AND id_ramader = :id_ramader';
        $stmtUpd = $this->db->prepare($sqlUpd);
        $updParams = [];
        $updParams['estat']      = $newStatus;
        $updParams['id']         = $suborderId;
        $updParams['id_ramader'] = $rancherId;
        return $stmtUpd->execute($updParams);
    }

    public function getConsumerOrders(string $userId)
    {
        $sql = 'SELECT c.*,
                       ae.adreca,
                       ae.codi_postal,
                       ae.municipi,
                       ae.provincia
                FROM comandes c
                LEFT JOIN adreces_enviament ae ON c.id_adreca = ae.id
                WHERE c.id_comprador = :id_comprador
                ORDER BY c.creat_el DESC, c.id DESC';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['id_comprador'] = $userId;
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        $result = [];
        foreach ($orders as $order) {
            $sqlSub = 'SELECT sr.*,
                              pr.nom_explotacio,
                              pr.comarca
                       FROM subcomandes_ramader sr
                       LEFT JOIN perfils_ramaders pr ON sr.id_ramader = pr.id
                       WHERE sr.id_comanda = :id_comanda
                       ORDER BY sr.id ASC';

            $stmtSub = $this->db->prepare($sqlSub);
            $subParams = [];
            $subParams['id_comanda'] = (string) $order['id'];
            $stmtSub->execute($subParams);
            $subs = $stmtSub->fetchAll();

            $subsWithLines = [];
            foreach ($subs as $sub) {
                $sub['linies'] = $this->getLinesBySuborder((string) $sub['id']);
                $subsWithLines[] = $sub;
            }

            $order['subcomandes'] = $subsWithLines;
            $result[] = $order;
        }

        return $result;
    }

    public function getOrderByPaymentIntent(string $paymentIntentId)
    {
        $sql = 'SELECT * FROM comandes WHERE stripe_payment_intent_id = :pi LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['pi'] = $paymentIntentId;
        $stmt->execute($params);
        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }
        return null;
    }

    public function markPaymentSucceeded(string $paymentIntentId)
    {
        $this->db->beginTransaction();

        try {
            $stmtOrder = $this->db->prepare('SELECT * FROM comandes WHERE stripe_payment_intent_id = :pi FOR UPDATE');
            $piParams = [];
            $piParams['pi'] = $paymentIntentId;
            $stmtOrder->execute($piParams);
            $order = $stmtOrder->fetch();

            if (!$order) {
                $this->db->rollBack();
                return 'NOT_FOUND';
            }

            if (isset($order['estat_pagament']) && (string) $order['estat_pagament'] === 'PAGAT') {
                $this->db->commit();
                return 'ALREADY_PAID';
            }

            $orderId = (string) $order['id'];
            $userId  = (string) $order['id_comprador'];

            $stmtUpdOrder = $this->db->prepare("UPDATE comandes SET estat_pagament = 'PAGAT' WHERE id = :id");
            $ordParams = [];
            $ordParams['id'] = $orderId;
            $stmtUpdOrder->execute($ordParams);

            $stmtUpdSubs = $this->db->prepare("UPDATE subcomandes_ramader SET estat_expedicio = 'PAGAT' WHERE id_comanda = :id_comanda");
            $subUpdParams = [];
            $subUpdParams['id_comanda'] = $orderId;
            $stmtUpdSubs->execute($subUpdParams);

            $sqlLines = 'SELECT lc.id_producte, lc.quantitat
                         FROM linies_comanda lc
                         INNER JOIN subcomandes_ramader sr ON lc.id_subcomanda = sr.id
                         WHERE sr.id_comanda = :id_comanda';
            $stmtLines = $this->db->prepare($sqlLines);
            $linesParams = [];
            $linesParams['id_comanda'] = $orderId;
            $stmtLines->execute($linesParams);
            $lines = $stmtLines->fetchAll();

            $stmtDecStock = $this->db->prepare('UPDATE productes SET estoc_unitats = GREATEST(estoc_unitats - :qty, 0) WHERE id = :id_producte');
            foreach ($lines as $line) {
                $stmtDecStock->bindValue(':qty', (int) $line['quantitat'], PDO::PARAM_INT);
                $stmtDecStock->bindValue(':id_producte', (string) $line['id_producte'], PDO::PARAM_STR);
                $stmtDecStock->execute();
            }

            $stmtDelRes = $this->db->prepare('DELETE FROM reserves_temporals_estoc WHERE id_usuari = :id_usuari');
            $delParams = [];
            $delParams['id_usuari'] = $userId;
            $stmtDelRes->execute($delParams);

            $this->db->commit();
            return 'PAID';
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function markPaymentFailed(string $paymentIntentId)
    {
        $this->db->beginTransaction();

        try {
            $stmtOrder = $this->db->prepare('SELECT * FROM comandes WHERE stripe_payment_intent_id = :pi FOR UPDATE');
            $piParams = [];
            $piParams['pi'] = $paymentIntentId;
            $stmtOrder->execute($piParams);
            $order = $stmtOrder->fetch();

            if (!$order) {
                $this->db->rollBack();
                return false;
            }

            if (isset($order['estat_pagament']) && (string) $order['estat_pagament'] === 'PAGAT') {
                $this->db->commit();
                return true;
            }

            $orderId = (string) $order['id'];
            $userId  = (string) $order['id_comprador'];

            $stmtUpdOrder = $this->db->prepare("UPDATE comandes SET estat_pagament = 'FALLIT' WHERE id = :id");
            $ordParams = [];
            $ordParams['id'] = $orderId;
            $stmtUpdOrder->execute($ordParams);

            $stmtDelRes = $this->db->prepare('DELETE FROM reserves_temporals_estoc WHERE id_usuari = :id_usuari');
            $delParams = [];
            $delParams['id_usuari'] = $userId;
            $stmtDelRes->execute($delParams);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
