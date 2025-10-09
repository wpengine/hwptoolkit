#!/bin/bash

# Calculate ports for this example
EXAMPLE_PATH="vanilla/toolbar-demo"
PORTS=$(node ../../../scripts/get-ports.js "$EXAMPLE_PATH" --json)

# Extract ports from JSON
export WP_PORT=$(echo "$PORTS" | grep -o '"WP_PORT": [0-9]*' | grep -o '[0-9]*')
export WP_TEST_PORT=$(echo "$PORTS" | grep -o '"WP_TEST_PORT": [0-9]*' | grep -o '[0-9]*')

echo "Starting WordPress for vanilla toolbar demo..."
echo "  WordPress: http://localhost:$WP_PORT"
echo "  WP Test: http://localhost:$WP_TEST_PORT"
echo ""

# Update .wp-env.json with calculated ports
cat > .wp-env.json <<EOF
{
  "phpVersion": "8.0",
  "plugins": [
    "https://github.com/wp-graphql/wp-graphql/releases/latest/download/wp-graphql.zip"
  ],
  "config": {
    "WP_DEBUG": true,
    "WP_DEBUG_LOG": true,
    "GRAPHQL_DEBUG": true
  },
  "port": $WP_PORT,
  "testsPort": $WP_TEST_PORT,
  "mappings": {
    "db": "./wp-env/db",
    "wp-content/mu-plugins": "./mu-plugin.php"
  },
  "lifecycleScripts": {
    "afterStart": "wp-env run cli -- wp rewrite structure '/%postname%/' && wp-env run cli -- wp rewrite flush"
  }
}
EOF

# Start wp-env
npx wp-env start
