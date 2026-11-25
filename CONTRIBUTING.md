# Contributing to GunGame AMXX 3++ — pur3gaming Edition

English | [Deutsch](#deutsch)

## 1. Introduction

GunGame AMXX 3++ is an independent continuation of Avalanche's GunGame AMXX 2.13c for Counter-Strike 1.6 and Condition Zero. Community contributions are welcome and subject to maintainer review and the applicable rights and licensing conditions.

## 2. Community and communication

English and German are welcome in [Discussions](https://github.com/JamesDaRock/GunGame-AMXX/discussions) and [Issues](https://github.com/JamesDaRock/GunGame-AMXX/issues). Search existing topics first. Use Discussions for support and exploratory ideas, and the appropriate issue form for reproducible bugs or specific feature requests. Treat others respectfully.

## 3. Reporting bugs

Include the game, GunGame version, operating system, AMX Mod X, engine, GameDLL and Metamod versions, statistics backend, relevant plugins and configuration, reproduction steps, expected and actual behavior, and relevant logs. Where available, include `gg_info` and `gg_status` output. Remove credentials and private information.

## 4. Feature requests

Explain the problem, proposed solution, benefits, configurability and potential gameplay, performance and compatibility impact. Discuss larger proposals before implementation.

## 5. Repository structure

| Branch | Purpose |
| --- | --- |
| `main` | Approved project state and release preparation |
| `develop` | Integration and ongoing development |
| `legacy/2.13c` | Historical standard distribution |
| `legacy/2.13c-sql` | Historical SQL distribution |

Do not modify historical branches or existing release tags.

## 6. Development workflow

1. Fork the repository or use an authorized working branch.
2. Start from the current `develop` branch.
3. Create a dedicated branch for the change.
4. Implement, test and document the change.
5. Open a pull request targeting `develop` and complete the template.
6. Address maintainer feedback.

Maintainers coordinate integration into `main` and releases. Do not submit external contributions directly to `main` or historical branches.

## 7. Branch naming

| Prefix | Purpose | Example |
| --- | --- | --- |
| `feature/` | New functionality | `feature/player-ranking` |
| `fix/` | Bug fixes | `fix/sql-reconnect` |
| `hotfix/` | Urgent fixes | `hotfix/server-crash` |
| `refactor/` | Refactoring | `refactor/sql-cache` |
| `docs/` | Documentation | `docs/installation` |
| `test/` | Temporary tests | `test/handicap` |
| `chore/` | Maintenance | `chore/github-templates` |
| `release/` | Release preparation | `release/3.0.0` |
| `legacy/` | Historical archives | `legacy/2.13c` |

Release and legacy branches are reserved for maintainer-managed work.

## 8. Commit messages

Use concise, descriptive English commit messages, such as `Fix SQL ranking cache`, `Update installation documentation` or `Improve handicap error handling`. Keep commits focused. Conventional Commits syntax is not required.

## 9. Coding guidelines

- Use English for new source code identifiers and comments; preserve existing interfaces and notices.
- Follow the existing formatting style of the affected source file.
- Avoid unrelated formatting changes and unnecessary refactoring.
- Keep changes focused and consider compatibility, performance and maintainability.
- Do not submit compiled AMXX plugins (`.amxx`).

## 10. Testing requirements

Testing is mandatory and must match the affected components. Compile changed Pawn sources using the documented AMX Mod X toolchain. Test relevant runtime behavior, including local or SQL statistics, rankings, handicap, gameplay, YaPB or CSDM integration where affected. For web changes, test affected PHP functionality and configuration. Report the environment, results, limitations and any tests not performed. Do not claim unperformed tests as successful.

## 11. Documentation requirements

Functional changes require corresponding documentation updates. Update affected installation instructions, CVAR references, SQL schema or migration guidance, configuration examples and manuals as applicable. Explain breaking changes and upgrade requirements.

## 12. Dependencies and architecture

New dependencies and major architectural changes require prior maintainer approval. Explain their purpose, compatibility, maintenance implications and licensing before implementation.

## 13. Pull requests

Submit one coherent change per pull request. Target `develop`, complete the bilingual pull request template, link relevant issues and provide test evidence and documentation impact. Do not include unrelated changes, credentials or private server data.

## 14. Review and approval

Only project maintainers may authorize integration. Address review feedback before merging. The configured GitHub pull request merge method is squash merging; eligible head branches are automatically deleted after merging. Maintainers coordinate release integration separately. Repository rules may not yet be technically enforced while the repository is private.

## 15. Security and privacy

Do not disclose vulnerabilities publicly in issues or discussions. Contact a maintainer privately to arrange a confidential reporting channel. Remove passwords, tokens, database credentials and private player or server data from submissions and logs.

## 16. Copyright and licensing

Preserve original copyright, author and upstream notices. Submit only material you have the necessary rights to contribute. Disclose third-party material and its applicable license or permission. See [LICENSE-STATUS.md](LICENSE-STATUS.md) and [CREDITS.md](CREDITS.md). This document does not establish a blanket project license, contributor license agreement or copyright assignment. Contributions with unresolved rights must not be integrated until clarified.

---

# Deutsch

## 1. Einleitung

GunGame AMXX 3++ ist eine unabhängige Weiterentwicklung von Avalanches GunGame AMXX 2.13c für Counter-Strike 1.6 und Condition Zero. Community-Beiträge sind willkommen und unterliegen der Prüfung durch Maintainer sowie den geltenden Rechte- und Lizenzbedingungen.

## 2. Community und Kommunikation

Englische und deutsche Beiträge sind in [Discussions](https://github.com/JamesDaRock/GunGame-AMXX/discussions) und [Issues](https://github.com/JamesDaRock/GunGame-AMXX/issues) willkommen. Durchsuche zunächst bestehende Themen. Nutze Discussions für Support und erste Ideen sowie das passende Issue-Formular für reproduzierbare Fehler oder konkrete Funktionsvorschläge. Behandle andere respektvoll.

## 3. Fehler melden

Gib Spiel, GunGame-Version, Betriebssystem, AMX-Mod-X-, Engine-, GameDLL- und Metamod-Version, Statistik-Backend, relevante Plugins und Konfiguration, Reproduktionsschritte, erwartetes und tatsächliches Verhalten sowie relevante Logs an. Ergänze, sofern verfügbar, die Ausgaben von `gg_info` und `gg_status`. Entferne Zugangsdaten und private Informationen.

## 4. Funktionsvorschläge

Beschreibe Problem, Lösungsvorschlag, Nutzen, Konfigurierbarkeit und mögliche Auswirkungen auf Gameplay, Performance und Kompatibilität. Stimme größere Vorschläge vor der Umsetzung ab.

## 5. Repository-Struktur

| Branch | Zweck |
| --- | --- |
| `main` | Freigegebener Projektstand und Release-Vorbereitung |
| `develop` | Integration und laufende Weiterentwicklung |
| `legacy/2.13c` | Historische Standarddistribution |
| `legacy/2.13c-sql` | Historische SQL-Distribution |

Historische Branches und bestehende Release-Tags dürfen nicht verändert werden.

## 6. Entwicklungsablauf

1. Erstelle einen Fork oder nutze einen autorisierten Arbeitsbranch.
2. Gehe vom aktuellen Branch `develop` aus.
3. Erstelle einen eigenen Branch für die Änderung.
4. Implementiere, teste und dokumentiere die Änderung.
5. Erstelle einen Pull Request gegen `develop` und fülle die Vorlage aus.
6. Bearbeite die Rückmeldungen der Maintainer.

Maintainer koordinieren die Übernahme nach `main` und die Releases. Externe Beiträge dürfen nicht direkt gegen `main` oder historische Branches eingereicht werden.

## 7. Branch-Benennung

| Präfix | Zweck | Beispiel |
| --- | --- | --- |
| `feature/` | Neue Funktionen | `feature/player-ranking` |
| `fix/` | Fehlerbehebungen | `fix/sql-reconnect` |
| `hotfix/` | Dringende Fehlerbehebungen | `hotfix/server-crash` |
| `refactor/` | Quellcodeüberarbeitung | `refactor/sql-cache` |
| `docs/` | Dokumentation | `docs/installation` |
| `test/` | Temporäre Tests | `test/handicap` |
| `chore/` | Wartungsarbeiten | `chore/github-templates` |
| `release/` | Release-Vorbereitung | `release/3.0.0` |
| `legacy/` | Historische Archive | `legacy/2.13c` |

Release- und Legacy-Branches sind der Verwaltung durch Maintainer vorbehalten.

## 8. Commit-Nachrichten

Verwende kurze, beschreibende englische Commit-Nachrichten, beispielsweise `Fix SQL ranking cache`, `Update installation documentation` oder `Improve handicap error handling`. Beschränke Commits auf zusammengehörige Änderungen. Conventional Commits sind nicht vorgeschrieben.

## 9. Quellcode-Richtlinien

- Verwende Englisch für neue Quellcode-Bezeichner und Kommentare; erhalte bestehende Schnittstellen und Hinweise.
- Behalte den vorhandenen Formatierungsstil der betroffenen Datei bei.
- Vermeide themenfremde Formatierungsänderungen und unnötige Überarbeitungen.
- Beschränke Änderungen auf ihren Zweck und berücksichtige Kompatibilität, Performance und Wartbarkeit.
- Reiche keine kompilierten AMXX-Plugins (`.amxx`) ein.

## 10. Testanforderungen

Tests sind verpflichtend und müssen den betroffenen Komponenten entsprechen. Kompiliere geänderte Pawn-Quellen mit der dokumentierten AMX-Mod-X-Toolchain. Prüfe betroffenes Laufzeitverhalten, einschließlich lokaler oder SQL-Statistiken, Ranglisten, Handicap, Gameplay, YaPB- oder CSDM-Integration, soweit relevant. Teste bei Webänderungen die betroffenen PHP-Funktionen und Konfigurationen. Dokumentiere Umgebung, Ergebnisse, Einschränkungen und nicht durchgeführte Tests. Gib nicht durchgeführte Tests nicht als erfolgreich an.

## 11. Dokumentationsanforderungen

Funktionsänderungen erfordern entsprechende Dokumentationsänderungen. Aktualisiere betroffene Installationsanleitungen, CVAR-Referenzen, SQL-Schemata oder Migrationshinweise, Konfigurationsbeispiele und Handbücher. Erläutere inkompatible Änderungen und Upgrade-Anforderungen.

## 12. Abhängigkeiten und Architektur

Neue Abhängigkeiten und größere Architekturänderungen erfordern die vorherige Zustimmung eines Maintainers. Erläutere Zweck, Kompatibilität, Wartungsaufwand und Lizenzierung vor der Umsetzung.

## 13. Pull Requests

Reiche eine zusammengehörige Änderung pro Pull Request ein. Wähle `develop` als Ziel, fülle die zweisprachige Vorlage aus, verlinke relevante Issues und dokumentiere Tests sowie Auswirkungen auf die Dokumentation. Füge keine themenfremden Änderungen, Zugangsdaten oder privaten Serverdaten hinzu.

## 14. Prüfung und Freigabe

Ausschließlich Projekt-Maintainer dürfen die Übernahme autorisieren. Bearbeite Rückmeldungen vor dem Merge. Die konfigurierte GitHub-Merge-Methode für Pull Requests ist Squash and merge; dafür geeignete Quellbranches werden nach dem Merge automatisch gelöscht. Maintainer koordinieren die Release-Integration separat. Solange das Repository privat ist, werden Repository-Regeln möglicherweise noch nicht technisch durchgesetzt.

## 15. Sicherheit und Datenschutz

Veröffentliche Sicherheitslücken nicht in öffentlichen Issues oder Discussions. Kontaktiere einen Maintainer privat, um einen vertraulichen Meldeweg abzustimmen. Entferne Passwörter, Tokens, Datenbankzugangsdaten und private Spieler- oder Serverdaten aus Beiträgen und Logs.

## 16. Urheberrecht und Lizenzierung

Erhalte ursprüngliche Copyright-, Entwickler- und Upstream-Hinweise. Reiche ausschließlich Material ein, für das du die erforderlichen Beitragsrechte besitzt. Kennzeichne Drittmaterial und die zugehörige Lizenz oder Erlaubnis. Siehe [LICENSE-STATUS.md](LICENSE-STATUS.md) und [CREDITS.md](CREDITS.md). Dieses Dokument legt keine pauschale Projektlizenz, Contributor License Agreement oder Urheberrechtsübertragung fest. Beiträge mit ungeklärten Rechten dürfen erst nach Klärung übernommen werden.
