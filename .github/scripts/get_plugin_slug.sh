#!/usr/bin/env bash

set -e

BASE_SHA="$1"
HEAD_SHA="$2"

git fetch --prune --unshallow 2>/dev/null || git fetch --prune

# Get changed files in plugins subdirectories
if [ "$BASE_SHA" = "release" ] || [ "$BASE_SHA" = "main" ]; then
  CHANGED_FILES=$(git diff --name-only HEAD~1 HEAD | grep '^plugins/[^/]\+/' || true)
else
  CHANGED_FILES=$(git diff --name-only "$BASE_SHA" "$HEAD_SHA" | grep '^plugins/[^/]\+/' || true)
fi

if [ -z "$CHANGED_FILES" ]; then
  echo "No plugin files changed"
  exit 1
fi

# Extract plugin names from both old and new paths
PLUGINS=()
for file in $CHANGED_FILES; do
  plugin=$(echo $file | cut -d/ -f2)
  PLUGINS+=("$plugin")
done

# Get unique plugin names
UNIQUE_PLUGINS=($(printf '%s\n' "${PLUGINS[@]}" | sort -u))

# Find the first plugin that actually exists
PLUGIN_SLUG=""
for plugin in "${UNIQUE_PLUGINS[@]}"; do
  if [ -d "plugins/$plugin" ]; then
    PLUGIN_SLUG="$plugin"
    echo "Found existing plugin directory: $PLUGIN_SLUG"
    break
  fi
done

if [ -z "$PLUGIN_SLUG" ]; then
  echo "No valid plugin directory found"
  exit 1
fi

echo "slug=$PLUGIN_SLUG" >> "$GITHUB_OUTPUT"
