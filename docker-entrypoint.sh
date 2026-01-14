#!/bin/bash
set -e

# Laravelのキャッシュをクリア
php artisan optimize:clear

# マイグレーションを実行（SKIP_MIGRATIONS=trueで無効化可能）
if [ "${SKIP_MIGRATIONS:-false}" = "true" ]; then
    echo "Skipping migrations (SKIP_MIGRATIONS=true)"
else
    echo "Running migrations..."
    php artisan migrate --force
fi

# Apacheを起動
exec "$@"
