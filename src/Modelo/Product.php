<?php

declare(strict_types=1);

namespace App\Modelo;

use App\Database;
use PDO;

final class Product
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByRancher(string $rancherId)
    {
        $sql = 'SELECT p.*, r.nom AS nom_raca
                FROM productes p
                LEFT JOIN races r ON p.id_raca = r.id
                WHERE p.id_ramader = :id_ramader
                ORDER BY p.nom ASC';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['id_ramader'] = $rancherId;
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findById(string $id)
    {
        $sql = 'SELECT p.*, r.nom AS nom_raca, r.id_categoria_animal
                FROM productes p
                LEFT JOIN races r ON p.id_raca = r.id
                WHERE p.id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['id'] = $id;
        $stmt->execute($params);

        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }
        return null;
    }

    public function create(array $data)
    {
        $sql = 'INSERT INTO productes (
                    id_ramader, id_raca, nom, descripcio, format_envasat,
                    pes_net_grams, preu_pack, estoc_unitats, codi_lot_tracabilitat,
                    certificat_ecologic, pastura_lliure, actiu, imatge_url
                ) VALUES (
                    :id_ramader, :id_raca, :nom, :descripcio, :format_envasat,
                    :pes_net_grams, :preu_pack, :estoc_unitats, :codi_lot_tracabilitat,
                    :certificat_ecologic, :pastura_lliure, :actiu, :imatge_url
                )';

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id_ramader', $data['id_ramader'], PDO::PARAM_STR);
        $stmt->bindValue(':id_raca', (int) $data['id_raca'], PDO::PARAM_INT);
        $stmt->bindValue(':nom', trim((string) $data['nom']), PDO::PARAM_STR);

        $desc = null;
        if (isset($data['descripcio']) && trim((string) $data['descripcio']) !== '') {
            $desc = trim((string) $data['descripcio']);
        }

        if ($desc === null) {
            $stmt->bindValue(':descripcio', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':descripcio', $desc, PDO::PARAM_STR);
        }

        $stmt->bindValue(':format_envasat', trim((string) $data['format_envasat']), PDO::PARAM_STR);
        $stmt->bindValue(':pes_net_grams', (int) $data['pes_net_grams'], PDO::PARAM_INT);
        $stmt->bindValue(':preu_pack', (string) $data['preu_pack'], PDO::PARAM_STR);
        $stmt->bindValue(':estoc_unitats', (int) $data['estoc_unitats'], PDO::PARAM_INT);
        $stmt->bindValue(':codi_lot_tracabilitat', trim((string) $data['codi_lot_tracabilitat']), PDO::PARAM_STR);
        $stmt->bindValue(':certificat_ecologic', !empty($data['certificat_ecologic']), PDO::PARAM_BOOL);
        $stmt->bindValue(':pastura_lliure', !empty($data['pastura_lliure']), PDO::PARAM_BOOL);

        $actiu = true;
        if (isset($data['actiu'])) {
            $actiu = (bool) $data['actiu'];
        }
        $stmt->bindValue(':actiu', $actiu, PDO::PARAM_BOOL);

        $img = null;
        if (isset($data['imatge_url']) && trim((string) $data['imatge_url']) !== '') {
            $img = trim((string) $data['imatge_url']);
        }

        if ($img === null) {
            $stmt->bindValue(':imatge_url', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':imatge_url', $img, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

    public function update(string $id, string $rancherId, array $data)
    {
        $sql = 'UPDATE productes
                SET id_raca = :id_raca,
                    nom = :nom,
                    descripcio = :descripcio,
                    format_envasat = :format_envasat,
                    pes_net_grams = :pes_net_grams,
                    preu_pack = :preu_pack,
                    estoc_unitats = :estoc_unitats,
                    codi_lot_tracabilitat = :codi_lot_tracabilitat,
                    certificat_ecologic = :certificat_ecologic,
                    pastura_lliure = :pastura_lliure,
                    imatge_url = :imatge_url
                WHERE id = :id AND id_ramader = :id_ramader';

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->bindValue(':id_ramader', $rancherId, PDO::PARAM_STR);
        $stmt->bindValue(':id_raca', (int) $data['id_raca'], PDO::PARAM_INT);
        $stmt->bindValue(':nom', trim((string) $data['nom']), PDO::PARAM_STR);

        $desc = null;
        if (isset($data['descripcio']) && trim((string) $data['descripcio']) !== '') {
            $desc = trim((string) $data['descripcio']);
        }

        if ($desc === null) {
            $stmt->bindValue(':descripcio', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':descripcio', $desc, PDO::PARAM_STR);
        }

        $stmt->bindValue(':format_envasat', trim((string) $data['format_envasat']), PDO::PARAM_STR);
        $stmt->bindValue(':pes_net_grams', (int) $data['pes_net_grams'], PDO::PARAM_INT);
        $stmt->bindValue(':preu_pack', (string) $data['preu_pack'], PDO::PARAM_STR);
        $stmt->bindValue(':estoc_unitats', (int) $data['estoc_unitats'], PDO::PARAM_INT);
        $stmt->bindValue(':codi_lot_tracabilitat', trim((string) $data['codi_lot_tracabilitat']), PDO::PARAM_STR);
        $stmt->bindValue(':certificat_ecologic', !empty($data['certificat_ecologic']), PDO::PARAM_BOOL);
        $stmt->bindValue(':pastura_lliure', !empty($data['pastura_lliure']), PDO::PARAM_BOOL);

        $img = null;
        if (isset($data['imatge_url']) && trim((string) $data['imatge_url']) !== '') {
            $img = trim((string) $data['imatge_url']);
        }

        if ($img === null) {
            $stmt->bindValue(':imatge_url', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':imatge_url', $img, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

    public function toggleActive(string $id, string $rancherId)
    {
        $sql = 'UPDATE productes
                SET actiu = NOT actiu
                WHERE id = :id AND id_ramader = :id_ramader';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['id']         = $id;
        $params['id_ramader'] = $rancherId;

        return $stmt->execute($params);
    }

    public function delete(string $id, string $rancherId)
    {
        $sql = 'DELETE FROM productes
                WHERE id = :id AND id_ramader = :id_ramader';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['id']         = $id;
        $params['id_ramader'] = $rancherId;

        return $stmt->execute($params);
    }

    public function getAllCategories()
    {
        $sql = 'SELECT id, nom FROM categories_animals ORDER BY nom ASC';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getAllRaces()
    {
        $sql = 'SELECT id, id_categoria_animal, nom FROM races ORDER BY nom ASC';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function createCategory(string $nom)
    {
        $sql = 'INSERT INTO categories_animals (nom) VALUES (:nom) RETURNING id';
        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['nom'] = trim($nom);
        $stmt->execute($params);
        $row = $stmt->fetch();
        if ($row && isset($row['id'])) {
            return (int) $row['id'];
        }
        return (int) $this->db->lastInsertId();
    }

    public function createRace(string $nom, int $idCategoriaAnimal)
    {
        $sql = 'INSERT INTO races (nom, id_categoria_animal) VALUES (:nom, :id_cat) RETURNING id';
        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['nom']    = trim($nom);
        $params['id_cat'] = $idCategoriaAnimal;
        $stmt->execute($params);
        $row = $stmt->fetch();
        if ($row && isset($row['id'])) {
            return (int) $row['id'];
        }
        return (int) $this->db->lastInsertId();
    }

    public function getPublicCatalog(array $filters = [])
    {
        $sql = 'SELECT p.*,
                       r.nom AS nom_raca,
                       r.id_categoria_animal,
                       c.nom AS nom_categoria,
                       pr.nom_explotacio,
                       pr.codi_rega,
                       pr.comarca
                FROM productes p
                INNER JOIN perfils_ramaders pr ON p.id_ramader = pr.id
                LEFT JOIN races r ON p.id_raca = r.id
                LEFT JOIN categories_animals c ON r.id_categoria_animal = c.id
                WHERE p.actiu = TRUE AND pr.estat_validacio = \'ACTIU\'';

        $params = [];

        if (isset($filters['categoria']) && trim((string) $filters['categoria']) !== '') {
            $cat = trim((string) $filters['categoria']);
            if (ctype_digit($cat)) {
                $sql = $sql . ' AND r.id_categoria_animal = :categoria_id';
                $params['categoria_id'] = (int) $cat;
            } else {
                $sql = $sql . ' AND LOWER(c.nom) = LOWER(:categoria_nom)';
                $params['categoria_nom'] = $cat;
            }
        }

        if (isset($filters['races']) && is_array($filters['races']) && !empty($filters['races'])) {
            $racePlaceholders = [];
            $idx = 0;
            foreach ($filters['races'] as $raceId) {
                $rid = (int) $raceId;
                if ($rid > 0) {
                    $ph = 'raca_' . $idx;
                    $racePlaceholders[] = ':' . $ph;
                    $params[$ph] = $rid;
                    $idx = $idx + 1;
                }
            }
            if (!empty($racePlaceholders)) {
                $sql = $sql . ' AND p.id_raca IN (' . implode(', ', $racePlaceholders) . ')';
            }
        }

        if (isset($filters['comarca']) && trim((string) $filters['comarca']) !== '') {
            $sql = $sql . ' AND LOWER(pr.comarca) = LOWER(:comarca)';
            $params['comarca'] = trim((string) $filters['comarca']);
        }

        if (isset($filters['id_ramader']) && trim((string) $filters['id_ramader']) !== '') {
            $sql = $sql . ' AND p.id_ramader = :id_ramader';
            $params['id_ramader'] = trim((string) $filters['id_ramader']);
        }

        if (isset($filters['preu_max']) && $filters['preu_max'] !== '' && is_numeric($filters['preu_max'])) {
            $preuMax = (float) $filters['preu_max'];
            if ($preuMax > 0) {
                $sql = $sql . ' AND (p.preu_pack * 1000.0 / NULLIF(p.pes_net_grams, 0)) <= :preu_max';
                $params['preu_max'] = $preuMax;
            }
        }

        if (!empty($filters['ecologic'])) {
            $sql = $sql . ' AND p.certificat_ecologic = TRUE';
        }

        if (!empty($filters['pastura'])) {
            $sql = $sql . ' AND p.pastura_lliure = TRUE';
        }

        if (isset($filters['q']) && trim((string) $filters['q']) !== '') {
            $sql = $sql . ' AND (p.nom ILIKE :q OR p.descripcio ILIKE :q OR pr.nom_explotacio ILIKE :q OR pr.comarca ILIKE :q OR r.nom ILIKE :q OR c.nom ILIKE :q)';
            $params['q'] = '%' . trim((string) $filters['q']) . '%';
        }

        $ordre = 'recents';
        if (isset($filters['ordre'])) {
            $ordre = (string) $filters['ordre'];
        }

        if ($ordre === 'preu_asc') {
            $sql = $sql . ' ORDER BY p.preu_pack ASC, p.nom ASC';
        } elseif ($ordre === 'preu_desc') {
            $sql = $sql . ' ORDER BY p.preu_pack DESC, p.nom ASC';
        } else {
            $sql = $sql . ' ORDER BY p.id DESC';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPublicProductById(string $id)
    {
        $sql = 'SELECT p.*,
                       r.nom AS nom_raca,
                       r.id_categoria_animal,
                       c.nom AS nom_categoria,
                       pr.nom_explotacio,
                       pr.codi_rega,
                       pr.comarca
                FROM productes p
                INNER JOIN perfils_ramaders pr ON p.id_ramader = pr.id
                LEFT JOIN races r ON p.id_raca = r.id
                LEFT JOIN categories_animals c ON r.id_categoria_animal = c.id
                WHERE p.id = :id AND p.actiu = TRUE AND pr.estat_validacio = \'ACTIU\'
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['id'] = $id;
        $stmt->execute($params);

        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }
        return null;
    }

    public function getActiveFarms()
    {
        $sql = 'SELECT pr.id,
                       pr.nom_explotacio,
                       pr.codi_rega,
                       pr.comarca,
                       u.nom AS nom_titular,
                       u.cognoms AS cognoms_titular,
                       COUNT(p.id) AS total_productes
                FROM perfils_ramaders pr
                INNER JOIN usuaris u ON pr.id = u.id
                LEFT JOIN productes p ON p.id_ramader = pr.id AND p.actiu = TRUE
                WHERE pr.estat_validacio = \'ACTIU\'
                GROUP BY pr.id, pr.nom_explotacio, pr.codi_rega, pr.comarca, u.nom, u.cognoms
                ORDER BY pr.nom_explotacio ASC';

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getActiveComarques()
    {
        $sql = 'SELECT DISTINCT comarca
                FROM perfils_ramaders
                WHERE estat_validacio = \'ACTIU\' AND comarca IS NOT NULL AND comarca <> \'\'
                ORDER BY comarca ASC';

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();
        $comarques = [];
        foreach ($rows as $row) {
            if (isset($row['comarca'])) {
                $comarques[] = (string) $row['comarca'];
            }
        }
        return $comarques;
    }

    public function getFeaturedProducts()
    {
        $sql = 'SELECT p.*,
                       r.nom AS nom_raca,
                       r.id_categoria_animal,
                       c.nom AS nom_categoria,
                       pr.nom_explotacio,
                       pr.codi_rega,
                       pr.comarca
                FROM productes p
                INNER JOIN perfils_ramaders pr ON p.id_ramader = pr.id
                LEFT JOIN races r ON p.id_raca = r.id
                LEFT JOIN categories_animals c ON r.id_categoria_animal = c.id
                WHERE p.actiu = TRUE AND pr.estat_validacio = \'ACTIU\'
                ORDER BY p.id DESC
                LIMIT 6';

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getPublicProductsByIds(array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = [];
        $params = [];
        $idx = 0;
        foreach ($ids as $id) {
            $cleanId = trim((string) $id);
            if ($cleanId !== '') {
                $ph = 'pid_' . $idx;
                $placeholders[] = ':' . $ph;
                $params[$ph] = $cleanId;
                $idx = $idx + 1;
            }
        }

        if (empty($placeholders)) {
            return [];
        }

        $sql = 'SELECT p.*,
                       r.nom AS nom_raca,
                       c.nom AS nom_categoria,
                       pr.nom_explotacio,
                       pr.codi_rega,
                       pr.comarca
                FROM productes p
                INNER JOIN perfils_ramaders pr ON p.id_ramader = pr.id
                LEFT JOIN races r ON p.id_raca = r.id
                LEFT JOIN categories_animals c ON r.id_categoria_animal = c.id
                WHERE p.id IN (' . implode(', ', $placeholders) . ')
                  AND p.actiu = TRUE
                  AND pr.estat_validacio = \'ACTIU\'';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
