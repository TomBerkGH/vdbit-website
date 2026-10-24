<?php
/**
 * Verwacht: $pageTitle, $pageDescription en optioneel $isHome, $noIndex en $trackPageview (bool).
 */
$isHome ??= false;
$noIndex ??= false;

if ($trackPageview ?? true) {
    track_pageview();
}

$base = $isHome ? '' : './';
$canonical = SITE['url'] . '/' . ($isHome ? '' : basename($_SERVER['SCRIPT_NAME']));
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="color-scheme" content="light dark">
    <?php if ($noIndex): ?><meta name="robots" content="noindex, nofollow"><?php endif ?>
    <link rel="canonical" href="<?= e($canonical) ?>">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="nl_NL">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e(SITE['url']) ?>/assets/img/logo-vdb-it.webp">

    <link rel="icon" href="<?= asset('assets/img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="preload" href="assets/fonts/inter-var.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">

    <script>
        // Thema zetten vóór het tekenen, zodat de pagina niet knippert.
        (function () {
            var theme = null;
            try { theme = localStorage.getItem('theme'); } catch (e) {}
            if (theme !== 'light' && theme !== 'dark') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.dataset.theme = theme;
        })();
    </script>
    <script src="<?= asset('assets/js/main.js') ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Naar de inhoud</a>

<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="./" aria-label="<?= e(SITE['name']) ?> - naar de homepage">
            <img class="logo logo-light" src="assets/img/logo-vdb-it.webp" alt="<?= e(SITE['name']) ?>" width="643" height="315">
            <img class="logo logo-dark" src="assets/img/logo-vdb-it-dark.webp" alt="" width="643" height="315">
        </a>

        <nav class="nav" id="nav" aria-label="Hoofdmenu">
            <a href="<?= $base ?>#diensten">Diensten</a>
            <a href="<?= $base ?>#werkwijze">Werkwijze</a>
            <a href="<?= $base ?>#cases">Cases</a>
            <a href="<?= $base ?>#over-mij">Over mij</a>
            <a class="btn btn-small" href="<?= $base ?>#contact">Contact</a>
        </nav>

        <div class="header-actions">
            <a class="icon-btn icon-btn-github" href="<?= e(SITE['github']) ?>" target="_blank" rel="noopener" aria-label="Broncode van deze website op GitHub" title="Broncode op GitHub">
                <?= github_icon() ?>
            </a>
            <button class="icon-btn theme-toggle" type="button" aria-label="Wissel tussen licht en donker thema">
                <svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4.5"/><path d="M12 1.5v3M12 19.5v3M1.5 12h3M19.5 12h3M4.6 4.6l2.1 2.1M17.3 17.3l2.1 2.1M4.6 19.4l2.1-2.1M17.3 6.7l2.1-2.1"/></svg>
                <svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.5A8.5 8.5 0 0 1 9.5 3.5a8.5 8.5 0 1 0 11 11z"/></svg>
            </button>
            <button class="icon-btn nav-toggle" type="button" aria-label="Menu openen" aria-expanded="false" aria-controls="nav">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
            </button>
        </div>
    </div>
</header>

<main id="main">
