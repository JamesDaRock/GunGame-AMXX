<?php
/**
 * Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Central portal URL generation and path routing.
 */
declare(strict_types=1);

function portal_valid_base(string $path): bool {
    return (bool) preg_match('~^/(?:[A-Za-z0-9._\~-]+/)*[A-Za-z0-9._\~-]*$~D', $path) && !preg_match('~(?:^|/)\.\.?(?:/|$)~', $path);
}

/** Public path, never an internal Apache alias when a public URL is configured. */
function portal_base(): string {
    $configured = $GLOBALS['c']['routing']['base_path'] ?? null;
    if (is_string($configured)) {
        $configured = '/' . trim($configured, '/') . '/';
        if ($configured === '//') $configured = '/';
        if (!portal_valid_base($configured)) $configured = null;
    }
    if (!is_string($configured)) {
        $url = $GLOBALS['c']['site_url'] ?? '';
        $configured = is_string($url) && filter_var($url, FILTER_VALIDATE_URL)
            && in_array(parse_url($url, PHP_URL_SCHEME), ['http','https'], true)
            && parse_url($url, PHP_URL_HOST) !== 'example.tld'
            ? (parse_url($url, PHP_URL_PATH) ?: '/') : null;
    }
    if (is_string($configured) && portal_valid_base($configured)) {
        return rtrim($configured, '/') . '/';
    }
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $base = rtrim(dirname($script), '/') . '/';
    $request = explode('?', $_SERVER['REQUEST_URI'] ?? '', 2)[0];
    // Match a suffix of the script directory against the public request path.
    if (str_starts_with($request, '/')) {
        while ($base !== '/') {
            if (str_starts_with($request, $base)) return $base;
            $base = substr($base, strpos($base, '/', 1));
        }
        return '/';
    }
    return $base;
}
function portal_path(string $path): string { return portal_base() . ltrim($path, '/'); }
function portal_rewrite(): bool { return ($GLOBALS['c']['routing']['rewrite'] ?? false) === true; }
function portal_routes(): array {
    return ['overview'=>'', 'servers'=>'servers', 'players'=>'players', 'leaderboard'=>'leaderboard', 'top10'=>'hall-of-fame', 'matches'=>'matches', 'credits'=>'credits'];
}
/** Central route contract: accepted filters and their actual reader defaults. */
function portal_filters(): array {
    return [
        'overview'=>['mode'=>'solo','sort'=>'wins','server'=>''],
        'servers'=>[], 'server'=>['server'=>''], 'credits'=>[],
        'players'=>['mode'=>'all','sort'=>'name','server'=>'','q'=>'','p'=>1,'per_page'=>25],
        'leaderboard'=>['mode'=>'all','sort'=>'wins','server'=>'','q'=>'','p'=>1,'per_page'=>25],
        'top10'=>['mode'=>'all','server'=>''],
        'matches'=>['mode'=>'all','server'=>'','q'=>'','p'=>1,'map'=>'','from'=>'','to'=>'','limit'=>25],
        'match'=>['server'=>'','ts'=>''],
        'player'=>['player'=>'','server'=>'','mode'=>'solo','return_view'=>'players','return_server'=>'','return_mode'=>'all','return_page'=>1,'return_limit'=>25,'return_sort'=>'','return_q'=>''],
    ];
}
/** Restore list state using the destination route's defaults, not profile filters. */
function portal_return_params(array $params): array {
    $view = ($params['return_view'] ?? '') === 'leaderboard' ? 'leaderboard' : 'players';
    $result = ['view'=>$view];
    foreach (['server'=>'server','mode'=>'mode','p'=>'page','per_page'=>'limit','sort'=>'sort','q'=>'q'] as $key=>$suffix) {
        $value = $params['return_'.$suffix] ?? null;
        $result[$key] = is_scalar($value) && $value !== '' ? $value : portal_filters()[$view][$key];
    }
    if (isset($params['lang']) && is_string($params['lang'])) $result['lang'] = $params['lang'];
    return $result;
}
function portal_clean(array $params): array {
    $view = $params['view'] ?? 'overview';
    $defaults = portal_filters()[$view] ?? [];
    $defaults = ['view'=>null,'lang'=>'auto'] + $defaults;
    if ($view === 'player') {
        $return = portal_return_params($params);
        $defaults['return_sort'] = portal_filters()[$return['view']]['sort'];
        $params['return_view'] = $return['view'];
    }
    $result = [];
    foreach ($defaults as $key=>$default) {
        $value = $params[$key] ?? null;
        if (!is_scalar($value) || $value === '' || ($default !== null && (string)$value === (string)$default)) continue;
        $result[$key] = $value;
    }
    return $result;
}
function portal_segment(string $value): string { return str_replace('%3A', ':', rawurlencode($value)); }
function portal_url(array $params): string {
    $view = $params['view'] ?? 'overview';
    $params = portal_clean($params);
    $path = 'index.php';
    if (portal_rewrite()) {
        $routes = portal_routes();
        if (isset($routes[$view])) { $path = $routes[$view] === '' ? '' : $routes[$view] . '/'; unset($params['view']); if ($view === 'overview') unset($params['player']); }
        elseif ($view === 'player' && !empty($params['player']) && portal_safe_segment((string)$params['player'])) {
            $path = 'players/' . portal_segment((string)$params['player']) . '/'; unset($params['view'], $params['player']);
        } elseif ($view === 'server' && !empty($params['server']) && portal_safe_segment((string)$params['server'])) {
            $path = 'servers/' . portal_segment((string)$params['server']) . '/'; unset($params['view'], $params['server']);
        } elseif ($view === 'match' && !empty($params['server']) && portal_safe_segment((string)$params['server']) && ctype_digit((string)($params['ts'] ?? ''))) {
            $path = 'matches/' . portal_segment((string)$params['server']) . '/' . $params['ts'] . '/'; unset($params['view'], $params['server'], $params['ts']);
        }
    }
    if (portal_rewrite() && $path !== 'index.php') {
        foreach ($params as $key => $value) {
            if (!portal_safe_segment((string)$value)) continue;
            $path .= $key . '/' . portal_segment((string)$value) . '/';
            unset($params[$key]);
        }
    }
    // Keep colons readable only in server identifiers, never arbitrary search text.
    $pairs = [];
    foreach ($params as $key=>$value) {
        $encoded = rawurlencode((string)$value);
        if (in_array($key, ['server','return_server'], true)) $encoded = str_replace('%3A', ':', $encoded);
        $pairs[] = rawurlencode($key) . '=' . $encoded;
    }
    $query = implode('&', $pairs);
    return portal_path($path) . ($query === '' ? '' : '?' . $query);
}
/** Avoid encoded slashes, dot segments and control characters in Apache paths. */
function portal_safe_segment(string $value): bool {
    return $value !== '' && $value !== '.' && $value !== '..'
        && strlen($value) <= 512 && !preg_match('~[/\\\\\x00-\x1f\x7f%?#]~', $value);
}
/** Resolve route identities followed by generic registered key/value pairs. */
function portal_resolve(): void {
    $path = explode('?', $_SERVER['REQUEST_URI'] ?? '', 2)[0];
    $base = portal_base();
    if (!str_starts_with($path, $base)) return;
    $relative = substr($path, strlen($base));
    if (in_array($relative, ['index.php', 'credits.php'], true)) return;
    if ($relative === '' && (isset($_GET['view']) || isset($_GET['player']))) return; // legacy root query
    $_GET['view'] = 'not_found';
    if (!portal_rewrite()) { if ($relative === '') $_GET['view']='overview'; return; }
    $parts = $relative === '' ? [] : array_map('rawurldecode', explode('/', rtrim($relative, '/')));
    foreach ($parts as $part) if (!portal_safe_segment($part)) return;
    $view = 'overview'; $identity = [];
    if ($parts && ($route = array_search($parts[0], portal_routes(), true)) !== false) {
        $view = $route; array_shift($parts);
    }
    if (in_array($view, ['players','servers'], true) && count($parts)%2 === 1) {
        $view = $view === 'players' ? 'player' : 'server';
        $identity[$view] = array_shift($parts);
    } elseif ($view === 'matches' && count($parts)>=2 && ctype_digit($parts[1]) && !array_key_exists($parts[0], portal_filters()['matches'])) {
        $view='match'; $identity=['server'=>array_shift($parts),'ts'=>array_shift($parts)];
    }
    if (count($parts)%2 !== 0) return;
    $allowed = ['lang'=>'auto'] + (portal_filters()[$view] ?? []);
    $decoded = $identity;
    while ($parts) {
        $key=array_shift($parts); $value=array_shift($parts);
        if (!array_key_exists($key,$allowed) || array_key_exists($key,$decoded)) return;
        $decoded[$key]=$value;
    }
    // Query fallback for unsafe path values; path values win except explicit language change.
    $query = array_intersect_key($_GET,$allowed);
    $query = array_filter($query,'is_scalar');
    $_GET = ['view'=>$view] + $decoded + $query;
    if (isset($query['lang'])) $_GET['lang']=$query['lang'];
}
/** Normalize native GET forms and legacy query links without redirecting unsafe fallback values. */
function portal_redirect_query(): void {
    if (!portal_rewrite() || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET' || empty($_SERVER['QUERY_STRING'])) return;
    $view=$_GET['view'] ?? (isset($_GET['player'])?'player':'overview');
    if (!is_string($view) || !isset(portal_filters()[$view])) return;
    $target=portal_url(['view'=>$view]+$_GET);
    if (!str_contains($target, '/index.php?') && $target !== ($_SERVER['REQUEST_URI'] ?? '') && str_starts_with($target,'/') && !str_starts_with($target,'//')) {
        header('Cache-Control: private, no-cache, must-revalidate');
        header('Location: '.$target,true,302); exit;
    }
}
