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

/** Read-only RC3 statistics queries and request filters. */
// Initialize the request and database result state.
$meta = null;
$err = null;
$db = null;
$players = [];
$record = null;
$lastgame = [];
$latestMatches = [];
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
$q = (function_exists('mb_substr') ? mb_substr($q, 0, 64) : substr($q, 0, 64));
$server = (string)($_GET['server'] ?? '');
$page = max(1, min(100000, (int)($_GET['p'] ?? 1))); 
$leaderLimit = in_array((int)($_GET['per_page'] ?? 25), [25,50,100], true) ? (int)($_GET['per_page'] ?? 25) : 25;
$limit = $route === 'top10' ? 10 : 25;
if (in_array($route, ['overview', 'top10'], true)) { $page = 1; $q = ''; }
$auth = (string)($_GET['player'] ?? '');
$auth = (function_exists('mb_substr') ? mb_substr($auth, 0, 64) : substr($auth, 0, 64));

// Build navigation URLs while retaining active filters.
function url(array $patch = []): string {
    global $mode, $sort, $q, $server, $page, $languagePreference, $route, $auth, $leaderLimit;
    return portal_url(array_merge(['lang' => $languagePreference, 'mode' => $mode, 'sort' => $sort, 'q' => $q, 'server' => $server, 'view' => $route, 'p' => $page, 'player' => $auth, 'per_page' => in_array($route, ['leaderboard','players'], true) ? $leaderLimit : null], $patch));
}

// Read dashboard data using prepared statements.
if (!in_array($route, ['credits', 'servers', 'not_found'], true)) {
try {
    foreach (['stats', 'streaks', 'lastgame'] as $t) {
        $c['tables'][$t] = tableName($c['tables'][$t]);
    }
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], (int)($c['port'] ?? 3306), $c['database']);
    $db = new PDO($dsn, $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);

    if (in_array($route, ['matches', 'match'], true)) {
        require __DIR__ . '/match_history.php';
        return;
    }

    if ($route === 'players') {
        require __DIR__ . '/players.php';
        return;
    }

    if ($route === 'leaderboard') {
        require __DIR__ . '/leaderboard.php';
        return;
    }

    if ($route === 'top10') {
        require __DIR__ . '/hall_of_fame.php';
        return;
    }

    // Discover available server filters.
    $servers = $db->query('SELECT DISTINCT serverip FROM ' . $c['tables']['stats'] . ' ORDER BY serverip')->fetchAll(PDO::FETCH_COLUMN);
    if ($server !== '' && !in_array($server, $servers, true) && $route !== 'server' && $route !== 'player') {
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
    if ($route !== 'players') { $where .= " AND ($wins > 0 OR $points > 0)"; }
    $st = $db->prepare('SELECT COUNT(*) FROM ' . $c['tables']['stats'] . $where);
    $st->execute($params);
    $total = (int)$st->fetchColumn();
    $maxPage = max(1, (int)ceil($total / $limit));
    $page = min($page, $maxPage);
    $offset = ($page - 1) * $limit;
    $orderBy = $route === 'players' ? 'name ASC, authid ASC, serverip ASC' : "$sortCol DESC, $wins DESC, $points DESC, name ASC, authid ASC, serverip ASC";
    $sql = "SELECT authid,name,serverip,timestamp,$wins AS wins,$points AS points,$streak AS streak FROM " . $c['tables']['stats'] . $where . " ORDER BY {$orderBy} LIMIT :lim OFFSET :off";
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
    // Read one latest saved match per server, independently of dashboard filters.
    if ($route === 'overview') {
        $matchTable = $c['tables']['lastgame'];
        $metadata = $db->query("SELECT m.* FROM $matchTable m JOIN (SELECT serverip, MAX(timestamp) AS latest FROM $matchTable WHERE id=0 GROUP BY serverip) latest ON latest.serverip=m.serverip AND latest.latest=m.timestamp WHERE m.id=0 ORDER BY m.timestamp DESC, m.serverip ASC")->fetchAll();
        $participants = $db->prepare("SELECT * FROM $matchTable WHERE serverip=:server AND timestamp=:timestamp AND id<>0 ORDER BY level DESC, id ASC");
        foreach ($metadata as $match) {
            $participants->execute(['server' => $match['serverip'], 'timestamp' => $match['timestamp']]);
            $rows = $participants->fetchAll();
            if (!$rows) continue;
            $winners = array_values(array_filter($rows, static fn($row) => (int)$match['flags'] !== 0 ? ((int)$row['flags'] & 1) !== 0 : (int)$row['id'] === (int)$match['points']));
            $latestMatches[] = ['metadata' => $match, 'participants' => count($rows), 'rows' => $rows, 'winners' => $winners];
        }
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
    $err = translate('error.database');
    error_log('GunGame dashboard: ' . $e->getMessage());
}

}
