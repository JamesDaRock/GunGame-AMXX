# Changelog

All notable changes to **GunGame AMXX 3++ – pur3gaming Edition** are documented here. This changelog follows the general structure of Keep a Changelog.

Original author: Avalanche. Maintainer of the continued development: JamesDaRock.

## [3.0.0-rc3] – 2026-11-25

Release candidate; public release and final package validation pending. Changes are relative to the original GunGame AMXX 2.13c distributions.

### Added

#### Core and statistics

- Unified runtime-selectable local file and MySQL/MariaDB statistics backends in a single plugin.
- Automatic creation of missing local statistics files, configurable statistics filenames and SQL table names.
- Support for dedicated GunGame SQL credentials and central AMXX SQL configuration, independent of the standard administration plugins or AMXBans.
- Native YaPB Fake SteamID integration through FakeMeta, with deferred authorization and individual bot statistics.
- Asynchronous player statistics and solo/team ranking prefetch through SQLx `SQL_ThreadQuery()`.
- Explicit statistics cache states for unloaded, loading, ready and failed data, with SteamID, UserID, slot and generation validation.
- Match-end statistics snapshots, asynchronous streak and points/wins persistence, and winner screen coordination with save completion.
- Asynchronous SQL Top 10 menu processing and deferred handicap ranking using the existing player cache (present in RC3 source; dedicated final regression results are incomplete).
- Bounded handicap retry and safe initial-level fallback, with protection against stale callbacks and repeated initialization (present in RC3 source).
- SQL connection failure detection, scheduled reconnect probes at 15-second intervals and asynchronous SQL backend initialization.
- Central project information and server console commands `gg_info` and `gg_status` for diagnostics.
- Runtime-configurable debug logging with global `gg_debug` and category controls for SQL, statistics, ranking, handicap, spawn, MOTD and cache. Levels 0–4; the higher applicable level is used. Regular error logging remains independent of debug settings.

#### Web statistics

- Responsive statistics dashboard with GunGame AMXX 3++ branding, highlighted top three players, detailed player profiles, search, sorting and filtering.
- Multi-server, solo/team and Counter-Strike 1.6/Condition Zero support.
- English and German localization with automatic discovery of language files.
- GameQ live server monitoring with database-based server discovery, online/offline status, server address, game and plugin version, player capacity, human/bot counts and utilization.
- Cached server queries and automatic refresh without RCON credentials.
- Previous/current/next map timeline, game-specific map images, fallback artwork and a synchronized map-time countdown.
- Steam connection links and optional HLstatsX/PsychoStats links, with per-server configuration and discovery from server rules.
- Dedicated credits page, multilingual metadata, canonical URLs, Open Graph and Twitter/X cards, social preview artwork and robots controls; API and WinMOTD pages marked noindex.
- Configurable website identity and timezone, with UTC as the default.

#### Documentation and distribution

- Expanded English installation, compilation, per-map configuration, CVAR, SQL and FAQ documentation.
- Separate historical 2.13c reference archive containing 110 CVARs, eight commands, SQL documentation and 17 original FAQ entries; 27 new FAQ entries for the continued development.
- GitHub README, contribution guidelines, credits, issue and pull request templates, Git attributes and ignore rules.
- Third-party notices and CSDM GPL-2.0 license text; outstanding redistribution questions documented separately.

### Changed

- Replaced routine synchronous player initialization and ranking queries with combined asynchronous prefetch and cache reuse.
- Moved timestamp updates to threaded SQL, protecting newer timestamps from delayed requests.
- Coordinated asynchronous game-end persistence, cache invalidation and winner MOTD requests.
- Configured synchronous and threaded SQL connections to use `utf8mb4`, with charset diagnostics and error handling.
- Centralized effective language selection, respecting AMXX client-language settings, server language and English fallback.
- Updated player names during timestamp-only saves, without requiring points gains or additional SQL queries; protected newer names from delayed updates.
- Preserved original YaPB-generated SteamIDs without prefixes or other modifications.
- Replaced temporary always-on diagnostics with runtime-controlled logging, disabled by default in the release configuration.
- Reorganized PHP code into API, includes, vendor, assets, languages and cache directories; avoided external CDN dependencies.
- Modernized dashboard layout, header, footer, logo, favicon, compact server cards and map previews.
- Improved winner MOTD match metadata, server association and solo/team distinction while retaining compatibility with the legacy game MOTD browser.
- Made CSDM optional and disabled its plugin list by default (`disabled-csdm.ini`); GunGame's internal deathmatch remains enabled by default. Documented the CSDM equipment-menu conflict.
- Prepared separate source release and repository layouts; preserved original 2.13c archives unchanged outside the current source tree.

### Fixed

- SQL initialization without standard AMXX administration plugins.
- Incorrect shared `BOT` identities, premature YaPB authorization, duplicate statistics requests and stale player cache reuse after identity or slot changes.
- Incorrect assignment of asynchronous results to replacement players through identity and generation checks.
- Corrupted international player names, including Cyrillic and Greek characters, and missing or empty external MOTD language parameters.
- Player names not being refreshed when points remained unchanged.
- Incorrect cross-server match associations and unreliable previous-map detection, using observed map changes and WinMOTD metadata.
- Web UTF-8 response headers, canonical URLs, credits configuration access, missing social preview artwork and unnecessary placeholder links.
- Missing fallback map image caused by an incorrect path.
- Broken internal documentation anchors in GunGame and CSDM documentation.

### Security and packaging

- Retained read-only web database access; added server address validation, optional server allowlist and external statistics URL validation.
- Protected the web cache directory against direct HTTP access and excluded generated cache data, local statistics, production configuration and temporary development files from the current package.
- Removed hardcoded community-specific website settings and production server addresses from the public-facing configuration; provided a neutral `config.example.php`.
- Removed compiled AMXX plugins from the current source distribution; retained source code and native CSDM modules. Documented local compilation and optional CSDM setup.
- Preserved original attribution and historical archives. No blanket new project license is asserted.
- Map artwork and Bootstrap Icons fonts were reintroduced into the final preparation tree after an earlier web-only packaging pass excluded them. Redistribution rights for artwork, logos, sounds and historical documentation remain subject to verification before publication.

### Compatibility

- Retains original gameplay, points and wins calculation, `gg_ignore_bots` behavior, solo/team statistics structure and local statistics support as design goals; no comprehensive regression guarantee is implied.
- Retains the existing SQL schema; no mandatory database schema migration is introduced by the documented changes.
- Target environment: Counter-Strike 1.6, Condition Zero, AMX Mod X 1.10, SQLx, MySQL/MariaDB, YaPB and optional CSDM 2.1.3d. Test coverage varies by component and game.

### Validation and performance

Development tests were performed on intermediate builds; they are not a claim that every test was repeated on the final RC3 package.

- Tested local storage, automatic file creation, central and dedicated SQL configuration, invalid/missing SQL settings, persistence across map changes and restarts, human and YaPB statistics, bot replacement, slot reuse, timestamp updates and cache reuse.
- In a nine-player initialization test, reduced observed synchronous SQL executions from 12 to 3 (75%), eliminating nine synchronous ranking queries; all nine threaded prefetch callbacks succeeded.
- Observed asynchronous match-end save durations of 382, 401 and 378 ms in separate nine-player development tests; no synchronous SQL execution was logged during the documented 382 ms and 378 ms save sequences.
- Observed average prefetch queue time around 15 ms; verified asynchronous streak/statistics persistence, cache invalidation and winner MOTD sequencing.
- Verified UTF-8 connection initialization, international name handling, name refresh without points gains, and runtime debug behavior in development tests.
- Tested SQL outage detection and scheduled reconnect attempts; SQL initialization and player caches recovered after a map change. A later build compiled and completed a map-change test with nine successful ranking prefetch callbacks and no observed escaping, SQL or runtime errors.
- Verified 164 PHP files with syntax checks before final packaging changes; checked ZIP integrity, historical archive identity, documentation anchors and absence of generated JSON cache and compiled AMXX files in the current package.
- Compared 125 registered CVARs with 122 active configuration entries; the remaining entries are the internal version CVAR and two development fault/delay controls.

### Known limitations

- The complete development log for work section 5 is unavailable. Recovered test evidence and the RC3 source support the entries above, but some intermediate changes and their test coverage cannot be reconstructed fully.
- Some synchronous SQL operations remain, including a connection used for escaping; complete elimination of blocking database operations is not claimed.
- Same-map recovery following a real SQL outage, interrupted saves, shutdown handling and lossless persistence under all failure conditions are not fully verified. Exactly-once transactional writes are not guaranteed.
- Full teamplay, Condition Zero, local-backend and third-party compatibility regression coverage remains incomplete.
- Existing SQL text columns may still use `utf8mb3`; connection charset changes alone do not enable four-byte characters in those columns. Previously truncated names cannot be recovered automatically.
- Debug levels 3 and 4 had substantially overlapping output in the documented runtime-debug tests.
- No dedicated server FPS or frametime benchmark has established a measured improvement.
- Final source package compilation and installation validation, and redistribution/license verification, remain pending.

## [2.13c] – Historical baseline

- Original GunGame AMXX by Avalanche, distributed in separate standard and SQL-enabled packages.
- Original `gg_213c_full.zip`, `gg_213c_full_sql.zip` and `ggsql_example.php` retained unchanged as historical references.
- This entry identifies the baseline for continued development; it does not reconstruct the original upstream release history.
