---
name: pr-review
description: PRレビュー支援
user-invocable: true
---

# PRレビュースキル

プルリクエストのレビューを効率的に行うためのガイドラインです。

## レビューチェックリスト

### 1. コード品質
- [ ] PSR-12準拠（PHP）
- [ ] 適切な命名（変数、関数、クラス）
- [ ] 不要なコードの削除
- [ ] 適切なコメント

### 2. セキュリティ
- [ ] SQLインジェクション対策
- [ ] XSS対策（Bladeでのエスケープ）
- [ ] CSRF対策（@csrf）
- [ ] 認可チェック（Policy使用）
- [ ] 機密情報のハードコーディングなし

### 3. パフォーマンス
- [ ] N+1問題の回避
- [ ] 適切なインデックス
- [ ] 不要なクエリの削除
- [ ] 大量データ処理の最適化

### 4. テスト
- [ ] 新機能にテストがあるか
- [ ] 既存テストが通るか
- [ ] エッジケースのカバー

### 5. ドキュメント
- [ ] 必要な変更がREADMEに反映
- [ ] API変更時のドキュメント更新

## GitHub MCPツール

```
mcp__plugin_github_github__pull_request_read   # PR詳細取得
mcp__plugin_github_github__list_pull_requests  # PR一覧
mcp__plugin_github_github__get_file_contents   # ファイル内容取得
mcp__plugin_github_github__create_pull_request # PR作成
```

## レビューコメントの書き方

### 優先度別
```
[Critical] セキュリティ脆弱性があります。修正必須です。

[Warning] パフォーマンス問題の可能性があります。

[Suggestion] こちらの書き方の方が読みやすいかもしれません。

[Question] この処理の意図を教えてください。

[Nitpick] 細かい点ですが、命名を統一しませんか？
```

### 具体的なフィードバック
```php
// 現在のコード
$battles = Battle::all()->where('user_id', $userId);

// 提案
// [Warning] このコードは全レコードを取得してからフィルタリングしています。
// クエリで絞り込む方が効率的です：
$battles = Battle::where('user_id', $userId)->get();
```

## PRマージ前の確認

1. **CI/CDの状態**
   - テストが全て通過
   - ビルドが成功

2. **レビュー承認**
   - 必要なレビュアーの承認

3. **コンフリクト**
   - ベースブランチとの競合なし

4. **マイグレーション**
   - 破壊的変更がないか確認
   - ロールバック可能か確認

## コミットメッセージ規約

```
feat: 新機能追加
fix: バグ修正
docs: ドキュメント更新
style: フォーマット修正
refactor: リファクタリング
test: テスト追加・修正
chore: ビルド・設定変更
```

例:
```
feat: 配信者モードのオーバーレイ機能を追加

- 勝敗カウントのリアルタイム表示
- 連勝/連敗ストリーク表示
- カスタマイズ可能なスタイル設定
```
