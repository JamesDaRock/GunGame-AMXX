<?php
/**
 * GunGame AMXX 3++ — Credits and third-party acknowledgements.
 * Standalone informational page; no database or game-server queries.
 */
declare(strict_types=1);
header('Content-Type: text/html; charset=UTF-8');

// Load language preferences without requiring a database connection.
$config = is_file(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : [];
$langs = [];
foreach (glob(__DIR__ . '/languages/*.php') as $file) {
    $code = basename($file, '.php');
    if (preg_match('/^[a-z]{2}$/D', $code)) {
        $langs[$code] = $file;
    }
}
$lang = $_GET['lang'] ?? ($config['dashboard']['language'] ?? 'en');
if (!is_string($lang) || !isset($langs[$lang])) {
    $lang = 'en';
}
$translations = array_replace(require $langs['en'], require $langs[$lang]);
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function tr(string $key): string {
    global $translations;
    return h($translations[$key] ?? $key);
}
// Preserve the original contributor lists from the GunGame source header.
$groups = [
    'credits_contributors' => '3volution, raa, BAILOPAN, VEN, SeNz0r',
    'credits_supporters' => '3volution, aligind4h0us3, arkshine, bmp, Curryking, Gunny, IdiotSavant, Mordekay, polakpolak, raa, Silver Dragon, Smileypt, Tomek Kalkowski, ToT | V!PER, Vm|Mayhem',
    'credits_translators' => 'arkshine, b!orn, commonbullet, Curryking, Deviance, D o o m, eD., e-N-z, Fr3ak0ut, godlike, harbu, iggy_bus, jopmako, jozzz, KylixMynxAltoLAG, MeRcyLeZZ, Min2liz, Mordekay, Morpheus759, rpm, SAMURAI16, Simbad, TEG, ToT | V!PER, trawiator, Twilight Suzuka, webpsiho',
];
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
        <title><?= h($config['credits_title'] ?? 'GunGame AMXX 3++ | Credits & Acknowledgements') ?></title>
        <meta name="description" content="<?= tr('credits_intro') ?>">
        <meta name="author" content="JamesDaRock">
        <meta name="application-name" content="GunGame AMXX 3++">
        <meta name="generator" content="GunGame AMXX 3++ Statistics">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

        <?php
        $configuredUrl = $config['site_url'] ?? '';
        $baseUrl = is_string($configuredUrl) && filter_var($configuredUrl, FILTER_VALIDATE_URL)
            && in_array(parse_url($configuredUrl, PHP_URL_SCHEME), ['http', 'https'], true)
            && !parse_url($configuredUrl, PHP_URL_QUERY) && !parse_url($configuredUrl, PHP_URL_FRAGMENT)
            && parse_url($configuredUrl, PHP_URL_HOST) !== 'example.tld'
            ? rtrim($configuredUrl, '/') . '/' : '';
        $pageUrl = $baseUrl . 'credits.php';
        ?>
        <!-- Canonical URL -->
        <?php if ($baseUrl !== ""): ?>
        <link rel="canonical" href="<?= h($pageUrl) ?>">
        <?php endif; ?>

        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="GunGame AMXX 3++">
        <meta property="og:title" content="<?= h($config['credits_title'] ?? 'GunGame AMXX 3++ | Credits & Acknowledgements') ?>">
        <meta property="og:description" content="<?= tr('credits_intro') ?>">
        <?php if ($baseUrl !== ""): ?>
        <meta property="og:url" content="<?= h($pageUrl) ?>">
        <?php endif; ?>
        <?php if ($baseUrl !== ""): ?>
        <meta property="og:image" content="<?= h($baseUrl . "assets/images/social-preview.png") ?>">
        <?php endif; ?>
        <?php if ($baseUrl !== ""): ?>
        <meta property="og:image:width" content="1200">
        <?php endif; ?>
        <?php if ($baseUrl !== ""): ?>
        <meta property="og:image:height" content="630">
        <?php endif; ?>
        <?php if ($baseUrl !== ""): ?>
        <meta property="og:image:alt" content="GunGame AMXX 3++ | Credits & Acknowledgements">
        <?php endif; ?>

        <!-- Twitter / X -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?= h($config['credits_title'] ?? 'GunGame AMXX 3++ | Credits & Acknowledgements') ?>">
        <meta name="twitter:description" content="<?= tr('credits_intro') ?>">
        <?php if ($baseUrl !== ""): ?>
        <meta name="twitter:image" content="<?= h($baseUrl . "assets/images/social-preview.png") ?>">
        <?php endif; ?>


        <!-- Favicons -->
        <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">

        <!-- Stylesheets -->
        <link rel="stylesheet" href="assets/style.css?v=8">
        <link rel="stylesheet" href="assets/icons.css">
    </head>

<body>
<main class="wrap">
    <header>
        <div class="brandbar">
            <a class="brand" href="index.php?lang=<?= h($lang) ?>">
                <span class="brandmark"><img src="assets/images/logo.svg" alt="GunGame AMXX"></span>
                <span class="brand-title"><?= tr('statistics') ?></span>
            </a>
            <nav aria-label="<?= tr('t3') ?>">
                <a href="index.php?lang=<?= h($lang) ?>"><?= tr('credits_back') ?></a>
                <span class="language-switch">
                    <?php foreach ($langs as $code => $file): ?>
                        <a href="?lang=<?= h($code) ?>" <?= $code === $lang ? 'aria-current="true"' : '' ?>><?= h(strtoupper($code)) ?></a>
                    <?php endforeach; ?>
                </span>
            </nav>
        </div>
    </header>
    <div class="section-title overview-title"><h1><?= tr('credits_title') ?></h1></div>
    <section class="panel credits-feature">
        <span class="eyebrow">GUNGAME AMXX 3++</span>
        <h2><?= tr('credits_intro') ?></h2>
        <p><?= tr('credits_project') ?></p>
        <a href="https://github.com/JamesDaRock/GunGame-AMXX">JamesDaRock · GitHub</a>
    </section>
    <section class="panel credits-original">
        <h2>Avalanche · GunGame AMXX 2.13c</h2>
        <p><?= tr('credits_original') ?></p>
        <?php foreach ($groups as $key => $names): ?>
            <h3><?= tr($key) ?></h3>
            <p class="muted"><?= h($names) ?></p>
        <?php endforeach; ?>
    </section>
    <div class="credits-grid">
        <section class="panel"><h2>CSDM 2.1.3d</h2><p><?= tr('credits_csdm') ?></p><a href="https://www.bailopan.net/csdm/">CSDM</a></section>
        <section class="panel"><h2>GameQ</h2><p><?= tr('credits_gameq') ?></p><a href="https://github.com/Austinb/GameQ">GitHub</a></section>
        <section class="panel"><h2>Bootstrap Icons</h2><p><?= tr('credits_icons') ?></p><a href="https://icons.getbootstrap.com/">Bootstrap Icons</a></section>
        <section class="panel"><h2><?= tr('credits_platform') ?></h2><p><?= tr('credits_platform_text') ?></p><a href="https://www.amxmodx.org/">AMX Mod X</a></section>
    </div>
    <section class="panel credits-original" id="licenses">
        <h2><?= tr('credits_licenses') ?></h2>
        <p><?= tr('credits_license_text') ?></p>
        <ul>
            <li><a href="THIRD-PARTY-NOTICES.md"><?= tr('credits_notice') ?></a></li>
            <li>GameQ: <a href="licenses/LGPL-3.0.txt">LGPL-3.0</a> · <a href="licenses/GPL-3.0.txt">GPL-3.0</a></li>
            <li>Bootstrap Icons: <a href="licenses/Bootstrap-Icons-MIT.txt">MIT</a></li>
        </ul>
    </section>
    <footer>
                <div class="project-credits">
                    GunGame AMXX 3++
                    &middot
                    ; Originally developed by Avalanche
                </div>
                <div class="maintainer-credits">
                    Maintained by JamesDaRock
                </div>
                <div>
                    <a href="credits.php?lang=<?= h($lang) ?>"><?= tr('credits_title') ?></a>
                    &middot
                    <a href="https://github.com/JamesDaRock/GunGame-AMXX">GitHub</a>
                </div>
    </footer>
</main>
</body>
</html>
