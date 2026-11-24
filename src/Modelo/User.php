<?php

declare(strict_types=1);

namespace App\Modelo;

use App\Database;
use PDO;
use Throwable;

final class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email)
    {
        $stmt = $this->db->prepare('SELECT * FROM usuaris WHERE email = :email LIMIT 1');
        $params = [];
        $params['email'] = mb_strtolower(trim($email));
        $stmt->execute($params);

        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }
        return null;
    }

    public function findById(string $id)
    {
        $stmt = $this->db->prepare('SELECT * FROM usuaris WHERE id = :id LIMIT 1');
        $params = [];
        $params['id'] = $id;
        $stmt->execute($params);

        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }
        return null;
    }

    public function findByGoogleId(string $googleId)
    {
        $stmt = $this->db->prepare('SELECT * FROM usuaris WHERE google_oauth_id = :gid LIMIT 1');
        $params = [];
        $params['gid'] = $googleId;
        $stmt->execute($params);

        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }
        return null;
    }

    public function create(array $data)
    {
        $sql = 'INSERT INTO usuaris (email, contrasenya_hash, nom, cognoms, telefon, rol, google_oauth_id, consentiment_rgpd)
                VALUES (:email, :contrasenya_hash, :nom, :cognoms, :telefon, \'CONSUMIDOR\', NULL, :consentiment_rgpd)';

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':email', mb_strtolower(trim($data['email'])), PDO::PARAM_STR);
        $stmt->bindValue(':contrasenya_hash', password_hash($data['password'], PASSWORD_BCRYPT), PDO::PARAM_STR);
        $stmt->bindValue(':nom', trim($data['nom']), PDO::PARAM_STR);
        $stmt->bindValue(':cognoms', trim($data['cognoms']), PDO::PARAM_STR);

        $telefon = null;
        if (isset($data['telefon']) && trim((string) $data['telefon']) !== '') {
            $telefon = trim((string) $data['telefon']);
        }

        if ($telefon === null) {
            $stmt->bindValue(':telefon', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':telefon', $telefon, PDO::PARAM_STR);
        }

        $stmt->bindValue(':consentiment_rgpd', true, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    public function createGoogleUser(array $data)
    {
        $sql = 'INSERT INTO usuaris (email, contrasenya_hash, nom, cognoms, telefon, rol, google_oauth_id, consentiment_rgpd)
                VALUES (:email, NULL, :nom, :cognoms, NULL, \'CONSUMIDOR\', :gid, TRUE)';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['email']   = mb_strtolower(trim($data['email']));
        $params['nom']     = $data['nom'];
        $params['cognoms'] = $data['cognoms'];
        $params['gid']     = $data['google_id'];

        return $stmt->execute($params);
    }

    public function updateProfile(string $id, array $data)
    {
        $sql = 'UPDATE usuaris
                SET nom = :nom,
                    cognoms = :cognoms,
                    telefon = :telefon
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->bindValue(':nom', trim($data['nom']), PDO::PARAM_STR);
        $stmt->bindValue(':cognoms', trim($data['cognoms']), PDO::PARAM_STR);

        $telefon = null;
        if (isset($data['telefon']) && trim((string) $data['telefon']) !== '') {
            $telefon = trim((string) $data['telefon']);
        }

        if ($telefon === null) {
            $stmt->bindValue(':telefon', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':telefon', $telefon, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

    public function getPendingRanchers()
    {
        $sql = 'SELECT u.id, u.nom, u.cognoms, u.email, u.telefon, pr.*
                FROM usuaris u
                INNER JOIN perfils_ramaders pr ON u.id = pr.id
                WHERE pr.estat_validacio = \'PENDENT\'
                ORDER BY u.nom ASC';

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function updateRancherStatus(string $rancherId, string $status)
    {
        $stmt = $this->db->prepare('UPDATE perfils_ramaders SET estat_validacio = :status WHERE id = :id');
        $params = [];
        $params['status'] = $status;
        $params['id']     = $rancherId;
        $res = $stmt->execute($params);

        if ($res && $status === 'ACTIU') {
            $stmtUser = $this->db->prepare("UPDATE usuaris SET rol = 'RAMADER' WHERE id = :id");
            $userParams = [];
            $userParams['id'] = $rancherId;
            $stmtUser->execute($userParams);
        }

        return $res;
    }

    public function createRancher(array $userData, array $rancherData)
    {
        $this->db->beginTransaction();

        try {
            $sqlUser = 'INSERT INTO usuaris (email, contrasenya_hash, nom, cognoms, telefon, rol, consentiment_rgpd)
                        VALUES (:email, :contrasenya_hash, :nom, :cognoms, :telefon, \'CONSUMIDOR\', :consentiment_rgpd)
                        RETURNING id';

            $stmtUser = $this->db->prepare($sqlUser);

            $stmtUser->bindValue(':email', mb_strtolower(trim($userData['email'])), PDO::PARAM_STR);
            $stmtUser->bindValue(':contrasenya_hash', password_hash($userData['password'], PASSWORD_BCRYPT), PDO::PARAM_STR);
            $stmtUser->bindValue(':nom', trim($userData['nom']), PDO::PARAM_STR);
            $stmtUser->bindValue(':cognoms', trim($userData['cognoms']), PDO::PARAM_STR);

            $telefon = null;
            if (isset($userData['telefon']) && trim((string) $userData['telefon']) !== '') {
                $telefon = trim((string) $userData['telefon']);
            }

            if ($telefon === null) {
                $stmtUser->bindValue(':telefon', null, PDO::PARAM_NULL);
            } else {
                $stmtUser->bindValue(':telefon', $telefon, PDO::PARAM_STR);
            }

            $stmtUser->bindValue(':consentiment_rgpd', true, PDO::PARAM_BOOL);
            $stmtUser->execute();

            $userRow = $stmtUser->fetch();
            if (!$userRow || empty($userRow['id'])) {
                $this->db->rollBack();
                return false;
            }

            $userId = (string) $userRow['id'];

            $sqlRancher = 'INSERT INTO perfils_ramaders (id, nom_explotacio, codi_rega, cif_nif, comarca, document_sanitari_url, estat_validacio)
                           VALUES (:id, :nom_explotacio, :codi_rega, :cif_nif, :comarca, :document_sanitari_url, \'PENDENT\')';

            $stmtRancher = $this->db->prepare($sqlRancher);

            $stmtRancher->bindValue(':id', $userId, PDO::PARAM_STR);
            $stmtRancher->bindValue(':nom_explotacio', trim($rancherData['nom_explotacio']), PDO::PARAM_STR);
            $stmtRancher->bindValue(':codi_rega', strtoupper(trim($rancherData['codi_rega'])), PDO::PARAM_STR);
            $stmtRancher->bindValue(':cif_nif', strtoupper(trim($rancherData['cif_nif'])), PDO::PARAM_STR);
            $stmtRancher->bindValue(':comarca', trim($rancherData['comarca']), PDO::PARAM_STR);
            $stmtRancher->bindValue(':document_sanitari_url', trim($rancherData['document_sanitari_url']), PDO::PARAM_STR);

            $stmtRancher->execute();

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getAddressesByUser(string $userId)
    {
        $sql = 'SELECT * FROM adreces_enviament
                WHERE id_usuari = :id_usuari
                ORDER BY es_principal DESC, id DESC';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['id_usuari'] = $userId;
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getAddressByIdForUser(string $addressId, string $userId)
    {
        $sql = 'SELECT * FROM adreces_enviament
                WHERE id = :id AND id_usuari = :id_usuari
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['id']        = $addressId;
        $params['id_usuari'] = $userId;
        $stmt->execute($params);

        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }
        return null;
    }

    public function createAddress(string $userId, array $data)
    {
        $esPrincipal = false;
        if (!empty($data['es_principal'])) {
            $esPrincipal = true;
        }

        if ($esPrincipal) {
            $stmtReset = $this->db->prepare('UPDATE adreces_enviament SET es_principal = FALSE WHERE id_usuari = :id_usuari');
            $resetParams = [];
            $resetParams['id_usuari'] = $userId;
            $stmtReset->execute($resetParams);
        }

        $sql = 'INSERT INTO adreces_enviament (id_usuari, adreca, codi_postal, municipi, provincia, es_principal)
                VALUES (:id_usuari, :adreca, :codi_postal, :municipi, :provincia, :es_principal)
                RETURNING id';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuari', $userId, PDO::PARAM_STR);
        $stmt->bindValue(':adreca', trim((string) $data['adreca']), PDO::PARAM_STR);
        $stmt->bindValue(':codi_postal', trim((string) $data['codi_postal']), PDO::PARAM_STR);
        $stmt->bindValue(':municipi', trim((string) $data['municipi']), PDO::PARAM_STR);
        $stmt->bindValue(':provincia', trim((string) $data['provincia']), PDO::PARAM_STR);
        $stmt->bindValue(':es_principal', $esPrincipal, PDO::PARAM_BOOL);
        $stmt->execute();

        $row = $stmt->fetch();
        if ($row && isset($row['id'])) {
            return (string) $row['id'];
        }
        return '';
    }

    public function deleteAddress(string $addressId, string $userId)
    {
        $sql = 'DELETE FROM adreces_enviament
                WHERE id = :id AND id_usuari = :id_usuari';

        $stmt = $this->db->prepare($sql);
        $params = [];
        $params['id']        = $addressId;
        $params['id_usuari'] = $userId;

        return $stmt->execute($params);
    }
}
