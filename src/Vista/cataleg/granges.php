<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="section-header">
    <div>
        <h1 class="section-title">Les nostres granges</h1>
        <p class="section-subtitle">Totes les explotacions han passat la verificació sanitària i disposen de codi REGA actiu.</p>
    </div>
    <a href="/register-ramader" class="btn btn-primary btn-sm">Ets ramader? Registra't</a>
</div>

<?php if (!empty($comarques)) { ?>
    <div class="comarques-grid mb-4">
        <?php foreach ($comarques as $com) { ?>
            <a href="/productes?comarca=<?php echo urlencode((string) $com); ?>" class="comarca-chip">
                <?php echo htmlspecialchars((string) $com, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php } ?>
    </div>
<?php } ?>

<?php if (empty($farms)) { ?>
    <div class="card">
        <p class="text-muted">Encara no hi ha granges validades per mostrar.</p>
    </div>
<?php } else { ?>
    <div class="farm-card-grid">
        <?php foreach ($farms as $farm) {
            $titular = trim((string) $farm['nom_titular'] . ' ' . (string) $farm['cognoms_titular']);
        ?>
            <article class="farm-card">
                <div class="product-meta-row">
                    <span class="badge badge-eco">REGA verificat</span>
                    <span class="badge"><?php echo htmlspecialchars((string) $farm['comarca'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <h2 style="font-size: 20px;"><?php echo htmlspecialchars((string) $farm['nom_explotacio'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="text-muted" style="font-size: 13px;">Titular <?php echo htmlspecialchars($titular, ENT_QUOTES, 'UTF-8'); ?></p>
                <p style="font-size: 13px;">Codi REGA <strong><?php echo htmlspecialchars((string) $farm['codi_rega'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                <p class="text-muted" style="font-size: 13px;"><?php echo (int) $farm['total_productes']; ?> productes en venda</p>
                <div class="mt-2">
                    <a href="/productes?id_ramader=<?php echo urlencode((string) $farm['id']); ?>" class="btn btn-primary btn-sm btn-block">Veure productes de la granja</a>
                </div>
            </article>
        <?php } ?>
    </div>
<?php } ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
