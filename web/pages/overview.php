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

/** Render the overview content module. */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
?>
<!-- Overview heading and summary statistics -->
                <div class="section-title overview-title">
                    <h1><?= translate('navigation.overview') ?></h1>
                    <nav class="overview-mode" aria-label="<?= translate('filter.mode') ?>">
                        <?php foreach (['solo','team'] as $gameMode): ?>
                            <a href="<?= h(url(['mode'=>$gameMode,'p'=>1])) ?>" <?= $mode === $gameMode ? 'aria-current="page"' : '' ?>><?= translate('mode.'.$gameMode) ?></a>
                        <?php endforeach; ?>
                    </nav><p class="muted"><?= translate('overview.subtitle') ?></p>
                </div>
                <section class="stats" aria-label="<?= translate('overview.statistics') ?>">
                    <article>
                        <i class="bi bi-people stat-icon" aria-hidden="true">
                        </i>
                        <small><?= translate('overview.player_entries') ?></small>
                        <strong><?= num($summary['players']) ?></strong>
                    </article>
                    <article>
                        <i class="bi bi-trophy stat-icon" aria-hidden="true">
                        </i>
                        <small><?= translate('overview.total_wins') ?></small>
                        <strong><?= num($summary['wins']) ?></strong>
                    </article>
                    <article>
                        <i class="bi bi-bullseye stat-icon" aria-hidden="true">
                        </i>
                        <small><?= translate('overview.total_points') ?></small>
                        <strong><?= num($summary['points']) ?></strong>
                    </article>
                    <article>
                        <i class="bi bi-fire stat-icon" aria-hidden="true">
                        </i>
                        <small><?= translate('overview.record') ?></small>
                        <strong><?= num($record['streak']??0) ?></strong>
                        <small><?= isset($record['name'])?h($record['name']):translate('overview.no_record') ?></small>
                    </article>
                </section>
<?php if ($page===1 && $q==='' && $players): ?>
                    <!-- Top three players -->
                    <section class="podium-section" aria-label="<?= translate('ranking.top_players') ?>">
                        <div class="section-title">
                            <h2><?= translate('ranking.top_players') ?></h2>
                            <span class="muted">
                                <?= translate('ranking.by') ?>
                                <?= $sort==='points'?translate('ranking.points'):translate('ranking.wins') ?>
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
                                        <?= translate('ranking.place') ?>
                                        <?= $i+1 ?>
                                    </span>
                                    <a class="player" href="<?= h(url(['view'=>'player','player'=>$p['authid'],'server'=>$p['serverip']])) ?>">
                                        <?= h($p['name']) ?>
                                    </a>
                                    <strong><?= num($p[$sort]) ?></strong>
                                    <small><?= $sort==='points'?translate('stats.points'):translate('stats.wins') ?></small>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
                <p><a href="<?= h(url(['view'=>'top10'])) ?>"><?= translate('navigation.top10') ?> →</a></p>
                <?php if ($liveEnabled) require __DIR__ . '/servers.php'; ?>
                <?php require __DIR__ . '/../includes/match_cards.php'; ?>
