# Basic CLI Example

This example demonstrates how to use the HWP CLI to interact with a WordPress site. It shows how to:
- Configure the CLI to connect to your WordPress site
- Use CLI commands to check WordPress and plugin status
- Use CLI commands to list installed HWP plugins

## Setup

1. Copy the environment configuration:
```bash
cp .env.example .env
```

2. Update `.env` with your WordPress site URL:
```bash
WP_URL=http://localhost:8889  # Change this to your WordPress site URL
```

3. Install dependencies:
```bash
pnpm install
```

## Available Commands

### Check WordPress Status

Check WordPress site status and plugin details:
```bash
pnpm status
```

Example output:
```
WordPress Status:

Environment: local
URL: http://localhost:8889
Version: 6.4.2
Debug Mode: Disabled

HWP Status:

Plugin: hwp-cli
Status: Active
Version: 1.0.0
REST API: Available
```

### List HWP Plugins

List all installed HWP plugins and their status:
```bash
pnpm plugins
```

Example output:
```
Installed HWP Plugins:

HWP CLI Plugin v1.0.0
Status: Active
NPM Package: Not specified
---
```

## Note

This example assumes you have a WordPress site running with the HWP CLI plugin installed and activated. The CLI commands will communicate with the WordPress site specified in your `.env` file.
