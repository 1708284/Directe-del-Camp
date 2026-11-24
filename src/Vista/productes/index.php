<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="section-header">
    <div>
        <h1 class="section-title">Els meus productes</h1>
        <p class="section-subtitle">Gestiona el catàleg, preus, estoc i visibilitat dels teus lots.</p>
    </div>
    <a href="/ramader/productes/crear" class="btn btn-primary">+ Crear nou producte</a>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Productes totals</div>
        <div class="kpi-value"><?php echo (int) $kpiTotal; ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Actius a la botiga</div>
        <div class="kpi-value"><?php echo (int) $kpiActive; ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Productes sense estoc</div>
        <div class="kpi-value" <?php if ((int) $kpiOutOfStock > 0) { echo 'style="color: var(--danger-text);"'; } ?>><?php echo (int) $kpiOutOfStock; ?></div>
    </div>
</div>

<?php if (empty($products)) { ?>
    <div class="card">
        <h2 style="font-size: 18px;">Encara no tens cap producte registrat</h2>
        <p class="text-muted mt-2">Crea el teu primer producte per començar a vendre directament des de la teva explotació.</p>
        <div class="mt-4">
            <a href="/ramader/productes/crear" class="btn btn-primary btn-sm">Crear el primer producte</a>
        </div>
    </div>
<?php } else { ?>
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producte</th>
                        <th>Raça i format</th>
                        <th>Preu</th>
                        <th>Estoc</th>
                        <th>Lot</th>
                        <th>Estat</th>
                        <th>Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p) {
                        $nomRaca = 'Sense raça';
                        if (isset($p['nom_raca']) && $p['nom_raca'] !== '') {
                            $nomRaca = (string) $p['nom_raca'];
                        }
                        $pesGrams = (int) $p['pes_net_grams'];
                        $preuPack = (float) $p['preu_pack'];
                        $preuKg = 0;
                        if ($pesGrams > 0) {
                            $preuKg = ($preuPack * 1000) / $pesGrams;
                        }
                        $estoc = (int) $p['estoc_unitats'];
                        $isActive = !empty($p['actiu']);
                    ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <?php if (!empty($p['imatge_url'])) { ?>
                                        <img src="<?php echo htmlspecialchars((string) $p['imatge_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="" class="table-thumb">
                                    <?php } else { ?>
                                        <span class="table-thumb-empty">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                        </span>
                                    <?php } ?>
                                    <div>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars((string) $p['nom'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="actions-row mt-2" style="gap: 4px;">
                                            <?php if (!empty($p['certificat_ecologic'])) { ?>
                                                <span class="badge badge-eco">Ecològic</span>
                                            <?php } ?>
                                            <?php if (!empty($p['pastura_lliure'])) { ?>
                                                <span class="badge">Pastura lliure</span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($nomRaca, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars((string) $p['format_envasat'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo $pesGrams; ?> g</div>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?php echo number_format($preuPack, 2, ',', '.'); ?> €</div>
                                <div class="text-muted" style="font-size: 12px;"><?php echo number_format($preuKg, 2, ',', '.'); ?> €/kg</div>
                            </td>
                            <td>
                                <?php if ($estoc <= 0) { ?>
                                    <span class="stock-zero">0 u. (Sense estoc)</span>
                                <?php } else { ?>
                                    <strong><?php echo $estoc; ?> u.</strong>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="badge"><?php echo htmlspecialchars((string) $p['codi_lot_tracabilitat'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td>
                                <form method="POST" action="/ramader/productes/toggle" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $p['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="toggle-btn <?php if ($isActive) { echo 'is-active'; } ?>">
                                        <span class="toggle-dot"></span>
                                        <span><?php if ($isActive) { echo 'Actiu'; } else { echo 'Pausat'; } ?></span>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="actions-row">
                                    <a href="/ramader/productes/editar?id=<?php echo urlencode((string) $p['id']); ?>" class="btn btn-secondary btn-sm">Editar</a>
                                    <form method="POST" action="/ramader/productes/eliminar" class="inline-form" onsubmit="return confirm('Segur que vols eliminar aquest producte?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $p['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>

<?php require __DIR__ . '/../layouts/sidebar_end.php'; ?>
