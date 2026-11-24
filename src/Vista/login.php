<?php
$pageTitle = 'Iniciar sessió';
require __DIR__ . '/layouts/header.php';
?>

<div class="auth-wrapper">
    <div class="card">
        <h1 class="section-title">Iniciar sessió</h1>
        <p class="section-subtitle mb-4">Accedeix al teu compte de Directe del Camp.</p>

        <?php if (!empty($error)) { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>

        <form method="POST" action="/login">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label for="login_email" class="form-label">Email</label>
                <input id="login_email" type="email" name="email" class="form-control" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="login_pass" class="form-label">Contrasenya</label>
                <input id="login_pass" type="password" name="password" class="form-control" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>

        <div class="divider"></div>
        <a href="/auth/google" class="btn btn-secondary btn-block">Identifica't amb Google</a>

        <div class="divider"></div>
        <p class="text-muted" style="font-size: 14px;">No tens compte? <a href="/register" style="text-decoration: underline; font-weight: 600;">Registra't</a></p>
        <p class="mt-2 text-muted" style="font-size: 14px;">Ets ramader o productor? <a href="/register-ramader" style="text-decoration: underline;">Registra la teva explotació aquí</a></p>
    </div>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
