# Shadova Log App ドキュメンテーション

このディレクトリは、Shadova Log App（シャドウバース ワールズビヨンド戦績管理アプリ）の設計、アーキテクチャ、開発規約に関するドキュメントを**カテゴリー別に階層化**して整理したものです。

---

## プロジェクト概要

**Shadova Log App** は、シャドウバース ワールズビヨンドの対戦履歴を記録・分析するWebアプリケーションです。

### 主な機能

| 機能 | 説明 |
|------|------|
| 対戦記録管理 | 勝敗、使用デッキ、相手クラスを記録 |
| 全対戦形式対応 | ランクマッチ、グランプリ、ルームマッチ、2Pick等 |
| 統計分析 | 勝率、クラス別分析、相性表 |
| 配信者モード | OBSオーバーレイ、セッション管理 |
| 共有機能 | 公開プロフィール |

### 技術スタック

| レイヤー | 技術 |
|---------|------|
| バックエンド | Laravel 11 (PHP 8.2+) |
| フロントエンド | Blade + Alpine.js 3.15 |
| CSS | Tailwind CSS 4.0 |
| ビルド | Vite 7.0 |
| データベース | PostgreSQL (Supabase) |
| ホスティング | Render (Docker) |

---

## ドキュメント構造

```
docs/
├── README.md                          # このファイル（全体インデックス）
│
├── architecture/                      # アーキテクチャ・設計
│   ├── system-overview.md            # システム全体概要
│   ├── db-schema.md                  # データベーススキーマ
│   └── frontend-architecture.md      # フロントエンド構成
│
├── api/                              # API・ルート仕様
│   └── api-reference.md              # 全ルートの仕様
│
├── design/                           # 機能設計・ベストプラクティス
│   └── feature-design.md             # 機能設計書
│
├── guides/                           # 開発ガイドライン
│   └── environment-setup.md          # 環境構築手順
│
├── deployment/                       # デプロイ・運用
│   └── deployment.md                 # デプロイ手順
│
└── operations/                       # 運用ツール・管理
    └── (今後追加予定)
```

---

## クイックスタート

### 新規開発者の方

1. **[guides/environment-setup.md](./guides/environment-setup.md)** - 開発環境をセットアップ
2. **[architecture/system-overview.md](./architecture/system-overview.md)** - システム全体を理解
3. **[architecture/db-schema.md](./architecture/db-schema.md)** - データベース構造を確認

### 既存開発者の方

- **[api/api-reference.md](./api/api-reference.md)** - ルート仕様の確認
- **[design/feature-design.md](./design/feature-design.md)** - 機能設計の確認
- **[architecture/frontend-architecture.md](./architecture/frontend-architecture.md)** - フロントエンド構成

### デプロイ担当者の方

- **[deployment/deployment.md](./deployment/deployment.md)** - デプロイ手順

---

## カテゴリー別ドキュメント

### [アーキテクチャ・設計](./architecture/)

システムの全体構造、レイヤー設計、データベース設計について解説します。

| ドキュメント | 説明 |
|-------------|------|
| [system-overview.md](./architecture/system-overview.md) | 技術スタック、システム構成図、ディレクトリ構造 |
| [db-schema.md](./architecture/db-schema.md) | テーブル定義、ER図、リレーション |
| [frontend-architecture.md](./architecture/frontend-architecture.md) | Blade/Alpine.js構成、画面設計 |

---

### [API・ルート仕様](./api/)

バックエンドが提供する全ルート・エンドポイントについて記述します。

| ドキュメント | 説明 |
|-------------|------|
| [api-reference.md](./api/api-reference.md) | 認証・対戦記録・設定・配信者モード等の全ルート |

---

### [機能設計](./design/)

各機能の詳細設計、ビジネスロジック、認可について記述します。

| ドキュメント | 説明 |
|-------------|------|
| [feature-design.md](./design/feature-design.md) | 7つの主要機能の設計詳細 |

---

### [開発ガイドライン](./guides/)

開発を始めるために必要な環境構築、コーディング規約について説明します。

| ドキュメント | 説明 |
|-------------|------|
| [environment-setup.md](./guides/environment-setup.md) | 環境変数設定とセットアップ手順 |

---

### [デプロイ・運用](./deployment/)

本番環境へのデプロイについて説明します。

| ドキュメント | 説明 |
|-------------|------|
| [deployment.md](./deployment/deployment.md) | Render/Supabaseへのデプロイ手順 |

---

## ドキュメント検索

| 探している情報 | 読むべきドキュメント |
|--------------|------------------|
| 環境構築方法 | [guides/environment-setup.md](./guides/environment-setup.md) |
| システム概要 | [architecture/system-overview.md](./architecture/system-overview.md) |
| データベース構造 | [architecture/db-schema.md](./architecture/db-schema.md) |
| フロントエンド構成 | [architecture/frontend-architecture.md](./architecture/frontend-architecture.md) |
| API仕様 | [api/api-reference.md](./api/api-reference.md) |
| 機能設計 | [design/feature-design.md](./design/feature-design.md) |
| デプロイ手順 | [deployment/deployment.md](./deployment/deployment.md) |

---

## 主要機能の設計概要

### 対戦記録管理

- 5つのゲームモード対応（ランク、フリー、ルーム、GP、2Pick）
- モード別の入力項目（ランク、グループ等）
- 統計の自動計算（勝率、連勝等）

### 配信者モード

- セッション管理（開始/終了）
- リアルタイムオーバーレイ（5秒間隔更新）
- カスタマイズ可能な外観設定
- 連勝カウンター

### 共有機能

- ユーザー名ベースの公開URL
- 期間指定可能な共有リンク
- 有効/無効の切り替え

---

## データベース概要

| テーブル | 説明 |
|----------|------|
| `users` | ユーザー情報 |
| `battles` | 対戦記録 |
| `decks` | デッキ（ソフトデリート） |
| `leader_classes` | クラスマスタ（7クラス） |
| `game_modes` | ゲームモードマスタ（5モード） |
| `ranks` | ランクマスタ |
| `groups` | グループマスタ |
| `share_links` | 共有リンク |
| `user_settings` | ユーザー設定 |
| `streamer_sessions` | 配信セッション |

---

## ドキュメント更新のガイドライン

ドキュメントを更新・追加する場合は、以下の原則に従ってください：

1. **適切なカテゴリーに配置**: どのカテゴリーに属するかを判断
2. **このREADME.mdを更新**: 新規ドキュメントをリストに追加
3. **リンク切れがないか確認**: 相互参照を確認
4. **実装と同期**: コード変更時はドキュメントも更新
