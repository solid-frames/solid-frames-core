# Solid Frames Core

WordPress MU-Plugin (Must-Use) mit globalen Sicherheits- und Performance-Standards für Solid-Frames-Projekte.

## Installation

Die Datei `sf_core_security.php` in das Verzeichnis `wp-content/mu-plugins/` der WordPress-Installation kopieren. MU-Plugins werden automatisch aktiviert, ein manuelles Aktivieren im Plugin-Bereich ist nicht nötig oder möglich.

## Funktionen

### Funktionalität & Workflow
- Erlaubt den Upload von `.vcf`-Dateien; SVG-Uploads nur für Administratoren.
- Deaktiviert E-Mail-Benachrichtigungen bei automatischen Core-, Plugin- und Theme-Updates sowie beim Admin-E-Mail-Check.
- Entfernt unnötige `<head>`-Links (RSD, WLW-Manifest, Shortlink).

### Sicherheit & Hardening
- Entfernt den WordPress-Generator-Meta-Tag und deaktiviert XML-RPC.
- Entfernt den `X-Pingback`-Header.
- Blockiert die REST-API-Endpunkte `/wp/v2/users` für nicht angemeldete Nutzer.
- Leitet Autoren-Archivseiten (`is_author()`) auf die Startseite um.
- Deaktiviert Application Passwords.
- Generalisiert Login-Fehlermeldungen, um Rückschlüsse auf gültige Benutzernamen zu verhindern.
- Passwort-Reset-Absicherung: Fehler beim "Passwort vergessen"-Formular werden ohne Detailinformationen zur Login-Seite umgeleitet, inklusive einer neutralen Bestätigungsnachricht.
- Erzwingt sichere Passwörter (mindestens 12 Zeichen, Groß-/Kleinschreibung, mindestens eine Ziffer) bei Profiländerungen und Passwort-Resets.

### Kommentare
- Deaktiviert Kommentare und Trackbacks global für alle Post-Typen.
- Entfernt die Kommentarverwaltung aus dem Admin-Menü und der Admin-Bar sowie das Dashboard-Widget für aktuelle Kommentare.
- Leitet direkte Zugriffe auf `edit-comments.php` um.

## Versionierung

Aktuelle Version: siehe Header von `sf_core_security.php` (`Version:`-Feld).
