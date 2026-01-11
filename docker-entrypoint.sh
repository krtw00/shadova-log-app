#!/bin/bash
set -e

# Laravelのキャッシュをクリアして再生成
php artisan config:cache
php artisan route:cache
php artisan view:cache

# マイグレーションを実行（環境変数で制御、デフォルトはスキップ）
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force || echo "Migration failed, continuing..."
else
    echo "Skipping migrations (set RUN_MIGRATIONS=true to enable)"
fi

# Apacheを起動
exec "$@"
