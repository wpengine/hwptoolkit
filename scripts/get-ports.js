#!/usr/bin/env node

/**
 * Calculate deterministic ports for examples based on their directory path.
 *
 * Usage: node scripts/get-ports.js [example-path]
 *
 * The example-path should be relative to the examples/ directory.
 * Example: "vanilla/toolbar-demo" or "next/toolbar-demo"
 *
 * If no path is provided, it will auto-detect from current working directory.
 */

const crypto = require('crypto');
const path = require('path');
const fs = require('fs');

/**
 * Simple hash function that converts a string to a number in range [0, 999]
 */
function hashToPort(str) {
  const hash = crypto.createHash('sha256').update(str).digest('hex');
  // Take first 8 hex chars and convert to decimal, then mod 900 + 100 to get range [100, 999]
  // This ensures we skip the first 100 ports to avoid conflicts with common services
  const num = parseInt(hash.substring(0, 8), 16);
  return (num % 900) + 100;
}

/**
 * Get the example path from current working directory
 */
function getExamplePathFromCwd() {
  const cwd = process.cwd();
  const examplesDir = path.join(__dirname, '..', 'examples');

  if (!cwd.startsWith(examplesDir)) {
    throw new Error(`Current directory is not inside examples/: ${cwd}`);
  }

  const relativePath = path.relative(examplesDir, cwd);
  // Get the first two segments (e.g., "vanilla/toolbar-demo")
  const segments = relativePath.split(path.sep);
  return segments.slice(0, 2).join('/');
}

/**
 * Calculate ports for a given example path
 */
function getPorts(examplePath) {
  const offset = hashToPort(examplePath);

  const frontendPort = 3000 + offset;
  const wpPort = 8000 + offset;
  const wpTestPort = wpPort + 1;

  return {
    FRONTEND_PORT: frontendPort,
    WP_PORT: wpPort,
    WP_TEST_PORT: wpTestPort,
    EXAMPLE_PATH: examplePath,
    PORT_OFFSET: offset
  };
}

// Main execution
const examplePath = process.argv[2] || getExamplePathFromCwd();
const ports = getPorts(examplePath);

// Output as shell-sourceable format if --shell flag
if (process.argv.includes('--shell')) {
  console.log(`export FRONTEND_PORT=${ports.FRONTEND_PORT}`);
  console.log(`export WP_PORT=${ports.WP_PORT}`);
  console.log(`export WP_TEST_PORT=${ports.WP_TEST_PORT}`);
}
// Output as JSON if --json flag
else if (process.argv.includes('--json')) {
  console.log(JSON.stringify(ports, null, 2));
}
// Default: human-readable format
else {
  console.log(`\nPorts for "${examplePath}":`);
  console.log(`  Frontend:    ${ports.FRONTEND_PORT}`);
  console.log(`  WordPress:   ${ports.WP_PORT}`);
  console.log(`  WP Test:     ${ports.WP_TEST_PORT}`);
  console.log(`  (offset: ${ports.PORT_OFFSET})\n`);
}

// Also export for require()
module.exports = { getPorts, hashToPort };
