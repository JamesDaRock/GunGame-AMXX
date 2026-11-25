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

/** Public JSON endpoint; targets are discovered from SQL, never from HTTP input. */
declare(strict_types=1);
header('X-Robots-Tag: noindex, nofollow');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
try {
    $c = require dirname(__DIR__) . '/config.php';
    $settings = $c['gameservers'] ?? [];
    if (!($settings['enabled'] ?? true)) {
        echo '{"servers":[],"updated":0}';
        exit;
    }
    require_once dirname(__DIR__) . '/includes/gameserver.php';
    $table = $c['tables']['stats'];
    if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $table)) {
        throw new RuntimeException('Invalid table name.');
    }
    $db = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], $c['port'] ?? 3306, $c['database']), $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $addresses = $db->query('SELECT DISTINCT serverip FROM `' . $table . '` ORDER BY serverip')->fetchAll(PDO::FETCH_COLUMN);
    $data = ggCached(ggTargets($addresses, $settings), $settings);
    // Enrich the cached live status with the latest completed match per server.
    // Observed map changes take precedence; SQL is the initial/fallback source.
    try {
        $lastgame = $c['tables']['lastgame'];
        if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $lastgame)) {
            throw new RuntimeException('Invalid lastgame table name.');
        }
        $match = $db->prepare('SELECT authid FROM `' . $lastgame . '` WHERE id=0 AND serverip=:server ORDER BY timestamp DESC LIMIT 1');
        foreach ($data['servers'] as &$card) {
            if (empty($card['online']) || !empty($card['previous_map'])) {
                continue;
            }
            $match->execute(['server' => $card['address']]);
            $played = ggMapName($match->fetchColumn() ?: '');
            if ($played !== '' && $played !== $card['map']) {
                $card['previous_map'] = $played;
                $card['previous_image'] = ggMapImage($card['game'], $played);
                $card['previous_source'] = 'match';
            }
        }
        unset($card);
    } catch (Throwable $e) {
        error_log('GunGame previous match: ' . $e->getMessage());
    }
    // Supply cache age without relying on the visitor's wall clock.
    $data['age'] = max(0, time() - (int)$data['updated']);
    unset($data['key']);
    echo json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    error_log('GunGame server status: ' . $e->getMessage());
    http_response_code(503);
    echo '{"error":true}';
}
