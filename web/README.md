# GunGame AMXX 3++ — Web Dashboard

Based on the original GunGame AMXX project by Avalanche; independently developed and maintained for the pur3gaming Edition by JamesDaRock.

## Installation / Einrichtung

1. Extract the package into your web directory / Archiv ins Webverzeichnis entpacken.
2. Copy `config.example.php` to `config.php`. Set your database connection, table names, `site_url` (absolute installation URL, including any subdirectory), title, language and timezone. / Eigene Datenbankdaten, URL, Titel, Sprache und Zeitzone eintragen.
3. Use PHP 8.2+ with PDO MySQL and an Apache configuration honoring the supplied `.htaccess` files. Use a read-only database account with SELECT access to the three configured tables. Allow outbound UDP to the configured public game servers if live queries are enabled.
4. Set directories to 755, files to 644 and config.php to 600 when PHP runs as the site owner. Ensure `cache/` is writable by PHP and inaccessible via HTTP.
5. Set `gameservers.allowed_servers` to your own IP:port list if desired. Optional HLstatsX and PsychoStats URLs belong in `gameservers.server_links`.
6. Add your own authorized map images under `assets/images/maps/cs16/` or `cscz/` (lowercase map name, jpg/png/webp). A neutral fallback is included.
7. Check rankings, credits, live server queries and the in-game MOTD on your installation. Never publish config.php or runtime cache files.

The default timezone is UTC. The example domain is not emitted as a canonical URL. The social preview is included. Bootstrap Icons CSS and WOFF/WOFF2 fonts are included.

## Optional pretty URLs / URL-Rewriting

Add `'routing' => ['rewrite' => true],` to the existing `config.php` array. The default is `false`; existing query-string URLs remain supported. Enable Apache `mod_rewrite` and allow the supplied `.htaccess` directives (`AllowOverride All` or equivalent permitted directives). On Debian, an administrator can use `a2enmod rewrite`, `apache2ctl configtest` and `systemctl reload apache2`. No RewriteBase is required for ordinary document-root or subdirectory installations. Test `/credits/` relative to the installation directory before enabling on a production site.

Routes: `/`, `/servers/`, `/servers/{IP:port}/`, `/players/`, `/players/{authid}/`, `/leaderboard/`, `/hall-of-fame/`, `/matches/`, `/matches/{IP:port}/{timestamp}/`, `/credits/`. Identifiers are URL-encoded; registered filters use generic `/key/value/` path pairs; unsafe path values remain query parameters. Player profiles retain their server filter. MOTD and JSON API URLs are unchanged. Existing files and directories bypass rewriting. Unknown portal paths return 404. Disabling rewriting restores query-string links; previously generated pretty paths then return 404.

For Nginx, configure the installation location with `try_files $uri $uri/ /index.php?$query_string;` (replace `/index.php` with the installation-relative absolute path, e.g. `/gungame/index.php`). Keep PHP execution and access restrictions for configuration, cache and internal directories separately configured; Nginx does not read `.htaccess`.

## License

The independently developed GunGame AMXX 3++ web dashboard is licensed under the GNU General Public License, version 2 or any later version.

**SPDX license identifier:** GPL-2.0-or-later

Copyright (C) 2026 Pascal 'JamesDaRock' Neumann.

The license applies to original project-specific PHP, CSS and JavaScript code, including language files and the example configuration.

See [LICENSE](LICENSE) for the complete license text.

## Third-Party Components

| Component | License |
|---|---|
| Original web dashboard code | GPL-2.0-or-later |
| GameQ | LGPL-3.0-or-later |
| Bootstrap Icons 1.11.2 | MIT |

Third-party components retain their respective licenses and copyright notices.

The GPL-2.0-or-later license permits the use of a later GPL version where required for compatibility with GameQ.

Additional information:

- [Third-Party Notices](THIRD-PARTY-NOTICES.md)
- [Third-Party License Texts](licenses/)

## Distribution

The web dashboard can be distributed separately from the GunGame AMXX game-server plugin.

This web package does not contain the game-server plugin or CSDM binaries.

When redistributing the web dashboard:

- Comply with the applicable GPL and third-party license conditions.
- Include all applicable license texts and copyright notices.
- Preserve the original contributor acknowledgements.
- Exclude production configuration files and generated runtime cache data.
- Include only third-party assets for which the necessary redistribution rights exist.

## Project Repository

https://github.com/JamesDaRock/GunGame-AMXX

GunGame AMXX 3++ – pur3gaming Edition is an independent continuation of the original GunGame AMXX project and is not an official Avalanche release.

Counter-Strike and Counter-Strike: Condition Zero are games developed by Valve. All respective trademarks and third-party rights remain with their original owners.

## RC4 web preview

Use the existing config.php unchanged, or copy config.example.php for a fresh installation. Routes use index.php?view=leaderboard (pagination: p). Navigation is server-rendered, not AJAX. Player records remain server-specific. Matches show the latest stored snapshot only. MOTD and server API remain unchanged. PHP PDO MySQL is required for database testing.

### RC4 web preview 02
- Overview embeds the existing live server cards with expandable public A2S player lists.
- Latest saved match per server, using RC3 metadata: played map, duration and next map.
- Missing duration or player-query results are displayed as unavailable; no schema migration.
- Match details link to the latest result for that server (not a pinned historical match).
- Existing config.php remains compatible. PHP and JavaScript syntax checked; live database and UDP/browser integration require deployment testing.


### Optional portal background (RC4 Preview 23)

The bundled local WebP background is enabled by default. Existing config.php files
remain compatible. To customize or disable it, add the following inside the
existing `dashboard` array (do not replace your database configuration):

```php
    'theme'=>[
        'background_enabled'=>true,
        'background_image'=>'assets/images/background.webp',
        'background_overlay'=>0.40,
        'panel_opacity'=>0.88,
    ],
```

Set `background_enabled` to `false` for the original solid theme. Custom images
must be local files under assets/. Missing or invalid image paths fall back to
the original theme. Panel opacity is constrained to 0.75–1.0 for readability.
The standalone legacy in-game MOTD is not affected.

### Winner MOTD styles

Copy the optional `motd` block from config.example.php into your existing config.php. The global style is `extended` by default; `classic` and `simple` reproduce the classic layouts. Optional `servers` entries override style and title by exact IP:port. The existing external MOTD URL is unchanged. Classic reads the live all-time streak record from the configured streaks table; it is not a historical snapshot field. All four web styles require the existing SQL match snapshot. No plugin or schema changes are required.

#### Optional URL style override

Use `motd.php?style=classic`, `motd.php?style=simple` `motd.php?style=extended` or `motd.php?style=modern` with the existing match parameters (`s`, `i`, `t`, `l`) as needed. A valid URL style overrides the server-specific and global web settings. Invalid or missing styles fall through to the next valid setting, finally `extended`. Only the four fixed template names are accepted.

Each style owns its complete HTML and CSS in `motd/classic.php`, `motd/simple.php` `motd/extended.php` or `motd/modern.php`. Replace the entire motd directory when updating. Each style has a separate `_teamplay.php` template selected automatically from snapshot metadata. The root `motd.php` normalizes GunGame's appended `?s=` separator, so a configured URL ending in `motd.php?style=classic` also works with the existing plugin.

Modern uses standalone dark blue/orange templates, with team-grouped results in teamplay. Exact team levels and weapon sequence length are not stored in the match snapshot; no estimated team progress is displayed.

### Routing conventions
Set `site_url` to the public installation URL, or set `routing.base_path` explicitly (e.g. `/_beta/`, or `/` for the domain root). The public path is independent of Apache filesystem aliases. `includes/routing.php` defines route-specific filter defaults in `portal_filters()`; extend that contract when adding filters or routes, and use `portal_url()` / `url()` for links. GET filter forms submit ordinary query parameters; the PHP router normalizes them with a temporary redirect, even without JavaScript. For example `/players/server/127.0.0.1:27015/mode/team/`. New scalar filters need only a route contract entry and a validated reader. Explicit language selection remains a GET action to update the preference cookie. Player profiles retain server and mode when needed.

Public base paths are normalized (for example `_beta` and `/_beta/` are equivalent). Invalid configured paths fall back to the public site URL. Player return links use the destination list defaults; the default player list and its default sort are omitted, while non-default search, server, mode, page and page size are preserved. Profile links no longer require a fragment.
