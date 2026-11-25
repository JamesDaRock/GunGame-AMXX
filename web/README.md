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
