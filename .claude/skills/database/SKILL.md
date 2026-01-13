---
name: database
description: DB操作・マイグレーション・SQL実行
user-invocable: true
---

# データベーススキル

SupabaseとLaravelマイグレーションの管理を支援します。

## マイグレーション操作

### 基本コマンド
```bash
# マイグレーション実行
php artisan migrate

# ロールバック
php artisan migrate:rollback

# リセット＋再実行
php artisan migrate:fresh

# 状態確認
php artisan migrate:status
```

### マイグレーション作成
```bash
# テーブル作成
php artisan make:migration create_テーブル名_table

# カラム追加
php artisan make:migration add_カラム名_to_テーブル名_table

# カラム変更
php artisan make:migration modify_カラム名_in_テーブル名_table
```

## Supabase MCPツール

### データベース操作
```
mcp__supabase__list_tables      # テーブル一覧
mcp__supabase__execute_sql      # SQLクエリ実行（読み取り）
mcp__supabase__apply_migration  # マイグレーション適用
mcp__supabase__list_migrations  # マイグレーション一覧
```

### ログとアドバイザー
```
mcp__supabase__get_logs         # データベースログ
mcp__supabase__get_advisors     # セキュリティ/パフォーマンス助言
```

## よく使うクエリパターン

### 対戦記録の統計
```sql
-- 勝率計算
SELECT
    result,
    COUNT(*) as count,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER(), 2) as percentage
FROM battles
WHERE user_id = ?
GROUP BY result;

-- クラス別勝率
SELECT
    lc.name as opponent_class,
    COUNT(CASE WHEN b.result = 'win' THEN 1 END) as wins,
    COUNT(*) as total,
    ROUND(COUNT(CASE WHEN b.result = 'win' THEN 1 END) * 100.0 / COUNT(*), 2) as win_rate
FROM battles b
JOIN leader_classes lc ON b.opponent_class_id = lc.id
WHERE b.user_id = ?
GROUP BY lc.id, lc.name
ORDER BY win_rate DESC;
```

### データ整合性チェック
```sql
-- 孤立したバトル記録
SELECT * FROM battles
WHERE deck_id IS NOT NULL
AND deck_id NOT IN (SELECT id FROM decks);

-- ユーザーごとの記録数
SELECT user_id, COUNT(*) as battle_count
FROM battles
GROUP BY user_id
ORDER BY battle_count DESC;
```

## トラブルシューティング

### 接続エラー
1. `.env`の接続情報を確認
2. Supabaseプロジェクトのステータス確認
3. IP制限の確認

### マイグレーションエラー
1. エラーメッセージを確認
2. 既存データとの整合性確認
3. 必要に応じてデータ移行スクリプト作成

## ベストプラクティス

- 本番DBへの直接変更は避ける
- マイグレーションで全ての変更を管理
- ロールバック可能なマイグレーションを書く
- 大きなデータ変更は段階的に実行
