#!/bin/bash
set -e

# デバッグ: 環境変数を表示
echo "=== Database Environment Variables ==="
echo "DB_HOST: $DB_HOST"
echo "DB_USERNAME: $DB_USERNAME"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_PORT: $DB_PORT"
echo "======================================="

# Laravelのキャッシュをクリア（キャッシュは使わない）
php artisan config:clear
php artisan route:clear
php artisan view:clear

# デバッグ: Laravelの設定を確認
echo "=== Laravel DB Config ==="
php artisan tinker --execute="echo 'DB Username: ' . config('database.connections.pgsql.username');"
echo "========================="

# マイグレーションを実行（環境変数で制御、デフォルトはスキップ）
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force || echo "Migration failed, continuing..."
else
    echo "Skipping migrations (set RUN_MIGRATIONS=true to enable)"
fi

# Apacheを起動
exec "$@"
