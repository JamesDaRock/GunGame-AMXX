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
 * Simple winner presentation.
 * The controller supplies validated snapshot data and language helpers.
 * Keep this template independent of portal CSS and JavaScript for the game browser.
 */
// Presentation files are loaded only by the central MOTD controller.
if (!defined('GUNGAME_MOTD_RENDER')) {
    http_response_code(404);
    exit;
}


/** Presentation-specific formatting. */
$teamplay = false;
$statsMode = $error ? 0 : (int) $meta['team'];
$color = static function ($row): string {
    return (int) ($row['team'] ?? 0) === 1 ? '#ff3333' : ((int) ($row['team'] ?? 0) === 2 ? '#78b9df' : '#eeeeee');
};
$winnerColor = $color($winner);
$ordinal = static function ($n): string {
    $n = (int) $n;
    if ($n % 100 >= 11 && $n % 100 <= 13) return $n . 'th';
    return $n . ([1=>'st',2=>'nd',3=>'rd'][$n % 10] ?? 'th');
};
$winnerName = $teamplay ? ((int) $meta['current_streak'] === 1 ? t('Terroristen','Terrorists') : t('Counter-Terroristen','Counter-Terrorists')) : ($winner['name'] ?? '');
?>
<!DOCTYPE html>
<html lang="<?= $de ? 'de' : 'en' ?>">
    <head><meta charset="UTF-8"><title><?= h($motdTitle) ?></title>
        <style>
body{background:#000;color:#eee;font:12px Georgia,"Times New Roman",serif;text-align:center;margin:8px;letter-spacing:.5px}
h1{font-size:23px;font-weight:normal;color:#00cc00;margin:0 0 18px}
.winner{width:80%;margin:auto;border-top:1px solid <?= $winnerColor ?>;border-bottom:1px solid <?= $winnerColor ?>;color:<?= $winnerColor ?>;font-size:23px;line-height:21px;letter-spacing:1px}
p{margin:8px 0;line-height:1.05}.subtitle{margin:0 0 10px}.accent{color:#00cc00}.next{margin-top:34px}
        </style>
    </head>
    <body>
        <h1><?= h($motdTitle) ?></h1>
<?php if ($error): ?><p><?= h($error) ?></p><?php else: ?>
<div class="winner"><?= h($winnerName) ?></div>
        <p class="subtitle"><?= $teamplay ? t('ist das Gewinnerteam!','is the winning team!') : t('ist der Gewinner!','is the winner!') ?></p>
<?php if ($loser): ?><p><?= t('Der letzte','The final') ?> <span class="accent"><?= h($winner['weapon']) ?></span> <?= t('Kill wurde gemacht gegen','kill was made against') ?> <span style="color:<?= $color($loser) ?>"><?= h($loser['name']) ?></span>.</p><?php endif; ?>
<?php if ($statsMode > 0): ?>
<p><?= t('Das ist','This is') ?> <span style="color:<?= $winnerColor ?>"><?= h($winner['name']) ?></span>'s <span class="accent"><?= h($ordinal($winner['wins'])) ?></span> <?= t('Sieg','win') ?>.</p>
<?php if ($me): ?>
<p><?= t('Du hast insgesamt','You have a total of') ?> <span class="accent"><?= h($me['wins']) ?></span> <?= t('Siege.','wins.') ?></p>

<?php endif; ?>

<?php endif; ?>
<p class="next"><?= t('Die nächste Map wird','The next map will be') ?> <span class="accent"><?= h($meta['name']) ?></span><?= t(' sein.','.') ?></p>
<?php endif; ?></body>
    </html>
