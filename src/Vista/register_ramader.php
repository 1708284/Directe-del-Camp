<?php
$pageTitle = 'Registre de Ramaders';
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

$explotacioVal = '';
if (isset($safeOld['nom_explotacio'])) {
    $explotacioVal = (string) $safeOld['nom_explotacio'];
}

$cifVal = '';
if (isset($safeOld['cif_nif'])) {
    $cifVal = (string) $safeOld['cif_nif'];
}

$comarcaVal = '';
if (isset($safeOld['comarca'])) {
    $comarcaVal = (string) $safeOld['comarca'];
}

$regaVal = '';
if (isset($safeOld['codi_rega'])) {
    $regaVal = (string) $safeOld['codi_rega'];
}
?>

<div class="auth-wrapper-wide">
    <div class="card">
        <span class="badge badge-eco mb-4">Alta d'explotació ramadera</span>
        <h1 class="section-title">Registre de Ramaders</h1>
        <p class="section-subtitle mb-4">Dona d'alta la teva explotació amb codi REGA per vendre directament als consumidors.</p>

        <?php if (!empty($displayError)) { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars((string) $displayError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>

        <form method="POST" action="/register-ramader" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">

            <h2 style="font-size: 17px; margin-bottom: 14px;">1. Dades d'Accés i Personals</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="rr_nom" class="form-label">Nom del titular *</label>
                    <input id="rr_nom" type="text" name="nom" class="form-control" required maxlength="100" value="<?php echo htmlspecialchars($nomVal, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label for="rr_cognoms" class="form-label">Cognoms *</label>
                    <input id="rr_cognoms" type="text" name="cognoms" class="form-control" required maxlength="150" value="<?php echo htmlspecialchars($cognomsVal, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="rr_telefon" class="form-label">Telèfon de contacte</label>
                    <input id="rr_telefon" type="tel" name="telefon" class="form-control" maxlength="20" placeholder="Ex. 612345678" value="<?php echo htmlspecialchars($telefonVal, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label for="rr_email" class="form-label">Correu electrònic *</label>
                    <input id="rr_email" type="email" name="email" class="form-control" required autocomplete="email" maxlength="255" value="<?php echo htmlspecialchars($emailVal, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="rr_pass" class="form-label">Contrasenya * (mínim 8 caràcters)</label>
                    <input id="rr_pass" type="password" name="password" class="form-control" required minlength="8" maxlength="72" autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="rr_pass2" class="form-label">Confirmar contrasenya *</label>
                    <input id="rr_pass2" type="password" name="password_confirm" class="form-control" required minlength="8" maxlength="72" autocomplete="new-password">
                </div>
            </div>

            <div class="divider"></div>
            <h2 style="font-size: 17px; margin-bottom: 14px;">2. Dades de l'Explotació Ramadera</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="rr_explotacio" class="form-label">Nom comercial de l'explotació *</label>
                    <input id="rr_explotacio" type="text" name="nom_explotacio" class="form-control" required maxlength="150" placeholder="Ex. Granja Can Mas" value="<?php echo htmlspecialchars($explotacioVal, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label for="rr_cif" class="form-label">NIF o CIF *</label>
                    <input id="rr_cif" type="text" name="cif_nif" class="form-control" required maxlength="15" value="<?php echo htmlspecialchars($cifVal, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="rr_comarca" class="form-label">Comarca *</label>
                    <input id="rr_comarca" type="text" name="comarca" class="form-control" required maxlength="100" placeholder="Ex. Osona, Ripollès" value="<?php echo htmlspecialchars($comarcaVal, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label for="rr_rega" class="form-label">Codi oficial REGA *</label>
                    <input id="rr_rega" type="text" name="codi_rega" class="form-control" required maxlength="14" pattern="^[eE][sS][0-9a-zA-Z]{12}$" placeholder="ES123456789012" value="<?php echo htmlspecialchars($regaVal, ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="form-help">Format oficial ES seguit de 12 caràcters alfanumèrics.</span>
                </div>
            </div>

            <div class="form-group">
                <label for="rr_doc" class="form-label">Certificat d'inscripció sanitària oficial * (PDF, màx. 5 MB)</label>
                <input id="rr_doc" type="file" name="document_sanitari" class="form-control" accept="application/pdf" required>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="consentiment_rgpd" value="1" required>
                    <span>Accepto la política de privacitat i el tractament de dades (RGPD) *</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Enviar sol·licitud d'alta com a ramader</button>
        </form>

        <div class="divider"></div>
        <p style="font-size: 14px;">Ets un consumidor particular? <a href="/register" style="text-decoration: underline; font-weight: 600;">Registra't aquí</a></p>
        <p class="mt-2 text-muted" style="font-size: 14px;">Ja tens compte? <a href="/login" style="text-decoration: underline;">Inicia sessió</a></p>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
