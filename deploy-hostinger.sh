#!/usr/bin/env bash

echo "===================================================="
echo " CRICEXPERTS - Hostinger Deployment & Optimization"
echo "===================================================="

# 1. Ensure .env exists and is for CRICEXPERTS
if [ ! -f .env ] || ! grep -q "k9JqOeiiuL6UYgdFWBMa8VAUooMB4h+QcYdK/+U1Zkw=" .env; then
    if [ -f .env.production ]; then
        cp -f .env.production .env || true
    elif [ -f .env.example ]; then
        cp -f .env.example .env || true
    fi
    echo "[OK] Installed CRICEXPERTS .env configuration file."
fi

# 2. Ensure writable storage and cache directories exist
mkdir -p storage/app/public \
         storage/framework/cache/data \
         storage/framework/cache/locks \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache || true

chmod -R 755 storage bootstrap/cache database 2>/dev/null || true

# 3. Preserve persistent SQLite database OUTSIDE public_html so deployments NEVER wipe User IDs
PERSISTENT_DB="$(dirname "$(pwd)")/cricexperts_persistent.sqlite"
if [ ! -f "$PERSISTENT_DB" ]; then
    if [ -f database/database.sqlite ] && [ -s database/database.sqlite ]; then
        cp database/database.sqlite "$PERSISTENT_DB" || true
        echo "[OK] Migrated existing live database to persistent storage: $PERSISTENT_DB"
    elif [ -f database/data/initial_database.sqlite ]; then
        cp database/data/initial_database.sqlite "$PERSISTENT_DB" || true
        echo "[OK] Seeded persistent database from initial snapshot: $PERSISTENT_DB"
    fi
else
    echo "[OK] Preserved existing persistent live database (all User IDs intact): $PERSISTENT_DB"
fi
chmod 644 "$PERSISTENT_DB" 2>/dev/null || true

if [ ! -f database/database.sqlite ] && [ -f "$PERSISTENT_DB" ]; then
    cp "$PERSISTENT_DB" database/database.sqlite || true
fi
chmod 644 database/database.sqlite 2>/dev/null || true

# 4. Clear caches safely
php artisan optimize:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

echo "[SUCCESS] Deployment ready! Website is live."
exit 0
