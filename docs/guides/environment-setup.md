# 環境構築ガイド

このドキュメントでは、Shadova Log Appの開発環境をセットアップする手順を説明します。

---

## 必要なソフトウェア

| ソフトウェア | バージョン | 用途 |
|-------------|-----------|------|
| PHP | 8.3+ | Laravel実行環境 |
| Composer | 2.x | PHPパッケージ管理 |
| Node.js | 20.x+ | フロントエンドビルド |
| npm / pnpm | 最新 | JSパッケージ管理 |
| Git | 最新 | バージョン管理 |

---

## 1. PHPのインストール

### Ubuntu / WSL

```bash
# PHPリポジトリを追加
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# PHP 8.3と必要な拡張をインストール
sudo apt install php8.3 php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-pgsql php8.3-bcmath

# バージョン確認
php -v
```

### macOS

```bash
# Homebrewでインストール
brew install php@8.3

# バージョン確認
php -v
```

---

## 2. Composerのインストール

```bash
# Composerをダウンロード・インストール
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# バージョン確認
composer -V
```

---

## 3. Node.jsのインストール

```bash
# nvmを使用する場合（推奨）
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
nvm install 20
nvm use 20

# バージョン確認
node -v
npm -v
```

---

## 4. プロジェクトのセットアップ

### リポジトリのクローン

```bash
git clone <repository-url>
cd shadova-log-app
```

### Laravelプロジェクトの作成（初回のみ）

```bash
# Laravelプロジェクトを作成
composer create-project laravel/laravel . --prefer-dist

# 依存関係のインストール
composer install
npm install
```

### 環境変数の設定

```bash
# .envファイルをコピー
cp .env.example .env

# アプリケーションキーを生成
php artisan key:generate
```

---

## 5. Supabaseのセットアップ

### Supabaseプロジェクトの作成

1. [Supabase](https://supabase.com/)にアクセス
2. 新規プロジェクトを作成
3. Project Settings > Database から接続情報を取得

### 環境変数の設定

`.env`ファイルを編集:

```bash
# Supabase Database
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-database-password

# Supabase Auth
SUPABASE_URL=https://xxxxxxxxxxxx.supabase.co
SUPABASE_KEY=your-anon-key
SUPABASE_SERVICE_KEY=your-service-role-key
```

### 接続情報の取得場所

| 項目 | 取得場所（Supabase Dashboard） |
|------|-------------------------------|
| DB_HOST | Settings > Database > Host |
| DB_PASSWORD | Settings > Database > Password |
| SUPABASE_URL | Settings > API > Project URL |
| SUPABASE_KEY | Settings > API > anon public |
| SUPABASE_SERVICE_KEY | Settings > API > service_role |

---

## 6. データベースマイグレーション

```bash
# マイグレーションを実行
php artisan migrate

# シーダーを実行（初期データ投入）
php artisan db:seed
```

---

## 7. 開発サーバーの起動

### バックエンド（Laravel）

```bash
# 開発サーバーを起動
php artisan serve

# http://localhost:8000 でアクセス可能
```

### フロントエンド（Vite）

```bash
# 別のターミナルで実行
npm run dev

# ホットリロードが有効になる
```

---

## 環境変数一覧

### `.env` ファイル

```bash
# アプリケーション設定
APP_NAME="Shadova Log"
APP_ENV=local
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=true
APP_URL=http://localhost:8000

# データベース（Supabase）
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-database-password

# Supabase Auth
SUPABASE_URL=https://xxxxxxxxxxxx.supabase.co
SUPABASE_KEY=your-anon-key
SUPABASE_SERVICE_KEY=your-service-role-key

# セッション・キャッシュ
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## セキュリティに関する注意事項

### 重要

1. **`.env`ファイルは絶対にコミットしない**
   - `.gitignore`に追加済み
   - 機密情報を含むため

2. **Supabase Service Keyの取り扱い**
   - `service_role`キーは管理者権限を持つ
   - フロントエンドには絶対に公開しない
   - サーバーサイドでのみ使用

3. **本番環境と開発環境で異なるキーを使用**
   - 開発用プロジェクトと本番用プロジェクトを分ける

---

## トラブルシューティング

### データベース接続エラー

```
SQLSTATE[08006] [7] could not connect to server
```

**解決策:**
1. Supabase Dashboardでプロジェクトが起動しているか確認
2. `.env`の接続情報が正しいか確認
3. IPアドレス制限がある場合は許可リストに追加

### Composerエラー

```
Your requirements could not be resolved to an installable set of packages
```

**解決策:**
```bash
# Composerのキャッシュをクリア
composer clear-cache

# 再インストール
composer install --no-cache
```

### PHP拡張が見つからない

```
PHP extension xxx is missing
```

**解決策:**
```bash
# Ubuntu/WSL
sudo apt install php8.3-xxx

# macOS
brew install php@8.3
```

---

## チェックリスト

新規メンバーのセットアップ時:

- [ ] PHP 8.3+がインストールされている
- [ ] Composerがインストールされている
- [ ] Node.js 20+がインストールされている
- [ ] `.env`ファイルを作成した
- [ ] `php artisan key:generate`を実行した
- [ ] Supabaseプロジェクトを作成した
- [ ] Supabaseの接続情報を`.env`に設定した
- [ ] `php artisan migrate`が成功した
- [ ] `php artisan serve`で開発サーバーが起動する
- [ ] `npm run dev`でフロントエンドビルドが動作する
