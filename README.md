# GunGame AMXX 3++ — pur3gaming Edition

Independent continuation of Avalanche's GunGame AMXX 2.13c for Counter-Strike 1.6 and Condition Zero.

**Development target:** 3.0.0-rc3 (release candidate).

## Features

- Original configurable GunGame weapon progression, teamplay and deathmatch.
- Local-file and MySQL/MariaDB statistics in one plugin.
- Threaded SQL statistics and ranking operations, identity-aware caching and deferred handicap handling.
- UTF-8 database support, winner MOTD snapshots and PHP rankings.
- YaPB fake SteamID support and server diagnostics (`gg_info`, `gg_status`).

## Documentation

- [Complete GunGame manual](docs/readme-gungame.htm) — installation, CVARs, SQL schema, administration and FAQ.
- [CSDM documentation](docs/readme-csdm.htm).
- [Changelog](CHANGELOG.md).

Download the HTML manuals and open them locally in a browser for the intended layout. GitHub's file viewer is not a full HTML website.

## Installation and downloads

The current tree contains the RC3 source distribution. Compile gungame.sma locally with AMX Mod X 1.10 before installation. No compiled AMXX plugins are bundled. CSDM is optional and disabled by default. See the manual for requirements and configuration. Back up existing configuration and statistics before upgrading.

## History

The planned baseline is the original 2.13c standard and SQL distributions and `ggsql_example.php`. The 3++ continuation will be imported after these baselines are recorded. No historical Git commits are represented as original upstream commits.

## Credits and licensing

Original author: Avalanche. Maintainer: Pascal “JamesDaRock” Neumann (pur3gaming). See [credits](CREDITS.md) and [licensing status](LICENSE-STATUS.md). This is not an official Avalanche release.

## Support

See [CONTRIBUTING.md](CONTRIBUTING.md). Do not publish database credentials or private server configuration.
