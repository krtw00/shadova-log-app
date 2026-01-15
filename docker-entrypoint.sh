#!/bin/bash
set -e

# Laravelのキャッシュをクリア
php artisan optimize:clear

# マイグレーションを実行（RUN_MIGRATIONS=trueで有効化）
# 注意: このプロジェクトではSupabase MCPでスキーマ管理しているため、
#       通常はマイグレーションを実行しない
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running migrations (RUN_MIGRATIONS=true)..."
    php artisan migrate --force
else
    echo "Skipping migrations (default behavior)"
fi

# Apacheを起動
exec "$@"
