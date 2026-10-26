#!/usr/bin/env bash

ORIGINAL_PATH=$(pwd)
BASEDIR=$(dirname "$0")
PROJECT_DIR="$WORDPRESS_ROOT_DIR/wp-content/plugins/$PLUGIN_SLUG"


source "${BASEDIR}/_lib.sh"

echo -e "$(status_message "WordPress: ${WP_VERSION} PHP: ${PHP_VERSION}")"

##
# Set up before running tests.
##
setup_before() {
	cd "$PROJECT_DIR"

	# Download c3 for testing.
	if [ ! -f "c3.php" ]; then
			echo "Downloading Codeception's c3.php"
			curl -L 'https://raw.github.com/Codeception/c3/2.0/c3.php' > "c3.php"
	fi

	# Set output permission
	echo "Setting Codeception output directory permissions"
	chmod 777 -R tests/_output
}

##
# Run tests.
##
run_tests() {
	if [[ -n "$DEBUG" ]]; then
		local debug="--debug"
	fi

	if [[ -n "$COVERAGE" ]]; then
		# Generate coverage in default output locations (XML + HTML)
		local coverage="--coverage --coverage-xml --coverage-html"
	fi

	# If maintenance mode is active, de-activate it
	if wp maintenance-mode is-active --allow-root >/dev/null 2>&1; then
		echo "Deactivating maintenance mode"
		wp maintenance-mode deactivate --allow-root
	fi

	echo "Running Unit and Integration tests"
	cd "$PROJECT_DIR"

	# IMPORTANT: Build Codeception classes before running tests
	echo "Building Codeception test classes"
	vendor/bin/codecept build -c codeception.dist.yml

	if [ $? -ne 0 ]; then
		echo "Error: Codeception build failed"
		exit 1
	fi

	XDEBUG_MODE=coverage vendor/bin/codecept run -c codeception.dist.yml ${suites} ${coverage:-} ${debug:-} ${debug:-}
	if [ $? -ne 0 ]; then
			echo "Error: Codeception tests failed with exit code $?"
			exit 1
	fi

	# Check code coverage if coverage was requested
	if [[ -n "$COVERAGE" ]]; then

		# Prefer XML summary for robustness; fallback to HTML if present
		if [[ -f "tests/_output/coverage.xml" ]]; then
			# Extract total statements and covered statements from the summary metrics line
			total_statements=$(grep -Eo ' statements="[0-9]+"' "tests/_output/coverage.xml" | tail -1 | grep -Eo '[0-9]+')
			total_covered=$(grep -Eo ' coveredstatements="[0-9]+"' "tests/_output/coverage.xml" | tail -1 | grep -Eo '[0-9]+')
			if [[ -n "$total_statements" && -n "$total_covered" && "$total_statements" -gt 0 ]]; then
				coverage_percent=$(awk "BEGIN { printf \"%.2f\", ($total_covered / $total_statements) * 100 }")
			fi
		fi

		if [[ -z "$coverage_percent" && -f "tests/_output/coverage/index.html" ]]; then
			coverage_percent=$(grep -Eo '[0-9]+\.[0-9]+%' "tests/_output/coverage/index.html" | head -1 | tr -d '%')
		fi
		if [[ -z "$coverage_percent" ]]; then
			echo "Warning: Could not determine code coverage percentage."
			exit 1
		fi

		echo "Code coverage percentage found: $coverage_percent"


		required_coverage=$(grep 'min_coverage:' codeception.dist.yml | awk '{print $2}')

		if [[ -z "$required_coverage" ]]; then
			echo "No min_coverage found in codeception.dist.yml. Defaulting to 80%"
			required_coverage=80
		fi

		coverage_int=${coverage_percent%.*}
		if (( coverage_int < required_coverage )); then
			echo -e "\033[0;31mError: Code coverage is ${coverage_percent}%, which is below the required ${required_coverage}%.\033[0m"
			exit 1
		else
			echo -e "\033[0;32mCode coverage is ${coverage_percent}% (required: ${required_coverage}%)\033[0m"
		fi

	fi

}

##
# Clean up after running tests.
##
cleanup_after() {
	cd "$PROJECT_DIR"

	# Remove c3.php if it exists and cleanup is not skipped
	if [ -f "c3.php" ] && [ "$SKIP_TESTS_CLEANUP" != "true" ]; then
		echo "Removing Codeception's c3.php"
		rm "c3.php"
	fi

	# Disable XDebug or PCOV if they were enabled for code coverage
	if [[ "$COVERAGE" == '1' ]]; then
		if [[ "$USING_XDEBUG" == '1' ]]; then
			echo "Disabling XDebug 3"
			rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
		else
			echo "Disabling pcov/clobber"
			docker-php-ext-disable pcov
			sed -i '/pcov.enabled=1/d' /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini
			sed -i '/pcov.directory=${PROJECT_DIR}/d' /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini
		fi
	fi

	# Set output permission back to default
	echo "Resetting Codeception output directory permissions"
	chmod 777 -R tests/_output
}

# Prepare to run tests.
echo "Setting up for Codeception tests"
setup_before

run_tests

# Clean up after running tests.
echo "Cleaning up after Codeception tests"
cleanup_after

# Check results and exit accordingly.
if [ -f "tests/_output/failed" ]; then
	echo "Codeception tests failed."
	exit 1
else
	echo "Codeception tests completed successfully!"
fi
