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

## ロールバック手順

デプロイ後に問題が発生した場合のロールバック手順です。

### Render でのロールバック

1. **Render ダッシュボードでのロールバック**
   - Events タブを開く
   - 正常に動作していたデプロイを選択
   - 「Rollback to this deploy」をクリック

2. **Git を使用したロールバック**
   ```bash
   # 前のコミットに戻す
   git revert HEAD
   git push origin main

   # または特定のコミットに戻す
   git revert <commit-hash>
   git push origin main
   ```

### マイグレーションのロールバック

```bash
# 最後のマイグレーションを1つ戻す
php artisan migrate:rollback --step=1

# 特定のバッチまで戻す
php artisan migrate:rollback --batch=3

# 全てロールバック（注意: データ損失の可能性）
php artisan migrate:reset
```

**注意事項:**
- ロールバック前に必ずバックアップを取得
- `down()` メソッドが正しく実装されているか確認
- 本番環境では `--force` オプションが必要

### 緊急時のロールバックフロー

```
1. 障害検知
   ↓
2. 影響範囲の確認（ユーザー影響度）
   ↓
3. ロールバック判断（5分以内に復旧できなければロールバック）
   ↓
4. Render でロールバック実行
   ↓
5. 動作確認
   ↓
6. 原因調査・修正
```

---

## Secrets / Config 管理

### 環境変数の分類

| 分類 | 例 | 管理方法 |
|-----|-----|---------|
| シークレット | `DB_PASSWORD`, `APP_KEY` | Render Environment Variables（暗号化） |
| 設定値 | `APP_ENV`, `LOG_LEVEL` | Render Environment Variables |
| 公開設定 | `APP_NAME`, `APP_LOCALE` | `.env.example` + Render |

### シークレット管理のベストプラクティス

1. **絶対にコミットしない**
   - `.env` は `.gitignore` に含める
   - シークレットを含むファイルをコミットしない

2. **Render での管理**
   - Environment Variables でシークレットを設定
   - 「Secret」タイプを使用（値が非表示になる）

3. **ローテーション**
   - `APP_KEY`: 定期的な更新は不要（ユーザーセッションが切れる）
   - `DB_PASSWORD`: 漏洩の疑いがある場合のみ更新
   - OAuth クライアントシークレット: プロバイダーのガイドラインに従う

### 環境別の設定

| 設定項目 | 開発環境 | 本番環境 |
|---------|---------|---------|
| `APP_ENV` | local | production |
| `APP_DEBUG` | true | **false** |
| `LOG_LEVEL` | debug | error |
| `SESSION_DRIVER` | file | database |
| `CACHE_STORE` | file | database |

---

## マイグレーション方針

### 本番環境でのマイグレーション

1. **デプロイ前の確認**
   ```bash
   # ローカルでマイグレーションテスト
   php artisan migrate:fresh
   php artisan migrate:rollback
   ```

2. **デプロイ時の実行**
   - Render の Start Command に含める（推奨）
   - または手動で `php artisan migrate --force`

3. **破壊的変更の対応**
   - カラム削除: 2段階デプロイ（1. 参照削除 → 2. カラム削除）
   - テーブル名変更: 新テーブル作成 → データ移行 → 旧テーブル削除

### マイグレーションのベストプラクティス

```php
// 良い例: ロールバック可能
public function up(): void
{
    Schema::table('battles', function (Blueprint $table) {
        $table->string('notes', 500)->nullable()->after('result');
    });
}

public function down(): void
{
    Schema::table('battles', function (Blueprint $table) {
        $table->dropColumn('notes');
    });
}

// 悪い例: ロールバック不可
public function down(): void
{
    // データが失われる可能性がある操作は慎重に
}
```

---

## リリース前チェックリスト

### コードレビュー

- [ ] PR がレビュー済み
- [ ] テストが全て通過
- [ ] コードスタイルチェック通過（`php artisan pint`）
- [ ] セキュリティ脆弱性なし（`composer audit`）

### 機能確認

- [ ] 新機能が仕様通り動作
- [ ] 既存機能への影響なし
- [ ] エラーハンドリングが適切
- [ ] レスポンシブ対応（モバイル確認）

### デプロイ準備

- [ ] マイグレーションファイルの確認
- [ ] 環境変数の追加・変更がある場合、Render に設定済み
- [ ] 依存パッケージの更新がある場合、`composer.lock` / `package-lock.json` コミット済み
- [ ] バックアップ取得済み（大規模変更の場合）

### デプロイ後確認

- [ ] デプロイが正常完了
- [ ] ヘルスチェック通過
- [ ] ログにエラーなし
- [ ] 主要機能の動作確認
  - [ ] ログイン/ログアウト
  - [ ] 対戦記録の作成/編集/削除
  - [ ] 統計表示
  - [ ] 配信者モード（有効な場合）

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
