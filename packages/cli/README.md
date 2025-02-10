# @hwp/cli

Command-line interface for the Headless WordPress Toolkit.

## Features

- Pure ES Modules
- No build step required
- WordPress plugin status checks
- Extensible command system

## Usage

```bash
# Check WordPress plugin status
pnpm dlx @hwp/cli status

# List installed HWP plugins
pnpm dlx @hwp/cli plugins
```

## Development

Each package includes its own WordPress development environment via `wp-env`:

```bash
# Start WordPress
pnpm dev

# Stop WordPress
pnpm stop

# Clean environment
pnpm clean
```

The WordPress environment will automatically:

1. Start at http://localhost:8889
2. Install and activate the hwp-cli plugin
3. Enable WP_DEBUG for development

## Plugin Integration

This package works with the `hwp-cli` WordPress plugin.
