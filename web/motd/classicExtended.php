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
 * Extended winner presentation.
 * The controller supplies validated snapshot data and language helpers.
 * Keep this template independent of portal CSS and JavaScript for the game browser.
 */
// Presentation files are loaded only by the central MOTD controller.
if (!defined('GUNGAME_MOTD_RENDER')) {
    http_response_code(404);
    exit;
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
        <h1><?= h($motdTitle) ?></h1>

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
