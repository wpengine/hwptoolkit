#!/usr/bin/env bash

set -e  # Exit on any error

echo "Setting up Docker environment..."

# Copy environment file
if [ -f "bin/local/.env.local" ]; then
    cp bin/local/.env.local .env
    echo "✓ Environment file copied"
else
    echo "❌ Error: bin/local/.env.local not found"
    exit 1
fi

# Build and start containers
echo "Building and starting Docker containers..."
composer run docker:build
docker compose up -d

# Wait for containers to be ready
echo "Waiting for containers to be ready..."
sleep 10

# Check if container is running
if ! docker ps | grep -q wpgraphql-velocity-wordpress-1; then
    echo "❌ Error: Container wpgraphql-velocity-wordpress-1 is not running"
    exit 1
fi

# Function to check if PHP extension is installed
check_extension() {
    local extension=$1
    docker exec wpgraphql-velocity-wordpress-1 php -m | grep -q "$extension"
}

# Install coverage driver (prefer PCOV over XDebug for performance)
echo "Setting up code coverage driver..."

if check_extension "pcov"; then
    echo "✓ PCOV already installed and loaded"
elif check_extension "xdebug"; then
    echo "✓ XDebug already installed and loaded"
else
    echo "Checking if PCOV is installed but not enabled..."

    # Check if PCOV is installed via PECL but not enabled
    if docker exec wpgraphql-velocity-wordpress-1 pecl list | grep -q "pcov"; then
        echo "PCOV is installed via PECL but not enabled. Enabling..."
        docker exec wpgraphql-velocity-wordpress-1 bash -c "
            docker-php-ext-enable pcov &&
            echo 'pcov.enabled=1' >> /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini
        "

        # Restart container to load the extension
        echo "Restarting container to load PCOV..."
        docker compose restart
        sleep 10

        if check_extension "pcov"; then
            echo "✓ PCOV enabled successfully"
        else
            echo "❌ Failed to enable PCOV"
        fi
    else
        echo "Installing PCOV for code coverage..."
        docker exec wpgraphql-velocity-wordpress-1 bash -c "
            pecl install --force pcov 2>/dev/null || true &&
            docker-php-ext-enable pcov &&
            echo 'pcov.enabled=1' >> /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini
        "

        # Restart container to load the extension
        echo "Restarting container to load PCOV..."
        docker compose restart
        sleep 10

        if check_extension "pcov"; then
            echo "✓ PCOV installed and enabled successfully"
        else
            echo "⚠️  PCOV setup failed, trying XDebug..."
            docker exec wpgraphql-velocity-wordpress-1 bash -c "
                pecl install --force xdebug 2>/dev/null || true &&
                docker-php-ext-enable xdebug &&
                echo 'xdebug.mode=coverage' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
            "

            # Restart container to load the extension
            echo "Restarting container to load XDebug..."
            docker compose restart
            sleep 10

            if check_extension "xdebug"; then
                echo "✓ XDebug installed and enabled successfully"
            else
                echo "❌ Failed to install both PCOV and XDebug"
                exit 1
            fi
        fi
    fi
fi

# Verify WordPress installation
echo "Checking WordPress installation..."
if docker exec wpgraphql-velocity-wordpress-1 wp core is-installed --allow-root 2>/dev/null; then
    echo "✓ WordPress is installed"
else
    echo "Installing WordPress..."
    docker exec wpgraphql-velocity-wordpress-1 wp core install \
        --url=http://localhost \
        --title="Test Site" \
        --admin_user=admin \
        --admin_password=admin \
        --admin_email=admin@example.com \
        --allow-root

    if [ $? -eq 0 ]; then
        echo "✓ WordPress installed successfully"
    else
        echo "❌ WordPress installation failed"
        exit 1
    fi
fi

# Install and activate the plugin if needed
echo "Checking plugin activation..."
if docker exec wpgraphql-velocity-wordpress-1 wp plugin is-active wpgraphql-velocity --allow-root 2>/dev/null; then
    echo "✓ Plugin is active"
else
    echo "Activating plugin..."
    docker exec wpgraphql-velocity-wordpress-1 wp plugin activate wpgraphql-velocity --allow-root
fi

# Verify coverage driver is working
echo "Verifying code coverage setup..."
if docker exec wpgraphql-velocity-wordpress-1 php -r "
    if (extension_loaded('pcov')) {
        echo 'PCOV is available';
        exit(0);
    } elseif (extension_loaded('xdebug')) {
        echo 'XDebug is available';
        exit(0);
    } else {
        echo 'No coverage driver available';
        exit(1);
    }
"; then
    echo "✓ Code coverage driver is ready"
else
    echo "❌ No code coverage driver available"
    exit 1
fi

echo ""
echo "🎉 Docker environment setup complete!"
echo ""
echo "You can now run tests with:"
echo "  docker exec -e COVERAGE=1 -e SUITES=wpunit -w /var/www/html/wp-content/plugins/wpgraphql-velocity wpgraphql-velocity-wordpress-1 bin/run-codeception.sh"
echo ""
echo "Or without coverage:"
echo "  docker exec -e SUITES=wpunit -w /var/www/html/wp-content/plugins/wpgraphql-velocity wpgraphql-velocity-wordpress-1 bin/run-codeception.sh"
