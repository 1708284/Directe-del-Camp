<?php
$pageTitleText = 'Directe del Camp';
if (isset($pageTitle) && $pageTitle !== '') {
    $pageTitleText = $pageTitle . ' - Directe del Camp';
}

$searchQuery = '';
if (isset($_GET['q'])) {
    $searchQuery = trim((string) $_GET['q']);
}

$activeCategorySlug = '';
if (isset($_GET['categoria'])) {
    $activeCategorySlug = strtolower(trim((string) $_GET['categoria']));
}

$currentPath = '/';
if (isset($_SERVER['REQUEST_URI'])) {
    $parsedPath = parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($parsedPath) {
        $currentPath = $parsedPath;
    }
}

$headerCartCount = 0;
if (isset($cartCount)) {
    $headerCartCount = (int) $cartCount;
}
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
<body>
<header class="site-header">
    <div class="container header-top">
        <a href="/" class="brand-link">
            <span class="brand-mark" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"></path>
                    <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path>
                </svg>
            </span>
            <span class="brand-text">
                <span class="brand-title">Directe del Camp</span>
                <span class="brand-subtitle">Del nostre camp, a casa teva.</span>
            </span>
        </a>

        <form class="header-search" method="GET" action="/productes">
            <input type="text" name="q" placeholder="Cerca carn, raça, granja o comarca..." value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" aria-label="Cercar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </form>

        <div class="header-actions">
            <span class="shipping-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
                Enviament en fred 24-48h
            </span>

            <a href="/cistella" class="cart-link" aria-label="Cistella">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span class="cart-badge"><?php echo $headerCartCount; ?></span>
            </a>

            <?php if (!empty($currentUser)) { ?>
                <?php if ($currentUser['rol'] === 'RAMADER') { ?>
                    <a href="/ramader/productes" class="btn btn-secondary btn-sm">Panell Ramader</a>
                <?php } elseif ($currentUser['rol'] === 'ADMIN') { ?>
                    <a href="/admin/ramaders" class="btn btn-secondary btn-sm">Panell Admin</a>
                <?php } ?>
                <a href="/perfil" class="btn btn-primary btn-sm"><?php echo htmlspecialchars((string) $currentUser['nom'], ENT_QUOTES, 'UTF-8'); ?></a>
                <a href="/logout" class="btn btn-secondary btn-sm">Sortir</a>
            <?php } else { ?>
                <a href="/login" class="btn btn-primary btn-sm">Iniciar sessió</a>
                <a href="/register" class="btn btn-secondary btn-sm">Registrar-se</a>
            <?php } ?>
        </div>
    </div>

    <div class="header-nav-bar">
        <div class="container header-subnav">
            <nav class="subnav-links" aria-label="Categories">
                <a href="/productes" class="subnav-link <?php if ($currentPath === '/productes' && $activeCategorySlug === '') { echo 'active'; } ?>">Tots els productes</a>
                <a href="/productes?categoria=vedella" class="subnav-link <?php if ($activeCategorySlug === 'vedella') { echo 'active'; } ?>">Vedella</a>
                <a href="/productes?categoria=porc" class="subnav-link <?php if ($activeCategorySlug === 'porc') { echo 'active'; } ?>">Porc</a>
                <a href="/productes?categoria=xai" class="subnav-link <?php if ($activeCategorySlug === 'xai') { echo 'active'; } ?>">Xai</a>
                <a href="/productes?categoria=aviram" class="subnav-link <?php if ($activeCategorySlug === 'aviram') { echo 'active'; } ?>">Aviram</a>
                <a href="/granges" class="subnav-link <?php if ($currentPath === '/granges') { echo 'active'; } ?>">Les nostres granges</a>
            </nav>
            <span class="subnav-note">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"></path>
                </svg>
                Del camp a casa, sempre en fred
            </span>
        </div>
    </div>
</header>
<main class="main-content">
    <div class="container">
        <?php if (!empty($flash_success)) { ?>
            <div class="alert alert-success"><?php echo htmlspecialchars((string) $flash_success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>
        <?php if (!empty($flash_error)) { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars((string) $flash_error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>
