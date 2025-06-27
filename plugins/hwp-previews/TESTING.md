# Testing HWP Previews

## Table of Contents

- [Overview](#overview)
	- [Directory Structure](#directory-structure)
	- [Technologies](#technologies)
- [Usage](#usage)
  - [Running Tests](#running-tests)
  - [GitHub Actions](#github-actions)
- [Setup Tests Locally](#setup-tests-locally)

---

## Overview

HWP Previews comes with automated tests for unit, integration, and acceptance (E2E) scenarios.

## Directory Structure

A list of related files and directories for testing:

```text
bin/
├── install-test-env.sh       # Set up test WP environment
├── run-codeception.sh        # Run Codeception tests
├── run-e2e.sh                # Run E2E (Playwright) tests
├── run-coverage.sh           # Generate coverage reports
└── local/
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
├── wpunit/                   # WPUnit (WordPress-aware unit/integration) test cases
├── wpunit.suite.dist.yml
└── wpunit/
    └── bootstrap.php         # Bootstrap for WPUnit tests

.env.dist                     # Example environment variables for testing
codeception.dist.yml          # Main Codeception config
```
---

## Technologies

We use the following technologies to run our tests:

- [Codeception](https://codeception.com/)
- [WPBrowser](https://wpbrowser.wptestkit.dev/)
- [WPUnit](https://github.com/lipemat/wp-unit)
- [Docker](https://www.docker.com/)
- [Composer](https://getcomposer.org/)
- [Playwright](https://playwright.dev/)
- [npm](https://www.npmjs.com/)

---

## Usage

Currently, the plugin has the following suite of tests:

1. **WP Unit Tests** – Unit and Integration Tests
2. **E2E Tests** – Acceptance tests

### Running Tests

| Command                                 | Description                                              |
|------------------------------------------|----------------------------------------------------------|
| `composer run test:unit:coverage`        | Run WPUnit (unit/integration) tests with coverage report |
| `composer run test:unit:coverage-html`   | Generate an HTML code coverage report                    |
| `composer run test:e2e`                  | Run end-to-end (E2E) acceptance tests                    |
| `composer run test`                      | Run all available test suites                            |

---

## GitHub Actions

We have a few checks which run for a new PR being merged to main

| Workflow                | Description                                 | Link                                                                 |
|-------------------------|---------------------------------------------|----------------------------------------------------------------------|
| Code Quality            | Runs static analysis and linting checks     | [View Workflow](../../actions/workflows/code-quality.yml)             |
| E2E Tests               | Runs Playwright end-to-end acceptance tests | [View Workflow](../../actions/workflows/e2e.yml)                      |
| Codeception (WPUnit)    | Runs unit and integration tests             | [View Workflow](../../actions/workflows/codeception.yml)              |


> **INFO:**  
> All tests are automatically run on every pull request via GitHub Actions. You can review test results and logs directly in the "Checks" tab of your PR on GitHub.

> **IMPORTANT:**  
> Test coverage for WP Unit Tests is 95%. Any new code will require tests to be added in order to pass.

---

## Setup Tests Locally

@TODO


## Contributing

If you feel like something is missing or you want to add tests or testing documentation, we encourage you to contribute! Please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details.
