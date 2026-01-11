# Shadova Log App ドキュメンテーション

このディレクトリは、Shadova Log App（シャドウバース戦績管理アプリ）の設計、アーキテクチャ、開発規約に関するドキュメントを**カテゴリー別に階層化**して整理したものです。

## 📖 ドキュメント構造

```
docs/
├── README.md （このファイル - 全体インデックス）
│
├── architecture/           📐 アーキテクチャ・設計
│   ├── system-overview.md
│   ├── db-schema.md
│   └── frontend-architecture.md
│
├── api/                    🔌 API・統合
│   └── api-reference.md
│
├── guides/                 📋 開発ガイドライン
│   ├── environment-setup.md
│   └── development-guide.md
│
├── design/                 💡 設計思想・ベストプラクティス
│   └── error-handling.md
│
├── deployment/             🚀 デプロイ・運用
│   └── deployment.md
│
└── operations/             🐛 運用ツール・管理
    └── (今後追加予定)
```

---

## 🎯 プロジェクト概要

**Shadova Log App** は、シャドウバース（Shadowverse）の対戦履歴を記録・分析するWebアプリケーションです。

### 主な機能
- 対戦履歴の記録（勝敗、使用デッキ、相手クラス）
- 全対戦形式対応（ランクマッチ、グランプリ、ルームマッチ、2Pick等）
- 勝率統計・クラス別分析
- 複数ユーザー対応

### 技術スタック
- **バックエンド:** Laravel 11 (PHP 8.3+)
- **フロントエンド:** Blade + Alpine.js / Inertia.js + Vue 3（検討中）
- **データベース:** Supabase (PostgreSQL)
- **認証:** Supabase Auth

---

## 🎯 クイックスタート

### 🆕 新規開発者の方
1. **[guides/environment-setup.md](./guides/environment-setup.md)** - 開発環境をセットアップ
2. **[guides/development-guide.md](./guides/development-guide.md)** - ブランチ戦略とワークフローを理解
3. **[architecture/](./architecture/)** - システムアーキテクチャを学習

### 👨‍💻 既存開発者の方
- **[api/api-reference.md](./api/api-reference.md)** - API仕様の確認
- **[architecture/db-schema.md](./architecture/db-schema.md)** - データベーススキーマの確認

### 🚀 デプロイ担当者の方
- **[deployment/deployment.md](./deployment/deployment.md)** - デプロイ手順

---

## 📚 カテゴリー別ドキュメント

### 🏗️ [アーキテクチャ・設計](./architecture/)
システムの全体構造、レイヤー設計、データベース設計について解説します。

**含まれる内容:**
- システム全体概要（Laravel + Supabase構成）
- データベーススキーマ（テーブル定義、リレーション）
- フロントエンド構造

---

### 🔌 [API・統合](./api/)
バックエンドが提供するAPIエンドポイント、通信仕様について記述します。

**含まれる内容:**
- 全APIエンドポイントの仕様
- リクエスト/レスポンス形式
- 認証方式（Supabase Auth）

---

### 📋 [開発ガイドライン](./guides/)
開発を始めるために必要な環境構築、ブランチ戦略、コーディング規約について説明します。

**含まれる内容:**
- 環境変数設定とセットアップ手順
- ブランチ戦略
- Git ワークフロー

---

### 💡 [設計思想・ベストプラクティス](./design/)
エラーハンドリング、コード品質に関するガイドラインを提供します。

---

### 🚀 [デプロイ・運用](./deployment/)
本番環境へのデプロイについて説明します。

---

## 🔍 ドキュメント検索

| 探している情報 | 読むべきドキュメント |
|--------------|------------------|
| 環境構築方法 | [guides/environment-setup.md](./guides/environment-setup.md) |
| ブランチ戦略 | [guides/development-guide.md](./guides/development-guide.md) |
| API仕様 | [api/api-reference.md](./api/api-reference.md) |
| データベース構造 | [architecture/db-schema.md](./architecture/db-schema.md) |
| システム概要 | [architecture/system-overview.md](./architecture/system-overview.md) |
| デプロイ手順 | [deployment/deployment.md](./deployment/deployment.md) |

---

## 💡 ドキュメント更新のガイドライン

ドキュメントを更新・追加する場合は、以下の原則に従ってください：

1. **適切なカテゴリーに配置**: どのカテゴリーに属するかを判断
2. **このREADME.mdを更新**: 新規ドキュメントをリストに追加
3. **リンク切れがないか確認**: 相互参照を確認
