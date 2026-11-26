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
 * GunGame AMXX 3++ - Winner MOTD
 *
 * Read-only viewer for completed match snapshots stored in the SQL database.
 *
 * Provides shared match data, language selection, dynamic template discovery,
 * server-specific configuration and automatic Solo/Teamplay detection.
 *
 * MOTD templates are independent presentation modules located in the
 * motd directory. New styles can be added without modifying this controller.
 */

declare(strict_types=1);

/* ==========================================================================
 * HTTP response headers
 * ========================================================================== */

header('Content-Type: text/html; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate');

/* ==========================================================================
 * Output escaping
 * ========================================================================== */

/**
 * Escape dynamic values before rendering HTML.
 *
 * @param mixed $value Value to escape.
 *
 * @return string HTML-safe value.
 */
function h($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/* ==========================================================================
 * Request parsing
 * ========================================================================== */

// GunGame appends ?s= even when the configured URL already has a query.
// Normalize only the known appended server parameter.

$query = $_SERVER['QUERY_STRING'] ?? '';

$query = preg_replace('/\?s=/', '&s=', $query, 1) ?? '';

parse_str($query, $params);

// Select the requested output language.

$de = ($params['l'] ?? 'en') === 'de';

/**
 * Return the text matching the selected language.
 *
 * @param string $deText German translation.
 * @param string $enText English translation.
 *
 * @return string Localized text.
 */
function t(string $deText, string $enText): string
{
    global $de;

    return $de ? $deText : $enText;
}

/* ==========================================================================
 * MOTD template discovery
 * ========================================================================== */

/**
 * Discover available MOTD styles.
 *
 * A valid style consists of two independent PHP templates:
 *
 *     styleName.php
 *     styleName_teamplay.php
 *
 * Only complete, readable template pairs located inside the MOTD directory
 * are registered. Symbolic links and invalid filenames are rejected.
 *
 * Style names retain their original capitalization.
 *
 * @param string $directory MOTD template directory.
 *
 * @return array<string, array<string, string>> Available template pairs.
 */
function discoverMotdStyles(string $directory): array
{
    $styles = [];

    $directory = realpath($directory);

    if ($directory === false || !is_dir($directory)) {
        return $styles;
    }

    $files = glob($directory . '/*.php') ?: [];

    foreach ($files as $file) {
        $name = pathinfo($file, PATHINFO_FILENAME);

        // Accept valid style identifiers only.

        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/D', $name)) {
            continue;
        }

        // Teamplay templates are registered through their Solo counterpart.

        if (str_ends_with(strtolower($name), '_teamplay')) {
            continue;
        }

        $soloFile = $directory . '/' . $name . '.php';

        $teamFile = $directory . '/' . $name . '_teamplay.php';

        // Require a complete and readable template pair.

        if (
            !is_file($soloFile)
            || !is_readable($soloFile)
            || is_link($soloFile)
            || !is_file($teamFile)
            || !is_readable($teamFile)
            || is_link($teamFile)
        ) {
            continue;
        }

        $soloPath = realpath($soloFile);
        $teamPath = realpath($teamFile);

        // Prevent templates outside the configured MOTD directory.

        if (
            $soloPath === false
            || $teamPath === false
            || dirname($soloPath) !== $directory
            || dirname($teamPath) !== $directory
        ) {
            continue;
        }

        $styles[$name] = [
            'solo'     => $soloPath,
            'teamplay' => $teamPath,
        ];
    }

    ksort($styles, SORT_STRING);

    return $styles;
}

/* ==========================================================================
 * MOTD style resolution
 * ========================================================================== */

/**
 * Resolve a requested style against the discovered template registry.
 *
 * Exact matches take precedence. Case-insensitive matches are accepted
 * only when they identify a single unambiguous style.
 *
 * @param mixed $candidate Requested style identifier.
 * @param array $styles Available template pairs.
 *
 * @return string|null Resolved style name or null.
 */
function resolveMotdStyle($candidate, array $styles): ?string
{
    if (!is_string($candidate)) {
        return null;
    }

    $candidate = trim($candidate);

    if (
        $candidate === ''
        || !preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/D', $candidate)
    ) {
        return null;
    }

    // Prefer the exact registered filename.

    if (isset($styles[$candidate])) {
        return $candidate;
    }

    // Allow case-insensitive selection without changing the filename.

    $matches = [];

    foreach (array_keys($styles) as $name) {
        if (strcasecmp($name, $candidate) === 0) {
            $matches[] = $name;
        }
    }

    return count($matches) === 1 ? $matches[0] : null;
}

/* ==========================================================================
 * Independent error presentation
 * ========================================================================== */

/**
 * Render a localized error without requiring an installed MOTD template.
 *
 * This fallback is independent of the selected presentation style.
 * Technical error details are written to the server log only.
 *
 * @param string $message Localized error message.
 *
 * @return void
 */
function renderMotdError(string $message): void
{
    global $de;

    ?>
    <!DOCTYPE html>
    <html lang="<?= $de ? 'de' : 'en' ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>GunGame AMXX 3++</title>

            <style>
                body {
                    margin: 0;
                    padding: 20px;
                    background: #000000;
                    color: #ffffff;
                    font-family: Arial, Helvetica, sans-serif;
                    font-size: 13px;
                    text-align: center;
                }

                h1 {
                    color: #ff9933;
                    font-size: 24px;
                    font-weight: normal;
                }

                .message {
                    margin: 20px auto;
                    padding: 15px;
                    max-width: 600px;
                    border: 1px solid #333333;
                    background: #151515;
                }

                .footer {
                    margin-top: 20px;
                    color: #888888;
                    font-size: 11px;
                }
            </style>
        </head>

        <body>
            <h1>GunGame AMXX 3++</h1>

            <div class="message">
                <?= h($message) ?>
            </div>

            <div class="footer">
                pur3gaming Edition
            </div>
        </body>
    </html>
    <?php
}

/* ============================================================================
 * Standalone design gallery and isolated template previews
 * ========================================================================== */

/** Render a gallery of all complete template pairs without database access. */
function renderMotdGallery(array $styles): void
{
    global $de;
    ?>
    <!DOCTYPE html>
    <html lang="<?= $de ? 'de' : 'en' ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>GunGame AMXX 3++ - MOTD Gallery</title>
        <style>
            body { background: #090e17; color: #e4edf5; font: 15px Arial, sans-serif; margin: 24px; }
            main { max-width: 1000px; margin: auto; }
            h1, a { color: #ffa34d; }
            section { border-top: 2px solid #354354; margin: 32px 0; }
            h3 { color: #a9bed1; }
            iframe { display: block; width: 100%; height: 640px; border: 1px solid #354354; background: #000; box-sizing: border-box; }
            p { line-height: 1.5; }
        </style>
    </head>
    <body>
        <main>
            <h1>GunGame AMXX 3++ - MOTD Gallery</h1>
            <p><?= h(t('Vorschau mit Beispieldaten', 'Preview with sample data')) ?> · <?= count($styles) ?> <?= h(t('Designs', 'styles')) ?></p>
            <p><a href="?gallery=1&amp;l=de">Deutsch</a> · <a href="?gallery=1&amp;l=en">English</a></p>
            <?php foreach ($styles as $name => $pair): ?>
                <section>
                    <h2><?= h($name) ?></h2>
                    <?php foreach (['solo', 'teamplay'] as $mode): ?>
                        <?php $url = '?' . http_build_query(['preview' => $name, 'mode' => $mode, 'l' => $de ? 'de' : 'en']); ?>
                        <h3><?= h(basename($pair[$mode])) ?> · <?= h(ucfirst($mode)) ?></h3>
                        <p><a href="<?= h($url) ?>" target="_blank" rel="noopener"><?= h(t('Einzeln öffnen', 'Open separately')) ?></a></p>
                        <iframe src="<?= h($url) ?>" title="<?= h($name . ' - ' . $mode) ?>" loading="lazy" sandbox></iframe>
                    <?php endforeach; ?>
                </section>
            <?php endforeach; ?>
            <?php if (!$styles): ?>
                <p><?= h(t('Keine vollständigen Designpaare gefunden.', 'No complete template pairs found.')) ?></p>
            <?php endif; ?>
        </main>
    </body>
    </html>
    <?php
}

/** Render one registered template with deterministic, fictional match data. */
function renderMotdPreview(string $template, string $style, bool $teamplay): void
{
    global $de;

    $error = '';
    $motdTitle = '[GUNGAME] AMXX';
    $timezone = 'UTC';
    $server = '192.0.2.1:27015';
    $timestamp = 1795726204;
    $id = 2;
    $players = [];
    foreach (['Padre', 'Hokage', 'phobos', 'Xero', 'Postal', 'Enchanter'] as $index => $name) {
        $team = $index % 2 + 1;
        $players[] = [
            'id' => $index + 1, 'name' => $name, 'team' => $team,
            'level' => 24 - $index * 2, 'weapon' => $index === 0 ? 'knife' : 'ak47',
            'flags' => ($index === 0 || ($teamplay && $team === 1)) ? 1 : 0,
            'wins' => 5 + $index, 'points' => 607 - $index * 35,
            'new_points' => 35 - $index * 5, 'current_streak' => $index === 0 ? 1 : 0,
            'record_streak' => 2, 'stats_position' => $index + 1,
            'teamtime_winning' => $team === 1 ? 682 : 0,
            'teamtime_losing' => $team === 2 ? 682 : 0,
            'serverip' => $server, 'timestamp' => $timestamp,
            'authid' => 'PREVIEW_' . ($index + 1),
        ];
    }
    $winner = $players[0];
    $loser = $players[1];
    $me = $players[1];
    $champion = ['name' => 'phobos', 'streak' => 4];
    $meta = [
        'id' => 0, 'flags' => (int) $teamplay, 'team' => 2, 'level' => 1,
        'wins' => 1, 'current_streak' => 1, 'stats_position' => 2,
        'points' => 1, 'new_points' => 2, 'authid' => 'gg_33_arena',
        'name' => 'de_dust', 'weapon' => '682', 'timestamp' => $timestamp,
        'serverip' => $server,
    ];

    define('GUNGAME_MOTD_RENDER', true);
    $bufferLevel = ob_get_level();
    ob_start();
    try {
        require $template;
        echo ob_get_clean();
    } catch (Throwable $e) {
        while (ob_get_level() > $bufferLevel) {
            ob_end_clean();
        }
        error_log('GunGame MOTD preview: ' . $e->getMessage());
        renderMotdError(t('Vorschau nicht verfügbar.', 'Preview unavailable.'));
    }
}

// Gallery and preview requests are isolated from the normal match viewer.
if (!isset($params["s"]) && !array_key_exists("preview", $params)) {
    renderMotdGallery(discoverMotdStyles(__DIR__ . '/motd'));
    exit;
}

if (!isset($params['s']) && array_key_exists('preview', $params)) {
    $previewStyles = discoverMotdStyles(__DIR__ . '/motd');
    $previewStyle = resolveMotdStyle($params['preview'], $previewStyles);
    $previewMode = $params['mode'] ?? 'solo';
    if ($previewStyle === null || !in_array($previewMode, ['solo', 'teamplay'], true)) {
        http_response_code(404);
        renderMotdError(t('Unbekannte Vorschau.', 'Unknown preview.'));
        exit;
    }
    renderMotdPreview($previewStyles[$previewStyle][$previewMode], $previewStyle, $previewMode === 'teamplay');
    exit;
}

/* ==========================================================================
 * Request parameters
 * ========================================================================== */

$id = filter_var(
    $params['i'] ?? null,
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1,
            'max_range' => 32,
        ],
    ]
) ?: 0;

$server = $params['s'] ?? '';

$timestamp = filter_var(
    $params['t'] ?? null,
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1,
            'max_range' => 4294967295,
        ],
    ]
);

/* ==========================================================================
 * Shared MOTD state
 * ========================================================================== */

$error = '';

$meta = null;
$players = [];
$me = null;
$winner = null;
$loser = null;
$champion = null;

$style = null;
$template = null;
$teamplay = false;

$motdTitle = '[GUNGAME] AMXX';
$timezone = 'UTC';

$availableStyles = [];

try {
    /* ======================================================================
     * Configuration loading
     * ====================================================================== */

    $configFile = __DIR__ . '/config.php';

    if (!is_file($configFile) || !is_readable($configFile)) {
        throw new RuntimeException('MOTD configuration file is unavailable');
    }

    $c = require $configFile;

    if (!is_array($c)) {
        throw new RuntimeException('Invalid MOTD configuration');
    }

    $settings = $c['motd'] ?? [];

    if (!is_array($settings)) {
        throw new RuntimeException('Invalid MOTD settings');
    }

    $serverSettings = $settings['servers'] ?? [];

    if (!is_array($serverSettings)) {
        throw new RuntimeException('Invalid MOTD server settings');
    }

    $override = is_string($server)
        ? ($serverSettings[$server] ?? [])
        : [];

    if (!is_array($override)) {
        throw new RuntimeException('Invalid server-specific MOTD settings');
    }

    /* ======================================================================
     * MOTD template discovery and selection
     * ====================================================================== */

    $availableStyles = discoverMotdStyles(__DIR__ . '/motd');

    if ($availableStyles === []) {
        throw new RuntimeException('No complete MOTD template pairs found');
    }

    // Style selection priority:
    //
    // 1. Explicit URL parameter.
    // 2. Server-specific configuration.
    // 3. Global configuration.
    //
    // Invalid candidates fall through to the next configured alternative.

    $styleCandidates = [
        $params['style'] ?? null,
        $override['style'] ?? null,
        $settings['style'] ?? null,
    ];

    foreach ($styleCandidates as $candidate) {
        $resolvedStyle = resolveMotdStyle(
            $candidate,
            $availableStyles
        );

        if ($resolvedStyle !== null) {
            $style = $resolvedStyle;

            break;
        }
    }

    // Do not select an arbitrary template when no valid style is configured.

    if ($style === null) {
        throw new RuntimeException('No valid MOTD style configured');
    }

    /* ======================================================================
     * MOTD title and timezone
     * ====================================================================== */

    $motdTitle = $override['title']
        ?? $settings['title']
        ?? '[GUNGAME] AMXX';

    if (!is_string($motdTitle) || trim($motdTitle) === '') {
        $motdTitle = '[GUNGAME] AMXX';
    }

    $timezone = $c['timezone'] ?? 'UTC';

    if (
        !is_string($timezone)
        || !in_array(
            $timezone,
            DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC),
            true
        )
    ) {
        $timezone = 'UTC';
    }

    /* ======================================================================
     * Server address validation
     * ====================================================================== */

    if (
        !is_string($server)
        || !preg_match('/^[0-9.]+:[0-9]{1,5}$/D', $server)
    ) {
        throw new RuntimeException('Invalid server');
    }

    [$ip, $port] = explode(':', $server, 2);

    if (
        !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
        || (int) $port < 1
        || (int) $port > 65535
    ) {
        throw new RuntimeException('Invalid server address');
    }

    /* ======================================================================
     * Match timestamp validation
     * ====================================================================== */

    if (isset($params['t']) && !$timestamp) {
        throw new RuntimeException('Invalid match timestamp');
    }

    /* ======================================================================
     * Database configuration validation
     * ====================================================================== */

    $tables = $c['tables'] ?? [];

    if (!is_array($tables)) {
        throw new RuntimeException('Invalid database table configuration');
    }

    $table = $tables['lastgame'] ?? '';

    // SQL identifiers cannot be bound as prepared statement parameters.
    // Validate configured table names before interpolating them into SQL.

    if (
        !is_string($table)
        || !preg_match('/^[a-zA-Z0-9_-]+$/D', $table)
    ) {
        throw new RuntimeException('Invalid match snapshot table');
    }

    foreach (['host', 'database', 'username', 'password'] as $key) {
        if (!isset($c[$key]) || !is_string($c[$key])) {
            throw new RuntimeException(
                'Invalid database configuration: ' . $key
            );
        }
    }

    /* ======================================================================
     * Database connection
     * ====================================================================== */

    $db = new PDO(
        sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $c['host'],
            (int) ($c['port'] ?? 3306),
            $c['database']
        ),
        $c['username'],
        $c['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    /* ======================================================================
     * Match snapshot retrieval
     * ====================================================================== */

    if ($timestamp) {
        // Retrieve the explicitly requested historical match.

        $stmt = $db->prepare(
            "SELECT *
             FROM `$table`
             WHERE serverip = ?
               AND timestamp = ?
             ORDER BY id
             LIMIT 33"
        );

        $stmt->execute([
            $server,
            $timestamp,
        ]);
    } else {
        // Retrieve the latest match containing a metadata row.

        $stmt = $db->prepare(
            "SELECT *
             FROM `$table`
             WHERE serverip = ?
               AND timestamp = (
                   SELECT MAX(timestamp)
                   FROM `$table`
                   WHERE serverip = ?
                     AND id = 0
               )
             ORDER BY id
             LIMIT 33"
        );

        $stmt->execute([
            $server,
            $server,
        ]);
    }

    $rows = $stmt->fetchAll();

    /* ======================================================================
     * Match metadata
     * ====================================================================== */

    // Row zero contains match metadata stored in overloaded player columns.
    // The column layout is defined by the GunGame SMA snapshot writer.

    foreach ($rows as $row) {
        if ((int) $row['id'] === 0) {
            $meta = $row;

            break;
        }
    }

    if (!$meta) {
        throw new RuntimeException('No match snapshot');
    }

    /* ======================================================================
     * Automatic match mode detection
     * ====================================================================== */

    // Match mode is determined by the stored snapshot, not by the server
    // address, requested style or current gameserver configuration.

    $teamplay = (int) ($meta['flags'] ?? 0) !== 0;

    /* ======================================================================
     * Player identification
     * ====================================================================== */

    foreach ($rows as $row) {
        // Exclude metadata and rows belonging to a different snapshot.

        if (
            (int) $row['id'] === 0
            || $row['timestamp'] !== $meta['timestamp']
            || $row['serverip'] !== $meta['serverip']
        ) {
            continue;
        }

        $players[] = $row;

        // Identify the player requesting the MOTD.

        if ((int) $row['id'] === $id) {
            $me = $row;
        }

        // Metadata points fields store the winner and last opponent IDs.

        if ((int) $row['id'] === (int) $meta['points']) {
            $winner = $row;
        }

        if ((int) $row['id'] === (int) $meta['new_points']) {
            $loser = $row;
        }
    }

    if (!$winner) {
        throw new RuntimeException('Winner missing from snapshot');
    }

    /* ======================================================================
     * Shared all-time streak record
     * ====================================================================== */

    // The global streak record is live and is not part of the historical
    // match snapshot.
    //
    // It is made available to every MOTD template. Individual templates
    // decide whether the record should be displayed.
    //
    // Failure to retrieve this optional statistic must not prevent the
    // remaining match results from being displayed.

    try {
        $streakTable = $tables['streaks'] ?? '';

        if (
            !is_string($streakTable)
            || !preg_match('/^[a-zA-Z0-9_-]+$/D', $streakTable)
        ) {
            throw new RuntimeException('Invalid streak table');
        }

        // Preserve the streak type used by the GunGame statistics backend.

        $type = (
            (int) $meta['level']
            && (int) $meta['flags']
        ) ? '1R' : '0R';

        $record = $db->prepare(
            "SELECT name, streak
             FROM `$streakTable`
             WHERE serverip = ?
               AND type = ?
             ORDER BY streak DESC
             LIMIT 1"
        );

        $record->execute([
            $server,
            $type,
        ]);

        $champion = $record->fetch() ?: null;
    } catch (Throwable $e) {
        error_log(
            'GunGame MOTD streak record: ' . $e->getMessage()
        );
    }

    /* ======================================================================
     * Player sorting
     * ====================================================================== */

    // Sort players by:
    //
    // 1. Level descending.
    // 2. Earned points descending.
    // 3. Player ID ascending.

    usort(
        $players,
        static fn($a, $b) =>
            ((int) $b['level'] <=> (int) $a['level'])
            ?: ((int) $b['new_points'] <=> (int) $a['new_points'])
            ?: ((int) $a['id'] <=> (int) $b['id'])
    );
} catch (Throwable $e) {
    /* ======================================================================
     * Match processing error handling
     * ====================================================================== */

    // Technical details are logged on the server and never exposed
    // directly to the requesting player.

    error_log('GunGame MOTD: ' . $e->getMessage());

    $error = t(
        'Keine vollständigen Spielergebnisse verfügbar.',
        'No complete match results available.'
    );
}

/* ==========================================================================
 * Final template resolution
 * ========================================================================== */

// If configuration or template discovery failed, render the independent
// error presentation instead of attempting to load a missing template.

if ($style === null || !isset($availableStyles[$style])) {
    renderMotdError(
        t(
            'Kein gültiges MOTD-Design verfügbar.',
            'No valid MOTD style available.'
        )
    );

    exit;
}

// Select the registered Solo or Teamplay template.

$templateMode = $teamplay ? 'teamplay' : 'solo';

$template = $availableStyles[$style][$templateMode] ?? null;

// Recheck the selected file before rendering.

if (
    !is_string($template)
    || !is_file($template)
    || !is_readable($template)
    || is_link($template)
) {
    error_log('GunGame MOTD: Selected template is unavailable');

    renderMotdError(
        t(
            'Das ausgewählte MOTD-Design ist nicht verfügbar.',
            'The selected MOTD style is unavailable.'
        )
    );

    exit;
}

/* ==========================================================================
 * Template rendering
 * ========================================================================== */

// Templates use this constant to prevent direct HTTP access.

define('GUNGAME_MOTD_RENDER', true);

// Buffer template output so rendering failures can be handled without
// exposing partially generated HTML to the requesting player.

$bufferLevel = ob_get_level();

ob_start();

try {
    require $template;

    $output = ob_get_clean();

    if ($output === false) {
        throw new RuntimeException('Unable to retrieve MOTD output');
    }

    echo $output;
} catch (Throwable $e) {
    // Remove incomplete template output.

    while (ob_get_level() > $bufferLevel) {
        ob_end_clean();
    }

    error_log(
        'GunGame MOTD template rendering: ' . $e->getMessage()
    );

    renderMotdError(
        t(
            'Das MOTD-Design konnte nicht geladen werden.',
            'Unable to render the MOTD style.'
        )
    );
}
