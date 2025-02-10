#!/bin/bash

# Get the package name from the first argument, default to "cli" if not provided
PACKAGE=${1:-cli}

# Clean up first
bash ./scripts/clean.sh

# Run the dev command for the specified package
echo "Starting development environment for @hwp/$PACKAGE..."
pnpm --filter "@hwp/$PACKAGE" dev
