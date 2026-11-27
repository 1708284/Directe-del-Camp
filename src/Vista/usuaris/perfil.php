<?php
$pageTitle = 'El meu perfil';
require __DIR__ . '/../layouts/header.php';

$email = '';
if (isset($user['email'])) {
    $email = (string) $user['email'];
}

$rol = 'CONSUMIDOR';
if (isset($user['rol'])) {
    $rol = (string) $user['rol'];
}

$nom = '';
if (isset($user['nom'])) {
    $nom = (string) $user['nom'];
}

$cognoms = '';
if (isset($user['cognoms'])) {
    $cognoms = (string) $user['cognoms'];
}

$telefon = '';
if (isset($user['telefon'])) {
    $telefon = (string) $user['telefon'];
}

$savedAddresses = [];
if (isset($addresses) && is_array($addresses)) {
    $savedAddresses = $addresses;
}
?>

<div class="section-header">
    <div>
        <h1 class="section-title">El meu perfil</h1>
        <p class="section-subtitle">Gestiona les teves dades personals i adreces d'enviament refrigerat.</p>
    </div>
    <div class="actions-row">
        <a href="/perfil/comandes" class="btn btn-secondary btn-sm">Les meves comandes</a>
        <?php if ($rol === 'RAMADER') { ?>
            <a href="/ramader/productes" class="btn btn-primary btn-sm">Panell Ramader</a>
        <?php } elseif ($rol === 'ADMIN') { ?>
            <a href="/admin/ramaders" class="btn btn-primary btn-sm">Panell Admin</a>
        <?php } ?>
    </div>
</div>

<div class="profile-grid">
    <section class="card">
        <h2 class="card-title">Dades personals</h2>
        <form method="POST" action="/perfil">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label class="form-label">Correu electrònic (no modificable)</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">Rol del compte</label>
                <div><span class="badge badge-dark"><?php echo htmlspecialchars($rol, ENT_QUOTES, 'UTF-8'); ?></span></div>
            </div>

            <div class="form-group">
                <label for="perfil_nom" class="form-label">Nom *</label>
                <input id="perfil_nom" type="text" name="nom" class="form-control" required maxlength="100" value="<?php echo htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="perfil_cognoms" class="form-label">Cognoms *</label>
                <input id="perfil_cognoms" type="text" name="cognoms" class="form-control" required maxlength="150" value="<?php echo htmlspecialchars($cognoms, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="perfil_telefon" class="form-label">Telèfon de contacte</label>
                <input id="perfil_telefon" type="tel" name="telefon" class="form-control" maxlength="20" placeholder="Ex. 612345678" value="<?php echo htmlspecialchars($telefon, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Actualitzar dades del perfil</button>
        </form>
    </section>

    <section class="card">
        <h2 class="card-title">Adreces d'enviament</h2>

        <?php if (empty($savedAddresses)) { ?>
            <p class="text-muted mb-4">Encara no tens cap adreça guardada.</p>
        <?php } else { ?>
            <div class="mb-4">
                <?php foreach ($savedAddresses as $addr) {
                    $addrLine = '';
                    if (isset($addr['adreca'])) {
                        $addrLine = (string) $addr['adreca'];
                    }
                    $cp = '';
                    if (isset($addr['codi_postal'])) {
                        $cp = (string) $addr['codi_postal'];
                    }
                    $mun = '';
                    if (isset($addr['municipi'])) {
                        $mun = (string) $addr['municipi'];
                    }
                    $prov = '';
                    if (isset($addr['provincia'])) {
                        $prov = (string) $addr['provincia'];
                    }
                ?>
                    <div class="address-card">
                        <div>
                            <div style="font-weight: 600;">
                                <?php echo htmlspecialchars($addrLine, ENT_QUOTES, 'UTF-8'); ?>
                                <?php if (!empty($addr['es_principal'])) { ?>
                                    <span class="badge badge-eco" style="margin-left: 6px;">Principal</span>
                                <?php } ?>
                            </div>
                            <div class="text-muted" style="font-size: 13px;">
                                <?php echo htmlspecialchars($cp, ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($mun, ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($prov, ENT_QUOTES, 'UTF-8'); ?>)
                            </div>
                        </div>
                        <form method="POST" action="/perfil/adreces/eliminar" onsubmit="return confirm('Segur que vols eliminar aquesta adreça?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $addr['id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="divider"></div>
        <h3 style="font-size: 16px; margin-bottom: 12px;">Afegir nova adreça</h3>

        <form method="POST" action="/perfil/adreces/crear">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label for="addr_adreca" class="form-label">Adreça completa *</label>
                <input id="addr_adreca" type="text" name="adreca" class="form-control" required maxlength="255" placeholder="Carrer, número, pis...">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="addr_cp" class="form-label">Codi postal *</label>
                    <input id="addr_cp" type="text" name="codi_postal" class="form-control" required maxlength="10" placeholder="Ex. 08500">
                </div>
                <div class="form-group">
                    <label for="addr_municipi" class="form-label">Municipi *</label>
                    <input id="addr_municipi" type="text" name="municipi" class="form-control" required maxlength="100" placeholder="Ex. Vic">
                </div>
            </div>

            <div class="form-group">
                <label for="addr_provincia" class="form-label">Província *</label>
                <input id="addr_provincia" type="text" name="provincia" class="form-control" required maxlength="100" placeholder="Ex. Barcelona">
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="es_principal" value="1">
                    <span>Marcar com a adreça d'enviament principal</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Desar adreça</button>
        </form>
    </section>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
