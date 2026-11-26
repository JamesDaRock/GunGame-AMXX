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

declare(strict_types=1);

/** Version local assets by content, including deployments preserving timestamps. */
function asset_url(string $path): string
{
    static $versions = [];
    if (!preg_match('~^assets/[a-zA-Z0-9_./-]+$~D', $path) || strpos($path, '..') !== false) {
        return $path;
    }
    if (!array_key_exists($path, $versions)) {
        $file = dirname(__DIR__) . '/' . $path;
        $versions[$path] = is_file($file) && is_readable($file) ? hash_file('sha256', $file) : false;
    }
    if (function_exists('portal_path')) $url = portal_path($path); else $url = $path;
    return $versions[$path] ? $url . '?v=' . substr($versions[$path], 0, 16) : $url;
}
