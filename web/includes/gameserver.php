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

/** Read-only public GoldSrc queries and shared, atomic status cache. */
declare(strict_types=1);

function ggTargets(array $addresses, array $settings): array
{
    $targets = [];
    $allowed = $settings['allowed_servers'] ?? [];
    foreach ($addresses as $address) {
        if (!is_string($address) || !preg_match('/^([0-9.]+):([0-9]{1,5})$/D', $address, $m)) {
            continue;
        }
        if (!filter_var($m[1], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_GLOBAL_RANGE)
            || (int)$m[2] < 1 || (int)$m[2] > 65535) {
            continue;
        }
        if ($allowed && !in_array($address, $allowed, true)) {
            continue;
        }
        $targets[$address] = $address;
    }
    return array_slice(array_values($targets), 0, 32);
}

function ggMapImage(string $game, string $map): string
{
    $base = 'assets/images/maps/';
    $game = $game === 'cscz' ? 'cscz' : 'cs16';
    if (preg_match('/^[a-z0-9_-]+$/D', $map)) {
        foreach (array_unique([$game, 'cs16']) as $folder) {
            foreach (['webp', 'jpg', 'png'] as $ext) {
                $path = $base . $folder . '/' . $map . '.' . $ext;
                if (is_file(dirname(__DIR__) . '/' . $path)) {
                    return $path;
                }
            }
        }
    }
    return $base . 'noimage.jpg';
}

/** Accept only map identifiers, never arbitrary file paths. */
function ggMapName($value): string
{
    $name = strtolower(trim((string)$value));
    return preg_match('/^[a-z0-9_-]{1,128}$/D', $name) ? $name : '';
}

/** Only explicit HTTP(S) statistics URLs are usable as public links. */
function ggStatsUrl($value): string
{
    if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_URL)) return '';
    $parts = parse_url($value);
    return in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
        && !empty($parts['host']) && !isset($parts['user'], $parts['pass'])
        && !isset($parts['user']) && !isset($parts['pass']) ? $value : '';
}

/** Recognize explicit rule names; a version alone does not provide a link. */
function ggStatsLinks(array $rules, array $configured = []): array
{
    $rules = array_change_key_case($rules, CASE_LOWER);
    $links = [];
    foreach (['hlstatsx' => ['hlstatsx', 'hlstats'], 'psychostats' => ['psychostats']] as $type => $prefixes) {
        $url = ggStatsUrl($configured[$type] ?? '');
        $detected = $url !== '';
        foreach ($prefixes as $prefix) {
            foreach (['', '_version', '_url', '_weburl', '_web_url'] as $suffix) {
                $value = $rules[$prefix . $suffix] ?? '';
                if (is_scalar($value) && !in_array(strtolower(trim((string)$value)), ['', '0', 'off', 'false', 'disabled'], true)) {
                    $detected = true;
                    if ($url === '') $url = ggStatsUrl($value);
                }
            }
        }
        $links[$type] = ['detected' => $detected, 'url' => $url];
    }
    return $links;
}

/** Allowlist public GunGame rules; absent or invalid values remain unknown. */
function ggPublicRules(array $rules): array
{
    $rules = array_change_key_case($rules, CASE_LOWER);
    $public = [];
    foreach ([
        'gg_enabled',
        'gg_map_iterations',
        'gg_ignore_bots',
        'gg_stats_mode',
        'gg_stats_split',
        'gg_stats_winbonus',
        'gg_dm',
        'gg_dm_sp_time',
        'gg_dm_sp_mode',
        'gg_dm_spawn_random',
        'gg_dm_spawn_delay',
        'gg_block_objectives',
        'gg_teamplay',
        'gg_teamplay_knife_mod',
        'gg_teamplay_nade_mod',
        'gg_teamplay_timeratio',
        'gg_ff_auto',
        'gg_max_lvl',
        'gg_triple_on',
        'gg_turbo',
        'gg_knife_pro',
        'gg_knife_elite',
        'gg_suicide_penalty',
        'gg_pickup_others',
        'gg_handicap_on',
        'gg_top10_handicap',
        'gg_warmup_timer_setting',
        'gg_kills_per_lvl',
        'gg_kills_botmod',
        'gg_give_armor',
        'gg_give_helmet',
        'gg_tk_penalty',
        'gg_awp_oneshot',
        'gg_bots_skipnade',
        'gg_bots_knifeable',
    ] as $key) {
        $value = $rules[$key] ?? null;
        if (is_scalar($value) && preg_match('/^-?\d{1,9}(?:\.\d{1,9})?$/D', trim((string)$value))) {
            $public[$key] = trim((string)$value);
        }
    }
    return $public;
}

function ggQuery(array $targets, array $settings, array $previous = []): array
{
    require_once dirname(__DIR__) . '/vendor/GameQ/Autoloader.php';
    $query = new \GameQ\GameQ();
    $query->setOption('timeout', max(1, min(5, (int)($settings['query_timeout'] ?? 2))));
    foreach ($targets as $address) {
        $query->addServer(['type' => 'cs16', 'host' => $address, 'id' => $address]);
    }
    $results = $targets ? $query->process() : [];
    $cards = [];
    foreach ($targets as $address) {
        $r = $results[$address] ?? [];
        $online = !empty($r['gq_online']) && isset($r['hostname'], $r['map']);
        $game = strtolower((string)($r['game_dir'] ?? '')) === 'czero' ? 'cscz' : 'cs16';
        $map = ggMapName($r['map'] ?? '');
        $last = $previous[$address] ?? [];
        $lastMap = ggMapName($last['last_observed_map'] ?? $last['map'] ?? '');
        $previousMap = ggMapName($last['previous_map'] ?? '');
        if ($online && $map !== '' && $lastMap !== '' && $lastMap !== $map) {
            $previousMap = $lastMap;
        }
        $nextMap = $online ? ggMapName($r['amx_nextmap'] ?? $r['nextmap'] ?? '') : '';
        if (in_array($nextMap, ['unknown', 'pending', 'none', 'vote'], true)) {
            $nextMap = '';
        }
        $remaining = trim((string)($r['amx_timeleft'] ?? ''));
        if (!preg_match('/^\d{1,3}:[0-5]\d$/D', $remaining)) {
            $remaining = '';
        }
        $players = max(0, (int)($r['num_players'] ?? 0));
        $bots = isset($r['num_bots']) ? max(0, min($players, (int)$r['num_bots'])) : null;
        // Expose only public A2S player fields, never internal query metadata.
        $connectedPlayers = null;
        if ($online && !empty($r['players']) && is_array($r['players'])) {
            $connectedPlayers = [];
            foreach ($r['players'] as $player) {
                $connectedPlayers[] = [
                    'name' => (string)($player['name'] ?? $player['gq_name'] ?? ''),
                    'score' => isset($player['score']) ? (int)$player['score'] : null,
                    'seconds' => isset($player['time']) && is_numeric($player['time']) ? max(0, (int)$player['time']) : null,
                ];
            }
        } elseif ($online && $players === 0) {
            $connectedPlayers = [];
        }
        $cards[] = [
            'connected_players' => $connectedPlayers,
            'gungame_rules' => $online ? ggPublicRules($r) : [],
            'address' => $address, 'online' => $online,
            'stats_links' => ggStatsLinks($online ? $r : [], $settings['server_links'][$address] ?? []),
            'name' => $online ? (string)$r['hostname'] : $address,
            'game' => $online ? $game : '', 'map' => $online ? $map : '',
            'image' => ggMapImage($game, $online ? $map : ''),
            'players' => $players, 'max' => max(0, (int)($r['max_players'] ?? 0)),
            'bots' => $bots, 'version' => $online ? (string)($r['gg_version'] ?? '') : '',
             'password' => !empty($r['password']),
            'last_observed_map' => $online && $map !== '' ? $map : $lastMap,
            'previous_map' => $previousMap,
            'previous_image' => ggMapImage($game, $previousMap),
            'next_map' => $nextMap,
            'next_image' => ggMapImage($game, $nextMap),
            'timeleft' => $online ? $remaining : '', 
        ];
    }
    return $cards;
}

function ggCached(array $targets, array $settings): array
{
    $file = dirname(__DIR__) . '/cache/servers.json';
    $ttl = max(15, min(3600, (int)($settings['cache_ttl'] ?? 60)));
    $key = hash('sha256', json_encode($targets));
    $read = static function () use ($file, $key): ?array {
        $data = is_file($file) ? json_decode((string)file_get_contents($file), true) : null;
        return is_array($data) && ($data['key'] ?? '') === $key ? $data : null;
    };
    $old = $read();
    if ($old && time() - $old['updated'] < $ttl && !array_filter($old['servers'], static fn($card) => !array_key_exists('connected_players', $card) || !array_key_exists('gungame_rules', $card))) {
        return $old;
    }
    $lock = fopen($file . '.lock', 'c');
    if (!$lock) {
        throw new RuntimeException('Cache directory is not writable.');
    }
    try {
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            return $old ?? ['updated' => 0, 'servers' => [], 'pending' => true];
        }
        $old = $read();
        if ($old && time() - $old['updated'] < $ttl && !array_filter($old['servers'], static fn($card) => !array_key_exists('connected_players', $card) || !array_key_exists('gungame_rules', $card))) {
            return $old;
        }
        // Preserve observed map history even when the discovered server set changes.
        $history = is_file($file) ? json_decode((string)file_get_contents($file), true) : [];
        $previous = array_column($history['servers'] ?? [], null, 'address');
        $cards = ggQuery($targets, $settings, $previous);
        $data = ['key' => $key, 'updated' => time(), 'servers' => $cards];
        $json = json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        $tmp = tempnam(dirname($file), 'gg-');
        if ($tmp === false || file_put_contents($tmp, $json) === false || !rename($tmp, $file)) {
            if ($tmp && is_file($tmp)) { unlink($tmp); }
            throw new RuntimeException('Cache write failed.');
        }
        return $data;
    } finally {
        fclose($lock);
    }
}
