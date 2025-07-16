#!/bin/bash

# Exit on any error
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get the version from package.json
VERSION=$(node -p "require('./package.json').version")

if [ -z "$VERSION" ]; then
    echo -e "${RED}Error: Could not read version from package.json${NC}"
    exit 1
fi

echo -e "${YELLOW}Syncing version ${VERSION} across all files...${NC}"

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

# Update wpgraphql-debug-extensions.php
PLUGIN_FILE="wpgraphql-debug-extensions.php"

if [ -f "$PLUGIN_FILE" ]; then
    echo "Updating main plugin file: $PLUGIN_FILE..."

    # Update WordPress plugin header version (handles beta versions)
    sed -i.bak -E "s/(\* Version:[[:space:]]*)[0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9]+)?/\1$VERSION/g" "$PLUGIN_FILE" && rm "${PLUGIN_FILE}.bak"

    # Update WPGRAPHQL_DEBUG_EXTENSIONS_VERSION define statement
    sed -i.bak -E "s/(define\([[:space:]]*['\"]WPGRAPHQL_DEBUG_EXTENSIONS_VERSION['\"][[:space:]]*,[[:space:]]*['\"])[0-9]+\.[0-9]+\.[0-9]+(-[a-zA-Z0-9]+)?(['\"][[:space:]]*\))/\1$VERSION\3/g" "$PLUGIN_FILE" && rm "${PLUGIN_FILE}.bak"

    echo -e "${GREEN}✓ Updated $PLUGIN_FILE${NC}"
else
    echo -e "${YELLOW}⚠ $PLUGIN_FILE not found${NC}"
fi

echo -e "${GREEN}✅ Version sync complete! All files updated to version ${VERSION}${NC}"
echo -e "${YELLOW}Files will be staged by the workflow's 'git add .' command${NC}"``
