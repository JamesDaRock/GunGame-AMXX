<?php
/**
 * Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 * This file is part of GunGame AMXX 3++ - pur3gaming Edition.
 * See LICENSE for the full license text.
 */

/** Independent victory screen using the controller's match snapshot. */
if (!defined('GUNGAME_MOTD_RENDER')) {
    http_response_code(404);
    exit;
}

// Resolve presentation colors from the stored team identifiers.
$colors = [1 => '#ff7777', 2 => '#78baff'];
$winnerColor = $colors[(int) ($winner['team'] ?? 0)] ?? '#ffb454';
$loserColor = $colors[(int) ($loser['team'] ?? 0)] ?? '#aab7c8';
$winningTeam = (int) ($meta['current_streak'] ?? 0);
$winnerColor = $colors[$winningTeam] ?? '#ffb454';
$teamNames = [1 => 'TERRORISTS', 2 => 'COUNTER-TERRORISTS'];
?>
<!DOCTYPE html>
<html lang="<?= $de ? 'de' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <title>GunGame AMXX 3++</title>
    <style>
        body { margin: 0; padding: 10px; background: #080e16; color: #e7edf5; font: 12px Arial, sans-serif; }
        .wrap { max-width: 760px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { padding: 5px; text-align: center; vertical-align: middle; word-wrap: break-word; }
        .header { border-bottom: 2px solid #f49a36; margin-bottom: 8px; }
        .brand { color: #ff9e43; font-size: 19px; font-weight: bold; }
        .muted { color: #a4b5c7; font-size: 11px; }
        .hero { background: #111d2a; border: 1px solid #304052; padding: 10px; text-align: center; }
        .eyebrow { color: #ffb454; font-size: 11px; letter-spacing: 3px; font-weight: bold; }
        h1 { margin: 5px 0; font-size: 29px; word-wrap: break-word; }
        .value { font-size: 22px; font-weight: bold; }
        .stats { margin-top: 8px; border-top: 1px solid #304052; }
        .personal { margin-top: 8px; padding: 7px; border: 1px solid #293b4c; background: #101923; }
        .next { margin-top: 8px; background: #152131; border-left: 3px solid #ff9e43; }
        .map { font-size: 20px; color: #ff9e43; font-weight: bold; }
        .footer { margin-top: 8px; text-align: center; color: #8296aa; font-size: 10px; }
        .symbol { font-size: 24px; color: #ffb454; }
    </style>
</head>
<body>
    <div class="wrap">
        <table class="header">
            <tr>
                <td style="text-align: left; width: 65%;">
                    <span class="brand">GUNGAME 3++</span><br>
                    <span class="muted">PUR3GAMING EDITION</span>
                </td>
                <td class="muted" style="text-align: right;">TEAMPLAY / <?= t('MATCHENDE', 'MATCH END') ?></td>
            </tr>
        </table>
        <?php if ($error): ?>
            <div class="hero"><?= h($error) ?></div>
        <?php else: ?>
            <div class="hero" style="border-color: <?= h($winnerColor) ?>; ">
                <div class="symbol" style="color: <?= h($winnerColor) ?>; ">&#9733;</div>
                <div class="eyebrow"><?= t('TEAMSIEG', 'TEAM VICTORY') ?></div>
                <h1 style="color: <?= h($winnerColor) ?>; "><?= h($teamNames[$winningTeam] ?? t('Unbekannt', 'Unknown')) ?></h1>
                <table class="stats">
                    <tr>
                        <?php foreach ([2, 1] as $team): ?>
                            <td style="background: <?= $team === 2 ? '#173555' : '#301d26' ?>; color: <?= h($colors[$team]) ?>; ">
                                <?= $team === 2 ? 'CT' : 'T' ?><br>
                                <span class="value"><?= in_array($winningTeam, [1, 2], true) ? ($team === $winningTeam ? t('SIEG', 'VICTORY') : t('NIEDERLAGE', 'DEFEAT')) : '—' ?></span>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </table>
                <p class="muted"><?= t('Entscheidender Kill', 'Decisive kill') ?><br>
                    <strong style="color: <?= h($winnerColor) ?>; "><?= h($winner['name']) ?></strong>
                    <?php if ($loser): ?>
                        &nbsp; / &nbsp; <strong style="color: <?= h($loserColor) ?>; "><?= h($loser['name']) ?></strong>
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($me): ?>
                <div class="personal">
                    <div class="muted"><?= t('DEIN ERGEBNIS', 'YOUR PERFORMANCE') ?> &middot; <?= h($me['name']) ?></div>
                    <table>
                        <tr>
                            <td><span class="value" style="color: <?= (int) $me['new_points'] >= 0 ? '#55dc98' : '#ff7777' ?>; "><?= h(sprintf('%+d', (int) $me['new_points'])) ?></span><br><?= t('Erhaltene Punkte', 'Points earned') ?></td>
                            <td><span class="value"><?= h($me['points']) ?></span><br><?= t('Teampunkte', 'Team points') ?></td>
                            <td><span class="value"><?= h($me['wins']) ?></span><br><?= t('Teamsiege', 'Team wins') ?></td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>
            <table class="next">
                <tr>
                    <td style="text-align: left; padding-left: 12px; ">
                        <span class="muted"><?= t('NÄCHSTE MAP', 'UP NEXT') ?></span><br>
                        <span class="map"><?= h($meta['name'] ?: '—') ?></span>
                    </td>
                    <td style="text-align: right; width: 35px; color: #ff9e43; font-size: 24px; ">&raquo;</td>
                </tr>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
