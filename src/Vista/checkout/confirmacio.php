<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-wrapper-wide">
    <div class="card" style="text-align: center; padding: 40px 28px;">
        <div style="width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 999px; background-color: var(--success-bg); color: var(--success-text); display: flex; align-items: center; justify-content: center;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>

        <h1 class="section-title">Gràcies per la teva comanda</h1>
        <p class="section-subtitle mt-2">Hem rebut la teva sol·licitud de pagament i les granges començaran a preparar la carn tan bon punt es confirmi l'operació.</p>

        <?php if ($order !== null) { ?>
            <div class="traceability-card" style="text-align: left; max-width: 480px; margin: 24px auto;">
                <div class="summary-row">
                    <span>Referència de comanda</span>
                    <strong>#<?php echo htmlspecialchars(substr((string) $order['id'], 0, 8), ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Import total</span>
                    <strong><?php echo number_format((float) $order['import_total'], 2, ',', '.'); ?> €</strong>
                </div>
                <div class="summary-row">
                    <span>Estat del pagament</span>
                    <span class="badge badge-eco"><?php echo htmlspecialchars((string) $order['estat_pagament'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
        <?php } ?>

        <div class="actions-row mt-4" style="justify-content: center;">
            <a href="/perfil/comandes" class="btn btn-primary">Veure les meves comandes</a>
            <a href="/productes" class="btn btn-secondary">Tornar al catàleg</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
