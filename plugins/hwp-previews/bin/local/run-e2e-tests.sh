#!/usr/bin/env bash

# Run Playwright E2E tests for GitHub Actions

set -e

# Install dependencies if needed
npm ci

# Install composer dependencies
composer install

# Install Playwright browsers
npx playwright install --with-deps

# Start wp-env
npm run wp-env start

# Run Playwright tests
npm run test:e2e

# Stop wp-env
npm run wp-env stop
