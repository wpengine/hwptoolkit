# Testing HWP Previews

This plugin uses [Codeception](https://codeception.com/) with [WPBrowser](https://wpbrowser.wptestkit.dev/) for automated testing.  
Tests are organized into suites for unit, integration (wpunit), functional, and acceptance testing.

---

## Test Suites

- **unit**: Pure PHP unit tests, no WordPress loaded.
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

### Unit Tests

Run unit tests (no WordPress loaded):

```bash
composer run test:unit
# or
vendor/bin/codecept run unit
```

### WPUnit (WordPress-aware Unit/Integration) Tests

Run WPUnit tests (WordPress loaded):

```bash
composer run test:wpunit
# or
vendor/bin/codecept run wpunit
```

### Functional Tests

Run functional tests (simulate web requests):

```bash
composer run test:functional
# or
vendor/bin/codecept run functional
```

### Acceptance Tests

Run browser-based acceptance tests:

```bash
composer run test:acceptance
# or
vendor/bin/codecept run acceptance
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
├── acceptance/           # Acceptance test cases
├── functional/           # Functional test cases
├── unit/                 # Unit test cases
├── wpunit/               # WPUnit (WordPress-aware unit/integration) test cases
├── acceptance.suite.dist.yml
├── functional.suite.dist.yml
├── unit.suite.dist.yml
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
