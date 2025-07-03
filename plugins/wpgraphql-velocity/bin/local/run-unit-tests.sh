#!/usr/bin/env bash

# Running Unit Tests for the WPGraphQL Velocity Plugin

# Usage:
## - sh bin/local/run-unit-tests.sh
## - sh bin/local/run-unit-tests.sh coverage --coverage-html

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
	echo "Docker is not running. Please start Docker Desktop and try again."
	open -a Docker
	exit 1
fi

COVERAGE_ARG=""
COVERAGE_OUTPUT_ARG=""

if [[ "$1" == "coverage" ]]; then
	COVERAGE_ARG="-e COVERAGE=1"
	shift
	if [[ -n "$1" ]]; then
		COVERAGE_OUTPUT_ARG="-e COVERAGE_OUTPUT=$1"
	fi
fi

docker exec $COVERAGE_ARG $COVERAGE_OUTPUT_ARG -e SUITES=wpunit -w /var/www/html/wp-content/plugins/wpgraphql-velocity wpgraphql-velocity-wordpress-1 bin/run-codeception.sh
