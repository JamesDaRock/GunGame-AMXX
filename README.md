# GunGame AMXX 3++ — pur3gaming Edition

[![Release](https://img.shields.io/badge/release-3.0.0--rc3-blue)](https://github.com/JamesDaRock/GunGame-AMXX/releases/tag/v3.0.0-rc3)
[![Issues](https://img.shields.io/github/issues/JamesDaRock/GunGame-AMXX)](https://github.com/JamesDaRock/GunGame-AMXX/issues)
[![Pull Requests](https://img.shields.io/github/issues-pr/JamesDaRock/GunGame-AMXX)](https://github.com/JamesDaRock/GunGame-AMXX/pulls)
[![Discussions](https://img.shields.io/badge/GitHub-Discussions-blue)](https://github.com/JamesDaRock/GunGame-AMXX/discussions)

Independent continuation of Avalanche's GunGame AMXX 2.13c for Counter-Strike 1.6 and Condition Zero.

**Current release candidate:** 3.0.0-rc3.

## Table of contents

- [Features](#features)
- [Installation and downloads](#installation-and-downloads)
- [Documentation](#documentation)
- [Repository structure](#repository-structure)
- [Project history](#project-history)
- [Community and support](#community-and-support)
- [Credits and licensing](#credits-and-licensing)

## Features

- Original configurable GunGame weapon progression, teamplay and deathmatch.
- Local-file and MySQL/MariaDB statistics in one plugin.
- Threaded SQL statistics and ranking operations, identity-aware caching and deferred handicap handling.
- UTF-8 database support, winner MOTD snapshots and PHP rankings.
- YaPB fake SteamID support and server diagnostics (`gg_info`, `gg_status`).

## Installation and downloads

See the [GitHub Releases](https://github.com/JamesDaRock/GunGame-AMXX/releases) page and the [RC3 source tag](https://github.com/JamesDaRock/GunGame-AMXX/tree/v3.0.0-rc3).

1. Back up your existing configuration and statistics before upgrading.
2. Obtain the source distribution for the intended version.
3. Compile `gungame.sma` locally with AMX Mod X 1.10. The RC3 source distribution does not bundle compiled AMXX plugins.
4. Install and configure GunGame according to the [complete manual](docs/readme-gungame.htm).
5. Configure the statistics backend and optional components as required. CSDM is optional and disabled by default.
6. Test the installation before deploying it to a production server.

RC3 is a release candidate, not a final stable release.

## Documentation

- [Complete GunGame manual](docs/readme-gungame.htm) — installation, CVARs, SQL schema, administration and FAQ.
- [CSDM documentation](docs/readme-csdm.htm).
- [Web interface documentation](web/README.md).
- [Changelog](CHANGELOG.md).

Download the HTML manuals and open them locally in a browser for the intended layout. GitHub's file viewer is not a full HTML website.

## Repository structure

| Branch | Purpose |
| --- | --- |
| [`main`](https://github.com/JamesDaRock/GunGame-AMXX/tree/main) | Default branch and release-oriented project state. |
| [`develop`](https://github.com/JamesDaRock/GunGame-AMXX/tree/develop) | Active development and target for external pull requests. |
| [`legacy/2.13c`](https://github.com/JamesDaRock/GunGame-AMXX/tree/legacy/2.13c) | Historical standard distribution. |
| [`legacy/2.13c-sql`](https://github.com/JamesDaRock/GunGame-AMXX/tree/legacy/2.13c-sql) | Historical SQL distribution. |

## Project history

The repository records the original GunGame AMXX 2.13c standard and SQL distributions, including `ggsql_example.php`, as historical baselines. The GunGame AMXX 3++ continuation has been imported after these baselines.

These historical imports are not original upstream Git commits.

| Version | Source tag | Status |
| --- | --- | --- |
| 2.13c | [`v2.13c`](https://github.com/JamesDaRock/GunGame-AMXX/tree/v2.13c) | Historical archive |
| 2.13c SQL | [`v2.13c-sql`](https://github.com/JamesDaRock/GunGame-AMXX/tree/v2.13c-sql) | Historical archive |
| 3.0.0-rc3 | [`v3.0.0-rc3`](https://github.com/JamesDaRock/GunGame-AMXX/tree/v3.0.0-rc3) | Release candidate |

Historical versions are preserved for reference and do not receive active support or security updates.

## Community and support

English and German are welcome in project communication.

Use [GitHub Discussions](https://github.com/JamesDaRock/GunGame-AMXX/discussions) for installation, configuration, SQL, web interface and compatibility questions. Use the [issue forms](https://github.com/JamesDaRock/GunGame-AMXX/issues/new/choose) for reproducible bugs and concrete feature requests.

| Document | Purpose |
| --- | --- |
| [SUPPORT.md](SUPPORT.md) | Support channels, supported versions and diagnostic information. |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Development workflow, contribution requirements and pull requests. |
| [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) | Community conduct and moderation. |
| [SECURITY.md](SECURITY.md) | Supported versions and confidential vulnerability reporting. |

External code contributions should target `develop`. Review the contribution guidelines before opening a pull request.

Do not publish database credentials, private server configuration or security vulnerabilities in public discussions or issues. Follow the confidential reporting instructions in the relevant community document.

## Credits and licensing

**Original author:** Avalanche.

**Maintainer:** Pascal “JamesDaRock” Neumann (pur3gaming).

This is an independent continuation, not an official Avalanche release.

See [credits](CREDITS.md) and [licensing status](LICENSE-STATUS.md) for attribution and licensing information. No blanket project license is asserted here.
