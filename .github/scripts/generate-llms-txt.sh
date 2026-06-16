#!/bin/bash
set -e

OUTPUT="llms.txt"
REPO_URL="https://github.com/wpengine/hwptoolkit/blob/main"
REPO_DIR_URL="https://github.com/wpengine/hwptoolkit/tree/main"

# Extract description from a file:
# 1. Frontmatter "description:" field
# 2. First non-empty line after the first "# " heading
# 3. Empty string
get_desc() {
  local file="$1"
  local desc
  desc=$(grep -m 1 "^description:" "$file" | sed 's/description: "\(.*\)"/\1/' | tr -d '"' || true)
  if [ -n "$desc" ]; then
    echo "$desc"
    return
  fi
  awk '/^# /{found=1; next} found && NF{print; exit}' "$file" || true
}

cat > "$OUTPUT" << 'HEADER'
# Headless WordPress Toolkit

> A modern toolkit for building headless WordPress applications with WPGraphQL, plugins, and framework examples.

HEADER

# Plugins Section
echo "## [Plugins]($REPO_DIR_URL/plugins)" >> "$OUTPUT"
echo "" >> "$OUTPUT"
# Add description from plugins/README.md
if [ -f "plugins/README.md" ]; then
  plugins_desc=$(get_desc "plugins/README.md")
  if [ -n "$plugins_desc" ]; then
    echo "$plugins_desc" >> "$OUTPUT"
    echo "" >> "$OUTPUT"
  fi
fi
find plugins -maxdepth 2 -name "README.md" | LC_ALL=C sort | while IFS= read -r file; do
  # Skip the root plugins/README.md
  if [ "$file" != "plugins/README.md" ]; then
    title=$(grep -m 1 "^# " "$file" | sed 's/^# //' || true)
    echo "- [$title]($REPO_URL/$file)" >> "$OUTPUT"
  fi
done

# Packages Section
echo "" >> "$OUTPUT"
echo "## [Packages]($REPO_DIR_URL/packages)" >> "$OUTPUT"
echo "" >> "$OUTPUT"
if [ -f "packages/README.md" ]; then
  packages_desc=$(get_desc "packages/README.md")
  if [ -n "$packages_desc" ]; then
    echo "$packages_desc" >> "$OUTPUT"
    echo "" >> "$OUTPUT"
  fi
fi
find packages -maxdepth 2 -name "README.md" | LC_ALL=C sort | while IFS= read -r file; do
  if [ "$file" != "packages/README.md" ]; then
    title=$(grep -m 1 "^# " "$file" | sed 's/^# //' || true)
    desc=$(get_desc "$file")
    if [ -n "$desc" ]; then
      echo "- [$title]($REPO_URL/$file): $desc" >> "$OUTPUT"
    else
      echo "- [$title]($REPO_URL/$file)" >> "$OUTPUT"
    fi
  fi
done

# Documentation Section
echo "" >> "$OUTPUT"
echo "## [Documentation]($REPO_DIR_URL/docs)" >> "$OUTPUT"
echo "" >> "$OUTPUT"
# Add description from docs/README.md frontmatter
if [ -f "docs/README.md" ]; then
  docs_desc=$(grep -m 1 "^description:" docs/README.md | sed 's/description: "\(.*\)"/\1/' | tr -d '"' || true)
  if [ -n "$docs_desc" ]; then
    echo "$docs_desc" >> "$OUTPUT"
    echo "" >> "$OUTPUT"
  fi
fi

# General docs (explanation, how-to)
find docs/explanation docs/how-to -name "index.md" 2>/dev/null | LC_ALL=C sort | while IFS= read -r file; do
  title=$(grep -m 1 "^title:" "$file" | sed 's/title: "\(.*\)"/\1/' | tr -d '"' || true)
  desc=$(grep -m 1 "^description:" "$file" | sed 's/description: "\(.*\)"/\1/' | tr -d '"' || true)
  if [ -z "$title" ]; then
    title=$(grep -m 1 "^# " "$file" | sed 's/^# //' || true)
  fi
  if [ -n "$desc" ]; then
    echo "- [$title]($REPO_URL/$file): $desc" >> "$OUTPUT"
  else
    echo "- [$title]($REPO_URL/$file)" >> "$OUTPUT"
  fi
done

# Plugin-specific docs
for plugin_dir in docs/plugins/*/; do
  if [ -d "$plugin_dir" ]; then
    plugin_name=$(basename "$plugin_dir")
    echo "" >> "$OUTPUT"
    echo "- **$plugin_name**" >> "$OUTPUT"
    find "$plugin_dir" -name "index.md" | LC_ALL=C sort | while IFS= read -r file; do
      title=$(grep -m 1 "^title:" "$file" | sed 's/title: "\(.*\)"/\1/' | tr -d '"' || true)
      desc=$(grep -m 1 "^description:" "$file" | sed 's/description: "\(.*\)"/\1/' | tr -d '"' || true)
      if [ -z "$title" ]; then
        title=$(grep -m 1 "^# " "$file" | sed 's/^# //' || true)
      fi
      if [ -n "$desc" ]; then
        echo "  - [$title]($REPO_URL/$file): $desc" >> "$OUTPUT"
      else
        echo "  - [$title]($REPO_URL/$file)" >> "$OUTPUT"
      fi
    done
  fi
done

# Examples Section
echo "" >> "$OUTPUT"
echo "## [Examples]($REPO_DIR_URL/examples)" >> "$OUTPUT"
echo "" >> "$OUTPUT"
# Add description from examples/README.md
if [ -f "examples/README.md" ]; then
  examples_desc=$(get_desc "examples/README.md")
  if [ -n "$examples_desc" ]; then
    echo "$examples_desc" >> "$OUTPUT"
    echo "" >> "$OUTPUT"
  fi
fi
find examples -maxdepth 3 -name "README.md" | LC_ALL=C sort | while IFS= read -r file; do
  # Skip the root examples/README.md
  if [ "$file" != "examples/README.md" ]; then
    dir=$(dirname "$file" | sed 's|examples/||')
    title=$(grep -m 1 "^# " "$file" | sed 's/^# //' || true)
    desc=$(get_desc "$file")
    if [ -n "$desc" ]; then
      echo "- [$title]($REPO_URL/$file): $desc" >> "$OUTPUT"
    else
      echo "- [$title]($REPO_URL/$file): $dir" >> "$OUTPUT"
    fi
  fi
done

echo "Generated $OUTPUT"
