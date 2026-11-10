<?php /** @var ?string $error @var string $csrf */ ?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sessió - Directe del Camp</title>
</head>
<body>
<h1>Iniciar sessió</h1>

<?php if (!empty($error)): ?>
    <p><strong><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></strong></p>
<?php endif; ?>

<form method="post" action="/login">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

    <p><label>Email<br><input type="email" name="email" required autocomplete="email"></label></p>
    <p><label>Contrasenya<br><input type="password" name="password" required autocomplete="current-password"></label></p>

    <p><button type="submit">Entrar</button></p>
</form>

<p><a href="/auth/google">Identifica't amb Google</a></p>
<p>No tens compte? <a href="/register">Registra't</a></p>
</body>
</html>
