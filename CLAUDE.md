# Shadova Log App

シャドウバース（Shadowverse）の戦績管理Webアプリケーション

## プロジェクト概要

対戦ゲーム「シャドウバース」のプレイヤー向け戦績管理ツール。デッキ管理、対戦記録、統計分析、配信者向けオーバーレイ機能を提供する。

## 技術スタック

| レイヤー | 技術 | バージョン |
|---------|------|-----------|
| Backend | Laravel | 12.x |
| PHP | PHP | 8.2+ |
| Frontend | Alpine.js + Blade | 3.x |
| CSS | Tailwind CSS | 4.0 |
| Build | Vite | 7.x |
| Database | PostgreSQL | Supabase |
| Deploy | Render | - |

## 重要なコマンド

```bash
# 開発サーバー起動（推奨：全サービス同時起動）
composer dev

# 個別起動
php artisan serve --port=3000  # バックエンド
npm run dev                     # フロントエンド（Vite）

# データベース
php artisan migrate            # マイグレーション実行
php artisan migrate:fresh      # DBリセット＋マイグレーション
php artisan db:seed            # シーダー実行

# テスト
composer test                  # PHPUnit実行
php artisan test               # テスト実行

# ビルド
npm run build                  # 本番ビルド

# セットアップ（初回）
composer setup
```

## プロジェクト構造

```
app/
├── Http/Controllers/     # コントローラー
├── Models/              # Eloquentモデル
├── Policies/            # 認可ポリシー
├── Providers/           # サービスプロバイダー
└── Notifications/       # 通知クラス

resources/
├── views/               # Bladeテンプレート
└── js/                  # JavaScript（Alpine.js）

database/
├── migrations/          # DBマイグレーション
├── factories/           # モデルファクトリ
└── seeders/             # シーダー

routes/
└── web.php              # Webルート定義

config/                  # Laravel設定ファイル
tests/                   # PHPUnitテスト
```

## 主要モデルとリレーション

```
User
├── hasMany: Deck
├── hasMany: Battle
├── hasMany: ShareLink
├── hasMany: Group
├── hasOne: UserSetting
└── hasMany: StreamerSession

Battle
├── belongsTo: User
├── belongsTo: Deck (nullable)
├── belongsTo: LeaderClass (my_class_id)
├── belongsTo: LeaderClass (opponent_class_id)
├── belongsTo: Rank
├── belongsTo: Group
└── belongsTo: GameMode
```

## コード規約

### PHP/Laravel
- PSR-12準拠
- `php artisan pint` でフォーマット
- 型宣言を積極的に使用
- Eloquentリレーションは明示的に定義
- Policyでアクセス制御

### JavaScript/Alpine.js
- 2スペースインデント
- Alpine.jsのリアクティブデータは`x-data`で定義
- `@click`等のディレクティブを使用

### データベース
- テーブル名: スネークケース複数形 (例: `battles`)
- カラム名: スネークケース (例: `opponent_class_id`)
- 外部キー: `{関連テーブル単数形}_id`
- SoftDeletesは必要な場合のみ

### コメント
- 日本語コメント使用可
- 複雑なロジックには説明を追加

## 環境変数（必須）

```bash
# Supabase Database
DB_CONNECTION=pgsql
DB_HOST=db.xxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=xxxxx

# アプリケーション
APP_URL=http://localhost:3000
```

## デプロイ

- プラットフォーム: Render
- ブランチ: main
- 自動デプロイ: 有効

詳細は @docs/guides/environment-setup.md を参照

## MCPサーバー連携

このプロジェクトでは以下のMCPサーバーが利用可能:
- **Supabase**: データベース操作、マイグレーション、ログ確認
- **Render**: デプロイ状況確認、ログ監視
- **GitHub**: PR作成、Issue管理

## セキュリティ注意事項

- `.env`ファイルは絶対にコミットしない
- Supabase Service Keyはサーバーサイドのみで使用
- ユーザー入力は必ずバリデーション
- Policy経由でアクセス制御を実施
