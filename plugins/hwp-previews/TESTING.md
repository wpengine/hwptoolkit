# Testing HWP Previews

This plugin uses [Codeception](https://codeception.com/) with [WPBrowser](https://wpbrowser.wptestkit.dev/) for automated testing.  
Tests are organized into suites for unit, integration (wpunit), functional, and acceptance testing.

---

## Test Suites

- **wpunit**: Unit/integration tests with WordPress loaded.
- **functional**: Simulates web requests, runs WordPress in a test environment.
- **acceptance**: Browser-based tests (WPBrowser/WPWebDriver).

Configuration files for each suite are in the `tests/` directory (e.g., `unit.suite.dist.yml`, `wpunit.suite.dist.yml`).

---

## Local Test Environment

The plugin provides scripts to set up a local WordPress environment for testing, using Docker and environment variables defined in `.env.dist`.

### Prerequisites

- Docker (for local environment)
- Composer
- Node.js (for building assets, if needed)

---

## Setup

1. **Copy and configure environment variables:**

   ```bash
   @TODO
   cp .env.dist .env
   # Edit .env as needed for your local setup
   ```


2. **Set up the test WordPress environment:**

   ```bash
   bin/install-test-env.sh
   ```

   This script will:
   - Create the test database (unless `SKIP_DB_CREATE=true`)
   - Download and install WordPress in the directory specified by `WORDPRESS_ROOT_DIR`
   - Symlink the plugin into the WordPress plugins directory
   - Activate the plugin and set up test data

---

## Running Tests

Currently the plugin has the following suite of tests

1. WP Unit Tests - (Unit and Integration Tests)
2. E2E Tests - Playright tests

### WPUnit (WordPress-aware Unit/Integration) Tests

Run WPUnit tests (WordPress loaded):

```bash
sh bin/local/run-unit-tests.sh coverage
```

> [!IMPORTANT]
> You can also add coverage e.g. `sh bin/local/run-unit-tests.sh coverage --coverage-html` and the output will be saved in [tests/_output/coverage/dashboard.html](tests/_output/coverage/dashboard.html)


### E2WTests

Run browser-based acceptance tests:

```bash
sh bin/local/run-e2e-tests.sh coverage
```

### All Tests

To run all suites:

```bash
composer run test
# or
vendor/bin/codecept run
```

---

## Code Coverage

To generate code coverage reports (requires Xdebug or PCOV):

```bash
# Example for wpunit suite
SUITES=wpunit COVERAGE=1 bin/run-codeception.sh
```

Coverage output will be in `tests/_output/` or as specified by `COVERAGE_OUTPUT`.

---

## Useful Scripts

- `bin/install-test-env.sh` — Sets up the WordPress test environment.
- `bin/run-codeception.sh` — Runs Codeception tests inside the plugin directory.
- `bin/local/run-unit-tests.sh` — Runs unit tests in Docker.
- `bin/local/run-qa.sh` — Runs code quality checks (PHPStan, PHPCS, Psalm).

---

## Notes

- The test database will be reset during setup. **Do not use a database with important data.**
- You can customize which suites to run by setting the `SUITES` environment variable.
- See `.env.dist` for all available environment variables and their descriptions.

---

```text
tests/
├── _data/                # Test data (e.g. DB dumps)
├── _envs/                # Environment configs
├── _output/              # Test output (logs, coverage)
├── _support/             # Helper classes, modules
├── wpunit/               # WPUnit (WordPress-aware unit/integration) test cases
├── wpunit.suite.dist.yml
└── wpunit/
    └── bootstrap.php     # Bootstrap for WPUnit tests

bin/
├── install-test-env.sh   # Script to set up test WP environment
├── run-codeception.sh    # Script to run Codeception tests
└── local/
    ├── run-unit-tests.sh # Run unit tests in Docker
    └── run-qa.sh         # Run code quality checks

.env.dist                 # Example environment variables for testing
codeception.dist.yml      # Main Codeception config
```
