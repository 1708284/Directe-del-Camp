<?php

declare(strict_types=1);


require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Modelo/User.php';

const GOOGLE_AUTH_URL     = 'https://accounts.google.com/o/oauth2/v2/auth';
const GOOGLE_TOKEN_URL    = 'https://oauth2.googleapis.com/token';
const GOOGLE_USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';


function auth_show_login(?string $error = null): void
{
    $data = [];
    $data['error'] = $error;
    auth_render('login', $data);
}

function auth_process_login(): void
{
    auth_verify_csrf();

    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] > 5) {
        auth_show_login('Massa intents. Torna-ho a provar més tard.');
        return;
    }

    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? user_find_by_email($email) : null;
    $hash = (string) ($user['contrasenya_hash'] ?? $user['password_hash'] ?? '');

    if (!$user || $hash === '' || !password_verify($password, $hash)) {
        auth_show_login('Credencials incorrectes.');
        return;
    }

    unset($_SESSION['login_attempts']);
    auth_login_user($user);
}


function auth_show_register(?string $error = null, array $old = []): void
{
    $flashError = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_error']);

    $flashOld = $_SESSION['flash_old'] ?? [];
    unset($_SESSION['flash_old']);

    $data = [];
    $data['error'] = $error !== null ? $error : $flashError;
    $data['old']   = !empty($old) ? $old : $flashOld;

    auth_render('register', $data);
}

function auth_process_register(): void
{
    auth_verify_csrf();

    $nom             = trim((string) ($_POST['nom'] ?? ''));
    $cognoms         = trim((string) ($_POST['cognoms'] ?? ''));
    $telefon         = trim((string) ($_POST['telefon'] ?? ''));
    $email           = trim((string) ($_POST['email'] ?? ''));
    $password        = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
    $rgpd            = isset($_POST['consentiment_rgpd']) && (string) $_POST['consentiment_rgpd'] === '1';

    $old = [];
    $old['nom']     = $nom;
    $old['cognoms'] = $cognoms;
    $old['telefon'] = $telefon;
    $old['email']   = $email;

    $error = null;
    if ($nom === '' || mb_strlen($nom) > 100) {
        $error = 'El nom és obligatori (màx. 100 caràcters).';
    } elseif ($cognoms === '' || mb_strlen($cognoms) > 150) {
        $error = 'Els cognoms són obligatoris (màx. 150 caràcters).';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'L\'email no és vàlid.';
    } elseif ($password === '') {
        $error = 'La contrasenya és obligatòria.';
    } elseif ($passwordConfirm === '') {
        $error = 'Has de confirmar la contrasenya.';
    } elseif (mb_strlen($password) < 8) {
        $error = 'La contrasenya ha de tenir com a mínim 8 caràcters.';
    } elseif (strlen($password) > 72) {
        $error = 'La contrasenya és massa llarga (màx. 72 bytes per BCRYPT).';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Les contrasenyes no coincideixen.';
    } elseif (!$rgpd) {
        $error = 'Has d\'acceptar la política de privacitat (RGPD).';
    }

    if ($error !== null) {
        $_SESSION['flash_error'] = $error;
        $_SESSION['flash_old']   = $old;
        auth_redirect('/register');
    }

    if (user_find_by_email($email)) {
        $_SESSION['flash_error'] = 'Aquest correu electrònic ja està registrat.';
        $_SESSION['flash_old']   = $old;
        auth_redirect('/register');
    }

    try {
        $userData = [];
        $userData['nom']      = $nom;
        $userData['cognoms']  = $cognoms;
        $userData['telefon']  = $telefon;
        $userData['email']    = $email;
        $userData['password'] = $password;

        user_create($userData);
    } catch (Throwable $e) {
        error_log('Register error: ' . $e->getMessage());
        $debug = db_env('APP_DEBUG', 'false') === 'true';
        if ($debug) {
            $_SESSION['flash_error'] = 'Error al registrar: ' . $e->getMessage();
        } else {
            $_SESSION['flash_error'] = 'No s\'ha pogut completar el registre.';
        }
        $_SESSION['flash_old'] = $old;
        auth_redirect('/register');
    }

    $newUser = user_find_by_email($email);
    auth_login_user($newUser);
}


function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    auth_redirect('/');
}

#google

function auth_google_redirect(): void
{
    $state = bin2hex(random_bytes(32));
    $_SESSION['oauth_state'] = $state;
    $_SESSION['oauth_rgpd'] = ($_GET['rgpd'] ?? '') === '1';

    $queryParams = [];
    $queryParams['client_id']     = db_env('GOOGLE_CLIENT_ID');
    $queryParams['redirect_uri']  = db_env('GOOGLE_REDIRECT_URI');
    $queryParams['response_type'] = 'code';
    $queryParams['scope']         = 'openid email profile';
    $queryParams['state']         = $state;
    $queryParams['prompt']        = 'select_account';

    $query = http_build_query($queryParams);
    auth_redirect(GOOGLE_AUTH_URL . '?' . $query);
}

function auth_google_callback(): void
{
    $state    = (string) ($_GET['state'] ?? '');
    $expected = (string) ($_SESSION['oauth_state'] ?? '');
    $rgpd     = (bool) ($_SESSION['oauth_rgpd'] ?? false);
    unset($_SESSION['oauth_state'], $_SESSION['oauth_rgpd']);

    if ($expected === '' || !hash_equals($expected, $state) || empty($_GET['code'])) {
        auth_show_login('No s\'ha pogut iniciar sessió amb Google.');
        return;
    }

    $tokenBody = [];
    $tokenBody['code']          = (string) $_GET['code'];
    $tokenBody['client_id']     = db_env('GOOGLE_CLIENT_ID');
    $tokenBody['client_secret'] = db_env('GOOGLE_CLIENT_SECRET');
    $tokenBody['redirect_uri']  = db_env('GOOGLE_REDIRECT_URI');
    $tokenBody['grant_type']    = 'authorization_code';

    $token = auth_http_json(GOOGLE_TOKEN_URL, 'POST', http_build_query($tokenBody));

    if (empty($token['access_token'])) {
        auth_show_login('No s\'ha pogut iniciar sessió amb Google.');
        return;
    }

    $profile = auth_http_json(GOOGLE_USERINFO_URL, 'GET', null, $token['access_token']);

    if (empty($profile['sub']) || empty($profile['email']) || ($profile['email_verified'] ?? false) !== true) {
        auth_show_login('El compte de Google no té un email verificat.');
        return;
    }

    $user = user_find_by_google_id($profile['sub']);
    if (!$user) {
        $user = user_find_by_email($profile['email']);
    }

    if (!$user) {
        if (!$rgpd) {
            auth_show_register('Per crear un compte amb Google has d\'acceptar la política de privacitat.');
            return;
        }
        try {
            $googleUserData = [];
            $googleUserData['nom']       = mb_substr((string) ($profile['given_name'] ?? 'Usuari'), 0, 100);
            $googleUserData['cognoms']   = mb_substr((string) ($profile['family_name'] ?? ''), 0, 150);
            $googleUserData['email']     = $profile['email'];
            $googleUserData['google_id'] = $profile['sub'];

            user_create_google_user($googleUserData);
        } catch (Throwable $e) {
            error_log('Google register error: ' . $e->getMessage());
            auth_show_login('No s\'ha pogut iniciar sessió amb Google.');
            return;
        }
        $user = user_find_by_google_id($profile['sub']);
    }

    auth_login_user($user);
}

#helpers

function auth_login_user(?array $user): void
{
    if (!$user) {
        auth_show_login('No s\'ha pogut iniciar sessió.');
        return;
    }
    session_regenerate_id(true);

    $sessionUser = [];
    $sessionUser['id']    = (string) $user['id'];
    $sessionUser['nom']   = (string) $user['nom'];
    $sessionUser['email'] = (string) ($user['email'] ?? '');
    $sessionUser['rol']   = (string) $user['rol'];

    $_SESSION['user'] = $sessionUser;
    auth_redirect('/');
}

function auth_verify_csrf(): void
{
    $sent = (string) ($_POST['csrf_token'] ?? '');
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(403);
        exit('Petició no vàlida.');
    }
}

function auth_http_json(string $url, string $method, ?string $body = null, ?string $bearer = null): array
{
    $headers = [];
    $headers[] = 'Accept: application/json';
    if ($body !== null) {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }
    if ($bearer !== null) {
        $headers[] = 'Authorization: Bearer ' . $bearer;
    }

    $httpOpts = [];
    $httpOpts['method']        = $method;
    $httpOpts['header']        = implode("\r\n", $headers);
    $httpOpts['content']       = $body !== null ? $body : '';
    $httpOpts['timeout']       = 10;
    $httpOpts['ignore_errors'] = true;

    $sslOpts = [];
    $sslOpts['verify_peer']      = true;
    $sslOpts['verify_peer_name'] = true;

    $contextOpts = [];
    $contextOpts['http'] = $httpOpts;
    $contextOpts['ssl']  = $sslOpts;

    $ctx = stream_context_create($contextOpts);

    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        error_log('OAuth HTTP error contacting ' . parse_url($url, PHP_URL_HOST));
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function auth_render(string $view, array $data = []): void
{
    $data['csrf'] = $_SESSION['csrf_token'];
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../Vista/' . $view . '.php';
}

function auth_redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}