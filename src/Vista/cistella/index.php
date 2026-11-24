<?php
require __DIR__ . '/../layouts/header.php';

$groups = [];
if (isset($summary['groups']) && is_array($summary['groups'])) {
    $groups = $summary['groups'];
}
?>

<div class="section-header">
    <div>
        <h1 class="section-title">La meva cistella</h1>
        <p class="section-subtitle">Enviament directe refrigerat (8,00 € per cada granja productora).</p>
    </div>
    <a href="/productes" class="btn btn-secondary btn-sm">← Seguir comprant</a>
</div>

<?php if (empty($groups)) { ?>
    <div class="card">
        <h2 style="font-size: 18px;">La teva cistella està buida</h2>
        <p class="text-muted mt-2">Afegeix productes de les nostres granges per preparar la teva comanda en fred.</p>
        <div class="mt-4">
            <a href="/productes" class="btn btn-primary">Explorar el catàleg</a>
        </div>
    </div>
<?php } else { ?>
    <div class="checkout-grid">
        <div>
            <?php foreach ($groups as $grp) { ?>
                <section class="vendor-group-card">
                    <div class="vendor-group-header">
                        <div>
                            <span><?php echo htmlspecialchars((string) $grp['nom_explotacio'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="text-muted" style="font-weight: 400;"> · <?php echo htmlspecialchars((string) $grp['comarca'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <span class="badge badge-eco">Enviament fred 8,00 €</span>
                    </div>

                    <?php foreach ($grp['items'] as $item) {
                        $prod = $item['product'];
                        $pid = (string) $item['id_producte'];
                        $qty = (int) $item['quantitat'];
                        $estocMax = (int) $prod['estoc_unitats'];
                    ?>
                        <div class="cart-item-row">
                            <div style="display: flex; align-items: center; gap: 14px; flex: 1;">
                                <?php if (!empty($prod['imatge_url'])) { ?>
                                    <img src="<?php echo htmlspecialchars((string) $prod['imatge_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="" class="table-thumb">
                                <?php } else { ?>
                                    <span class="table-thumb-empty">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    </span>
                                <?php } ?>
                                <div>
                                    <a href="/producte?id=<?php echo urlencode($pid); ?>" style="font-weight: 600;">
                                        <?php echo htmlspecialchars((string) $prod['nom'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                    <div class="text-muted" style="font-size: 13px;">
                                        <?php echo htmlspecialchars((string) $prod['format_envasat'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo number_format((float) $item['preu_unitari'], 2, ',', '.'); ?> €/pack
                                    </div>
                                </div>
                            </div>

                            <div class="actions-row">
                                <form method="POST" action="/cistella/actualitzar" class="actions-row">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id_producte" value="<?php echo htmlspecialchars($pid, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="number" name="quantitat" class="form-control" min="1" max="<?php echo $estocMax; ?>" value="<?php echo $qty; ?>" style="width: 72px; padding: 6px 8px;">
                                    <button type="submit" class="btn btn-secondary btn-sm">Actualitzar</button>
                                </form>

                                <div style="min-width: 75px; text-align: right; font-weight: 700;">
                                    <?php echo number_format((float) $item['subtotal_linia'], 2, ',', '.'); ?> €
                                </div>

                                <form method="POST" action="/cistella/eliminar">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id_producte" value="<?php echo htmlspecialchars($pid, ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Treure</button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                </section>
            <?php } ?>
        </div>

        <aside class="card">
            <h2 class="card-title">Resum de la cistella</h2>

            <div class="summary-row">
                <span>Subtotal productes (IVA 10% inclòs)</span>
                <strong><?php echo number_format((float) $summary['subtotal_productes'], 2, ',', '.'); ?> €</strong>
            </div>
            <div class="summary-row">
                <span>Transport refrigerat (<?php echo count($groups); ?> granges × 8,00 €)</span>
                <strong><?php echo number_format((float) $summary['cost_enviament_total'], 2, ',', '.'); ?> €</strong>
            </div>

            <div class="divider"></div>

            <div class="summary-row" style="font-size: 13px; color: var(--text-muted);">
                <span>Base imposable productes</span>
                <span><?php echo number_format((float) $summary['base_productes'], 2, ',', '.'); ?> €</span>
            </div>
            <div class="summary-row" style="font-size: 13px; color: var(--text-muted);">
                <span>IVA productes (10%)</span>
                <span><?php echo number_format((float) $summary['iva_productes'], 2, ',', '.'); ?> €</span>
            </div>
            <div class="summary-row" style="font-size: 13px; color: var(--text-muted);">
                <span>Base imposable transport</span>
                <span><?php echo number_format((float) $summary['base_enviament'], 2, ',', '.'); ?> €</span>
            </div>
            <div class="summary-row" style="font-size: 13px; color: var(--text-muted);">
                <span>IVA transport (21%)</span>
                <span><?php echo number_format((float) $summary['iva_enviament'], 2, ',', '.'); ?> €</span>
            </div>

            <div class="summary-total">
                <span>Total</span>
                <span><?php echo number_format((float) $summary['import_total'], 2, ',', '.'); ?> €</span>
            </div>

            <div class="mt-4">
                <?php if (!empty($currentUser)) { ?>
                    <a href="/checkout" class="btn btn-primary btn-block">Tramitar comanda</a>
                <?php } else { ?>
                    <a href="/login" class="btn btn-primary btn-block">Inicia sessió per comprar</a>
                    <p class="form-help" style="text-align: center;">Has d'identificar-te per seleccionar l'adreça i fer el pagament.</p>
                <?php } ?>
            </div>
        </aside>
    </div>
<?php } ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
