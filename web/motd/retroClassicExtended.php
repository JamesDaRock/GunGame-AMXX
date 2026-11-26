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
 * Retro Classic Extended winner presentation.
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
    <head><meta charset="UTF-8"><title>GunGame AMXX 3++</title><style>
body {
    background: #090e17;
    color: #e1e6ee;
    font: 12px Arial,sans-serif;
    margin: 8px;
}
h1 {
    font-size: 21px;
    color: #f49a36;
    margin: 0;
}
h2 {
    font-size: 24px;
    margin: 5px 0;
    color: #fff;
}
p {
    margin: 5px 0;
}
.header {
    border-bottom: 2px solid #e88a27;
    padding: 5px 0;
}
.mode,.accent,th {
    color: #f49a36;
}
.muted {
    color: #aab7c8;
}
.panel {
    background: #151e2b;
    padding: 8px;
    text-align: center;
    margin: 8px 0;
}
.personal {
    background: #202c3c;
    padding: 7px;
    margin: 8px 0;
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
td,th {
    padding: 4px;
    text-align: center;
}
.name {
    text-align: left;
}
.score td {
    border-bottom: 1px solid #283545;
}
.alt {
    background: #111925;
}
.me {
    background: #35404e;
}
.gain {
    color: #64d99b;
}
.value {
    font-size: 18px;
    font-weight: bold;
}
.red {
    color: #f87171;
}
.blue {
    color: #82baff;
}
.redpanel {
    background: #2e171c;
}
.bluepanel {
    background: #13243b;
}
.footer {
    text-align: center;
    border-top: 1px solid #334155;
    padding: 6px;
    margin-top: 8px;
}
</style>
    </head>
    <body>
        <table class="header">
            <tr>
                <td class="name"><h1>GUNGAME AMXX 3++</h1><span class="muted">pur3gaming Edition</span></td>
                <td class="mode" style="text-align:right">SOLO</td>
            </tr>
        </table>
<?php if ($error): ?><div class="panel"><?= h($error) ?></div><?php else: ?>
<div class="panel"><span class="accent"><?= t('GEWINNER','MATCH WINNER') ?></span><h2><?= h($winner['name']) ?></h2><?php if ($loser): ?><p><?= t('Letzter Gegner:', 'Last opponent:') ?> <?= h($loser['name']) ?></p><?php endif; ?>
<table>
        <tr>
            <td><span class="value accent"><?= h($winner['new_points']) ?></span><br><?= t('Erhaltene Punkte','Earned points') ?></td>
            <td><span class="value accent"><?= h($winner['points']) ?></span><br><?= t('Gesamtpunkte','Total points') ?></td>
            <td><span class="value accent"><?= h($winner['wins']) ?></span><br><?= t('Siege','Wins') ?></td>
        </tr>
    </table>
</div>
<?php if ($me): ?><table class="personal">
<tr>
    <td class="name"><span class="muted"><?= t('DEIN ERGEBNIS','YOUR RESULT') ?></span><br><b><?= h($me['name']) ?></b></td>
    <td><span class="value gain"><?= h(sprintf('%+d',(int)$me['new_points'])) ?></span><br><?= t('Punkte','Points') ?></td>
    <td><span class="value"><?= h($me['points']) ?></span><br><?= t('Gesamt','Total') ?></td>
    <td><span class="value"><?= (int)$me['stats_position'] > 0 ? '#'.h($me['stats_position']) : '–' ?></span><br><?= t('Rang','Rank') ?></td>
</tr>
</table><?php endif; ?>
<?php $displayPlayers = $players; ?><table class="score">
    <tr>
        <th class="name"><?= t('Spieler','Player') ?></th>
        <th>Lvl</th>
        <th><?= t('Waffe','Weapon') ?></th>
        <th>+P</th>
        <th><?= t('Punkte','Points') ?></th>
        <th><?= t('Siege','Wins') ?></th>
        <th><?= t('Rang','Rank') ?></th>
    </tr>
<?php foreach ($displayPlayers as $index => $r): ?><tr class="<?= (int)$r['id'] === $id ? 'me' : ($index % 2 === 0 ? 'alt' : '') ?>">
    <td class="name <?= ((int)$r['flags'] & 1) ? 'accent' : '' ?>"><?= h($r['name']) ?></td>
    <td><?= h($r['level']) ?></td>
    <td><?= h($r['weapon']) ?></td>
    <td class="gain"><?= h(sprintf('%+d',(int)$r['new_points'])) ?></td>
    <td><?= h($r['points']) ?></td>
    <td><?= h($r['wins']) ?></td>
    <td><?= (int)$r['stats_position'] > 0 ? h($r['stats_position']) : '–' ?></td>
</tr><?php endforeach; ?></table>
<?php $duration = (string)($meta['weapon'] ?? ''); $durationText = ctype_digit($duration) ? sprintf('%02d:%02d',intdiv((int)$duration,60),(int)$duration%60) : '–'; $finished = (new DateTimeImmutable('@'.(int)$meta['timestamp']))->setTimezone(new DateTimeZone($timezone)); ?>
<table class="panel">
<tr>
    <td width="50%"><span class="muted"><?= t('GESPIELTE MAP','PLAYED MAP') ?></span><p><b><?= h($meta['authid'] ?: '–') ?></b></p><?= t('Spieldauer','Duration') ?>: <?= h($durationText) ?></td>
    <td><span class="muted"><?= t('NÄCHSTE MAP','NEXT MAP') ?></span><p class="accent"><b><?= h($meta['name']) ?></b></p></td>
</tr>
</table>
<div class="footer muted"><?= h($meta['serverip']) ?> · <?= h($finished->format($de ? 'd.m.Y – H:i:s' : 'Y-m-d – H:i:s')) ?><p class="accent">gungame.pur3gaming.com</p></div>
<?php endif; ?></body>
</html>
