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

// RC4 retains the RC3 configuration contract.
return [
    'host'=>'127.0.0.1',
    'port'=>3306,
    'database'=>'amx',
    'username'=>'gungame_readonly',
    'password'=>'CHANGE_ME',
    'tables'=>[
        'stats'=>'gg_stats',
        'streaks'=>'gg_streaks',
        'lastgame'=>'gg_winmotd'
    ],

    'title' => 'GunGame AMXX 3++ | Rankings & Live Server Statistics',
    'site_url' => 'https://example.tld/',

    // Optional pretty URLs (Apache mod_rewrite or equivalent server routing).
    'routing' => [
        'rewrite' => false, 
        'base_path' => '/' // null: use site_url or request path
    ], 

    // Optional dashboard branding. Copy this block into your existing config.php.
    'dashboard'=>[
        'language'=>'en', // en or de
        'community_logo'=>'', // Optional local file, e.g. assets/logo.png
        // Optional global background; omitted settings use these defaults.
        'theme'=>[
            'background_enabled'=>true,
            'background_image'=>'assets/images/gungame-3_background-1774x887.webp',
            'background_overlay'=>0.55,
            'panel_opacity'=>0.92,
        ],
     ],

    // Public UDP server queries; optional explicit allowlist of IP:port entries.
    'gameservers' => [
        'enabled' => true,
        'allowed_servers' => [],
        // Optional per-server statistics URLs; empty means automatic rule detection.
        'server_links' => [
            /* '203.0.113.10:27015' => [
                'hlstatsx' => '',
                'psychostats' => ''
            ],*/
        ],
        'cache_ttl' => 60,
        'query_timeout' => 2,
        'refresh_interval' => 60,
    ],

    // Winner MOTD: classic (original), simple (compact), extended (scoreboard), modern (pur3gaming).
    // Priority: URL ?style=classic|simple|extended|modern, server override, global style, extended.
    // Teamplay is detected from the snapshot.
    // Invalid styles are ignored. Existing configurations remain compatible.
    'motd' => [
        'style' => 'extended',
        'title' => '[GUNGAME] AMXX',
        'servers' => [
            // '203.0.113.10:27015' => ['style' => 'classic'],
            // '203.0.113.10:27016' => ['style' => 'simple', 'title' => 'My GunGame'],
        ],
    ],

    'timezone'=>'UTC',
];
