#!/bin/bash

# Composer Package Updater Script
# Usage: ./update_composer_package.sh <version> <release_url> [package_name] [description]
# e.g. .github/scripts/update_composer_package.sh "0.0.5" "https://github.com/wpengine/hwptoolkit/releases/download/%40wpengine%2Fhwp-previews-wordpress-plugin-0.0.5/hwp-previews.zip" "wpengine/previews" "A WordPress plugin for headless previews."


set -e

# Function to display usage
usage() {
    echo "Usage: $0 <version> <release_url> [package_name] [description]"
    echo ""
    echo "Arguments:"
    echo "  version        - Package version (e.g., 0.0.5)"
    echo "  release_url    - Download URL for the package zip file"
    echo "  package_name   - Optional: Package name (auto-detected from URL if not provided)"
    echo "  description    - Optional: Package description (uses generic default if not provided)"
    echo ""
    echo "Examples:"
    echo "  $0 '0.0.2' 'https://github.com/wpengine/hwptoolkit/releases/download/%40wpengine%2Fwpgraphql-webhooks-wordpress-plugin-0.0.2/wp-graphql-webhooks.zip'"
    echo ""
    echo "  With custom package name and description:"
    echo "  $0 '0.0.5' 'https://example.com/plugin.zip' 'wpengine/my-plugin' 'My custom plugin description'"
    echo ""
    echo "Note: Updates plugins/package.json file"
    exit 1
}

# Check if minimum arguments provided
if [ "$#" -lt 2 ]; then
    echo "Error: Missing required arguments"
    usage
fi

# Arguments
VERSION="$1"
RELEASE_URL="$2"
PACKAGE_NAME="$3"
DESCRIPTION="$4"
COMPOSER_FILE="plugins/package.json"

# Function to extract package name from release URL
extract_package_name() {
    local url="$1"

    # Extract from URL patterns like:
    # https://github.com/wpengine/hwptoolkit/releases/download/%40wpengine%2F{PLUGIN}-wordpress-plugin-{VERSION}/{ZIP_NAME}.zip

    # Method 1: Extract from the release tag (between %40wpengine%2F and -wordpress-plugin)
    if [[ "$url" =~ %40wpengine%2F([^-]+)-wordpress-plugin ]]; then
        local plugin_name="${BASH_REMATCH[1]}"
        echo "wpengine/$plugin_name"
        return
    fi

    # Method 2: Extract from @wpengine/ format
    if [[ "$url" =~ @wpengine/([^-]+)-wordpress-plugin ]]; then
        local plugin_name="${BASH_REMATCH[1]}"
        echo "wpengine/$plugin_name"
        return
    fi

    # Method 3: Extract from the zip filename at the end of URL
    local filename=$(basename "$url" .zip)
    if [[ "$filename" =~ ^(.+)$ ]]; then
        # Clean up common prefixes but keep the core name
        local clean_name="$filename"
        clean_name=$(echo "$clean_name" | sed 's/^hwp-//')
        clean_name=$(echo "$clean_name" | sed 's/^wp-//')
        clean_name=$(echo "$clean_name" | sed 's/^wpengine-//')
        echo "wpengine/$clean_name"
        return
    fi

    # Method 4: Try to extract from middle part of URL path
    if [[ "$url" =~ /([^/]+)\.zip$ ]]; then
        local zip_name="${BASH_REMATCH[1]}"
        # Remove common prefixes
        zip_name=$(echo "$zip_name" | sed 's/^hwp-//')
        zip_name=$(echo "$zip_name" | sed 's/^wp-//')
        zip_name=$(echo "$zip_name" | sed 's/^wpengine-//')
        echo "wpengine/$zip_name"
        return
    fi

    # Fallback
    echo "wpengine/unknown-plugin"
}

# Function to generate generic description
generate_description() {
    local package_name="$1"
    echo "A WordPress plugin for headless functionality."
}

# Auto-detect package name if not provided
if [ -z "$PACKAGE_NAME" ]; then
    PACKAGE_NAME=$(extract_package_name "$RELEASE_URL")
    echo "Auto-detected package name: $PACKAGE_NAME"
fi

# Auto-generate description if not provided
if [ -z "$DESCRIPTION" ]; then
    DESCRIPTION=$(generate_description "$PACKAGE_NAME")
    echo "Using description: $DESCRIPTION"
fi

echo "Updating composer package:"
echo "  Package: $PACKAGE_NAME"
echo "  Version: $VERSION"
echo "  File: $COMPOSER_FILE"
echo "  URL: $RELEASE_URL"
echo "  Description: $DESCRIPTION"

# Create plugins directory if it doesn't exist
mkdir -p "$(dirname "$COMPOSER_FILE")"

# Create initial package.json if it doesn't exist
if [ ! -f "$COMPOSER_FILE" ]; then
    echo "Creating initial $COMPOSER_FILE..."
    cat > "$COMPOSER_FILE" << 'EOF'
{
    "packages": {}
}
EOF
fi

# Validate JSON structure
if ! python3 -m json.tool "$COMPOSER_FILE" >/dev/null 2>&1; then
    echo "Error: '$COMPOSER_FILE' is not valid JSON"
    exit 1
fi

# Function to update package using jq
update_package_jq() {
    local package_name="$1"
    local version="$2"
    local release_url="$3"
    local description="$4"
    local composer_file="$5"

    # Create the new package version object
    jq --arg pkg "$package_name" \
       --arg ver "$version" \
       --arg url "$release_url" \
       --arg desc "$description" \
       '
       # Ensure the packages object exists
       if .packages == null then .packages = {} else . end |

       # Ensure the package namespace exists
       if .packages[$pkg] == null then .packages[$pkg] = {} else . end |

       # Add/update the version
       .packages[$pkg][$ver] = {
           "name": $pkg,
           "version": $ver,
           "type": "wordpress-plugin",
           "description": $desc,
           "homepage": "https://github.com/wpengine/hwptoolkit",
           "license": "GPL-2.0",
           "authors": [
               {
                   "name": "WP Engine Headless OSS Development Team",
                   "email": "headless-oss@wpengine.com",
                   "homepage": "https://wpengine.com/"
               }
           ],
           "support": {
               "issues": "https://github.com/wpengine/hwptoolkit/issues",
               "email": "support@wpengine.com"
           },
           "dist": {
               "url": $url,
               "type": "zip"
           },
           "require": {
               "composer/installers": "~1.0 || ~2.0"
           }
       }' "$composer_file" > "${composer_file}.tmp" && mv "${composer_file}.tmp" "$composer_file"
}

# Function to update package using Python (fallback)
update_package_python() {
    local package_name="$1"
    local version="$2"
    local release_url="$3"
    local description="$4"
    local composer_file="$5"

    python3 << EOF
import json

# Read the composer file
with open('$composer_file', 'r') as f:
    data = json.load(f)

# Ensure packages object exists
if 'packages' not in data:
    data['packages'] = {}

# Ensure package namespace exists
if '$package_name' not in data['packages']:
    data['packages']['$package_name'] = {}

# Create the new version entry
data['packages']['$package_name']['$version'] = {
    "name": "$package_name",
    "version": "$version",
    "type": "wordpress-plugin",
    "description": "$description",
    "homepage": "https://github.com/wpengine/hwptoolkit",
    "license": "GPL-2.0",
    "authors": [
        {
            "name": "WP Engine Headless OSS Development Team",
            "email": "headless-oss@wpengine.com",
            "homepage": "https://wpengine.com/"
        }
    ],
    "support": {
        "issues": "https://github.com/wpengine/hwptoolkit/issues",
        "email": "support@wpengine.com"
    },
    "dist": {
        "url": "$release_url",
        "type": "zip"
    },
    "require": {
        "composer/installers": "~1.0 || ~2.0"
    }
}

# Write back to file with proper formatting
with open('$composer_file', 'w') as f:
    json.dump(data, f, indent=4, separators=(',', ': '))

print(f"Successfully updated {package_name} version {version}")
EOF
}

# Update the package
if command -v jq >/dev/null 2>&1; then
    echo "Using jq for JSON manipulation..."
    update_package_jq "$PACKAGE_NAME" "$VERSION" "$RELEASE_URL" "$DESCRIPTION" "$COMPOSER_FILE"
else
    echo "jq not found, using Python fallback..."
    update_package_python "$PACKAGE_NAME" "$VERSION" "$RELEASE_URL" "$DESCRIPTION" "$COMPOSER_FILE"
fi

# Validate the updated JSON
if ! python3 -m json.tool "$COMPOSER_FILE" >/dev/null 2>&1; then
    echo "Error: Updated JSON is invalid"
    exit 1
fi

echo "✅ Successfully updated $PACKAGE_NAME to version $VERSION"

# Show the updated section
echo ""
echo "Updated package entry:"
if command -v jq >/dev/null 2>&1; then
    jq --arg pkg "$PACKAGE_NAME" --arg ver "$VERSION" '.packages[$pkg][$ver]' "$COMPOSER_FILE"
else
    echo "Install jq to see formatted output, or check the file: $COMPOSER_FILE"
fi

# Optional: Show package summary
echo ""
echo "📦 Package Summary:"
echo "   Name: $PACKAGE_NAME"
echo "   Version: $VERSION"
echo "   Description: $DESCRIPTION"
echo "   Download URL: $RELEASE_URL"
echo "   File updated: $COMPOSER_FILE"

# Check if this is a new package or version update
if command -v jq >/dev/null 2>&1; then
    EXISTING_VERSIONS=$(jq -r --arg pkg "$PACKAGE_NAME" '.packages[$pkg] // {} | keys | length' "$COMPOSER_FILE")
    if [ "$EXISTING_VERSIONS" -gt 1 ]; then
        echo "   Status: Updated existing package (now has $EXISTING_VERSIONS versions)"
    else
        echo "   Status: Added new package"
    fi
fi
