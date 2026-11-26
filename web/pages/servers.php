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

/** Render the servers content module. */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
?>
<?php if ($liveEnabled): ?>
                    <!-- Live server status is loaded independently of the rankings. -->
                    <section id="gameservers" class="server-section" data-base="<?= h(portal_base()) ?>" data-rewrite="<?= portal_rewrite() ? '1' : '0' ?>" data-detail-url="<?= h(portal_url(['view'=>'server','server'=>'GGADDRESSPLACEHOLDER','lang'=>$languagePreference])) ?>" data-server="<?= $route === 'server' ? h($server) : '' ?>" data-fallback-image="<?= h(asset_url('assets/images/maps/noimage.jpg')) ?>" data-refresh="<?= max(15, min(3600, (int)($c['gameservers']['refresh_interval'] ?? 60))) ?>">
                        <div class="section-title">
                            <h2><?= translate('servers_title') ?></h2>
                            <span id="server-summary" class="muted" role="status"><?= translate('servers_loading') ?></span>
                        </div>
                        <?php if ($route === 'servers'): ?>
                        <!-- Aggregate only reachable servers; unavailable capacity is unknown. -->
                        <div id="server-totals" class="stats server-totals" hidden></div>
                        <?php endif; ?>
                        <div id="server-cards" class="server-grid"></div>
                    </section>
                    <script id="server-translations" type="application/json"><?= json_encode(array_filter($translations, fn($k) => str_starts_with($k, 'servers_'), ARRAY_FILTER_USE_KEY), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
                    <script src="<?= h(asset_url('assets/js/servers.js')) ?>" defer></script>
                <?php else: ?><p class="muted"><?= translate('server.disabled') ?></p><?php endif; ?>
                
