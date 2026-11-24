<?php
$pageTitle = 'Registre';
require __DIR__ . '/layouts/header.php';

$safeOld = [];
if (isset($old) && is_array($old)) {
    $safeOld = $old;
}

$displayError = null;
if (isset($error) && $error !== null) {
    $displayError = $error;
}

$nomVal = '';
if (isset($safeOld['nom'])) {
    $nomVal = (string) $safeOld['nom'];
}

$cognomsVal = '';
if (isset($safeOld['cognoms'])) {
    $cognomsVal = (string) $safeOld['cognoms'];
}

$telefonVal = '';
if (isset($safeOld['telefon'])) {
    $telefonVal = (string) $safeOld['telefon'];
}

$emailVal = '';
if (isset($safeOld['email'])) {
    $emailVal = (string) $safeOld['email'];
}
?>

<div class="auth-wrapper">
    <div class="card">
        <h1 class="section-title">Crear compte</h1>
        <p class="section-subtitle mb-4">Compra directament a les granges verificades de proximitat.</p>

        <?php if (!empty($displayError)) { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars((string) $displayError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>

        <form method="POST" action="/register">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="reg_nom" class="form-label">Nom *</label>
                    <input id="reg_nom" type="text" name="nom" class="form-control" required maxlength="100" value="<?php echo htmlspecialchars($nomVal, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label for="reg_cognoms" class="form-label">Cognoms *</label>
                    <input id="reg_cognoms" type="text" name="cognoms" class="form-control" required maxlength="150" value="<?php echo htmlspecialchars($cognomsVal, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="reg_telefon" class="form-label">Telèfon</label>
                <input id="reg_telefon" type="tel" name="telefon" class="form-control" maxlength="20" placeholder="Opcional" value="<?php echo htmlspecialchars($telefonVal, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="reg_email" class="form-label">Email *</label>
                <input id="reg_email" type="email" name="email" class="form-control" required autocomplete="email" maxlength="255" value="<?php echo htmlspecialchars($emailVal, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="reg_pass" class="form-label">Contrasenya * (mínim 8 caràcters)</label>
                    <input id="reg_pass" type="password" name="password" class="form-control" required minlength="8" maxlength="72" autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="reg_pass2" class="form-label">Confirmar contrasenya *</label>
                    <input id="reg_pass2" type="password" name="password_confirm" class="form-control" required minlength="8" maxlength="72" autocomplete="new-password">
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="consentiment_rgpd" value="1" required>
                    <span>Accepto la política de privacitat i el tractament de les meves dades (RGPD) *</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Registrar-me</button>
        </form>

        <div class="divider"></div>

        <h2 style="font-size: 16px; margin-bottom: 10px;">O registra't amb Google</h2>
        <form method="GET" action="/auth/google">
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="rgpd" value="1" required>
                    <span>Accepto la política de privacitat (RGPD).</span>
                </label>
            </div>
            <button type="submit" class="btn btn-secondary btn-block">Identifica't amb Google</button>
        </form>

        <div class="divider"></div>
        <p style="font-size: 14px;">Ets ramader o productor? <a href="/register-ramader" style="text-decoration: underline; font-weight: 600;">Registra la teva explotació aquí</a></p>
        <p class="mt-2 text-muted" style="font-size: 14px;">Ja tens compte? <a href="/login" style="text-decoration: underline;">Inicia sessió</a></p>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
