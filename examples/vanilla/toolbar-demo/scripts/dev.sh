#!/bin/bash

# Calculate ports for this example
EXAMPLE_PATH="vanilla/toolbar-demo"
PORTS=$(node ../../../scripts/get-ports.js "$EXAMPLE_PATH" --json)

# Extract ports from JSON
export FRONTEND_PORT=$(echo "$PORTS" | grep -o '"FRONTEND_PORT": [0-9]*' | grep -o '[0-9]*')
export WP_PORT=$(echo "$PORTS" | grep -o '"WP_PORT": [0-9]*' | grep -o '[0-9]*')
export WP_TEST_PORT=$(echo "$PORTS" | grep -o '"WP_TEST_PORT": [0-9]*' | grep -o '[0-9]*')

echo "Starting vanilla toolbar demo..."
echo "  Frontend: http://localhost:$FRONTEND_PORT"
echo "  WordPress: http://localhost:$WP_PORT"
echo ""

# Start Vite dev server with calculated port
cd example-app && vite --port "$FRONTEND_PORT"
