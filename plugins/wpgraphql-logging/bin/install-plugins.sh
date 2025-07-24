#!/bin/bash

# Exit if any command fails.
set -e

source ".env"

# WPGraphQL
install_wpgraphql() {
	if ! $( wp plugin is-installed wp-graphql --allow-root ); then
		wp plugin install wp-graphql --allow-root
	fi
	wp plugin activate wp-graphql --allow-root
}

# Run the install functions.
cd $WORDPRESS_ROOT_DIR

install_wpgraphql
