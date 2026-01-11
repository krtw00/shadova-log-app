# アーキテクチャ・設計

このディレクトリは、Shadova Log Appのシステムアーキテクチャ、データベース設計、全体的な構造に関するドキュメントをまとめたものです。

## ドキュメント一覧

### コアアーキテクチャ

- **[system-overview.md](./system-overview.md)** - Laravel + Supabase を用いたシステム全体構造、認証フロー、API設計について解説します。
- **[db-schema.md](./db-schema.md)** - Supabase (PostgreSQL) データベースのテーブル定義、リレーションシップについて記述します。

## このセクションを読むべき人

- **新規開発者**: アーキテクチャ全体を理解したい場合
- **機能追加時**: 既存のレイヤー構造を確認しながら開発したい場合
- **リファクタリング**: システム構造を改善する際の参考資料

## 読む順序

1. [system-overview.md](./system-overview.md) - システム全体構造を理解
2. [db-schema.md](./db-schema.md) - データベース設計の詳細を確認
