<?php
$pageTitleText = 'Panell de gestió - Directe del Camp';
if (isset($pageTitle) && $pageTitle !== '') {
    $pageTitleText = $pageTitle . ' - Directe del Camp';
}

$breadcrumbTitle = 'Panell';
if (isset($pageTitle) && $pageTitle !== '') {
    $breadcrumbTitle = $pageTitle;
}

$activeNav = '';
if (isset($sidebarActive)) {
    $activeNav = (string) $sidebarActive;
}

$sidebarRole = '';
$sidebarUserName = 'Usuari';
if (isset($currentUser['rol'])) {
    $sidebarRole = (string) $currentUser['rol'];
}
if (isset($currentUser['nom'])) {
    $sidebarUserName = (string) $currentUser['nom'];
}
$userInitial = strtoupper(mb_substr($sidebarUserName, 0, 1));
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitleText, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@700&display=swap">
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="body-dashboard">
<div class="dashboard-shell">
    <aside class="dashboard-sidebar">
        <a href="/" class="sidebar-brand">
            <span class="brand-mark" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"></path>
                    <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path>
                </svg>
            </span>
            <span class="brand-text">
                <span class="brand-title">Directe del Camp</span>
                <span class="brand-subtitle"><?php if ($sidebarRole === 'ADMIN') { echo 'Administració'; } else { echo 'Espai Ramader'; } ?></span>
            </span>
        </a>

        <nav class="sidebar-nav">
            <?php if ($sidebarRole === 'RAMADER') { ?>
                <a href="/ramader/productes" class="sidebar-link <?php if ($activeNav === 'productes') { echo 'active'; } ?>">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                    Els meus productes
                </a>
                <a href="/ramader/productes/crear" class="sidebar-link <?php if ($activeNav === 'crear_producte') { echo 'active'; } ?>">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Nou producte
                </a>
                <a href="/ramader/comandes" class="sidebar-link <?php if ($activeNav === 'comandes') { echo 'active'; } ?>">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line></svg>
                    Comandes rebudes
                </a>
            <?php } elseif ($sidebarRole === 'ADMIN') { ?>
                <a href="/admin/ramaders" class="sidebar-link <?php if ($activeNav === 'ramaders') { echo 'active'; } ?>">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Validació de ramaders
                </a>
            <?php } ?>
            <a href="/perfil" class="sidebar-link <?php if ($activeNav === 'perfil') { echo 'active'; } ?>">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                El meu perfil
            </a>
            <a href="/" class="sidebar-link">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                Veure botiga pública
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-mini">
                <span class="avatar-circle"><?php echo htmlspecialchars($userInitial, ENT_QUOTES, 'UTF-8'); ?></span>
                <div>
                    <div style="font-size: 13px; font-weight: 600;"><?php echo htmlspecialchars($sidebarUserName, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($sidebarRole, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>
            <a href="/logout" class="btn btn-secondary btn-sm btn-block">Tancar sessió</a>
        </div>
    </aside>

    <div class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="breadcrumbs">
                <a href="/">Directe del Camp</a>
                <span>/</span>
                <strong><?php echo htmlspecialchars($breadcrumbTitle, ENT_QUOTES, 'UTF-8'); ?></strong>
            </div>
            <div class="actions-row">
                <?php if ($sidebarRole === 'RAMADER') { ?>
                    <a href="/ramader/productes/crear" class="btn btn-primary btn-sm">+ Nou producte</a>
                <?php } ?>
                <a href="/productes" class="btn btn-secondary btn-sm">Catàleg públic</a>
            </div>
        </header>

        <main class="dashboard-content">
            <?php if (!empty($flash_success)) { ?>
                <div class="alert alert-success"><?php echo htmlspecialchars((string) $flash_success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php } ?>
            <?php if (!empty($flash_error)) { ?>
                <div class="alert alert-error"><?php echo htmlspecialchars((string) $flash_error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php } ?>
