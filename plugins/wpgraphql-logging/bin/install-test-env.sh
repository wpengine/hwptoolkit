#!/usr/bin/env bash

# Exit if any command fails.
set -e

ORIGINAL_PATH=$(pwd)
BASEDIR=$(dirname "$0")

# Include common environment variables and functions
source "${BASEDIR}/_lib.sh"

# Common variables.
TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo $TMPDIR | sed -e "s/\/$//")

WP_DEBUG=${WP_DEBUG:-true}
SCRIPT_DEBUG=${SCRIPT_DEBUG:-true}
WP_VERSION=${WP_VERSION:-"latest"}

##
# Install the database.
##
install_db() {
	if [ "${SKIP_DB_CREATE}" = "true" ]; then
		return 0
	fi

	# parse DB_HOST for port or socket references
	local PARTS=(${WORDPRESS_DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]}
	local DB_SOCK_OR_PORT=${PARTS[1]}
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ]; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ]; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ]; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# create database
	echo -e "$(status_message "Creating the database (if it does not exist)...")"

	RESULT=$(mysql -u $WORDPRESS_DB_USER --password="$WORDPRESS_DB_PASSWORD" --skip-column-names -e "SHOW DATABASES LIKE '$WORDPRESS_DB_NAME'"$EXTRA)
	if [ "$RESULT" != $WORDPRESS_DB_NAME ]; then
		mysqladmin create $WORDPRESS_DB_NAME --user="$WORDPRESS_DB_USER" --password="$WORDPRESS_DB_PASSWORD"$EXTRA
	fi
}

download() {
	if [ $(which curl) ]; then
		curl -s "$1" >"$2"
	elif [ $(which wget) ]; then
		wget -nv -O "$2" "$1"
	fi
}

install_wordpress() {
	# Create the WordPress root directory if it doesn't exist.
	echo -e "$(status_message "Switching to the WordPress root directory $WORDPRESS_ROOT_DIR")"
	mkdir -p "$WORDPRESS_ROOT_DIR"
	cd "$WORDPRESS_ROOT_DIR" || { echo -e "$(error_message "Failed to enter directory: $WORDPRESS_ROOT_DIR")"; exit 1; }

	# Download WordPress
	if [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
		mkdir -p $TMPDIR/wordpress-nightly
		download https://wordpress.org/nightly-builds/wordpress-latest.zip $TMPDIR/wordpress-nightly/wordpress-nightly.zip
		unzip -q $TMPDIR/wordpress-nightly/wordpress-nightly.zip -d $TMPDIR/wordpress-nightly/
		mv $TMPDIR/wordpress-nightly/wordpress/* $WORDPRESS_ROOT_DIR
	else
		if [ $WP_VERSION == 'latest' ]; then
			local ARCHIVE_NAME='latest'
		elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+ ]]; then
			# https serves multiple offers, whereas http serves single.
			download https://api.wordpress.org/core/version-check/1.7/ $TMPDIR/wp-latest.json
			if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
				# version x.x.0 means the first release of the major version, so strip off the .0 and download version x.x
				LATEST_VERSION=${WP_VERSION%??}
			else
				# otherwise, scan the releases and get the most up to date minor version of the major release
				local VERSION_ESCAPED=$(echo $WP_VERSION | sed 's/\./\\\\./g')
				LATEST_VERSION=$(grep -o '"version":"'$VERSION_ESCAPED'[^"]*' $TMPDIR/wp-latest.json | sed 's/"version":"//' | head -1)
			fi
			if [[ -z "$LATEST_VERSION" ]]; then
				local ARCHIVE_NAME="wordpress-$WP_VERSION"
			else
				local ARCHIVE_NAME="wordpress-$LATEST_VERSION"
			fi
		else
			local ARCHIVE_NAME="wordpress-$WP_VERSION"
		fi
		download https://wordpress.org/${ARCHIVE_NAME}.tar.gz $TMPDIR/wordpress.tar.gz
		tar --strip-components=1 -zxmf $TMPDIR/wordpress.tar.gz -C $WORDPRESS_ROOT_DIR
	fi
}

configure_wordpress() {
	if [ "${SKIP_WP_SETUP}" = "true" ]; then
		echo -e "$(warning_message "Skipping WordPress setup...")"
		return 0
	fi

	cd $WORDPRESS_ROOT_DIR

	# Create a wp-config.php file if it doesn't exist.
	if [ ! -f "wp-config.php" ]; then
		echo -e "$(status_message "Creating wp-config.php file...")"
		wp config create --dbname="$WORDPRESS_DB_NAME" --dbuser="$WORDPRESS_DB_USER" --dbpass="$WORDPRESS_DB_PASSWORD" --dbhost="$WORDPRESS_DB_HOST" --dbprefix="$WORDPRESS_TABLE_PREFIX" --allow-root
	fi

	# Install WordPress.
	echo -e "$(status_message "Installing WordPress...")"

	SITE_TITLE=${WORDPRESS_SITE_TITLE:-"WPGraphQL Logging Tests"}

	wp core install --title="$SITE_TITLE" --admin_user="$WORDPRESS_ADMIN_USER" --admin_password="$WORDPRESS_ADMIN_PASSWORD" --admin_email="$WORDPRESS_ADMIN_EMAIL" --skip-email --url="$WORDPRESS_URL" --allow-root

	echo -e "$(status_message "Running WordPress version: $(wp core version --allow-root) at $(wp option get home --allow-root)")"
}

setup_file_permissions() {
	# Make sure the uploads and upgrade folders exist and we have permissions to add files.
	echo -e "$(status_message "Ensuring that files can be uploaded...")"

	mkdir -p \
		wp-content/uploads \
		wp-content/upgrade
	chmod 777 \
		wp-content \
		wp-content/plugins \
		wp-config.php \
		wp-settings.php \
		wp-content/uploads \
		wp-content/upgrade

	# Install a dummy favicon to avoid 404 errors.
	echo -e "$(status_message "Installing a dummy favicon...")"
	touch favicon.ico
	chmod 767 favicon.ico
}

setup_plugin() {
	if [ "${SKIP_WP_SETUP}" = "true" ]; then
		echo -e "$(warning_message "Skipping wpgraphql-logging installation...")"
		return 0
	fi

	# Add this repo as a plugin to the repo
	if [ ! -d $WORDPRESS_ROOT_DIR/wp-content/plugins/wpgraphql-logging ]; then
		echo -e "$(status_message "Symlinking the plugin to the WordPress plugins directory...")"

		cd "$ORIGINAL_PATH"
		ln -s "$(pwd)" "$WORDPRESS_ROOT_DIR/wp-content/plugins/$PLUGIN_SLUG"
	fi

	cd "$ORIGINAL_PATH"

	# Install composer deps
	echo -e "$(status_message "Installing Composer deps")"
	composer install

	if [ -f "package.json" ]; then
		# Install npm deps
		echo -e "$(status_message "Installing NPM Deps")"
		npm install --no-audit --no-fund --no-progress

		# Build the plugin
		# npm run build
	fi
}

post_setup() {
	# Ensure we are in the WordPress root directory.
	cd "$WORDPRESS_ROOT_DIR"

	# Activate the plugin.
	echo -e "$(status_message "Activating the plugin...")"
	wp plugin activate "$PLUGIN_SLUG" --allow-root

	# Set pretty permalinks.
	echo -e "$(status_message "Setting permalink structure...")"
	wp rewrite structure '/%year%/%monthnum%/%postname%/' --hard --allow-root
	wp rewrite flush --allow-root

	wp config set WP_DEBUG true --raw --allow-root
	wp config set WP_DEBUG_LOG true --raw --allow-root
	wp config set GRAPHQL_DEBUG true --raw --allow-root

	wp core update-db --allow-root

	# Disable Update Checks
	echo -e "$(status_message "Disabling update checks...")"
	wp config set WP_AUTO_UPDATE_CORE false --raw --type=constant --quiet --allow-root
	wp config set AUTOMATIC_UPDATER_DISABLED true --raw --type=constant --quiet --allow-root

	# Export the db for codeception to use
	SQLDUMP="$WORDPRESS_ROOT_DIR/wp-content/plugins/$PLUGIN_SLUG/tests/_data/dump.sql"
	mkdir -p "$(dirname "$SQLDUMP")"
	if [ ! -f "$SQLDUMP" ]; then
		echo -e "$(status_message "Exporting test database dump...")"

		wp db export "$SQLDUMP" --allow-root
	fi

	echo -e "$(status_message "Installed plugins")"
	wp plugin list --allow-root
}

##
#  The main function to install the test environment.
##

echo "Installing test environment for WordPress ${WP_VERSION}..."

# Create the database if it doesn't exist.
install_db

# If this is the test site, we reset the database so no posts/comments/etc.
# dirty up the tests.
if [ "$1" == '--reset-site' ]; then
	echo -e "$(status_message "Resetting test database...")"
	wp db reset --yes --quiet --allow-root
fi

install_wordpress
configure_wordpress
setup_file_permissions

# Plugins are in a separate script to keep things clean.
echo -e "$(status_message "Installing external plugins...")"
cd "$ORIGINAL_PATH"

bash "$ORIGINAL_PATH/$BASEDIR/install-plugins.sh"

setup_plugin
post_setup
