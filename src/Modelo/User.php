<?php

declare(strict_types=1);

require_once __DIR__ . '/../Database.php';

function user_find_by_email(string $email): ?array
{
    $db = db_get_connection();
    $stmt = db_prepare($db, 'SELECT * FROM usuaris WHERE email = :email LIMIT 1');
    $params = [];
    $params['email'] = mb_strtolower(trim($email));
    db_execute($stmt, $params);
    $result = db_fetch($stmt);
    return $result ? $result : null;
}

function user_find_by_id(string $id): ?array
{
    $db = db_get_connection();
    $stmt = db_prepare($db, 'SELECT * FROM usuaris WHERE id = :id LIMIT 1');
    $params = [];
    $params['id'] = $id;
    db_execute($stmt, $params);
    $result = db_fetch($stmt);
    return $result ? $result : null;
}

function user_find_by_google_id(string $googleId): ?array
{
    $db = db_get_connection();
    $stmt = db_prepare($db, 'SELECT * FROM usuaris WHERE google_oauth_id = :gid LIMIT 1');
    $params = [];
    $params['gid'] = $googleId;
    db_execute($stmt, $params);
    $result = db_fetch($stmt);
    return $result ? $result : null;
}


function user_create(array $data): bool
{
    $db = db_get_connection();
    $sql = 'INSERT INTO usuaris (email, contrasenya_hash, nom, cognoms, telefon, rol, google_oauth_id, consentiment_rgpd)
            VALUES (:email, :contrasenya_hash, :nom, :cognoms, :telefon, \'CONSUMIDOR\', NULL, :consentiment_rgpd)';

    $stmt = db_prepare($db, $sql);

    db_bind_value($stmt, ':email', mb_strtolower(trim($data['email'])), PDO::PARAM_STR);
    db_bind_value($stmt, ':contrasenya_hash', password_hash($data['password'], PASSWORD_BCRYPT), PDO::PARAM_STR);
    db_bind_value($stmt, ':nom', trim($data['nom']), PDO::PARAM_STR);
    db_bind_value($stmt, ':cognoms', trim($data['cognoms']), PDO::PARAM_STR);

    $telefon = isset($data['telefon']) && trim((string) $data['telefon']) !== ''
        ? trim((string) $data['telefon'])
        : null;

    if ($telefon === null) {
        db_bind_value($stmt, ':telefon', null, PDO::PARAM_NULL);
    } else {
        db_bind_value($stmt, ':telefon', $telefon, PDO::PARAM_STR);
    }

    db_bind_value($stmt, ':consentiment_rgpd', true, PDO::PARAM_BOOL);

    return db_execute($stmt);
}

function user_create_google_user(array $data): bool
{
    $db = db_get_connection();
    $sql = 'INSERT INTO usuaris (email, contrasenya_hash, nom, cognoms, telefon, rol, google_oauth_id, consentiment_rgpd)
            VALUES (:email, NULL, :nom, :cognoms, NULL, \'CONSUMIDOR\', :gid, TRUE)';

    $stmt = db_prepare($db, $sql);
    $params = [];
    $params['email']   = mb_strtolower(trim($data['email']));
    $params['nom']     = $data['nom'];
    $params['cognoms'] = $data['cognoms'];
    $params['gid']     = $data['google_id'];

    return db_execute($stmt, $params);
}
