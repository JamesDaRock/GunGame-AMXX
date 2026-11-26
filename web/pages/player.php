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

/** Render the player content module. */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
?>
<?php if ($profile): ?>
                    <!-- Player profile -->
                    <section class="panel" id="profile">
                        <div class="section-title">
                            <h2><?= translate('player.profile') ?></h2>
                            <a href="<?= h(portal_url(portal_return_params(array_merge($_GET, ['lang'=>$languagePreference])))) ?>">
                                <?= translate('player.close') ?>
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
                                <small><?= translate('player.solo_wins') ?></small>
                                <strong><?= num($profile['wins']) ?></strong>
                            </div>
                            <div>
                                <small><?= translate('player.solo_points') ?></small>
                                <strong><?= num($profile['points']) ?></strong>
                            </div>
                            <div>
                                <small><?= translate('player.solo_streak') ?></small>
                                <strong><?= num($profile['streak']) ?></strong>
                            </div>
                            <div>
                                <small><?= translate('player.team_wins') ?></small>
                                <strong><?= num($profile['wins_tp']) ?></strong>
                            </div>
                            <div>
                                <small><?= translate('player.team_points') ?></small>
                                <strong><?= num($profile['points_tp']) ?></strong>
                            </div>
                            <div>
                                <small><?= translate('player.team_streak') ?></small>
                                <strong><?= num($profile['streak_tp']) ?></strong>
                            </div>
                        </div>
                        <p class="muted">
                            <?= translate('player.last_saved') ?>
                            <?= dt($profile['timestamp']) ?>
                        </p>
                    </section>
                <?php else: ?><p class="panel"><?= translate('player.not_found') ?></p><?php endif; ?>
                
