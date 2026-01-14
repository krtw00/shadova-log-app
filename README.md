# Shadova Log App

シャドウバース ワールズビヨンドの戦績管理Webアプリケーション

## 概要

対戦ゲーム「シャドウバース ワールズビヨンド」のプレイヤー向け戦績管理ツールです。デッキ管理、対戦記録、統計分析、配信者向けオーバーレイ機能を提供します。

## 主な機能

| 機能 | 説明 |
|------|------|
| 対戦記録管理 | 勝敗、使用デッキ、相手クラスを記録 |
| 全対戦形式対応 | ランクマッチ、グランプリ、ルームマッチ、2Pick等 |
| 統計分析 | 勝率、クラス別分析、相性表 |
| 配信者モード | OBSオーバーレイ、セッション管理 |
| 共有機能 | 公開プロフィール |
| OAuth認証 | Google/Discord でのログイン |

## 技術スタック

| レイヤー | 技術 | バージョン |
|---------|------|-----------|
| Backend | Laravel | 12.x |
| PHP | PHP | 8.2+ |
| Frontend | Alpine.js + Blade | 3.x |
| CSS | Tailwind CSS | 4.0 |
| Build | Vite | 7.x |
| Database | PostgreSQL | Supabase |
| Auth | Laravel Socialite | 5.x |
| Deploy | Render | - |

## クイックスタート

### 必要条件

- PHP 8.2+
- Composer 2.x
- Node.js 20+
- PostgreSQL（または Supabase）

### セットアップ

```bash
# リポジトリをクローン
git clone <repository-url>
cd shadova-log-app

# セットアップ（依存関係インストール、キー生成、マイグレーション、ビルド）
composer setup

# または個別に実行
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### 環境変数

`.env` ファイルに以下を設定してください：

```bash
# データベース（Supabase）
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=xxxxx

# アプリケーション
APP_URL=http://localhost:3000

# OAuth（オプション）
GOOGLE_CLIENT_ID=xxxxx
GOOGLE_CLIENT_SECRET=xxxxx
GOOGLE_REDIRECT_URI=${APP_URL}/auth/google/callback

DISCORD_CLIENT_ID=xxxxx
DISCORD_CLIENT_SECRET=xxxxx
DISCORD_REDIRECT_URI=${APP_URL}/auth/discord/callback
```

### 開発サーバー起動

```bash
# 全サービス同時起動（推奨）
composer dev

# または個別起動
php artisan serve --port=3000  # バックエンド
npm run dev                     # フロントエンド（Vite）
```

## コマンド一覧

| コマンド | 説明 |
|---------|------|
| `composer dev` | 開発サーバー起動（全サービス同時） |
| `composer test` | PHPUnit テスト実行 |
| `composer setup` | 初期セットアップ |
| `php artisan migrate` | マイグレーション実行 |
| `php artisan migrate:fresh --seed` | DBリセット＋シード |
| `npm run build` | 本番ビルド |

## ドキュメント

詳細なドキュメントは [docs/](./docs/) を参照してください。

| ドキュメント | 内容 |
|-------------|------|
| [システム概要](./docs/architecture/system-overview.md) | アーキテクチャ、技術スタック |
| [データベース設計](./docs/architecture/db-schema.md) | テーブル定義、ER図 |
| [API仕様](./docs/api/api-reference.md) | 全ルートの仕様 |
| [機能設計](./docs/design/feature-design.md) | 各機能の詳細設計 |
| [環境構築ガイド](./docs/guides/environment-setup.md) | 開発環境セットアップ |
| [デプロイ手順](./docs/deployment/deployment.md) | Render/Supabase デプロイ |

## プロジェクト構造

```
shadova-log-app/
├── app/
│   ├── Http/Controllers/     # コントローラー
│   ├── Models/              # Eloquentモデル
│   ├── Policies/            # 認可ポリシー
│   └── Notifications/       # 通知クラス
├── resources/
│   ├── views/               # Bladeテンプレート
│   └── js/                  # JavaScript（Alpine.js）
├── database/
│   ├── migrations/          # DBマイグレーション
│   ├── factories/           # モデルファクトリ
│   └── seeders/             # シーダー
├── routes/
│   └── web.php              # Webルート定義
├── docs/                    # ドキュメント
└── tests/                   # PHPUnitテスト
```

## デプロイ

- **プラットフォーム**: Render
- **ブランチ**: main
- **自動デプロイ**: 有効

## ライセンス

MIT License
