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

/**
 * GunGame AMXX 3++ – Winner MOTD
 *
 * Read-only viewer for completed match snapshots stored in the SQL database.
 * Supports English and German output for the in-game MOTD.
 */

declare(strict_types=1);

/* --------------------------------------------------------------------------
 * HTTP response headers
 * -------------------------------------------------------------------------- */
header('Content-Type: text/html; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store, no-cache, must-revalidate');

/* --------------------------------------------------------------------------
 * Output escaping and language selection
 * -------------------------------------------------------------------------- */

/** Escape dynamic values before rendering HTML. */
function h($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$de = ($_GET['l'] ?? 'en') === 'de';

/** Return the text matching the selected language. */
function t(string $deText, string $enText): string
{
    global $de;

    return $de ? $deText : $enText;
}

/* --------------------------------------------------------------------------
 * Request parameters and initial snapshot state
 * -------------------------------------------------------------------------- */
$id = filter_input(INPUT_GET, 'i', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 32],
]) ?: 0;

$server = $_GET['s'] ?? '';
$timestamp = filter_input(INPUT_GET, 't', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 4294967295],
]);

$error = '';
$meta = null;
$players = [];
$me = null;
$winner = null;
$loser = null;

try {
    /* ----------------------------------------------------------------------
     * Configuration and database connection
     * ---------------------------------------------------------------------- */
    $c = require __DIR__ . '/config.php';
    $timezone = $c['timezone'] ?? 'UTC';

    if (!is_string($timezone) || !in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC), true)) {
        $timezone = 'UTC';
    }

    // Validate the configured table name before interpolating it into SQL.
    $table = $c['tables']['lastgame'];

    if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $table)) {
        throw new RuntimeException('Invalid table');
    }

    $db = new PDO(
        sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $c['host'],
            (int) ($c['port'] ?? 3306),
            $c['database']
        ),
        $c['username'],
        $c['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    /* ----------------------------------------------------------------------
     * Server address and match timestamp validation
     * ---------------------------------------------------------------------- */
    if (!is_string($server) || !preg_match('/^[0-9.]+:[0-9]{1,5}$/D', $server)) {
        throw new RuntimeException('Invalid server');
    }

    [$ip, $port] = explode(':', $server, 2);

    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || (int) $port < 1 || (int) $port > 65535) {
        throw new RuntimeException('Invalid server address');
    }

    if (isset($_GET['t']) && !$timestamp) {
        throw new RuntimeException('Invalid match timestamp');
    }

    /* ----------------------------------------------------------------------
     * Snapshot retrieval
     * ---------------------------------------------------------------------- */
    if ($timestamp) {
        // Retrieve the explicitly requested match.
        $stmt = $db->prepare("SELECT * FROM `$table` WHERE serverip = ? AND timestamp = ? ORDER BY id LIMIT 33");
        $stmt->execute([$server, $timestamp]);
    } else {
        // Without a timestamp, retrieve the latest match with a metadata row.
        $stmt = $db->prepare("SELECT * FROM `$table` WHERE serverip = ? AND timestamp = (SELECT MAX(timestamp) FROM `$table` WHERE serverip = ? AND id = 0) ORDER BY id LIMIT 33");
        $stmt->execute([$server, $server]);
    }

    $rows = $stmt->fetchAll();

    /* ----------------------------------------------------------------------
     * Match metadata and player identification
     * ---------------------------------------------------------------------- */
    // Row zero overloads player columns with match metadata (GunGame SMA).
    foreach ($rows as $r) {
        if ((int) $r['id'] === 0) {
            $meta = $r;
        }
    }

    if (!$meta) {
        throw new RuntimeException('No match snapshot');
    }

    foreach ($rows as $r) {
        // Exclude metadata and rows belonging to a different snapshot.
        if ((int) $r['id'] === 0 || $r['timestamp'] !== $meta['timestamp'] || $r['serverip'] !== $meta['serverip']) {
            continue;
        }

        $players[] = $r;

        if ((int) $r['id'] === $id) {
            $me = $r;
        }

        // The metadata points fields store the winner and last opponent IDs.
        if ((int) $r['id'] === (int) $meta['points']) {
            $winner = $r;
        }

        if ((int) $r['id'] === (int) $meta['new_points']) {
            $loser = $r;
        }
    }

    if (!$winner) {
        throw new RuntimeException('Winner missing from snapshot');
    }

    /* Sort by level descending, earned points descending, then ID ascending. */
    usort(
        $players,
        fn($a, $b) => ((int) $b['level'] <=> (int) $a['level'])
            ?: ((int) $b['new_points'] <=> (int) $a['new_points'])
            ?: ((int) $a['id'] <=> (int) $b['id'])
    );
} catch (Throwable $e) {
    /* Log technical details on the server and display a localized message. */
    error_log('GunGame MOTD: ' . $e->getMessage());
    $error = t('Keine vollständigen Spielergebnisse verfügbar.', 'No complete match results available.');
}
?><!DOCTYPE html>
<html lang="<?= $de ? 'de' : 'en' ?>">
    <head>
        <meta charset="UTF-8">
        <title>GunGame AMXX</title>
        <style>
            /* Base layout and typography */
            body {
                background: #000;
                color: #eee;
                font: 14px Arial, sans-serif;
                margin: 12px;
                text-align: center;
            }

            h1 {
                font-size: 24px;
                color: #00cc00;
                margin: 8px;
            }

            h2 {
                font-size: 22px;
                color: #ff5555;
                margin: 8px;
            }

            p {
                margin: 7px;
            }

            /* Match results table */
            table {
                border-collapse: collapse;
                width: 100%;
                margin: 12px auto;
                font-size: 12px;
            }

            th,
            td {
                padding: 4px;
                border-bottom: 1px solid #444;
                text-align: center;
            }

            th {
                color: #ffcc55;
            }

            td.name {
                text-align: left;
            }

            /* Player highlighting and text accents */
            .me {
                background: #442222;
            }

            .win {
                color: #66dd66;
            }

            .muted {
                color: #aaa;
            }

            .accent {
                color: #00dd00;
            }
        </style>
    </head>
    <body>
        <h1>[GUNGAME] AMXX</h1>

        <?php if ($error): ?>
            <!-- Localized error message -->
            <p><?= h($error) ?></p>
        <?php else: ?>
            <!-- Winner and last opponent -->
            <h2><?= h($winner['name']) ?> <?= t('gewinnt!', 'wins!') ?></h2>

            <?php if ($loser): ?>
                <p><?= t('Letzter Gegner:', 'Last opponent:') ?> <?= h($loser['name']) ?></p>
            <?php endif; ?>

            <p><?= t('Siege', 'Wins') ?>: <span class="accent"><?= h($winner['wins']) ?></span> &nbsp; <?= t('Punkte', 'Points') ?>: <span class="accent"><?= h($winner['points']) ?></span></p>

            <!-- Personal match statistics, when the requested player exists -->
            <?php if ($me): ?>
                <p><?= t('Deine erhaltenen Punkte:', 'Your earned points:') ?> <span class="accent"><?= h($me['new_points']) ?></span> | <?= t('Gesamtpunkte:', 'Total points:') ?> <?= h($me['points']) ?> | <?= t('Siege:', 'Wins:') ?> <?= h($me['wins']) ?></p>
            <?php endif; ?>

            <!-- Sorted match results and player highlighting -->
            <table>
                <thead>
                    <tr>
                        <th><?= t('Spieler', 'Player') ?></th>
                        <th>Level</th>
                        <th><?= t('Waffe', 'Weapon') ?></th>
                        <th>+ P</th>
                        <th><?= t('Punkte', 'Points') ?></th>
                        <th><?= t('Siege', 'Wins') ?></th>
                        <th><?= t('Rang', 'Rank') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($players as $r): ?>
                        <tr class="<?= (int) $r['id'] === $id ? 'me' : '' ?>">
                            <td class="name <?= ((int) $r['flags'] & 1) ? 'win' : '' ?>"><?= h($r['name']) ?></td>
                            <td><?= h($r['level']) ?></td>
                            <td><?= h($r['weapon']) ?></td>
                            <td><?= h($r['new_points']) ?></td>
                            <td><?= h($r['points']) ?></td>
                            <td><?= h($r['wins']) ?></td>
                            <td><?= (int) $r['stats_position'] > 0 ? h($r['stats_position']) : '–' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Match metadata: played map, duration, finish time and next map -->
            <p><?= t('Gespielte Map:', 'Played map:') ?> <span class="accent"><?= h($meta['authid'] !== '' ? $meta['authid'] : t('Nicht gespeichert', 'Not recorded')) ?></span></p>

            <?php
            /* Convert stored duration in seconds and format the finish timestamp. */
            $duration = (string) ($meta['weapon'] ?? '');
            $durationText = ctype_digit($duration)
                ? sprintf('%02d:%02d %s', intdiv((int) $duration, 60), (int) $duration % 60, t('Min.', 'min.'))
                : t('Nicht gespeichert', 'Not recorded');
            $finished = (new DateTimeImmutable('@' . (int) $meta['timestamp']))->setTimezone(new DateTimeZone($timezone));
            ?>

            <p><?= t('Spieldauer:', 'Match duration:') ?> <span class="accent"><?= h($durationText) ?></span></p>
            <p><?= t('Spielabschluss:', 'Match ended:') ?> <span class="accent"><?= h($finished->format($de ? 'd.m.Y – H:i:s' : 'Y-m-d – H:i:s')) ?></span></p>
            <p><?= t('Nächste Map:', 'Next map:') ?> <span class="accent"><?= h($meta['name']) ?></span></p>
            <p class="muted"><?= h($meta['serverip']) ?></p>
        <?php endif; ?>
    </body>
</html>
