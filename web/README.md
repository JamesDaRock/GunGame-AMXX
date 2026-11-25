# GunGame AMXX 3++ — Web dashboard

Originally developed by Avalanche; maintained by JamesDaRock.

## Installation / Einrichtung

1. Extract the package into your web directory / Archiv ins Webverzeichnis entpacken.
2. Copy `config.example.php` to `config.php`. Set your database connection, table names, `site_url` (absolute installation URL, including any subdirectory), title, language and timezone. / Eigene Datenbankdaten, URL, Titel, Sprache und Zeitzone eintragen.
3. Use PHP 8.2+ with PDO MySQL and an Apache configuration honoring the supplied `.htaccess` files. Use a read-only database account with SELECT access to the three configured tables. Allow outbound UDP to the configured public game servers if live queries are enabled.
4. Set directories to 755, files to 644 and config.php to 600 when PHP runs as the site owner. Ensure `cache/` is writable by PHP and inaccessible via HTTP.
5. Set `gameservers.allowed_servers` to your own IP:port list if desired. Optional HLstatsX and PsychoStats URLs belong in `gameservers.server_links`.
6. Add your own authorized map images under `assets/images/maps/cs16/` or `cscz/` (lowercase map name, jpg/png/webp). A neutral fallback is included.
7. Check rankings, credits, live server queries and the in-game MOTD on your installation. Never publish config.php or runtime cache files.

The default timezone is UTC. The example domain is not emitted as a canonical URL. The social preview is included. Bootstrap Icons CSS and WOFF/WOFF2 fonts are included.

## Distribution

See THIRD-PARTY-NOTICES.md and licenses/. The original GunGame and CSDM license obligations and upstream notices must still be verified before public distribution. This web package does not contain the game-server plugin or CSDM binaries. Map artwork and game logos are present in this preparation tree; redistribution rights must be verified before publication.
