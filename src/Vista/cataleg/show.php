<?php require __DIR__ . '/../layouts/header.php'; ?>

<?php if ($product === null) { ?>
    <div class="card">
        <h1 class="section-title">Producte no trobat</h1>
        <p class="text-muted mt-2">Aquest producte no existeix o actualment no està disponible.</p>
        <div class="mt-4">
            <a href="/productes" class="btn btn-primary">Tornar al catàleg</a>
        </div>
    </div>
<?php } else {
    $pesGrams = (int) $product['pes_net_grams'];
    $preuPack = (float) $product['preu_pack'];
    $preuKg = 0;
    if ($pesGrams > 0) {
        $preuKg = ($preuPack * 1000) / $pesGrams;
    }
    $estoc = (int) $product['estoc_unitats'];
    $nomCategoria = 'Carn de proximitat';
    if (isset($product['nom_categoria']) && $product['nom_categoria'] !== '') {
        $nomCategoria = (string) $product['nom_categoria'];
    }
    $nomRaca = 'Sense especificar';
    if (isset($product['nom_raca']) && $product['nom_raca'] !== '') {
        $nomRaca = (string) $product['nom_raca'];
    }
    $sistemaCria = 'Cria tradicional controlada';
    if (!empty($product['certificat_ecologic']) && !empty($product['pastura_lliure'])) {
        $sistemaCria = 'Ecològic certificat i pastura lliure';
    } elseif (!empty($product['certificat_ecologic'])) {
        $sistemaCria = 'Ramaderia ecològica certificada';
    } elseif (!empty($product['pastura_lliure'])) {
        $sistemaCria = 'Pastura lliure a l\'aire lliure';
    }
?>
    <div class="mb-4">
        <a href="/productes" class="text-muted" style="font-size: 13px;">← Tornar al catàleg</a>
    </div>

    <div class="product-detail-grid">
        <div class="product-detail-image">
            <?php if (!empty($product['imatge_url'])) { ?>
                <img src="<?php echo htmlspecialchars((string) $product['imatge_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $product['nom'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php } else { ?>
                <div class="product-thumb-placeholder">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                </div>
            <?php } ?>
        </div>

        <div>
            <div class="actions-row mb-4">
                <span class="badge"><?php echo htmlspecialchars($nomCategoria, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php if (!empty($product['certificat_ecologic'])) { ?>
                    <span class="badge badge-eco">Ecològic</span>
                <?php } ?>
                <?php if (!empty($product['pastura_lliure'])) { ?>
                    <span class="badge">Pastura lliure</span>
                <?php } ?>
            </div>

            <h1 style="font-size: 32px; line-height: 1.15;"><?php echo htmlspecialchars((string) $product['nom'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="text-muted mt-2"><?php echo htmlspecialchars((string) $product['nom_explotacio'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars((string) $product['comarca'], ENT_QUOTES, 'UTF-8'); ?></p>

            <div style="display: flex; align-items: baseline; gap: 16px; margin-top: 20px;">
                <span style="font-family: 'Manrope', 'Inter', sans-serif; font-size: 32px; font-weight: 700;"><?php echo number_format($preuPack, 2, ',', '.'); ?> €</span>
                <span class="text-muted" style="font-size: 15px;">(<?php echo number_format($preuKg, 2, ',', '.'); ?> €/kg · <?php echo $pesGrams; ?> g)</span>
            </div>

            <p class="text-muted mt-2" style="font-size: 14px;">Format <?php echo htmlspecialchars((string) $product['format_envasat'], ENT_QUOTES, 'UTF-8'); ?> · IVA inclòs (10%)</p>

            <?php if (!empty($product['descripcio'])) { ?>
                <p class="mt-4" style="color: var(--text-secondary);"><?php echo nl2br(htmlspecialchars((string) $product['descripcio'], ENT_QUOTES, 'UTF-8')); ?></p>
            <?php } ?>

            <div class="traceability-card">
                <h2 style="font-size: 16px;">Fitxa de traçabilitat i origen</h2>
                <div class="traceability-grid">
                    <div>
                        <span class="trace-item-label">Animal i raça</span>
                        <span class="trace-item-val"><?php echo htmlspecialchars($nomCategoria, ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($nomRaca, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div>
                        <span class="trace-item-label">Sistema de cria</span>
                        <span class="trace-item-val"><?php echo htmlspecialchars($sistemaCria, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div>
                        <span class="trace-item-label">Origen i comarca</span>
                        <span class="trace-item-val"><?php echo htmlspecialchars((string) $product['comarca'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div>
                        <span class="trace-item-label">Conservació</span>
                        <span class="trace-item-val">0-4 °C en fred</span>
                    </div>
                    <div>
                        <span class="trace-item-label">Codi oficial REGA</span>
                        <span class="trace-item-val"><?php echo htmlspecialchars((string) $product['codi_rega'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div>
                        <span class="trace-item-label">Lot de traçabilitat</span>
                        <span class="trace-item-val"><?php echo htmlspecialchars((string) $product['codi_lot_tracabilitat'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
            </div>

            <?php if ($estoc > 0) { ?>
                <form method="POST" action="/cistella/afegir" class="card">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="id_producte" value="<?php echo htmlspecialchars((string) $product['id'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="actions-row">
                        <div style="width: 110px;">
                            <label for="qty_input" class="form-label">Quantitat</label>
                            <input id="qty_input" type="number" name="quantitat" class="form-control" min="1" max="<?php echo $estoc; ?>" value="1" required>
                        </div>
                        <div style="flex: 1; padding-top: 22px;">
                            <button type="submit" class="btn btn-primary btn-block">Afegir al carretó</button>
                        </div>
                    </div>
                    <span class="form-help">Estoc disponible actualment <?php echo $estoc; ?> unitats.</span>
                </form>
            <?php } else { ?>
                <div class="alert alert-error">Aquest producte està temporalment esgotat.</div>
            <?php } ?>
        </div>
    </div>

    <section class="card">
        <div class="section-header" style="margin-bottom: 0; align-items: center;">
            <div>
                <span class="badge badge-eco mb-4">Explotació verificada</span>
                <h2 class="section-title"><?php echo htmlspecialchars((string) $product['nom_explotacio'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="section-subtitle">Comarca <?php echo htmlspecialchars((string) $product['comarca'], ENT_QUOTES, 'UTF-8'); ?> · Registre oficial REGA <?php echo htmlspecialchars((string) $product['codi_rega'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <a href="/productes?id_ramader=<?php echo urlencode((string) $product['id_ramader']); ?>" class="btn btn-secondary">Veure més productes d'aquesta granja</a>
        </div>
    </section>
<?php } ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
