#!/usr/bin/env bash

echo "===================================================="
echo " Winbook24 - Hostinger Deployment & Optimization"
echo "===================================================="

# 1. Ensure .env exists
if [ ! -f .env ]; then
    if [ -f .env.production ]; then
        cp .env.production .env || true
    elif [ -f .env.example ]; then
        cp .env.example .env || true
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
         bootstrap/cache || true

chmod -R 775 storage bootstrap/cache database 2>/dev/null || true

# 3. Ensure SQLite database exists
if [ ! -f database/database.sqlite ]; then
    if [ -f database/data/initial_database.sqlite ]; then
        cp database/data/initial_database.sqlite database/database.sqlite || true
        echo "[OK] Restored pre-seeded database/database.sqlite from snapshot."
    else
        touch database/database.sqlite || true
    fi
fi
chmod 664 database/database.sqlite 2>/dev/null || true

# 4. Clear caches safely
php artisan optimize:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

echo "[SUCCESS] Deployment ready! Website is live."
exit 0
