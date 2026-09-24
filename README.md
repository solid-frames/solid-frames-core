```
   _____ ______   __________  ____  ______
  / ___// ____/  / ____/ __ \/ __ \/ ____/
  \__ \/ /_     / /   / / / / /_/ / __/   
 ___/ / __/    / /___/ /_/ / _, _/ /___   
/____/_/       \____/\____/_/ |_/_____/   
```

# Solid Frames Core

![Version](https://img.shields.io/badge/version-1.0.9-blue)
![Type](https://img.shields.io/badge/type-MU--Plugin-informational)
![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-777bb4)
![WordPress](https://img.shields.io/badge/WordPress-%3E%3D5.5-21759b)

WordPress MU-Plugin (Must-Use) mit globalen Sicherheits- und Performance-Standards für Solid-Frames-Projekte.

## Inhaltsverzeichnis

- [Installation](#installation)
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
| Application Passwords | Deaktiviert. |
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
| 1.0.9 | Passwort-Richtlinie auf reine Längenprüfung (14 Zeichen) vereinfacht, REST-API-Passwortänderungen abgesichert, SVG-Upload-Freischaltung entfernt (wird von Bricks Builder gesteuert). |
| 1.0.8 | Autor-Enumeration über Hook-Priorität, oEmbed- und Sitemap-Filter geschlossen; Login-Fehler über `authenticate`-Filter vereinheitlicht; Passwort-Reset-Enumeration über `lostpassword_post` geschlossen; Passwort-Längenprüfung korrigiert (`mb_strlen` statt `strlen`). |
| 1.0.7 | Ausgangsstand. |

Aktuelle Version: siehe Header von `sf_core_security.php` (`Version:`-Feld).
