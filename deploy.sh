#!/bin/bash
# Prepare project for Hostinger deployment
# Run this locally before uploading files

set -e

echo "[1/4] Installing production dependencies..."
composer install --no-dev --optimize-autoloader

echo "[2/4] Building frontend assets..."
npm run build

echo "[3/4] Removing public/hot (if exists)..."
rm -f public/hot

echo "[4/4] Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo ""
echo "Done. You can now upload to Hostinger."
echo "See DEPLOYMENT_CHECKLIST.md for server setup."
