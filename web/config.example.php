<?php
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

    // Optional dashboard branding. Copy this block into your existing config.php.
    'dashboard'=>[
        'language'=>'en', // en or de
        'community_logo'=>'', // Optional local file, e.g. assets/logo.png
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

    'timezone'=>'UTC',
];
