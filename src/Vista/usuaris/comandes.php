<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="section-header">
    <div>
        <h1 class="section-title">Les meves comandes</h1>
        <p class="section-subtitle">Consulta l'estat del pagament i el seguiment refrigerat de cada granja.</p>
    </div>
    <a href="/perfil" class="btn btn-secondary btn-sm">← Tornar al perfil</a>
</div>

<?php if (empty($orders)) { ?>
    <div class="card">
        <h2 style="font-size: 18px;">Encara no has fet cap comanda</h2>
        <p class="text-muted mt-2">Descobreix els productes de proximitat de les nostres explotacions verificades.</p>
        <div class="mt-4">
            <a href="/productes" class="btn btn-primary btn-sm">Anar al catàleg</a>
        </div>
    </div>
<?php } else { ?>
    <?php foreach ($orders as $ord) {
        $ordId = (string) $ord['id'];
        $estatPag = 'PENDENT';
        if (isset($ord['estat_pagament'])) {
            $estatPag = (string) $ord['estat_pagament'];
        }
        $totalOrd = 0;
        if (isset($ord['import_total'])) {
            $totalOrd = (float) $ord['import_total'];
        }
        $subs = [];
        if (isset($ord['subcomandes']) && is_array($ord['subcomandes'])) {
            $subs = $ord['subcomandes'];
        }
    ?>
        <section class="vendor-group-card">
            <div class="vendor-group-header">
                <div>
                    <strong>Comanda #<?php echo htmlspecialchars(substr($ordId, 0, 8), ENT_QUOTES, 'UTF-8'); ?></strong>
                    <?php if (!empty($ord['adreca'])) { ?>
                        <span class="text-muted" style="font-weight: 400; margin-left: 10px;">
                            Enviament a <?php echo htmlspecialchars((string) $ord['adreca'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars((string) $ord['municipi'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php } ?>
                </div>
                <div class="actions-row">
                    <?php if ($estatPag === 'PAGAT') { ?>
                        <span class="badge badge-success">Pagat</span>
                    <?php } elseif ($estatPag === 'FALLIT') { ?>
                        <span class="badge badge-danger">Pagament fallit</span>
                    <?php } else { ?>
                        <span class="badge badge-warning">Pendent de pagament</span>
                    <?php } ?>
                    <strong><?php echo number_format($totalOrd, 2, ',', '.'); ?> €</strong>
                </div>
            </div>

            <div style="padding: 18px;">
                <?php foreach ($subs as $sub) {
                    $farmName = 'Explotació ramadera';
                    if (isset($sub['nom_explotacio']) && $sub['nom_explotacio'] !== '') {
                        $farmName = (string) $sub['nom_explotacio'];
                    }
                    $estatLog = 'PENDENT';
                    if ($estatPag === 'PAGAT' && isset($sub['estat_expedicio'])) {
                        $estatLog = (string) $sub['estat_expedicio'];
                    } elseif ($estatPag === 'FALLIT') {
                        $estatLog = 'FALLIT';
                    }
                    $tracking = '';
                    if (isset($sub['codi_seguiment_transport']) && $sub['codi_seguiment_transport'] !== null) {
                        $tracking = (string) $sub['codi_seguiment_transport'];
                    }
                    $linies = [];
                    if (isset($sub['linies']) && is_array($sub['linies'])) {
                        $linies = $sub['linies'];
                    }
                ?>
                    <div class="card mb-4" style="padding: 16px; background-color: var(--bg-subtle);">
                        <div class="product-meta-row mb-4">
                            <div>
                                <strong><?php echo htmlspecialchars($farmName, ENT_QUOTES, 'UTF-8'); ?></strong>
                                <?php if (!empty($sub['comarca'])) { ?>
                                    <span class="text-muted" style="font-size: 13px;"> · <?php echo htmlspecialchars((string) $sub['comarca'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php } ?>
                            </div>
                            <div class="actions-row">
                                <?php if ($estatLog === 'LLIURAT') { ?>
                                    <span class="badge badge-success">Lliurat</span>
                                <?php } elseif ($estatLog === 'ENVIAT') { ?>
                                    <span class="badge badge-eco">Enviat</span>
                                <?php } elseif ($estatLog === 'EN_PREPARACIO') { ?>
                                    <span class="badge badge-dark">En preparació</span>
                                <?php } elseif ($estatLog === 'PAGAT') { ?>
                                    <span class="badge badge-warning">Rebut per la granja</span>
                                <?php } else { ?>
                                    <span class="badge"><?php echo htmlspecialchars($estatLog, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php } ?>
                                <?php if ($tracking !== '') { ?>
                                    <span class="badge">Seguiment <?php echo htmlspecialchars($tracking, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php } ?>
                            </div>
                        </div>

                        <?php foreach ($linies as $lin) { ?>
                            <div class="summary-row" style="padding: 4px 0;">
                                <span><?php echo (int) $lin['quantitat']; ?>x <?php echo htmlspecialchars((string) $lin['producte_nom'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <strong><?php echo number_format((float) $lin['subtotal_linia'], 2, ',', '.'); ?> €</strong>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </section>
    <?php } ?>
<?php } ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
