#!/bin/bash
set -e

# Laravelのキャッシュをクリアして再生成
php artisan config:cache
php artisan route:cache
php artisan view:cache

# マイグレーションを実行（本番環境）
php artisan migrate --force

# Apacheを起動
exec "$@"
