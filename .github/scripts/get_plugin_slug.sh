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

# Find all valid plugins that have changes
VALID_PLUGINS=()
for plugin in "${UNIQUE_PLUGINS[@]}"; do
  if [ -d "plugins/$plugin" ]; then
    count=$(printf '%s\n' "${PLUGINS[@]}" | grep -c "^$plugin$")
    VALID_PLUGINS+=("$plugin")
    echo "Found plugin with $count changes: $plugin"
  fi
done

# Output all valid plugins as JSON array for matrix strategy
if [ ${#VALID_PLUGINS[@]} -gt 0 ]; then
  PLUGINS_JSON=$(printf '%s\n' "${VALID_PLUGINS[@]}" | jq -R . | jq -s .)
  echo "plugins=$PLUGINS_JSON" >> "$GITHUB_OUTPUT"
  echo "has-plugins=true" >> "$GITHUB_OUTPUT"
  
  # For backward compatibility, set slug to first plugin
  PLUGIN_SLUG="${VALID_PLUGINS[0]}"
else
  echo "plugins=[]" >> "$GITHUB_OUTPUT"
  echo "has-plugins=false" >> "$GITHUB_OUTPUT"
  PLUGIN_SLUG=""
fi

if [ -z "$PLUGIN_SLUG" ]; then
  echo "No valid plugin directory found"
  exit 1
fi

echo "slug=$PLUGIN_SLUG" >> "$GITHUB_OUTPUT"

echo "Changed files: $CHANGED_FILES"
echo "Detected plugin(s): ${UNIQUE_PLUGINS[*]}"
