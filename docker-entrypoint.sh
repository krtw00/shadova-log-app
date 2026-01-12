#!/bin/bash
set -e

# Laravelのキャッシュをクリア
php artisan optimize:clear

# マイグレーションは明示的に有効化された場合のみ実行
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force
else
    echo "Skipping migrations (set RUN_MIGRATIONS=true to enable)"
fi

# Apacheを起動
exec "$@"
