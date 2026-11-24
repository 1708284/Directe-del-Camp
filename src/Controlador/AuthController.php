<?php

declare(strict_types=1);

namespace App\Controlador;

use App\Database;
use App\Modelo\User;
use Throwable;

final class AuthController extends BaseController
{
    private const GOOGLE_AUTH_URL     = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL    = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

    public function showLogin($error = null)
    {
        $viewData = [];
        $viewData['error'] = $error;
        $this->render('login', $viewData);
    }

    public function processLogin()
    {
        $this->verifyCsrf();

        $attempts = 0;
        if (isset($_SESSION['login_attempts'])) {
            $attempts = (int) $_SESSION['login_attempts'];
        }
        $attempts = $attempts + 1;
        $_SESSION['login_attempts'] = $attempts;

        if ($_SESSION['login_attempts'] > 5) {
            $this->showLogin('Massa intents. Torna-ho a provar més tard.');
            return;
        }

        $email = '';
        if (isset($_POST['email'])) {
            $email = trim((string) $_POST['email']);
        }

        $password = '';
        if (isset($_POST['password'])) {
            $password = (string) $_POST['password'];
        }

        $user = null;
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $model = new User();
            $user = $model->findByEmail($email);
        }

        $hash = '';
        if ($user !== null) {
            if (isset($user['contrasenya_hash'])) {
                $hash = (string) $user['contrasenya_hash'];
            } elseif (isset($user['password_hash'])) {
                $hash = (string) $user['password_hash'];
            }
        }

        if (!$user || $hash === '' || !password_verify($password, $hash)) {
            $this->showLogin('Credencials incorrectes.');
            return;
        }

        unset($_SESSION['login_attempts']);
        $this->loginUser($user);
    }

    public function showRegister($error = null, array $old = [])
    {
        $flashError = null;
        if (isset($_SESSION['flash_error'])) {
            $flashError = $_SESSION['flash_error'];
        }
        unset($_SESSION['flash_error']);

        $flashOld = [];
        if (isset($_SESSION['flash_old'])) {
            $flashOld = $_SESSION['flash_old'];
        }
        unset($_SESSION['flash_old']);

        $finalError = $error;
        if ($finalError === null) {
            $finalError = $flashError;
        }

        $finalOld = $old;
        if (empty($finalOld)) {
            $finalOld = $flashOld;
        }

        $viewData = [];
        $viewData['error'] = $finalError;
        $viewData['old']   = $finalOld;

        $this->render('register', $viewData);
    }

    public function processRegister()
    {
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

        $email = '';
        if (isset($_POST['email'])) {
            $email = trim((string) $_POST['email']);
        }

        $password = '';
        if (isset($_POST['password'])) {
            $password = (string) $_POST['password'];
        }

        $passwordConfirm = '';
        if (isset($_POST['password_confirm'])) {
            $passwordConfirm = (string) $_POST['password_confirm'];
        }

        $rgpd = false;
        if (isset($_POST['consentiment_rgpd']) && (string) $_POST['consentiment_rgpd'] === '1') {
            $rgpd = true;
        }

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
            $this->redirect('/register');
        }

        $model = new User();
        try {
            if ($model->findByEmail($email)) {
                $_SESSION['flash_error'] = 'Aquest correu electrònic ja està registrat.';
                $_SESSION['flash_old']   = $old;
                $this->redirect('/register');
            }

            $createData = [];
            $createData['nom']      = $nom;
            $createData['cognoms']  = $cognoms;
            $createData['telefon']  = $telefon;
            $createData['email']    = $email;
            $createData['password'] = $password;

            $model->create($createData);
        } catch (Throwable $e) {
            error_log('Register error: ' . $e->getMessage());
            $debug = false;
            if (Database::env('APP_DEBUG', 'false') === 'true') {
                $debug = true;
            }

            if ($debug) {
                $_SESSION['flash_error'] = 'Error al registrar: ' . $e->getMessage();
            } else {
                $_SESSION['flash_error'] = 'No s\'ha pogut completar el registre.';
            }
            $_SESSION['flash_old'] = $old;
            $this->redirect('/register');
        }

        $newUser = $model->findByEmail($email);
        $this->loginUser($newUser);
    }

    private function uploadPdfToSupabase(array $file)
    {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if (isset($file['size']) && (int) $file['size'] > 5242880) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            return null;
        }

        $filename = bin2hex(random_bytes(16)) . '.pdf';

        $supabaseUrl = Database::env('SUPABASE_URL', '');
        $serviceKey  = Database::env('SUPABASE_SERVICE_KEY', '');

        if ($supabaseUrl === '' || $serviceKey === '') {
            error_log('Supabase storage credentials missing');
            return null;
        }

        $bucket = 'documents-sanitaris';
        $uploadUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/' . $bucket . '/' . $filename;

        $fileContent = file_get_contents($file['tmp_name']);

        $headers = [];
        $headers[] = 'Authorization: Bearer ' . $serviceKey;
        $headers[] = 'apiKey: ' . $serviceKey;
        $headers[] = 'Content-Type: application/pdf';

        $httpOptions = [];
        $httpOptions['method']        = 'POST';
        $httpOptions['header']        = implode("\r\n", $headers);
        $httpOptions['content']       = $fileContent;
        $httpOptions['timeout']       = 30;
        $httpOptions['ignore_errors'] = true;

        $contextOptions = [];
        $contextOptions['http'] = $httpOptions;

        $ctx = stream_context_create($contextOptions);
        $res = @file_get_contents($uploadUrl, false, $ctx);

        if ($res === false) {
            error_log('Failed to upload PDF to Supabase');
            return null;
        }

        $publicUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/public/' . $bucket . '/' . $filename;
        return $publicUrl;
    }

    public function showRegisterRamader($error = null, array $old = [])
    {
        $flashError = null;
        if (isset($_SESSION['flash_error'])) {
            $flashError = $_SESSION['flash_error'];
        }
        unset($_SESSION['flash_error']);

        $flashOld = [];
        if (isset($_SESSION['flash_old'])) {
            $flashOld = $_SESSION['flash_old'];
        }
        unset($_SESSION['flash_old']);

        $finalError = $error;
        if ($finalError === null) {
            $finalError = $flashError;
        }

        $finalOld = $old;
        if (empty($finalOld)) {
            $finalOld = $flashOld;
        }

        $viewData = [];
        $viewData['error'] = $finalError;
        $viewData['old']   = $finalOld;

        $this->render('register_ramader', $viewData);
    }

    public function processRegisterRamader()
    {
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

        $email = '';
        if (isset($_POST['email'])) {
            $email = trim((string) $_POST['email']);
        }

        $password = '';
        if (isset($_POST['password'])) {
            $password = (string) $_POST['password'];
        }

        $passwordConfirm = '';
        if (isset($_POST['password_confirm'])) {
            $passwordConfirm = (string) $_POST['password_confirm'];
        }

        $nomExplotacio = '';
        if (isset($_POST['nom_explotacio'])) {
            $nomExplotacio = trim((string) $_POST['nom_explotacio']);
        }

        $cifNif = '';
        if (isset($_POST['cif_nif'])) {
            $cifNif = trim((string) $_POST['cif_nif']);
        }

        $comarca = '';
        if (isset($_POST['comarca'])) {
            $comarca = trim((string) $_POST['comarca']);
        }

        $codiRega = '';
        if (isset($_POST['codi_rega'])) {
            $codiRega = trim((string) $_POST['codi_rega']);
        }

        $rgpd = false;
        if (isset($_POST['consentiment_rgpd']) && (string) $_POST['consentiment_rgpd'] === '1') {
            $rgpd = true;
        }

        $old = [];
        $old['nom']            = $nom;
        $old['cognoms']        = $cognoms;
        $old['telefon']        = $telefon;
        $old['email']          = $email;
        $old['nom_explotacio'] = $nomExplotacio;
        $old['cif_nif']        = $cifNif;
        $old['comarca']        = $comarca;
        $old['codi_rega']      = $codiRega;

        $hasDoc = false;
        if (isset($_FILES['document_sanitari']) && $_FILES['document_sanitari']['error'] === UPLOAD_ERR_OK) {
            $hasDoc = true;
        }

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
        } elseif ($nomExplotacio === '' || mb_strlen($nomExplotacio) > 150) {
            $error = 'El nom de l\'explotació és obligatori (màx. 150 caràcters).';
        } elseif ($cifNif === '' || mb_strlen($cifNif) > 15) {
            $error = 'El CIF o NIF és obligatori (màx. 15 caràcters).';
        } elseif ($comarca === '' || mb_strlen($comarca) > 100) {
            $error = 'La comarca és obligatòria (màx. 100 caràcters).';
        } elseif (!preg_match('/^ES[0-9A-Z]{12}$/i', $codiRega)) {
            $error = 'El codi REGA no és vàlid (ha de començar per ES seguit de 12 caràcters alfanumèrics).';
        } elseif (!$hasDoc) {
            $error = 'Has d\'adjuntar el certificat sanitari oficial en format PDF.';
        } elseif (!$rgpd) {
            $error = 'Has d\'acceptar la política de privacitat (RGPD).';
        }

        if ($error !== null) {
            $_SESSION['flash_error'] = $error;
            $_SESSION['flash_old']   = $old;
            $this->redirect('/register-ramader');
        }

        $model = new User();
        try {
            if ($model->findByEmail($email)) {
                $_SESSION['flash_error'] = 'Aquest correu electrònic ja està registrat.';
                $_SESSION['flash_old']   = $old;
                $this->redirect('/register-ramader');
            }

            $pdfUrl = $this->uploadPdfToSupabase($_FILES['document_sanitari']);
            if ($pdfUrl === null) {
                $_SESSION['flash_error'] = 'No s\'ha pogut pujar el document sanitari (assegura\'t que sigui un PDF vàlid de menys de 5MB).';
                $_SESSION['flash_old']   = $old;
                $this->redirect('/register-ramader');
            }

            $userData = [];
            $userData['nom']      = $nom;
            $userData['cognoms']  = $cognoms;
            $userData['telefon']  = $telefon;
            $userData['email']    = $email;
            $userData['password'] = $password;

            $rancherData = [];
            $rancherData['nom_explotacio']        = $nomExplotacio;
            $rancherData['cif_nif']               = $cifNif;
            $rancherData['comarca']               = $comarca;
            $rancherData['codi_rega']             = $codiRega;
            $rancherData['document_sanitari_url'] = $pdfUrl;

            $model->createRancher($userData, $rancherData);
            $_SESSION['flash_success'] = 'Sol·licitud registrada correctament. El teu compte s\'activarà com a ramader un cop l\'administrador verifiqui la documentació sanitària.';
            $this->redirect('/login');
        } catch (Throwable $e) {
            error_log('Register ramader error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'No s\'ha pogut registrar la sol·licitud d\'alta com a ramader.';
            $_SESSION['flash_old']   = $old;
            $this->redirect('/register-ramader');
        }
    }

    public function logout()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->redirect('/');
    }

    public function googleRedirect()
    {
        $state = bin2hex(random_bytes(32));
        $_SESSION['oauth_state'] = $state;

        $rgpdParam = '';
        if (isset($_GET['rgpd'])) {
            $rgpdParam = (string) $_GET['rgpd'];
        }
        $_SESSION['oauth_rgpd'] = ($rgpdParam === '1');

        $params = [];
        $params['client_id']     = Database::env('GOOGLE_CLIENT_ID');
        $params['redirect_uri']  = Database::env('GOOGLE_REDIRECT_URI');
        $params['response_type'] = 'code';
        $params['scope']         = 'openid email profile';
        $params['state']         = $state;
        $params['prompt']        = 'select_account';

        $query = http_build_query($params);
        $this->redirect(self::GOOGLE_AUTH_URL . '?' . $query);
    }

    public function googleCallback()
    {
        $state = '';
        if (isset($_GET['state'])) {
            $state = (string) $_GET['state'];
        }

        $expected = '';
        if (isset($_SESSION['oauth_state'])) {
            $expected = (string) $_SESSION['oauth_state'];
        }

        $rgpd = false;
        if (isset($_SESSION['oauth_rgpd'])) {
            $rgpd = (bool) $_SESSION['oauth_rgpd'];
        }

        unset($_SESSION['oauth_state'], $_SESSION['oauth_rgpd']);

        if ($expected === '' || !hash_equals($expected, $state) || empty($_GET['code'])) {
            $this->showLogin('No s\'ha pogut iniciar sessió amb Google.');
            return;
        }

        $tokenBody = [];
        $tokenBody['code']          = (string) $_GET['code'];
        $tokenBody['client_id']     = Database::env('GOOGLE_CLIENT_ID');
        $tokenBody['client_secret'] = Database::env('GOOGLE_CLIENT_SECRET');
        $tokenBody['redirect_uri']  = Database::env('GOOGLE_REDIRECT_URI');
        $tokenBody['grant_type']    = 'authorization_code';

        $token = $this->httpJson(self::GOOGLE_TOKEN_URL, 'POST', http_build_query($tokenBody));

        if (empty($token['access_token'])) {
            $this->showLogin('No s\'ha pogut iniciar sessió amb Google.');
            return;
        }

        $profile = $this->httpJson(self::GOOGLE_USERINFO_URL, 'GET', null, $token['access_token']);

        $emailVerified = false;
        if (isset($profile['email_verified']) && $profile['email_verified'] === true) {
            $emailVerified = true;
        }

        if (empty($profile['sub']) || empty($profile['email']) || !$emailVerified) {
            $this->showLogin('El compte de Google no té un email verificat.');
            return;
        }

        $model = new User();
        $user = $model->findByGoogleId($profile['sub']);
        if ($user === null) {
            $user = $model->findByEmail($profile['email']);
        }

        if (!$user) {
            if (!$rgpd) {
                $this->showRegister('Per crear un compte amb Google has d\'acceptar la política de privacitat.');
                return;
            }
            try {
                $givenName = 'Usuari';
                if (isset($profile['given_name'])) {
                    $givenName = (string) $profile['given_name'];
                }

                $familyName = '';
                if (isset($profile['family_name'])) {
                    $familyName = (string) $profile['family_name'];
                }

                $googleData = [];
                $googleData['nom']       = mb_substr($givenName, 0, 100);
                $googleData['cognoms']   = mb_substr($familyName, 0, 150);
                $googleData['email']     = $profile['email'];
                $googleData['google_id'] = $profile['sub'];

                $model->createGoogleUser($googleData);
            } catch (Throwable $e) {
                error_log('Google register error: ' . $e->getMessage());
                $this->showLogin('No s\'ha pogut iniciar sessió amb Google.');
                return;
            }
            $user = $model->findByGoogleId($profile['sub']);
        }

        $this->loginUser($user);
    }

    private function loginUser($user)
    {
        if (!$user) {
            $this->showLogin('No s\'ha pogut iniciar sessió.');
            return;
        }
        session_regenerate_id(true);

        $userEmail = '';
        if (isset($user['email'])) {
            $userEmail = (string) $user['email'];
        }

        $sessionUser = [];
        $sessionUser['id']    = (string) $user['id'];
        $sessionUser['nom']   = (string) $user['nom'];
        $sessionUser['email'] = $userEmail;
        $sessionUser['rol']   = (string) $user['rol'];

        $_SESSION['user'] = $sessionUser;
        $this->redirect('/');
    }

    private function httpJson(string $url, string $method, $body = null, $bearer = null)
    {
        $headers = [];
        $headers[] = 'Accept: application/json';
        if ($body !== null) {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }
        if ($bearer !== null) {
            $headers[] = 'Authorization: Bearer ' . $bearer;
        }

        $content = '';
        if ($body !== null) {
            $content = $body;
        }

        $httpOptions = [];
        $httpOptions['method']        = $method;
        $httpOptions['header']        = implode("\r\n", $headers);
        $httpOptions['content']       = $content;
        $httpOptions['timeout']       = 10;
        $httpOptions['ignore_errors'] = true;

        $sslOptions = [];
        $sslOptions['verify_peer']      = true;
        $sslOptions['verify_peer_name'] = true;

        $contextOptions = [];
        $contextOptions['http'] = $httpOptions;
        $contextOptions['ssl']  = $sslOptions;

        $ctx = stream_context_create($contextOptions);

        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            error_log('OAuth HTTP error contacting ' . parse_url($url, PHP_URL_HOST));
            return [];
        }
        $data = json_decode($raw, true);
        if (is_array($data)) {
            return $data;
        }
        return [];
    }
}
