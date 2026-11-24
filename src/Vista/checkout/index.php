<?php
require __DIR__ . '/../layouts/header.php';

$groups = [];
if (isset($summary['groups']) && is_array($summary['groups'])) {
    $groups = $summary['groups'];
}

$savedAddresses = [];
if (isset($addresses) && is_array($addresses)) {
    $savedAddresses = $addresses;
}

$hasSavedAddresses = !empty($savedAddresses);
?>

<div class="section-header">
    <div>
        <h1 class="section-title">Finalitzar comanda</h1>
        <p class="section-subtitle">Selecciona la teva adreça d'entrega refrigerada i revisa el resum de la compra.</p>
    </div>
    <a href="/cistella" class="btn btn-secondary btn-sm">← Tornar a la cistella</a>
</div>

<div class="checkout-grid">
    <div>
        <section class="card mb-4">
            <h2 class="card-title">1. Adreça d'enviament refrigerat</h2>

            <?php if ($hasSavedAddresses) { ?>
                <div class="mb-4">
                    <?php
                    $addrIdx = 0;
                    foreach ($savedAddresses as $addr) {
                        $isDef = false;
                        if (!empty($addr['es_principal']) || $addrIdx === 0) {
                            $isDef = true;
                        }
                        $addrIdx = $addrIdx + 1;
                    ?>
                        <label class="address-radio-card">
                            <input type="radio" name="id_adreca_enviament" value="<?php echo htmlspecialchars((string) $addr['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php if ($isDef) { echo 'checked'; } ?>>
                            <div>
                                <div style="font-weight: 600;">
                                    <?php echo htmlspecialchars((string) $addr['adreca'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if (!empty($addr['es_principal'])) { ?>
                                        <span class="badge badge-eco" style="margin-left: 6px;">Principal</span>
                                    <?php } ?>
                                </div>
                                <div class="text-muted" style="font-size: 13px;">
                                    <?php echo htmlspecialchars((string) $addr['codi_postal'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars((string) $addr['municipi'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars((string) $addr['provincia'], ENT_QUOTES, 'UTF-8'); ?>)
                                </div>
                            </div>
                        </label>
                    <?php } ?>

                    <label class="address-radio-card">
                        <input type="radio" name="id_adreca_enviament" value="nova">
                        <div>
                            <div style="font-weight: 600;">Utilitzar una adreça nova</div>
                            <div class="text-muted" style="font-size: 13px;">Es guardarà al teu perfil automàticament.</div>
                        </div>
                    </label>
                </div>
            <?php } else { ?>
                <input type="hidden" name="id_adreca_enviament" value="nova">
            <?php } ?>

            <div id="new_address_fields" <?php if ($hasSavedAddresses) { echo 'style="display: none;"'; } ?>>
                <div class="form-group">
                    <label for="co_adreca" class="form-label">Adreça completa *</label>
                    <input id="co_adreca" type="text" name="nova_adreca" class="form-control" maxlength="255" placeholder="Carrer, número, pis...">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="co_cp" class="form-label">Codi postal *</label>
                        <input id="co_cp" type="text" name="nova_codi_postal" class="form-control" maxlength="10" placeholder="Ex. 08500">
                    </div>
                    <div class="form-group">
                        <label for="co_mun" class="form-label">Municipi *</label>
                        <input id="co_mun" type="text" name="nova_municipi" class="form-control" maxlength="100" placeholder="Ex. Vic">
                    </div>
                </div>
                <div class="form-group">
                    <label for="co_prov" class="form-label">Província *</label>
                    <input id="co_prov" type="text" name="nova_provincia" class="form-control" maxlength="100" placeholder="Ex. Barcelona">
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="card-title">2. Pagament amb targeta</h2>
            <p class="text-muted" style="font-size: 14px;">Passarel·la de pagament en integració.</p>
        </section>
    </div>

    <aside class="card">
        <h2 class="card-title">Resum de la comanda</h2>

        <?php foreach ($groups as $grp) { ?>
            <div style="padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid var(--border-color);">
                <div style="font-weight: 600; font-size: 14px; margin-bottom: 6px;">
                    <?php echo htmlspecialchars((string) $grp['nom_explotacio'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php foreach ($grp['items'] as $item) { ?>
                    <div class="summary-row" style="padding: 3px 0; font-size: 13px;">
                        <span><?php echo (int) $item['quantitat']; ?>x <?php echo htmlspecialchars((string) $item['product']['nom'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span><?php echo number_format((float) $item['subtotal_linia'], 2, ',', '.'); ?> €</span>
                    </div>
                <?php } ?>
                <div class="summary-row" style="padding: 3px 0; font-size: 12px; color: var(--text-muted);">
                    <span>Enviament refrigerat</span>
                    <span>8,00 €</span>
                </div>
            </div>
        <?php } ?>

        <div class="summary-row">
            <span>Subtotal productes</span>
            <strong><?php echo number_format((float) $summary['subtotal_productes'], 2, ',', '.'); ?> €</strong>
        </div>
        <div class="summary-row">
            <span>Transport refrigerat</span>
            <strong><?php echo number_format((float) $summary['cost_enviament_total'], 2, ',', '.'); ?> €</strong>
        </div>
        <div class="summary-row" style="font-size: 13px; color: var(--text-muted);">
            <span>Base imposable total</span>
            <span><?php echo number_format((float) $summary['base_total'], 2, ',', '.'); ?> €</span>
        </div>
        <div class="summary-row" style="font-size: 13px; color: var(--text-muted);">
            <span>IVA inclòs (10% prod. + 21% env.)</span>
            <span><?php echo number_format((float) $summary['iva_total'], 2, ',', '.'); ?> €</span>
        </div>

        <div class="summary-total">
            <span>Total a pagar</span>
            <span><?php echo number_format((float) $summary['import_total'], 2, ',', '.'); ?> €</span>
        </div>

        <div class="form-group mt-4">
            <label class="checkbox-label">
                <input type="checkbox" id="acceptar_condicions" name="acceptar_condicions" value="1">
                <span style="font-size: 13px;">Accepto les condicions de venda directa, cadena de fred i política de privacitat.</span>
            </label>
        </div>

        <button type="button" class="btn btn-primary btn-block" disabled>Finalitzar Comanda</button>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var newAddrBox = document.getElementById('new_address_fields');
    var radios = document.querySelectorAll('input[name="id_adreca_enviament"]');
    for (var i = 0; i < radios.length; i++) {
        radios[i].addEventListener('change', function() {
            if (this.value === 'nova') {
                newAddrBox.style.display = 'block';
            } else {
                newAddrBox.style.display = 'none';
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
