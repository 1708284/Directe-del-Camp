<?php
function escape_html(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$old = isset($old) ? $old : [];
$displayError = $error !== null ? $error : ($_SESSION['flash_error'] ?? null);
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Registre - Directe del Camp</title>
</head>
<body>
<h1>Crear compte</h1>

<?php if (!empty($displayError)): ?>
    <p style="color: red;"><strong><?= escape_html($displayError) ?></strong></p>
<?php endif; ?>

<form method="POST" action="/register">
    <input type="hidden" name="csrf_token" value="<?= escape_html($csrf) ?>">

    <p>
        <label>Nom *<br>
            <input type="text" name="nom" required maxlength="100" value="<?= escape_html($old['nom'] ?? '') ?>">
        </label>
    </p>

    <p>
        <label>Cognoms *<br>
            <input type="text" name="cognoms" required maxlength="150" value="<?= escape_html($old['cognoms'] ?? '') ?>">
        </label>
    </p>

    <p>
        <label>Telèfon<br>
            <input type="tel" name="telefon" maxlength="20" placeholder="Opcional" value="<?= escape_html($old['telefon'] ?? '') ?>">
        </label>
    </p>

    <p>
        <label>Email *<br>
            <input type="email" name="email" required autocomplete="email" maxlength="255" value="<?= escape_html($old['email'] ?? '') ?>">
        </label>
    </p>

    <p>
        <label>Contrasenya * (mínim 8 caràcters)<br>
            <input type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
        </label>
    </p>

    <p>
        <label>Confirmar contrasenya *<br>
            <input type="password" name="password_confirm" required minlength="8" maxlength="72" autocomplete="new-password">
        </label>
    </p>

    <p>
        <label>
            <input type="checkbox" name="consentiment_rgpd" value="1" required>
            Accepto la política de privacitat i el tractament de les meves dades (RGPD) *
        </label>
    </p>

    <p><button type="submit">Registrar-me</button></p>
</form>

<h2>O registra't amb Google</h2>
<form method="get" action="/auth/google">
    <p>
        <label>
            <input type="checkbox" name="rgpd" value="1" required>
            Accepto la política de privacitat (RGPD).
        </label>
    </p>
    <p><button type="submit">Identifica't amb Google</button></p>
</form>

<p>Ja tens compte? <a href="/login">Inicia sessió</a></p>
</body>
</html>
