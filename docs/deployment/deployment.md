# デプロイメントガイド

このドキュメントは、Shadova Log App のデプロイ手順について記述します。

---

## 本番環境構成

| コンポーネント | サービス | 説明 |
|---------------|---------|------|
| アプリケーション | Render | Docker コンテナホスティング |
| データベース | Supabase | PostgreSQL マネージド |
| DNS | (任意) | カスタムドメイン設定 |

---

## Render へのデプロイ

### 前提条件

- Render アカウント
- GitHub リポジトリ
- Supabase プロジェクト

### 1. Web Service の作成

1. Render ダッシュボードで「New +」→「Web Service」を選択
2. GitHub リポジトリを接続
3. 以下の設定を行う:

| 設定項目 | 値 |
|----------|-----|
| Name | `shadova-log` |
| Region | `Singapore` (推奨) |
| Branch | `main` |
| Runtime | `Docker` |
| Instance Type | `Starter` 以上 |

### 2. 環境変数の設定

Render の「Environment」タブで以下を設定:

```env
# アプリケーション
APP_NAME="Shadova Log"
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_URL=https://your-app.onrender.com

# ロケール
APP_LOCALE=ja
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=ja_JP

# データベース (Supabase)
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-password

# セッション・キャッシュ・キュー
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# メール (本番用SMTPサービス)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Shadova Log"

# ログ
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### 3. Dockerfile

プロジェクトルートの `Dockerfile`:

```dockerfile
FROM php:8.4-apache

# システムパッケージのインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm

# PHP拡張のインストール
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apacheの設定
RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# 作業ディレクトリ
WORKDIR /var/www/html

# ソースコードをコピー
COPY . .

# 依存関係のインストール
RUN composer install --optimize-autoloader --no-dev
RUN npm ci && npm run build

# パーミッション設定
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# ポート公開
EXPOSE 80

# 起動コマンド
CMD ["apache2-foreground"]
```

### 4. Apache 設定

`docker/apache.conf`:

```apache
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

### 5. 初回デプロイ後の作業

デプロイ完了後、Render Shell または SSH で以下を実行:

```bash
# マイグレーション実行
php artisan migrate --force

# キャッシュクリア
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Supabase データベース設定

### 1. プロジェクト作成

1. Supabase ダッシュボードで新規プロジェクト作成
2. リージョンは「Northeast Asia (Tokyo)」推奨
3. 強力なパスワードを設定

### 2. 接続情報の取得

Project Settings → Database から以下を取得:

- Host
- Database name
- Port
- User
- Password

### 3. 接続テスト

```bash
psql "postgres://postgres:[password]@db.xxxxx.supabase.co:5432/postgres"
```

---

## ローカル開発環境

### セットアップ

```bash
# リポジトリクローン
git clone https://github.com/your-repo/shadova-log-app.git
cd shadova-log-app

# 依存関係インストール
composer install
npm install

# 環境設定
cp .env.example .env
php artisan key:generate

# .env を編集してDB接続情報を設定

# マイグレーション
php artisan migrate

# 開発サーバー起動
composer run dev
```

### 開発コマンド

| コマンド | 説明 |
|---------|------|
| `composer run dev` | 開発サーバー起動（PHP + Queue + Logs + Vite） |
| `npm run dev` | Vite 開発サーバー |
| `npm run build` | 本番ビルド |
| `php artisan migrate` | マイグレーション実行 |
| `php artisan migrate:fresh` | DB リセット + マイグレーション |

---

## Docker ローカル開発

### ビルド & 起動

```bash
# イメージビルド
docker build -t shadova-log .

# コンテナ起動
docker run -p 80:80 \
  -e APP_KEY=base64:xxxxx \
  -e DB_HOST=host.docker.internal \
  -e DB_DATABASE=shadova_log \
  shadova-log
```

---

## CI/CD

### GitHub Actions (例)

`.github/workflows/deploy.yml`:

```yaml
name: Deploy to Render

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Deploy to Render
        uses: johnbeynon/render-deploy-action@v0.0.8
        with:
          service-id: ${{ secrets.RENDER_SERVICE_ID }}
          api-key: ${{ secrets.RENDER_API_KEY }}
```

---

## 本番環境チェックリスト

### デプロイ前

- [ ] `APP_ENV=production` 設定済み
- [ ] `APP_DEBUG=false` 設定済み
- [ ] `APP_KEY` が設定済み
- [ ] データベース接続情報が正しい
- [ ] HTTPS 設定済み
- [ ] メール設定済み（パスワードリセット用）

### デプロイ後

- [ ] マイグレーション実行済み
- [ ] キャッシュ生成済み
- [ ] ログイン・登録が動作する
- [ ] 対戦記録の作成が動作する
- [ ] Mixed Content エラーがない

---

## トラブルシューティング

### Mixed Content エラー

HTTPS 環境で HTTP リソースを読み込もうとしている場合:

1. `APP_URL` が `https://` で始まっていることを確認
2. `TrustProxies` ミドルウェアが有効か確認
3. 必要に応じて `AppServiceProvider` で `URL::forceScheme('https')` を追加

### セッションが保持されない

1. `SESSION_DRIVER=database` が設定されているか確認
2. `sessions` テーブルが存在するか確認
3. Cookie ドメインが正しいか確認

### データベース接続エラー

1. Supabase の接続情報が正しいか確認
2. IP 許可リストに Render の IP が含まれているか確認
3. SSL 設定が正しいか確認

### 500 エラー

1. `storage/logs/laravel.log` を確認
2. `APP_DEBUG=true` に一時的に変更して詳細を確認
3. パーミッション設定を確認

---

## バックアップ

### データベースバックアップ

Supabase ダッシュボードからバックアップをダウンロード可能。

手動バックアップ:
```bash
pg_dump "postgres://..." > backup.sql
```

### リストア

```bash
psql "postgres://..." < backup.sql
```

---

## スケーリング

### 垂直スケーリング

Render のインスタンスタイプをアップグレード:

- Starter → Standard → Pro

### 水平スケーリング

現在はシングルインスタンス構成。
将来的にセッション管理を Redis に移行することで水平スケーリング対応可能。

---

## 監視

### Render モニタリング

- CPU / メモリ使用率
- レスポンスタイム
- エラーレート

### ログ確認

Render ダッシュボードの「Logs」タブから確認可能。

---

## 関連ドキュメント

- [システム概要](../architecture/system-overview.md)
- [環境セットアップ](../guides/environment-setup.md)
