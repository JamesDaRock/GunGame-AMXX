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
 * GunGame AMXX - Statistics Dashboard
 *
 * Read-only rankings, player profiles and latest match results.
 * Configuration: config.php | Translations: languages/*.php
 */

// File entry point and strict typing.
declare(strict_types = 1);
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/assets.php';

// Load the local installation configuration.
$configFile = dirname(__DIR__) . '/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    exit('Copy config.example.php to config.php and configure the database.');
}
$c = require $configFile;
portal_resolve();
$timezone = $c['timezone'] ?? 'UTC';
if (!is_string($timezone) || !in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC), true)) {
    $timezone = 'UTC';
}
date_default_timezone_set($timezone);
$liveEnabled = (bool)($c['gameservers']['enabled'] ?? true);

// Escape dynamic output for HTML contexts.
function h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Format integer statistics for the selected language.
function num($v): string {
    return number_format((int)$v, 0, '.', ($GLOBALS['lang'] ?? 'en') === 'de' ? '.' : ',');
}

// Format stored Unix timestamps for display.
function dt($v): string {
    return (int)$v > 0 ? date(($GLOBALS['lang'] ?? 'en') === 'de' ? 'd.m.Y H:i' : 'Y-m-d H:i', (int)$v) : '–';
}

// Validate configured SQL identifiers before interpolation.
function tableName($s): string {
    if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $s)) {
        throw new RuntimeException('Ungültiger Tabellenname');
    }
    return '`' . $s . '`';
}

// Dashboard configuration and language discovery.
$d = $c['dashboard'] ?? [];
$langs = [];
foreach (glob(dirname(__DIR__) . '/languages/*.php') as $file) {
    $code = basename($file, '.php');
    if (preg_match('/^[a-z]{2}$/D', $code)) {
        $langs[$code] = $file;
    }
}
// Discover native language names without changing the existing translation contract.
$languageNames = [];
$languageCatalogues = [];
foreach ($langs as $code => $file) {
    $catalogue = require $file;
    if (!is_array($catalogue)) continue;
    $languageNames[$code] = (string)($catalogue['language.native_name'] ?? strtoupper($code));
    $languageCatalogues[$code] = $catalogue;
}

// Persist only validated preferences; automatic mode follows browser priorities.
$requestedLanguage = $_GET['lang'] ?? null;
$languagePreference = $_COOKIE['gg_web_language'] ?? 'auto';
if (is_string($requestedLanguage) && ($requestedLanguage === 'auto' || isset($languageCatalogues[$requestedLanguage]))) {
    $languagePreference = $requestedLanguage;
    setcookie('gg_web_language', $languagePreference, ['expires'=>time()+31536000, 'path'=>function_exists('portal_base') ? portal_base() : '/', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'httponly'=>true, 'samesite'=>'Lax']);
}
if (!is_string($languagePreference) || ($languagePreference !== 'auto' && !isset($languageCatalogues[$languagePreference]))) {
    $languagePreference = 'auto';
}
portal_redirect_query();
$lang = $languagePreference;
if ($lang === 'auto') {
    $lang = $d['language'] ?? 'en';
    if (!is_string($lang) || !isset($languageCatalogues[$lang])) $lang = 'en';
    $browserLanguages = [];
    foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '') as $entry) {
        if (preg_match('/^\s*([a-z]{2})(?:-[a-z0-9-]+)?\s*(?:;\s*q=(0(?:\.\d+)?|1(?:\.0+)?))?\s*$/iD', $entry, $matches)) {
            $code = strtolower($matches[1]);
            $quality = isset($matches[2]) ? (float)$matches[2] : 1.0;
            if ($quality > 0 && isset($languageCatalogues[$code])) $browserLanguages[$code] = max($quality, $browserLanguages[$code] ?? 0);
        }
    }
    arsort($browserLanguages, SORT_NUMERIC);
    if ($browserLanguages) $lang = array_key_first($browserLanguages);
}
$translations = array_replace($languageCatalogues['en'], $languageCatalogues[$lang]);

// Look up and escape translated interface text.
function translate(string $key): string {
    global $translations;
    return h($translations[$key] ?? $key);
}

// Validate the optional custom logo path.
$logo = (string)($d['community_logo'] ?? '');
if (!preg_match('~^assets/[a-zA-Z0-9_./-]+\.(png|jpg|jpeg|webp|svg)$~iD', $logo) || strpos($logo, '..') !== false) {
    $logo = '';
}


// Optional local theme settings; existing installations need no config migration.
$theme = is_array($d['theme'] ?? null) ? $d['theme'] : [];
$themeImage = $theme['background_image'] ?? 'assets/images/gungame-3_background-1774x887.webp';
$themeEnabled = ($theme['background_enabled'] ?? true) === true
    && is_string($themeImage)
    && preg_match('~^assets/[a-zA-Z0-9_./-]+\.(webp|png|jpg|jpeg)$~iD', $themeImage)
    && strpos($themeImage, '..') === false
    && is_file(dirname(__DIR__) . '/' . $themeImage);
// Clamp numeric settings to preserve valid CSS and readable panels.
$themeOverlay = is_numeric($theme['background_overlay'] ?? null) ? max(0, min(1, (float)$theme['background_overlay'])) : 0.40;
$themeOpacity = is_numeric($theme['panel_opacity'] ?? null) ? max(0.75, min(1, (float)$theme['panel_opacity'])) : 0.88;
