#!/bin/bash

# Exit if any command fails.
set -e

source ".env"

## Add the `wp plugin install` and `wp plugin activate` commands here for any external plugins that this one depends on for testing.
#
# Example: Install and activate WPGraphQL from the .org plugin repository.
#
# if ! $( wp plugin is-installed wp-graphql --allow-root ); then
#   wp plugin install wp-graphql --allow-root
# fi
# wp plugin activate wp-graphql  --allow-root
#
# Example: Install and activate the WPGraphQL Upload plugin from GitHub.
#
# if ! $( wp plugin is-installed wp-graphql-upload --allow-root ); then
#   wp plugin install https://github.com/dre1080/wp-graphql-upload/archive/refs/heads/master.zip --allow-root
# fi
# wp plugin activate wp-graphql-upload --allow-root

# We use an old version of WPGraphQL Content Blocks for testing the PUC.

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