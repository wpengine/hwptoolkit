---
title: How To Guide: Run Tests
description: Learn how to run the automated tests for the WPGraphQL Logging plugin.
---

## Overview

HWP Previews comes with automated tests for unit, integration, and acceptance (E2E) scenarios to ensure code quality and functionality.

### Directory Structure

A list of related files and directories for testing:

```text
bin/
├── install-test-env.sh       # Set up test WP environment
├── run-codeception.sh        # Run Codeception tests
├── run-e2e.sh                # Run E2E (Playwright) tests
├── run-coverage.sh           # Generate coverage reports
└── local/
    ├── setup-docker-env.sh   # Setup Docker environment
    ├── run-unit-tests.sh     # Run unit tests in Docker with Codeception
    ├── run-e2e-tests.sh      # Run e2e tests in Docker with Playwright
    ├── run-qa.sh             # Run php code quality checks with PHPStan, Psalm and PHPCS
    ├── run-wpunit.sh         # Run WPUnit tests in Docker
    └── run-functional.sh     # Run functional tests in Docker

tests/
├── _data/                    # Test data (e.g. DB dumps)
├── _envs/                    # Environment configs
├── _output/                  # Test output (logs, coverage)
├── _support/                 # Helper classes, modules
├── e2e/                      # End-to-end tests (Playwright)
│   ├── specs/                # Test specifications
│   │   ├── basic-usage.spec.js
│   │   ├── data-cleanup.spec.js
│   │   └── exclude-and-sanitize.spec.js
│   ├── plugins/              # Test helper plugins
│   ├── config/               # E2E test configuration
│   ├── utils.js              # Helper functions
│   ├── constants.js          # Test constants
│   └── playwright.config.js  # Playwright configuration
├── wpunit/                   # WPUnit (WordPress-aware unit/integration) test cases
├── wpunit.suite.dist.yml
└── wpunit/
    └── bootstrap.php         # Bootstrap for WPUnit tests

.env.dist                     # Example environment variables for testing
codeception.dist.yml          # Main Codeception config
```

### Technologies

We use the following technologies to run our tests:

* [Codeception](https://codeception.com/) - PHP testing framework
* [WPBrowser](https://wpbrowser.wptestkit.dev/) - WordPress-specific testing tools
* [WPUnit](https://github.com/lipemat/wp-unit) - WordPress unit testing
* [Docker](https://www.docker.com/) - Containerized testing environment
* [Composer](https://getcomposer.org/) - PHP dependency management
* [Playwright](https://playwright.dev/) - End-to-end testing framework
* [npm](https://www.npmjs.com/) - JavaScript package manager

***

## Usage

The plugin includes the following test suites:

1. **WP Unit Tests** – Unit and Integration Tests
2. **E2E Tests** – Acceptance tests using Playwright

### Running Tests

| Command                                | Description                                              |
| -------------------------------------- | -------------------------------------------------------- |
| `composer run test:unit:coverage`      | Run WPUnit (unit/integration) tests with coverage report |
| `composer run test:unit:coverage-html` | Generate an HTML code coverage report                    |
| `composer run test:e2e`                | Run end-to-end (E2E) acceptance tests                    |
| `composer run test`                    | Run all available test suites                            |

### GitHub Actions

Automated testing runs on every pull request via GitHub Actions for a modified plugin:

| Workflow                 | Description                                 | Status                                                    |
| ------------------------ | ------------------------------------------- | --------------------------------------------------------- |
| **Code Quality**         | Runs static analysis and linting checks     | [View Workflow](../../actions/workflows/code-quality.yml) |
| **E2E Tests**            | Runs Playwright end-to-end acceptance tests | [View Workflow](../../actions/workflows/e2e.yml)          |
| **Codeception (WPUnit)** | Runs unit and integration tests             | [View Workflow](../../actions/workflows/codeception.yml)  |

## E2E Tests

End-to-end tests use Playwright to simulate real user workflows from configuring the plugin to viewing logs and managing data.

### Test Suites

| Test Suite                       | Description                      | Key Scenarios                                            |
| -------------------------------- | -------------------------------- | -------------------------------------------------------- |
| **basic-usage.spec.js**          | Core logging functionality       | Enable logging, execute queries, view logs, download CSV |
| **exclude-and-sanitize.spec.js** | Query filtering and data privacy | Exclude queries, sanitize sensitive data                 |
| **data-cleanup.spec.js**         | Data management                  | Configure automatic log deletion, verify cron job        |

### Test Helper Plugins

Located in `tests/e2e/plugins/`:

* **`reset-wpgraphql-logging-settings`** - Resets plugin settings and clears logs table for clean test state

### Running E2E Tests

```shell
# Start wp-env (make sure Docker is running)
npm run wp-env start

# Run all E2E tests
npm run test:e2e

# Run specific test file
npm run test:e2e tests/e2e/specs/basic-usage.spec.js

# Run tests in headed mode (with browser UI)
npm run test:e2e:debug
```

> \[!IMPORTANT]
> Test coverage for WP Unit Tests is **95%**. Any new code will require tests to be added in order to pass CI checks. This is set in [text](codeception.dist.yml) in the parameter `min_coverage`.

***

## Setup Tests Locally

### Prerequisites

* Docker and Docker Compose installed and running
* Composer installed
* Node.js and npm installed (for E2E tests)
* Terminal/command line access

### Docker Setup

> \[!NOTE]
> You need Docker running locally before setting up tests. Alternatively, you can copy `.env.dist` to `.env` and update the database details to point to your local database. However, this will make database changes, so we recommend using the Docker setup instead.

To set up your local Docker environment, run:

```shell
sh bin/local/setup-docker-env.sh
```

This script will automatically handle the complete Docker environment setup process.

### What the Setup Script Does

The setup script performs the following operations:

#### 1. Environment Verification

* ✅ Checks that Docker is running
* ✅ Verifies required files exist

#### 2. Configuration Setup

* 📁 Copies `bin/local/.env.local` to `.env`
  * Uses local development configuration (different from `.env.dist`)
  * Sets appropriate database credentials and WordPress settings

#### 3. Docker Container Management

* 🐳 Runs `composer run docker:build`
  * Executes `sh bin/build-docker.sh` to create the Docker container
  * Builds WordPress environment with PHP 8.2
* 🚀 Runs `docker compose up -d` to start the container in detached mode
  * Creates container named `wpgraphql-logging-wordpress-1`
  * Sets up WordPress with test database

#### 4. Code Coverage Setup

* 🔧 Installs and configures PCOV extension (preferred for performance)
* 🔄 Falls back to XDebug if PCOV installation fails
* ⚙️ Configures coverage settings automatically
* 🔄 Restarts container to ensure extensions are loaded

#### 5. WordPress Installation

* 📝 Installs WordPress if not already present
* 🔌 Activates the plugin automatically
* ✅ Verifies the installation is working correctly

### Running Tests Locally

Once setup is complete, you can run tests using Composer:

<\[!NOTE]
< Ensure the docker container is running before executing tests. You can run `composer run docker:start` to start it.

```shell
# Run unit tests with coverage
composer run test:unit:coverage

# Run all tests
composer run test

# Run E2E tests
composer run test:e2e
```

For a full list of available test commands, see the [Usage](#usage) section above.

***

## Troubleshooting

### Container Issues

```shell
# Check container status
docker ps | grep wpgraph-logging

# Restart containers if needed
docker compose restart

# View container logs
docker compose logs wpgraph-logging-wordpress-1
```

### Permission Issues

```shell
# Fix test output permissions
docker exec wpgraphql-logging-wordpress-1 chmod 777 -R tests/_output
```

### Coverage Driver Issues

```shell
# Check which coverage driver is available
docker exec wpgraphql-logging-wordpress-1 php -m | grep -E "(pcov|xdebug)"

# Re-run setup if coverage isn't working
sh bin/local/setup-docker-env.sh
```

### WordPress Database Issues

```shell
# Reinstall WordPress
docker exec wpgraphql-logging-wordpress-1 wp core install \
  --url=http://localhost \
  --title="Test Site" \
  --admin_user=admin \
  --admin_password=admin \
  --admin_email=admin@example.com \
  --allow-root
```

### Clean Up Environment

```shell
# Stop containers
docker compose down

# Remove containers and volumes (complete cleanup)
docker compose down -v
```

***

## Contributing

If you feel like something is missing or you want to add tests or testing documentation, we encourage you to contribute! Please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details.
