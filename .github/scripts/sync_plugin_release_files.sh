#!/usr/bin/env bash

# Syncs a plugin's version from its package.json into the files release-please doesn't know about:
# the plugin header, readme.txt Stable tag, composer.json, and plugins/composer-packages.json.
# Usage: .github/scripts/sync_plugin_release_files.sh <plugin-slug>

set -e

SLUG="$1"
PLUGIN_DIR="plugins/$SLUG"

if [ -z "$SLUG" ] || [ ! -f "$PLUGIN_DIR/package.json" ] || [ ! -f "$PLUGIN_DIR/composer.json" ]; then
  echo "Usage: $0 <plugin-slug> (plugin needs both package.json and composer.json)"
  exit 1
fi

# Update the plugin header, readme.txt, and composer.json via the plugin's own version script
(cd "$PLUGIN_DIR" && npm run version)

NEW_VERSION=$(jq -r '.version' "$PLUGIN_DIR/package.json")

# Build the download URL
# Format is [github-repository]/releases/download/[encoded-package-name]-[version]/[plugin-slug].zip
PKG_NAME=$(jq -r '.name' "$PLUGIN_DIR/package.json")
ENCODED_PKG_NAME=$(python3 -c "import urllib.parse,sys; print(urllib.parse.quote(sys.argv[1], safe=''))" "$PKG_NAME")
RELEASE_URL="https://github.com/wpengine/hwptoolkit/releases/download/${ENCODED_PKG_NAME}-${NEW_VERSION}/${SLUG}.zip"

COMPOSER_NAME=$(jq -r '.name' "$PLUGIN_DIR/composer.json")
DESCRIPTION=$(jq -r '.description // empty' "$PLUGIN_DIR/composer.json")

if [ -z "$NEW_VERSION" ] || [ -z "$COMPOSER_NAME" ] || [ -z "$DESCRIPTION" ]; then
  echo "Error: Missing required values for update_composer_package.sh"
  echo "NEW_VERSION: $NEW_VERSION"
  echo "COMPOSER_NAME: $COMPOSER_NAME"
  echo "DESCRIPTION: $DESCRIPTION"
  exit 1
fi

.github/scripts/update_composer_package.sh "$NEW_VERSION" "$RELEASE_URL" "$COMPOSER_NAME" "$DESCRIPTION"
