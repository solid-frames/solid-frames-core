```
   _____ ______   __________  ____  ______
  / ___// ____/  / ____/ __ \/ __ \/ ____/
  \__ \/ /_     / /   / / / / /_/ / __/   
 ___/ / __/    / /___/ /_/ / _, _/ /___   
/____/_/       \____/\____/_/ |_/_____/   
```

# Solid Frames Core

![Version](https://img.shields.io/badge/version-1.0.11-blue)
![Type](https://img.shields.io/badge/type-MU--Plugin-informational)
![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-777bb4)
![WordPress](https://img.shields.io/badge/WordPress-%3E%3D5.5-21759b)

WordPress MU-Plugin (Must-Use) mit globalen Sicherheits- und Performance-Standards für Solid-Frames-Projekte.

## Inhaltsverzeichnis

- [Installation](#installation)
  - [Ausrollen und Aktualisieren per WP-CLI / Shell](#ausrollen-und-aktualisieren-per-wp-cli--shell)
- [Anforderungen](#anforderungen)
- [Funktionen](#funktionen)
  - [Funktionalität & Workflow](#funktionalität--workflow)
  - [Sicherheit & Hardening](#sicherheit--hardening)
  - [Kommentare](#kommentare)
- [Changelog](#changelog)

## Installation

Die Datei `sf_core_security.php` in das Verzeichnis `wp-content/mu-plugins/` der WordPress-Installation kopieren.

```
wp-content/
└── mu-plugins/
    └── sf_core_security.php
```

MU-Plugins werden automatisch aktiviert, ein manuelles Aktivieren im Plugin-Bereich ist nicht nötig oder möglich.

### Ausrollen und Aktualisieren per WP-CLI / Shell

`wp plugin install` kann MU-Plugins nicht installieren (es legt Plugins in `wp-content/plugins/` ab, nicht in `mu-plugins/`). Stattdessen wird die Datei direkt von GitHub geladen. Im WordPress-Verzeichnis:

```bash
mkdir -p wp-content/mu-plugins
curl -fsSL -o wp-content/mu-plugins/sf_core_security.php \
  https://raw.githubusercontent.com/solid-frames/solid-frames-core/main/sf_core_security.php
```

Ein Update funktioniert mit demselben Befehl. Für reproduzierbare Rollouts statt `main` einen Tag oder Commit-Hash in der URL verwenden. Das Repository ist öffentlich, ein Token ist nicht nötig.

Installierte Version prüfen:

```bash
grep -m1 'Version:' wp-content/mu-plugins/sf_core_security.php
wp plugin list --status=must-use
```

Es gibt keine automatischen Updates, das Plugin wird nur beim Ausrollen aktualisiert.

## Anforderungen

| Voraussetzung | Version |
| --- | --- |
| WordPress | ≥ 5.5 (wegen `wp_sitemaps_add_provider`) |
| PHP | ≥ 7.4, mit `mbstring`-Extension |

## Funktionen

### Funktionalität & Workflow

| Funktion | Beschreibung |
| --- | --- |
| Upload-Mimetypes | Erlaubt den Upload von `.vcf`-Dateien. |
| Update-Benachrichtigungen | Deaktiviert E-Mail-Benachrichtigungen bei automatischen Core-, Plugin- und Theme-Updates sowie beim Admin-E-Mail-Check. |
| `<head>`-Aufräumen | Entfernt unnötige Links (RSD, WLW-Manifest, Shortlink). |

### Sicherheit & Hardening

| Funktion | Beschreibung |
| --- | --- |
| Generator & XML-RPC | Entfernt den WordPress-Generator-Meta-Tag und deaktiviert XML-RPC (zusätzlich zu Blocks auf Vhost-/CDN-Ebene). |
| `X-Pingback`-Header | Wird aus der Response entfernt. |
| REST-API-Nutzerliste | Blockiert `/wp/v2/users` für nicht angemeldete Nutzer. |
| Autor-Enumeration | Leitet Autoren-Archivseiten (`is_author()`) mit Priorität vor Cores `redirect_canonical` auf die Startseite um, entfernt Autor-Daten aus oEmbed-Antworten und deaktiviert den User-Sitemap-Provider. |
| Application Passwords | Standardmäßig deaktiviert. Pro Projekt freischaltbar über `define( 'SF_ALLOW_APP_PASSWORDS', true );` in der `wp-config.php` (z. B. für den Bricks-Builder-MCP). Nur bei Bedarf aktivieren und einen eigenen Benutzer mit minimaler Rolle verwenden. |
| Login-Fehlermeldungen | Vereinheitlicht über einen `authenticate`-Filter auf eine neutrale Meldung, damit keine Rückschlüsse auf gültige Benutzernamen möglich sind. Andere Meldungen (z. B. Passwort-Richtlinie) bleiben sichtbar. |
| Passwort-Reset-Absicherung | Verrät über `lostpassword_post` nicht, ob ein Benutzername/E-Mail existiert; einheitliche Bestätigungsmeldung unabhängig vom Ergebnis. |
| Passwort-Richtlinie | Erzwingt mindestens 14 Zeichen (NIST 800-63B: Länge statt erzwungener Komplexität) bei Profiländerungen, Passwort-Resets und Änderungen über die REST-API. |

### Kommentare

| Funktion | Beschreibung |
| --- | --- |
| Global deaktiviert | Kommentare und Trackbacks werden für alle Post-Typen deaktiviert. |
| Admin-UI bereinigt | Entfernt Kommentarverwaltung aus Admin-Menü und Admin-Bar sowie das Dashboard-Widget für aktuelle Kommentare. |
| Direktzugriff | Zugriffe auf `edit-comments.php` werden umgeleitet. |

## Changelog

| Version | Änderungen |
| --- | --- |
| 1.0.11 | Deployment-Anleitung (Shell/WP-CLI) in der README ergänzt, Code-Kommentar bereinigt. |
| 1.0.10 | Application Passwords per Konstante `SF_ALLOW_APP_PASSWORDS` projektweise freischaltbar (Standard bleibt deaktiviert). |
| 1.0.9 | Passwort-Richtlinie auf reine Längenprüfung (14 Zeichen) vereinfacht, REST-API-Passwortänderungen abgesichert, SVG-Upload-Freischaltung entfernt (wird von Bricks Builder gesteuert). |
| 1.0.8 | Autor-Enumeration über Hook-Priorität, oEmbed- und Sitemap-Filter geschlossen; Login-Fehler über `authenticate`-Filter vereinheitlicht; Passwort-Reset-Enumeration über `lostpassword_post` geschlossen; Passwort-Längenprüfung korrigiert (`mb_strlen` statt `strlen`). |
| 1.0.7 | Ausgangsstand. |

Aktuelle Version: siehe Header von `sf_core_security.php` (`Version:`-Feld).
