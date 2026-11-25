<?php
/**
 * GunGame AMXX - Statistics Dashboard
 *
 * Read-only rankings, player profiles and latest match results.
 * Configuration: config.php | Translations: languages/*.php
 */

// File entry point and strict typing.
declare(strict_types = 1);
header('Content-Type: text/html; charset=UTF-8');

// Load the local installation configuration.
$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    exit('Copy config.example.php to config.php and configure the database.');
}
$c = require $configFile;
$timezone = $c['timezone'] ?? 'UTC';
if (!is_string($timezone) || !in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC), true)) {
    $timezone = 'UTC';
}
date_default_timezone_set($timezone);
$liveEnabled = (bool)($c['gameservers']['enabled'] ?? true);

// Escape dynamic output for HTML contexts.
function h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Format integer statistics for the selected language.
function num($v): string {
    return number_format((int)$v, 0, '.', ($GLOBALS['lang'] ?? 'en') === 'de' ? '.' : ',');
}

// Format stored Unix timestamps for display.
function dt($v): string {
    return (int)$v > 0 ? date(($GLOBALS['lang'] ?? 'en') === 'de' ? 'd.m.Y H:i' : 'Y-m-d H:i', (int)$v) : '–';
}

// Validate configured SQL identifiers before interpolation.
function tableName($s): string {
    if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $s)) {
        throw new RuntimeException('Ungültiger Tabellenname');
    }
    return '`' . $s . '`';
}

// Dashboard configuration and language discovery.
$d = $c['dashboard'] ?? [];
$langs = [];
foreach (glob(__DIR__ . '/languages/*.php') as $file) {
    $code = basename($file, '.php');
    if (preg_match('/^[a-z]{2}$/D', $code)) {
        $langs[$code] = $file;
    }
}
$lang = $_GET['lang'] ?? ($d['language'] ?? 'en');
if (!is_string($lang) || !isset($langs[$lang])) {
    $lang = 'en';
}
$translations = require $langs['en'];
$translations = array_replace($translations, require $langs[$lang]);

// Look up and escape translated interface text.
function tr(string $key): string {
    global $translations;
    return h($translations[$key] ?? $key);
}

// Validate the optional custom logo path.
$logo = (string)($d['community_logo'] ?? '');
if (!preg_match('~^assets/[a-zA-Z0-9_./-]+\.(png|jpg|jpeg|webp|svg)$~iD', $logo) || strpos($logo, '..') !== false) {
    $logo = '';
}

// Initialize the request and database result state.
$meta = null;
$err = null;
$db = null;
$players = [];
$record = null;
$lastgame = [];
$profile = null;
$total = 0;
$summary = ['players' => 0, 'wins' => 0, 'points' => 0];
$servers = [];

// Resolve ranking mode, sorting and request filters.
$mode = ($_GET['mode'] ?? 'solo') === 'team' ? 'team' : 'solo';
$wins = $mode === 'team' ? 'wins_tp' : 'wins';
$points = $mode === 'team' ? 'points_tp' : 'points';
$streak = $mode === 'team' ? 'streak_tp' : 'streak';
$sort = ($_GET['sort'] ?? 'wins') === 'points' ? 'points' : 'wins';
$sortCol = $sort === 'points' ? $points : $wins;
$q = trim((string)($_GET['q'] ?? ''));
$q = mb_substr($q, 0, 64);
$server = (string)($_GET['server'] ?? '');
$page = max(1, min(100000, (int)($_GET['page'] ?? 1)));
$limit = 25;
$auth = (string)($_GET['player'] ?? '');
$auth = mb_substr($auth, 0, 64);

// Build navigation URLs while retaining active filters.
function url(array $patch = []): string {
    global $mode, $sort, $q, $server, $page, $lang;
    return '?' . http_build_query(array_filter(array_merge(['lang' => $lang, 'mode' => $mode, 'sort' => $sort, 'q' => $q, 'server' => $server, 'page' => $page], $patch), fn($v) => $v !== '' && $v !== null));
}

// Read dashboard data using prepared statements.
try {
    foreach (['stats', 'streaks', 'lastgame'] as $t) {
        $c['tables'][$t] = tableName($c['tables'][$t]);
    }
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], (int)($c['port'] ?? 3306), $c['database']);
    $db = new PDO($dsn, $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);

    // Discover available server filters.
    $servers = $db->query('SELECT DISTINCT serverip FROM ' . $c['tables']['stats'] . ' ORDER BY serverip')->fetchAll(PDO::FETCH_COLUMN);
    if ($server !== '' && !in_array($server, $servers, true)) {
        $server = '';
    }

    // Build ranking filters and pagination.
    $where = ' WHERE 1=1';
    $params = [];
    if ($server !== '') {
        $where .= ' AND serverip=:server';
        $params['server'] = $server;
    }
    if ($q !== '') {
        $where .= ' AND (name LIKE :search OR authid LIKE :search2)';
        $params['search'] = '%' . $q . '%';
        $params['search2'] = '%' . $q . '%';
    }
    $where .= " AND ($wins > 0 OR $points > 0)";
    $st = $db->prepare('SELECT COUNT(*) FROM ' . $c['tables']['stats'] . $where);
    $st->execute($params);
    $total = (int)$st->fetchColumn();
    $maxPage = max(1, (int)ceil($total / $limit));
    $page = min($page, $maxPage);
    $offset = ($page - 1) * $limit;
    $sql = "SELECT authid,name,serverip,timestamp,$wins AS wins,$points AS points,$streak AS streak FROM " . $c['tables']['stats'] . $where . " ORDER BY $sortCol DESC, $wins DESC, $points DESC, name ASC LIMIT :lim OFFSET :off";
    $st = $db->prepare($sql);
    foreach ($params as $k => $v) {
        $st->bindValue(':' . $k, $v);
    }
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->bindValue(':off', $offset, PDO::PARAM_INT);
    $st->execute();
    $players = $st->fetchAll();

    // Load aggregate statistics for the selected server scope.
    $sw = $server !== '' ? ' WHERE serverip=:s' : '';
    $st = $db->prepare("SELECT COUNT(*) AS players,COALESCE(SUM($wins),0) AS wins,COALESCE(SUM($points),0) AS points FROM " . $c['tables']['stats'] . $sw);
    if ($server !== '') {
        $st->bindValue(':s', $server);
    }
    $st->execute();
    $summary = $st->fetch();
    $st = $db->prepare('SELECT name,authid,streak,timestamp,serverip FROM ' . $c['tables']['streaks'] . " WHERE type=:type" . ($server !== '' ? ' AND serverip=:server' : '') . " ORDER BY streak DESC LIMIT 1");
    $st->bindValue(':type', $mode === 'team' ? '1R' : '0R');
    if ($server !== '') {
        $st->bindValue(':server', $server);
    }
    $st->execute();
    $record = $st->fetch() ?: null;

    // Load an optional player profile.
    if ($auth !== '') {
        $st = $db->prepare('SELECT * FROM ' . $c['tables']['stats'] . ' WHERE authid=:auth' . ($server !== '' ? ' AND serverip=:server' : '') . ' ORDER BY timestamp DESC LIMIT 1');
        $st->bindValue(':auth', $auth);
        if ($server !== '') {
            $st->bindValue(':server', $server);
        }
        $st->execute();
        $profile = $st->fetch() ?: null;
    }
    // Select the latest complete match using its metadata row, scoped to one server.
    $st = $db->prepare('SELECT * FROM ' . $c['tables']['lastgame'] . ' WHERE id=0' . ($server !== '' ? ' AND serverip=:server' : '') . ' ORDER BY timestamp DESC,serverip ASC LIMIT 1');
    if ($server !== '') {
        $st->bindValue(':server', $server);
    }
    $st->execute();
    $meta = $st->fetch() ?: null;
    if ($meta) {
        $st = $db->prepare('SELECT * FROM ' . $c['tables']['lastgame'] . ' WHERE serverip=:s AND timestamp=:t AND id<>0 ORDER BY level DESC,new_points DESC,id ASC');
        $st->execute(['s' => $meta['serverip'], 't' => $meta['timestamp']]);
        $lastgame = $st->fetchAll();
    }
} catch (Throwable $e) {
    $err = tr('t55');
    error_log('GunGame dashboard: ' . $e->getMessage());
}
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
        <meta name="description" content="<?= tr('t59') ?>">
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
        $pageUrl = $baseUrl;
        ?>
        <!-- Canonical URL -->
        <?php if ($baseUrl !== ""): ?>
        <link rel="canonical" href="<?= h($pageUrl) ?>">
        <?php endif; ?>

        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="GunGame AMXX 3++">
        <meta property="og:title" content="<?= h($c['title'] ?? 'GunGame AMXX 3++ | Rankings & Live Server Statistics') ?>">
        <meta property="og:description" content="<?= tr('t59') ?>">
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
        <meta property="og:image:alt" content="GunGame AMXX 3++ Rankings and Live Server Statistics">
        <?php endif; ?>

        <!-- Twitter / X -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?= h($c['title'] ?? 'GunGame AMXX 3++ | Rankings & Live Server Statistics') ?>">
        <meta name="twitter:description" content="<?= tr('t59') ?>">
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
            <!-- Branding, navigation and language selector -->
            <header id="overview">
                <div class="brandbar">
                    <a class="brand" href="<?= h(url(['page'=>1,'q'=>null])) ?>">
                        <span class="brandmark">
                            <?php if ($logo!==''): ?>
                                <img src="<?= h($logo) ?>" alt="">
                            <?php else: ?>
                                <img src="assets/images/logo.svg" alt="GunGame">
                            <?php endif; ?>
                        </span>
                        <span class="brand-title"><?= tr('statistics') ?></span>
                    </a>
                    <nav aria-label="<?= tr('t3') ?>">
                        <a href="#overview"><?= tr('t0') ?></a>
                        <?php if ($liveEnabled): ?>
                            <a href="#gameservers"><?= tr('servers_title') ?></a>
                        <?php endif; ?>
                        <a href="#rankings"><?= tr('t1') ?></a>
                        <a href="#latest"><?= tr('t2') ?></a>
                        <span class="language-switch">
                            <i class="bi bi-globe" aria-hidden="true">
                            </i>
                            <?php foreach ($langs as $code=>$file): ?>
                                <a href="<?= h(url(['lang'=>$code,'player'=>$auth])) ?>" <?= $lang===$code?'aria-current="true"':'' ?>>
                                    <?= h(strtoupper($code)) ?>
                                </a>
                            <?php endforeach; ?>
                        </span>
                    </nav>
                </div>
            </header>
            <?php if ($err): ?>
                <div class="alert">
                    <?= $err ?>
                </div>
            <?php else: ?>
                <!-- Overview heading and summary statistics -->
                <div class="section-title overview-title">
                    <h1><?= tr('t0') ?></h1>
                </div>
                <section class="stats" aria-label="<?= tr('t5') ?>">
                    <article>
                        <i class="bi bi-people stat-icon" aria-hidden="true">
                        </i>
                        <small><?= tr('t6') ?></small>
                        <strong><?= num($summary['players']) ?></strong>
                    </article>
                    <article>
                        <i class="bi bi-trophy stat-icon" aria-hidden="true">
                        </i>
                        <small><?= tr('t7') ?></small>
                        <strong><?= num($summary['wins']) ?></strong>
                    </article>
                    <article>
                        <i class="bi bi-bullseye stat-icon" aria-hidden="true">
                        </i>
                        <small><?= tr('t8') ?></small>
                        <strong><?= num($summary['points']) ?></strong>
                    </article>
                    <article>
                        <i class="bi bi-fire stat-icon" aria-hidden="true">
                        </i>
                        <small><?= tr('t9') ?></small>
                        <strong><?= num($record['streak']??0) ?></strong>
                        <small><?= isset($record['name'])?h($record['name']):tr('t10') ?></small>
                    </article>
                </section>
                <?php if ($liveEnabled): ?>
                    <!-- Live server status is loaded independently of the rankings. -->
                    <section id="gameservers" class="server-section" data-refresh="<?= max(15, min(3600, (int)($c['gameservers']['refresh_interval'] ?? 60))) ?>">
                        <div class="section-title">
                            <h2><?= tr('servers_title') ?></h2>
                            <span id="server-summary" class="muted" role="status"><?= tr('servers_loading') ?></span>
                        </div>
                        <div id="server-cards" class="server-grid"></div>
                    </section>
                    <script id="server-translations" type="application/json"><?= json_encode(array_filter($translations, fn($k) => str_starts_with($k, 'servers_'), ARRAY_FILTER_USE_KEY), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
                    <script src="assets/js/servers.js?v=4" defer></script>
                <?php endif; ?>
                <!-- Ranking filters -->
                <form class="filters" method="get">
                    <input type="hidden" name="lang" value="<?= h($lang) ?>">
                    <label>
                        <?= tr('t11') ?>
                        <select name="server">
                            <option value=""><?= tr('t12') ?></option>
                            <?php foreach ($servers as $s): ?>
                                <option value="<?= h($s) ?>" <?= $s===$server?'selected':'' ?>>
                                    <?= h($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <?= tr('t13') ?>
                        <select name="mode">
                            <option value="solo" <?= $mode==='solo'?'selected':'' ?>>
                                <?= tr('t14') ?>
                            </option>
                            <option value="team" <?= $mode==='team'?'selected':'' ?>>
                                <?= tr('t15') ?>
                            </option>
                        </select>
                    </label>
                    <label>
                        <?= tr('t16') ?>
                        <select name="sort">
                            <option value="wins" <?= $sort==='wins'?'selected':'' ?>>
                                <?= tr('t17') ?>
                            </option>
                            <option value="points" <?= $sort==='points'?'selected':'' ?>>
                                <?= tr('t18') ?>
                            </option>
                        </select>
                    </label>
                    <label class="search">
                        <?= tr('t19') ?>
                        <input name="q" value="<?= h($q) ?>" placeholder="<?= tr('t20') ?>">
                    </label>
                    <button type="submit"><?= tr('t21') ?></button>
                </form>
                <?php if ($profile): ?>
                    <!-- Player profile -->
                    <section class="panel" id="profile">
                        <div class="section-title">
                            <h2><?= tr('t22') ?></h2>
                            <a href="<?= h(url(['player'=>null])) ?>">
                                <?= tr('t23') ?>
                            </a>
                        </div>
                        <h3><?= h($profile['name']) ?></h3>
                        <p class="muted">
                            <?= h($profile['authid']) ?>
                            ·
                            <?= h($profile['serverip']) ?>
                        </p>
                        <div class="profile-grid">
                            <div>
                                <small><?= tr('t24') ?></small>
                                <strong><?= num($profile['wins']) ?></strong>
                            </div>
                            <div>
                                <small><?= tr('t25') ?></small>
                                <strong><?= num($profile['points']) ?></strong>
                            </div>
                            <div>
                                <small><?= tr('t26') ?></small>
                                <strong><?= num($profile['streak']) ?></strong>
                            </div>
                            <div>
                                <small><?= tr('t27') ?></small>
                                <strong><?= num($profile['wins_tp']) ?></strong>
                            </div>
                            <div>
                                <small><?= tr('t28') ?></small>
                                <strong><?= num($profile['points_tp']) ?></strong>
                            </div>
                            <div>
                                <small><?= tr('t29') ?></small>
                                <strong><?= num($profile['streak_tp']) ?></strong>
                            </div>
                        </div>
                        <p class="muted">
                            <?= tr('t30') ?>
                            <?= dt($profile['timestamp']) ?>
                        </p>
                    </section>
                <?php endif; ?>
                <?php if ($page===1 && $q==='' && $players): ?>
                    <!-- Top three players -->
                    <section class="podium-section" aria-label="<?= tr('t31') ?>">
                        <div class="section-title">
                            <h2><?= tr('t31') ?></h2>
                            <span class="muted">
                                <?= tr('t56') ?>
                                <?= $sort==='points'?tr('t32'):tr('t33') ?>
                            </span>
                        </div>
                        <div class="podium">
                            <?php foreach (array_slice($players,0,3) as $i=>$p): ?>
                                <article class="podium-card place-<?= $i+1 ?>">
                                    <span class="place">
                                        #
                                        <?= $i+1 ?>
                                    </span>
                                    <span class="eyebrow">
                                        <?= tr('t34') ?>
                                        <?= $i+1 ?>
                                    </span>
                                    <a class="player" href="<?= h(url(['player'=>$p['authid'],'server'=>$p['serverip']])) ?>#profile">
                                        <?= h($p['name']) ?>
                                    </a>
                                    <strong><?= num($p[$sort]) ?></strong>
                                    <small><?= $sort==='points'?tr('t18'):tr('t17') ?></small>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
                <!-- Ranking table and pagination -->
                <section class="panel" id="rankings">
                    <div class="section-title">
                        <h2>
                            <?= tr('t35') ?>
                            <span class="muted">
                                ·
                                <?= h($mode==='team'?tr('t15'):tr('t14')) ?>
                            </span>
                        </h2>
                        <span class="muted">
                            <?= num($total) ?>
                            <?= tr('t36') ?>
                        </span>
                    </div>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th><?= tr('t37') ?></th>
                                    <th><?= tr('t38') ?></th>
                                    <th><?= tr('t17') ?></th>
                                    <th><?= tr('t18') ?></th>
                                    <th><?= tr('t39') ?></th>
                                    <th><?= tr('t40') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($players as $i=>$p): ?>
                                    <tr>
                                        <td>
                                            <span class="rank <?= ($offset+$i)<3?'medal':'' ?>">
                                                #
                                                <?= num($offset+$i+1) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a class="player" href="<?= h(url(['player'=>$p['authid'],'server'=>$p['serverip']])) ?>#profile">
                                                <?= h($p['name']) ?>
                                            </a>
                                            <small class="sub"><?= h($p['serverip']) ?></small>
                                        </td>
                                        <td class="accent"><?= num($p['wins']) ?></td>
                                        <td><?= num($p['points']) ?></td>
                                        <td><?= num($p['streak']) ?></td>
                                        <td class="muted"><?= dt($p['timestamp']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$players): ?>
                                    <tr>
                                        <td colspan="6" class="empty"><?= tr('t41') ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        <small><?= tr('t42') ?></small>
                        <div>
                            <?php if ($page>1): ?>
                                <a href="<?= h(url(['page'=>$page-1,'player'=>null])) ?>">
                                    <?= tr('t43') ?>
                                </a>
                            <?php endif; ?>
                            <span>
                                <?= tr('t44') ?>
                                <?= $page ?>
                                /
                                <?= $maxPage ?>
                            </span>
                            <?php if ($page<$maxPage): ?>
                                <a href="<?= h(url(['page'=>$page+1,'player'=>null])) ?>">
                                    <?= tr('t45') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                <!-- Latest completed match -->
                <section class="panel" id="latest">
                    <div class="section-title">
                        <h2><?= tr('t46') ?></h2>
                        <span class="muted"><?= tr('t47') ?></span>
                    </div>
                    <?php if ($lastgame): ?>
                        <div class="match-meta">
                            <div>
                                <small><?= tr('t48') ?></small>
                                <strong><?= (int)$meta['flags']!==0?tr('t15'):tr('t14') ?></strong>
                            </div>
                            <div>
                                <small><?= tr('t49') ?></small>
                                <strong>
                                    <?= h(dt($meta['timestamp'])) ?>
                                    <?= tr('t57') ?>
                                </strong>
                            </div>
                            <div>
                                <small><?= tr('t11') ?></small>
                                <strong><?= h($meta['serverip']) ?></strong>
                            </div>
                        </div>
                        <div class="table-scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th><?= tr('t38') ?></th>
                                        <th><?= tr('t50') ?></th>
                                        <th><?= tr('t51') ?></th>
                                        <th><?= tr('t17') ?></th>
                                        <th><?= tr('t18') ?></th>
                                        <th><?= tr('t52') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lastgame as $r): ?>
                                        <tr>
                                            <td><?= h($r['name']) ?></td>
                                            <td><?= num($r['level']) ?></td>
                                            <td><?= h($r['weapon']) ?></td>
                                            <td><?= num($r['wins']) ?></td>
                                            <td><?= num($r['points']) ?></td>
                                            <td><?= ( ((int)$meta['flags']!==0 ? ((int)$r['flags']&1)!==0 : (int)$r['id']===(int)$meta['points']))?'<span class="winner">'.tr('t53').'</span>':'–' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="muted"><?= tr('t54') ?></p>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
            <!-- Project credits -->
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
