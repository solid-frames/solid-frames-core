#!/usr/bin/env bash
# Rollt sf_core_security.php auf alle gefundenen WordPress-Installationen aus.
#
# Aufruf:  ./deploy-core.sh [ref] [--apply]
#   ref      Branch, Tag oder Commit (Standard: main)
#   --apply  tatsaechlich schreiben; ohne diese Option nur Vorschau mit Diff
#
# Die Installationen werden bei jedem Lauf automatisch gefunden (keine Liste noetig):
# /home/<benutzer>/htdocs/<domain>/wp-config.php. Anderes Muster per
# SITE_GLOB='/pfad/*/wp-config.php' ./deploy-core.sh ...
set -euo pipefail

REF="${1:-main}"
APPLY="${2:-}"
SITE_GLOB="${SITE_GLOB:-/home/*/htdocs/*/wp-config.php}"
URL="https://raw.githubusercontent.com/solid-frames/solid-frames-core/${REF}/sf_core_security.php"

shopt -s nullglob
CONFIGS=( $SITE_GLOB )
[ "${#CONFIGS[@]}" -gt 0 ] || { echo "Fehler: keine Installation gefunden ($SITE_GLOB)"; exit 1; }

TMP="$(mktemp)"; trap 'rm -f "$TMP"' EXIT
curl -fsSL -o "$TMP" "$URL"
php -l "$TMP" >/dev/null
echo "Ausrollen: $(grep -m1 'Version:' "$TMP")"

for cfg in "${CONFIGS[@]}"; do
  site="$(dirname "$cfg")"
  dest="$site/wp-content/mu-plugins/sf_core_security.php"

  if [ -f "$dest" ]; then
    if cmp -s "$dest" "$TMP"; then
      echo "== $site (aktuell)"
      continue
    fi
    echo "== $site (vorhanden: $(grep -m1 'Version:' "$dest" || echo 'unbekannt'))"
    diff -u "$dest" "$TMP" || true
  else
    echo "== $site (neu)"
  fi

  if [ "$APPLY" = "--apply" ]; then
    owner="$(stat -c %U "$site/wp-config.php")"
    mkdir -p "$(dirname "$dest")"
    install -o "$owner" -g "$owner" -m 644 "$TMP" "$dest"
    echo "   ausgerollt (Besitzer: $owner)"
  fi
done
