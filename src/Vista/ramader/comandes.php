<?php require __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="section-header">
    <div>
        <h1 class="section-title">Comandes rebudes</h1>
        <p class="section-subtitle">Gestiona la preparació i l'enviament refrigerat de les comandes dels teus clients.</p>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Pendents / En preparació</div>
        <div class="kpi-value"><?php echo (int) $kpiPending; ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Enviades en trànsit</div>
        <div class="kpi-value"><?php echo (int) $kpiShipped; ?></div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Lliurades completades</div>
        <div class="kpi-value"><?php echo (int) $kpiDelivered; ?></div>
    </div>
</div>

<h2 style="font-size: 18px; margin-bottom: 12px;">Comandes actives</h2>

<?php if (empty($pendingOrders)) { ?>
    <div class="card mb-4">
        <p class="text-muted">No tens cap comanda pendent de preparar o enviar.</p>
    </div>
<?php } else { ?>
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Comanda</th>
                        <th>Comprador i adreça</th>
                        <th>Productes</th>
                        <th>Import</th>
                        <th>Estat</th>
                        <th>Gestió logística</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingOrders as $sub) {
                        $subId = (string) $sub['id'];
                        $estat = '';
                        if (isset($sub['estat_expedicio'])) {
                            $estat = (string) $sub['estat_expedicio'];
                        }
                        $comprador = trim((string) $sub['comprador_nom'] . ' ' . (string) $sub['comprador_cognoms']);
                        $subtotalProd = 0;
                        if (isset($sub['subtotal_productes'])) {
                            $subtotalProd = (float) $sub['subtotal_productes'];
                        }
                        $costEnv = 0;
                        if (isset($sub['cost_enviament_individual'])) {
                            $costEnv = (float) $sub['cost_enviament_individual'];
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
                        <tr>
                            <td>
                                <strong>#<?php echo htmlspecialchars(substr($subId, 0, 8), ENT_QUOTES, 'UTF-8'); ?></strong>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($comprador, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars((string) $sub['comprador_email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php if (!empty($sub['adreca'])) { ?>
                                    <div class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars((string) $sub['adreca'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars((string) $sub['codi_postal'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars((string) $sub['municipi'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php } ?>
                            </td>
                            <td>
                                <?php foreach ($linies as $lin) { ?>
                                    <div style="font-size: 13px;">
                                        <strong><?php echo (int) $lin['quantitat']; ?>x</strong> <?php echo htmlspecialchars((string) $lin['producte_nom'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php } ?>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?php echo number_format($subtotalProd + $costEnv, 2, ',', '.'); ?> €</div>
                                <div class="text-muted" style="font-size: 12px;">Env. <?php echo number_format($costEnv, 2, ',', '.'); ?> €</div>
                            </td>
                            <td>
                                <?php if ($estat === 'PAGAT') { ?>
                                    <span class="badge badge-warning">Pagat</span>
                                <?php } elseif ($estat === 'EN_PREPARACIO') { ?>
                                    <span class="badge badge-dark">En preparació</span>
                                <?php } elseif ($estat === 'ENVIAT') { ?>
                                    <span class="badge badge-eco">Enviat</span>
                                    <?php if ($tracking !== '') { ?>
                                        <div class="text-muted mt-2" style="font-size: 12px;">Tracking - <?php echo htmlspecialchars($tracking, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="badge"><?php echo htmlspecialchars($estat, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($estat === 'PAGAT') { ?>
                                    <form method="POST" action="/ramader/comandes/estat">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="id_subcomanda" value="<?php echo htmlspecialchars($subId, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="nou_estat" value="EN_PREPARACIO">
                                        <button type="submit" class="btn btn-primary btn-sm">Començar preparació</button>
                                    </form>
                                <?php } elseif ($estat === 'EN_PREPARACIO') { ?>
                                    <form method="POST" action="/ramader/comandes/estat" class="actions-row">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="id_subcomanda" value="<?php echo htmlspecialchars($subId, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="nou_estat" value="ENVIAT">
                                        <input type="text" name="codi_seguiment_transport" class="form-control" required maxlength="100" placeholder="Codi seguiment" style="width: 150px; padding: 6px 8px; font-size: 12px;">
                                        <button type="submit" class="btn btn-primary btn-sm">Marcar enviat</button>
                                    </form>
                                <?php } elseif ($estat === 'ENVIAT') { ?>
                                    <form method="POST" action="/ramader/comandes/estat">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="id_subcomanda" value="<?php echo htmlspecialchars($subId, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="nou_estat" value="LLIURAT">
                                        <button type="submit" class="btn btn-secondary btn-sm">Marcar lliurat</button>
                                    </form>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>

<h2 style="font-size: 18px; margin-bottom: 12px;">Historial de comandes lliurades</h2>

<?php if (empty($historyOrders)) { ?>
    <div class="card">
        <p class="text-muted">Encara no hi ha comandes lliurades a l'historial.</p>
    </div>
<?php } else { ?>
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Comanda</th>
                        <th>Comprador</th>
                        <th>Productes</th>
                        <th>Import</th>
                        <th>Seguiment</th>
                        <th>Estat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historyOrders as $sub) {
                        $subId = (string) $sub['id'];
                        $comprador = trim((string) $sub['comprador_nom'] . ' ' . (string) $sub['comprador_cognoms']);
                        $subtotalProd = 0;
                        if (isset($sub['subtotal_productes'])) {
                            $subtotalProd = (float) $sub['subtotal_productes'];
                        }
                        $costEnv = 0;
                        if (isset($sub['cost_enviament_individual'])) {
                            $costEnv = (float) $sub['cost_enviament_individual'];
                        }
                        $tracking = '-';
                        if (isset($sub['codi_seguiment_transport']) && $sub['codi_seguiment_transport'] !== '') {
                            $tracking = (string) $sub['codi_seguiment_transport'];
                        }
                        $linies = [];
                        if (isset($sub['linies']) && is_array($sub['linies'])) {
                            $linies = $sub['linies'];
                        }
                    ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars(substr($subId, 0, 8), ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td><?php echo htmlspecialchars($comprador, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php foreach ($linies as $lin) { ?>
                                    <div style="font-size: 13px;"><?php echo (int) $lin['quantitat']; ?>x <?php echo htmlspecialchars((string) $lin['producte_nom'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php } ?>
                            </td>
                            <td><strong><?php echo number_format($subtotalProd + $costEnv, 2, ',', '.'); ?> €</strong></td>
                            <td><?php echo htmlspecialchars($tracking, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="badge badge-success">Lliurat</span></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
<?php } ?>

<?php require __DIR__ . '/../layouts/sidebar_end.php'; ?>
