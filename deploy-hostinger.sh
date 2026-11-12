#!/usr/bin/env bash
set -e

echo "===================================================="
echo " Winbook24 - Hostinger Deployment & Optimization"
echo "===================================================="

# 1. Ensure .env exists
if [ ! -f .env ]; then
    if [ -f .env.production ]; then
        cp .env.production .env
    else
        cp .env.example .env
    fi
    echo "[OK] Created .env configuration file."
fi

# 2. Ensure writable storage and cache directories exist
mkdir -p storage/app/public \
         storage/framework/cache/data \
         storage/framework/cache/locks \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

chmod -R 775 storage bootstrap/cache database || true

# 3. Ensure SQLite database exists (restore pre-seeded snapshot if missing)
if [ ! -f database/database.sqlite ]; then
    if [ -f database/data/initial_database.sqlite ]; then
        cp database/data/initial_database.sqlite database/database.sqlite
        echo "[OK] Restored pre-seeded database/database.sqlite from snapshot."
    else
        touch database/database.sqlite
        php artisan migrate --force
        php artisan db:seed --force
    fi
fi
chmod 664 database/database.sqlite || true

# 4. Clear & optimize Laravel caches
php artisan optimize:clear || true
php artisan view:clear || true

echo "[SUCCESS] Deployment ready! Website is live."

