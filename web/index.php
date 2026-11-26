<?php
/**
 * Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 * This file is part of GunGame AMXX 3++ - pur3gaming Edition.
 *
 * See LICENSE for the full license text.
 */

/** Central portal entry point and allowlisted page routing. */
declare(strict_types=1);
define('GUNGAME_WEB', true);
require_once __DIR__ . '/includes/routing.php';
require __DIR__ . '/includes/bootstrap.php';
// Dynamic HTML must be revalidated after deployment and language changes.
header('Cache-Control: private, no-cache, must-revalidate');
$routes = ['overview'=>'navigation.overview', 'servers'=>'navigation.servers', 'players'=>'navigation.players', 'leaderboard'=>'ranking.title', 'top10'=>'navigation.top10', 'matches'=>'navigation.matches', 'credits'=>'navigation.credits', 'player'=>'player.profile', 'server'=>'server.details', 'match'=>'match.details'];
$route = $_GET['view'] ?? (isset($_GET['player']) ? 'player' : 'overview');
if (!is_string($route) || !isset($routes[$route])) { http_response_code(404); $route = 'not_found'; }
require __DIR__ . '/includes/statistics.php';
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
    <head>
        <!-- Document settings -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="color-scheme" content="dark">
        <meta name="theme-color" content="#0b111b">

        <!-- Search engine optimization -->
        <title><?= h($c['title'] ?? 'GunGame AMXX 3++ | Rankings & Live Server Statistics') ?></title>
        <meta name="description" content="<?= translate('meta.description') ?>">
        <meta name="author" content="JamesDaRock">
        <meta name="application-name" content="GunGame AMXX 3++">
        <meta name="generator" content="GunGame AMXX 3++ Statistics">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

        <?php
        $configuredUrl = $c['site_url'] ?? '';
        $baseUrl = is_string($configuredUrl) && filter_var($configuredUrl, FILTER_VALIDATE_URL)
            && in_array(parse_url($configuredUrl, PHP_URL_SCHEME), ['http', 'https'], true)
            && !parse_url($configuredUrl, PHP_URL_QUERY) && !parse_url($configuredUrl, PHP_URL_FRAGMENT)
            && parse_url($configuredUrl, PHP_URL_HOST) !== 'example.tld'
            ? rtrim($configuredUrl, '/') . '/' : '';
        $canonicalParams = ['view'=>$route];
        if ($route === 'player') $canonicalParams += ['player'=>$auth,'server'=>$server];
        if ($route === 'server') $canonicalParams += ['server'=>$server];
        if ($route === 'match') $canonicalParams += ['server'=>$server,'ts'=>$_GET['ts'] ?? ''];
        $pageUrl = $baseUrl . substr(portal_url($canonicalParams), strlen(portal_base()));
        ?>
        <!-- Canonical URL -->
        <?php if ($baseUrl !== ""): ?>
        <link rel="canonical" href="<?= h($pageUrl) ?>">
        <?php endif; ?>

        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="GunGame AMXX 3++">
        <meta property="og:title" content="<?= h($c['title'] ?? 'GunGame AMXX 3++ | Rankings & Live Server Statistics') ?>">
        <meta property="og:description" content="<?= translate('meta.description') ?>">
        <?php if ($baseUrl !== ""): ?>
        <meta property="og:url" content="<?= h($pageUrl) ?>">
        <?php endif; ?>
        <?php if ($baseUrl !== ""): ?>
        <meta property="og:image" content="<?= h($baseUrl . ltrim(substr(asset_url('assets/images/gungame-3_social-preview-1774x887.png'), strlen(portal_base())), '/')) ?>">
        <?php endif; ?>
        <?php if ($baseUrl !== ""): ?>
        <meta property="og:image:width" content="1200">
        <?php endif; ?>
        <?php if ($baseUrl !== ""): ?>
        <meta property="og:image:height" content="630">
        <?php endif; ?>
        <?php if ($baseUrl !== ""): ?>
        <meta property="og:image:alt" content="GunGame AMXX 3++ Rankings and Live Server Statistics">
        <?php endif; ?>

        <!-- Twitter / X -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?= h($c['title'] ?? 'GunGame AMXX 3++ | Rankings & Live Server Statistics') ?>">
        <meta name="twitter:description" content="<?= translate('meta.description') ?>">
        <?php if ($baseUrl !== ""): ?>
        <meta name="twitter:image" content="<?= h($baseUrl . ltrim(substr(asset_url('assets/images/gungame-3_social-preview-1774x887.png'), strlen(portal_base())), '/')) ?>">
        <?php endif; ?>


        <!-- Favicons -->
        <link rel="icon" href="<?= h(asset_url('assets/images/favicon.svg')) ?>" type="image/svg+xml">

        <!-- Stylesheets -->
        <link rel="stylesheet" href="<?= h(asset_url('assets/style.css')) ?>">
        <link rel="stylesheet" href="<?= h(asset_url('assets/icons.css')) ?>">
    <link rel="stylesheet" href="<?= h(asset_url('assets/portal.css')) ?>">
    </head>
<body data-route-defaults="<?= h(json_encode(portal_filters(), JSON_THROW_ON_ERROR)) ?>" class="portal<?= $themeEnabled ? ' portal-themed' : '' ?>"<?php if ($themeEnabled): ?> style="background-image:linear-gradient(rgba(5,12,22,<?= h($themeOverlay) ?>),rgba(5,12,22,<?= h($themeOverlay) ?>)),url('<?= h(asset_url($themeImage)) ?>');--theme-panel-opacity:<?= h($themeOpacity) ?>"<?php endif; ?>>
<!-- Sticky portal navigation -->
<header class="portal-header" id="portal-header">
    <div class="portal-header-inner">
        <a class="brand" href="<?= h(url(['view'=>'overview','player'=>null,'p'=>1,'q'=>null])) ?>">
            <span class="brandmark"><img src="<?= h(asset_url($logo ?: 'assets/images/logo.svg')) ?>" alt="GunGame AMXX"></span>
            <span class="accent"><?= translate('statistics') ?></span>
        </a>
        <nav class="portal-navigation" aria-label="<?= translate('navigation.label') ?>">
            <?php foreach (array_slice($routes, 0, 6, true) as $name => $label): ?>
                <?php if ($name === 'servers' && !$liveEnabled) continue; ?>
                <a href="<?= h(url(['view'=>$name,'sort'=>$route===$name?$sort:(portal_filters()[$name]['sort']??null),'p'=>1,'q'=>null,'player'=>null,'server'=>$route===$name?$server:null,'mode'=>$route===$name?$mode:(portal_filters()[$name]['mode']??null)])) ?>" <?= ($route === $name || ($route === 'player' && $name === 'players') || ($route === 'server' && $name === 'servers') || ($route === 'match' && $name === 'matches')) ? 'aria-current="page"' : '' ?>><?= translate($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <form class="portal-language" method="get" action="<?= h(strtok(portal_url(['view'=>$route,'player'=>$route==='player'?$auth:null,'server'=>in_array($route,['player','server','match'],true)?$server:null,'ts'=>$route==='match'?($_GET['ts']??null):null]), '?')) ?>">
            <?php foreach (portal_clean(array_merge(['view'=>$route,'player'=>$auth,'server'=>$server,'mode'=>$mode,'sort'=>$sort,'q'=>$q,'p'=>$page], array_merge(in_array($route,['leaderboard','players'],true)?['per_page'=>$leaderLimit]:[], array_merge($matchLanguageParams ?? [], array_intersect_key($_GET, array_flip(['return_view','return_server','return_mode','return_page','return_limit','return_sort','return_q'])))))) as $key=>$value): ?>
                <?php if (!portal_rewrite() || !in_array($key, array_merge(['view'], $route==='player'?['player']:($route==='server'?['server']:($route==='match'?['server','ts']:[]))), true)): ?><input type="hidden" name="<?= h($key) ?>" value="<?= h($value) ?>"><?php endif; ?>
            <?php endforeach; ?>
            <label for="portal-language"><i class="bi bi-globe" aria-hidden="true"></i><span class="visually-hidden"><?= translate('language.label') ?></span></label>
            <select id="portal-language" name="lang">
                <option value="auto" <?= $languagePreference === 'auto' ? 'selected' : '' ?>><?= translate('language.auto') ?> (<?= h(strtoupper($lang)) ?>)</option>
                <?php foreach ($languageNames as $code=>$name): ?>
                    <option value="<?= h($code) ?>" <?= $languagePreference === $code ? 'selected' : '' ?>><?= h($name) ?></option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit"><?= translate('filter.apply') ?></button></noscript>
        </form>
    </div>
</header>
<main class="portal-main" id="portal-content">
<?php if ($route !== 'overview'): ?><h1 class="portal-page-title<?= $route==='top10'?' hall-page-title':'' ?>"><?= translate($routes[$route] ?? 'navigation.not_found') ?></h1><?php endif; ?>
<?php if ($route === 'not_found'): ?><p class="alert"><?= translate('navigation.not_found') ?></p><?php elseif ($err): ?><p class="alert"><?= $err ?></p><?php else: require __DIR__ . '/pages/' . $route . '.php'; endif; ?>
<footer class="portal-footer">
    <div class="footer-project">GunGame AMXX 3++</div>
    <div>Originally developed by Avalanche</div>
    <div>Maintained by JamesDaRock</div>
    <div class="footer-links"><a href="<?= h(url(['view'=>'credits','player'=>null,'mode'=>null,'sort'=>null,'p'=>null,'q'=>null,'server'=>null])) ?>">Credits</a> · <a href="https://github.com/JamesDaRock/GunGame-AMXX">GitHub</a></div>
</footer>
</main>
<button class="back-to-top" id="back-to-top" type="button" hidden aria-label="<?= translate('navigation.back_to_top') ?>" title="<?= translate('navigation.back_to_top') ?>">↑</button>
<script src="<?= h(asset_url('assets/js/portal.js')) ?>" defer></script>
</body></html>
