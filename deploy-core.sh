#!/usr/bin/env bash
# Rollt sf_core_security.php auf alle in der Liste genannten WordPress-Installationen aus.
#
# Aufruf:  ./deploy-core.sh [ref] [--apply]
#   ref      Branch, Tag oder Commit (Standard: main)
#   --apply  tatsaechlich schreiben; ohne diese Option nur Vorschau mit Diff
#
# Liste: eine WordPress-Installation pro Zeile (Pfad zum Ordner mit wp-config.php),
# Standard /root/sites.txt, ueberschreibbar mit SITES=/pfad/zur/liste.
set -euo pipefail

REF="${1:-main}"
APPLY="${2:-}"
SITES="${SITES:-/root/sites.txt}"
URL="https://raw.githubusercontent.com/solid-frames/solid-frames-core/${REF}/sf_core_security.php"

[ -s "$SITES" ] || { echo "Fehler: $SITES fehlt oder ist leer"; exit 1; }

TMP="$(mktemp)"; trap 'rm -f "$TMP"' EXIT
curl -fsSL -o "$TMP" "$URL"
php -l "$TMP" >/dev/null
echo "Ausrollen: $(grep -m1 'Version:' "$TMP")"

while read -r site; do
  [ -z "$site" ] && continue
  dest="$site/wp-content/mu-plugins/sf_core_security.php"

  if [ ! -f "$site/wp-config.php" ]; then
    echo "== $site: keine wp-config.php, uebersprungen"
    continue
  fi

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
done < <(grep -v '^[[:space:]]*$' "$SITES" | tr -d '\r')
