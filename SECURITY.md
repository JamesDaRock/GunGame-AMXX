# Security Policy / Sicherheitsrichtlinien

GunGame AMXX 3++ — pur3gaming Edition

[English](#english) | [Deutsch](#deutsch)

## English

### Supported versions

| Version | Security support |
| --- | --- |
| Current GunGame AMXX 3++ development (`develop`) | Supported |
| Current GunGame AMXX 3++ releases, including the current release candidate | Supported |
| Historical GunGame AMXX 2.13c | Archive only; no security updates |
| Historical GunGame AMXX 2.13c SQL | Archive only; no security updates |

Support does not imply a guarantee of fixes or a fixed response time. Historical distributions are preserved for reference, not maintained as secure production releases.

### Security scope

This policy covers security vulnerabilities in the GunGame AMXX plugin, SQL statistics and database communication, PHP web interface, configuration, CSDM integration, bundled dependencies and official release files.

Examples include unauthorized access, privilege bypass, SQL injection, cross-site scripting, exposure of confidential information and compromised release files. Ordinary bugs, performance issues and compatibility problems should be reported using the appropriate GitHub issue forms, provided they do not disclose security-sensitive information.

### Reporting a vulnerability

Report suspected vulnerabilities privately to the project maintainer using either of the following channels:

- [GitHub Private Vulnerability Reporting](https://github.com/JamesDaRock/GunGame-AMXX/security/advisories/new), when enabled and available.
- Email: [support@rootutilities.com](mailto:support@rootutilities.com).

If private reporting on GitHub is unavailable, use email. Do not disclose unresolved vulnerabilities in public issues, discussions, pull requests or comments.

Where possible, include:

- Affected component and exact version or commit.
- Relevant environment, including game, AMX Mod X, engine, GameDLL, database or PHP versions as applicable.
- Description, potential impact and steps to reproduce the vulnerability safely.
- Relevant sanitized logs, configuration excerpts and diagnostic output (`gg_info`, `gg_status`), where applicable.
- Suggested mitigation or fix, if available.

Never include passwords, tokens, database credentials, private player information or unnecessary production data. Test only systems you own or are authorized to test. Avoid disrupting services or accessing other people's data.

### Handling and coordinated disclosure

The maintainer will review reports as promptly as availability permits. No fixed response or resolution deadline is promised. Additional information may be requested to reproduce and assess the issue.

Please coordinate disclosure with the maintainer and allow time for investigation and remediation. The timing and content of public disclosure should be agreed following remediation. Reports will be handled confidentially to the extent reasonably possible.

### Security updates and recognition

Security fixes will be distributed through [official GitHub releases](https://github.com/JamesDaRock/GunGame-AMXX/releases) and, where appropriate, [GitHub Security Advisories](https://github.com/JamesDaRock/GunGame-AMXX/security/advisories).

Reporters may be credited on request and with their consent. Identifying information will not be published without consent.

---

## Deutsch

### Unterstützte Versionen

| Version | Sicherheitsunterstützung |
| --- | --- |
| Aktuelle GunGame AMXX 3++-Entwicklung (`develop`) | Unterstützt |
| Aktuelle GunGame AMXX 3++-Releases einschließlich des aktuellen Release Candidate | Unterstützt |
| Historisches GunGame AMXX 2.13c | Ausschließlich Archiv; keine Sicherheitsupdates |
| Historisches GunGame AMXX 2.13c SQL | Ausschließlich Archiv; keine Sicherheitsupdates |

Die Unterstützung beinhaltet keine Garantie für Fehlerbehebungen oder feste Reaktionszeiten. Historische Distributionen werden als Referenz erhalten und nicht als sichere Produktivversionen gewartet.

### Sicherheitsumfang

Diese Richtlinien gelten für Sicherheitslücken im GunGame-AMXX-Plugin, in der SQL-Statistik und Datenbankkommunikation, im PHP-Webinterface, in der Konfiguration, in der CSDM-Integration, in eingebundenen Abhängigkeiten und in offiziellen Release-Dateien.

Beispiele sind unberechtigte Zugriffe, Rechteumgehungen, SQL-Injection, Cross-Site-Scripting, Offenlegung vertraulicher Informationen und kompromittierte Release-Dateien. Normale Programmfehler, Performance- und Kompatibilitätsprobleme sollen über die entsprechenden GitHub-Issue-Formulare gemeldet werden, sofern dabei keine sicherheitsrelevanten Informationen offengelegt werden.

### Sicherheitslücken melden

Melde vermutete Sicherheitslücken vertraulich an den Projekt-Maintainer über einen der folgenden Wege:

- [GitHub Private Vulnerability Reporting](https://github.com/JamesDaRock/GunGame-AMXX/security/advisories/new), sofern aktiviert und verfügbar.
- E-Mail: [support@rootutilities.com](mailto:support@rootutilities.com).

Falls die vertrauliche Meldung über GitHub nicht verfügbar ist, nutze den E-Mail-Kontakt. Veröffentliche keine ungelösten Sicherheitslücken in öffentlichen Issues, Discussions, Pull Requests oder Kommentaren.

Eine Meldung sollte nach Möglichkeit Folgendes enthalten:

- Betroffene Komponente und genaue Version oder Commit.
- Relevante Umgebung, einschließlich Spiel-, AMX-Mod-X-, Engine-, GameDLL-, Datenbank- oder PHP-Versionen, soweit zutreffend.
- Beschreibung, mögliche Auswirkungen und Schritte zur sicheren Reproduktion der Sicherheitslücke.
- Relevante bereinigte Logs, Konfigurationsauszüge und Diagnoseausgaben (`gg_info`, `gg_status`), soweit zutreffend.
- Vorschlag zur Eindämmung oder Fehlerbehebung, falls vorhanden.

Übermittle keine Passwörter, Tokens, Datenbankzugangsdaten, privaten Spielerinformationen oder unnötigen Produktivdaten. Teste ausschließlich eigene Systeme oder Systeme, für die eine ausdrückliche Berechtigung vorliegt. Vermeide Dienstunterbrechungen und Zugriffe auf fremde Daten.

### Bearbeitung und koordinierte Veröffentlichung

Der Maintainer prüft Meldungen zeitnah nach Verfügbarkeit. Es werden keine festen Reaktions- oder Behebungsfristen zugesichert. Zur Reproduktion und Bewertung können weitere Informationen angefordert werden.

Stimme die Veröffentlichung mit dem Maintainer ab und räume Zeit für Untersuchung und Fehlerbehebung ein. Zeitpunkt und Inhalt der öffentlichen Bekanntgabe sollen nach der Fehlerbehebung abgestimmt werden. Meldungen werden im Rahmen des vernünftigerweise Möglichen vertraulich behandelt.

### Sicherheitsupdates und Anerkennung

Sicherheitskorrekturen werden über [offizielle GitHub-Releases](https://github.com/JamesDaRock/GunGame-AMXX/releases) und gegebenenfalls über [GitHub Security Advisories](https://github.com/JamesDaRock/GunGame-AMXX/security/advisories) bereitgestellt.

Meldende Personen können auf Wunsch und mit ihrer Zustimmung namentlich genannt werden. Identifizierende Informationen werden nicht ohne Zustimmung veröffentlicht.
