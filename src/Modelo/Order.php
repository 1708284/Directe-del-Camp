<?php

declare(strict_types=1);

namespace App\Modelo;

use App\Database;

final class Order
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
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
}
