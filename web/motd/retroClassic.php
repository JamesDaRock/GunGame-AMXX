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
 * Compact modern winner presentation. Independent of portal assets.
 */
if (!defined('GUNGAME_MOTD_RENDER')) {
    http_response_code(404);
    exit;
}

/* Resolve presentation colors and snapshot statistics mode. */
$statsMode = $error ? 0 : (int) $meta['team'];
$color = static function ($row): string {
    return [1 => '#ff5959', 2 => '#60aaff'][(int) ($row['team'] ?? 0)] ?? '#eeeeee';
};
$winnerColor = $color($winner);
$winnerName = $winner['name'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?= $de ? 'de' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($motdTitle) ?></title>
    <style>
        body {
            background: #090e17;
            color: #e1e6ee;
            font: 13px Arial, sans-serif;
            text-align: center;
            margin: 8px;
        }
        .wrap {
            max-width: 760px;
            margin: 0 auto;
        }
        h1 {
            font-size: 22px;
            color: #f49a36;
            margin: 0;
        }
        h2 {
            font-size: 26px;
            margin: 4px 0;
        }
        p {
            margin: 5px 0;
        }
        .header {
            border-bottom: 2px solid #f49a36;
            padding: 5px 0 9px;
        }
        .panel {
            background: #151e2b;
            border: 1px solid #304052;
            padding: 10px;
            margin: 8px 0;
        }
        .winner {
            border-top: 2px solid <?= $winnerColor ?>;
        }
        .personal {
            background: #202c3c;
        }
        .muted {
            color: #aab7c8;
            font-size: 11px;
        }
        .accent {
            color: #ffa34d;
        }
        .gain {
            color: #66d49a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 0;
        }
        td {
            text-align: center;
            padding: 4px;
        }
        .value {
            font-size: 21px;
            font-weight: bold;
        }
        .next {
            border-top: 1px solid #304052;
            padding: 8px 0;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <h1>GUNGAME AMXX 3++</h1>
            <p class="muted">pur3gaming Edition</p>
            <p class="muted">SOLO &middot; <?= t('RUNDE BEENDET', 'MATCH COMPLETED') ?></p>
        </div>
        <?php if ($error): ?>
            <p class="panel"><?= h($error) ?></p>
        <?php else: ?>
            <!-- Winner and final opponent -->
            <div class="panel winner">
                <p class="muted"><?= t('GEWINNER DER RUNDE', 'MATCH WINNER') ?></p>
                <h2 style="color: <?= $winnerColor ?>"><?= h($winnerName) ?></h2>
                <?php if ($loser): ?>
                    <p>
                        <?= t('Letzter Kill', 'Final kill') ?> (<?= h($winner['weapon']) ?>):
                        <span style="color: <?= $color($winner) ?>"><?= h($winner['name']) ?></span>
                        <?= t('gegen', 'against') ?>
                        <span style="color: <?= $color($loser) ?>"><?= h($loser['name']) ?></span>
                    </p>
                <?php endif; ?>
                <?php if ($statsMode > 0): ?>
                    <table>
                        <tr>
                            <td><span class="value accent"><?= h($winner['wins']) ?></span><br><?= t('Siege', 'Wins') ?></td>
                            <?php if ($statsMode === 2): ?>
                                <td><span class="value accent"><?= h($winner['points']) ?></span><br><?= t('Punkte', 'Points') ?></td>
                            <?php endif; ?>
                            <td><span class="value accent"><?= h($winner['current_streak']) ?></span><br><?= t('Siegesserie', 'Win streak') ?></td>
                        </tr>
                    </table>
                    <p class="muted"><?= t('Persönliche Bestserie:', 'Personal best streak:') ?> <?= h($winner['record_streak']) ?></p>
                <?php endif; ?>
            </div>
            <!-- Viewer-specific result, if present in the snapshot. -->
            <?php if ($statsMode > 0 && $me): ?>
                <div class="panel personal">
                    <p class="muted"><?= t('DEIN ERGEBNIS', 'YOUR RESULT') ?></p>
                    <table>
                        <tr>
                            <?php if ($statsMode === 2): ?>
                                <td><span class="value <?= (int) $me['new_points'] >= 0 ? 'gain' : 'accent' ?>"><?= (int) $me['new_points'] > 0 ? '+' : '' ?><?= h($me['new_points']) ?></span><br><?= t('Erhaltene Punkte', 'Points earned') ?></td>
                                <td><span class="value"><?= h($me['points']) ?></span><br><?= t('Gesamtpunkte', 'Total points') ?></td>
                            <?php endif; ?>
                            <td><span class="value"><?= h($me['wins']) ?></span><br><?= t('Siege', 'Wins') ?></td>
                        </tr>
                    </table>
                    <?php if ((int) $me['current_streak'] > 0): ?>
                        <p class="muted"><?= t('Siegesserie:', 'Win streak:') ?> <?= h($me['current_streak']) ?> &middot; <?= t('Bestserie:', 'Best streak:') ?> <?= h($me['record_streak']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($statsMode > 0 && $champion): ?>
                <p class="muted"><?= t('ALLZEIT-REKORD', 'ALL-TIME RECORD') ?></p>
                <p><strong><?= h($champion['name']) ?></strong> &middot; <?= h($champion['streak']) ?> <?= t('Siege in Folge', 'consecutive wins') ?></p>
            <?php endif; ?>
            <div class="next">
                <p class="muted"><?= t('NÄCHSTE MAP', 'NEXT MAP') ?></p>
                <strong class="accent"><?= h($meta['name']) ?></strong>
            </div>
        <?php endif; ?>
        <p class="muted">gungame.pur3gaming.com</p>
    </div>
</body>
</html>
