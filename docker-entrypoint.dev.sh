#!/bin/bash
set -e

# Wait for database
echo "Waiting for database..."
until php -r "new PDO('pgsql:host=db;dbname=shadova', 'postgres', 'postgres');" 2>/dev/null; do
    sleep 1
done
echo "Database is ready!"

# Install dependencies if not present
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    composer install
fi

if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
fi

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Clear and cache config
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Application is ready!"

exec "$@"
