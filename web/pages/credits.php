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

/** Render the credits content module. */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
?>
<?php
$groups = [
    'credits_contributors' => '3volution, raa, BAILOPAN, VEN, SeNz0r',
    'credits_supporters' => '3volution, aligind4h0us3, arkshine, bmp, Curryking, Gunny, IdiotSavant, Mordekay, polakpolak, raa, Silver Dragon, Smileypt, Tomek Kalkowski, ToT | V!PER, Vm|Mayhem',
    'credits_translators' => 'arkshine, b!orn, commonbullet, Curryking, Deviance, D o o m, eD., e-N-z, Fr3ak0ut, godlike, harbu, iggy_bus, jopmako, jozzz, KylixMynxAltoLAG, MeRcyLeZZ, Min2liz, Mordekay, Morpheus759, rpm, SAMURAI16, Simbad, TEG, ToT | V!PER, trawiator, Twilight Suzuka, webpsiho',
];
?>
    <div class="section-title overview-title"><h1><?= translate('credits_title') ?></h1></div>
    <section class="panel credits-feature">
        <span class="eyebrow">GUNGAME AMXX 3++</span>
        <h2><?= translate('credits_intro') ?></h2>
        <p><?= translate('credits_project') ?></p>
        <a href="https://github.com/JamesDaRock/GunGame-AMXX">JamesDaRock · GitHub</a>
    </section>
    <section class="panel credits-original">
        <h2>Avalanche · GunGame AMXX 2.13c</h2>
        <p><?= translate('credits_original') ?></p>
        <?php foreach ($groups as $key => $names): ?>
            <h3><?= translate($key) ?></h3>
            <p class="muted"><?= h($names) ?></p>
        <?php endforeach; ?>
    </section>
    <div class="credits-grid">
        <section class="panel"><h2>CSDM 2.1.3d</h2><p><?= translate('credits_csdm') ?></p><a href="https://www.bailopan.net/csdm/">CSDM</a></section>
        <section class="panel"><h2>GameQ</h2><p><?= translate('credits_gameq') ?></p><a href="https://github.com/Austinb/GameQ">GitHub</a></section>
        <section class="panel"><h2>Bootstrap Icons</h2><p><?= translate('credits_icons') ?></p><a href="https://icons.getbootstrap.com/">Bootstrap Icons</a></section>
        <section class="panel"><h2><?= translate('credits_platform') ?></h2><p><?= translate('credits_platform_text') ?></p><a href="https://www.amxmodx.org/">AMX Mod X</a></section>
    </div>
    <section class="panel credits-original" id="licenses">
        <h2><?= translate('credits_licenses') ?></h2>
        <p><?= translate('credits_license_text') ?></p>
        <ul>
            <li><a href="<?= h(portal_path('THIRD-PARTY-NOTICES.md')) ?>"><?= translate('credits_notice') ?></a></li>
            <li>GameQ: <a href="<?= h(portal_path('licenses/LGPL-3.0.txt')) ?>">LGPL-3.0</a> · <a href="<?= h(portal_path('licenses/GPL-3.0.txt')) ?>">GPL-3.0</a></li>
            <li>Bootstrap Icons: <a href="<?= h(portal_path('licenses/Bootstrap-Icons-MIT.txt')) ?>">MIT</a></li>
        </ul>
    </section>

