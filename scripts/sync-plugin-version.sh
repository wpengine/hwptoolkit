#!/bin/bash

# Syncs a plugin's package.json version into composer.json, readme.txt, the plugin header,
# and the <SLUG>_VERSION constant.
#
# Run it from the plugin folder (it's each plugin's `npm run version` script), or pass the
# plugin folder as the first argument.

# Exit on any error
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

cd "${1:-.}"

SLUG=$(basename "$PWD")
PLUGIN_FILE="$SLUG.php"
# e.g. hwp-previews -> HWP_PREVIEWS_VERSION
VERSION_CONSTANT="$(echo "$SLUG" | tr '[:lower:]-' '[:upper:]_')_VERSION"

# Get the version from package.json
VERSION=$(node -p "require('./package.json').version")

if [ -z "$VERSION" ]; then
    echo -e "${RED}Error: Could not read version from package.json${NC}"
    exit 1
fi

echo -e "${YELLOW}Syncing $SLUG version ${VERSION} across all files...${NC}"

# Update composer.json
if [ -f "composer.json" ]; then
    echo "Updating composer.json..."
    # Use sed to preserve original formatting (tabs/spaces)
    sed -i.bak -E "s/(\"version\":[[:space:]]*\")[^\"]*(\",?)/\1$VERSION\2/" composer.json && rm composer.json.bak
    echo -e "${GREEN}✓ Updated composer.json${NC}"
else
    echo -e "${YELLOW}⚠ composer.json not found${NC}"
fi

# Update readme.txt (WordPress style)
if [ -f "readme.txt" ]; then
    echo "Updating readme.txt..."
    sed -i.bak -E "s/(Stable tag:|Version:)[[:space:]]*[0-9]+\.[0-9]+\.[0-9]+/\1 $VERSION/g" readme.txt && rm readme.txt.bak
    echo -e "${GREEN}✓ Updated readme.txt${NC}"
else
    echo -e "${YELLOW}⚠ readme.txt not found${NC}"
fi

# Update the main plugin file
if [ -f "$PLUGIN_FILE" ]; then
    echo "Updating main plugin file: $PLUGIN_FILE..."

    # Update WordPress plugin header version (handles beta versions)
    sed -i.bak -E "s/(\* Version:[[:space:]]*)[0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9]+)?/\1$VERSION/g" "$PLUGIN_FILE" && rm "${PLUGIN_FILE}.bak"

    # Update the version constant's define statement
    sed -i.bak -E "s/(define\([[:space:]]*['\"]${VERSION_CONSTANT}['\"][[:space:]]*,[[:space:]]*['\"])[0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9]+)?(['\"][[:space:]]*\))/\1$VERSION\3/g" "$PLUGIN_FILE" && rm "${PLUGIN_FILE}.bak"

    echo -e "${GREEN}✓ Updated $PLUGIN_FILE${NC}"
else
    echo -e "${YELLOW}⚠ $PLUGIN_FILE not found${NC}"
fi

echo -e "${GREEN}✅ Version sync complete! All files updated to version ${VERSION}${NC}"
