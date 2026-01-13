---
name: deploy
description: Renderデプロイ・ログ確認
user-invocable: true
---

# デプロイスキル

Renderへのデプロイと本番環境の管理を支援します。

## デプロイの流れ

1. **コード変更をコミット・プッシュ**
   ```bash
   git add .
   git commit -m "変更内容"
   git push origin main
   ```

2. **Renderが自動デプロイを開始**
   - mainブランチへのプッシュで自動的にデプロイ開始
   - デプロイ状況はRender MCPで確認可能

## デプロイ状況の確認

### MCPツールを使用
```
mcp__render__list_services       # サービス一覧
mcp__render__list_deploys        # デプロイ履歴
mcp__render__get_deploy          # デプロイ詳細
mcp__render__list_logs           # ログ確認
```

### 確認すべき項目
- デプロイのステータス（live, build_in_progress, failed など）
- ビルドログでのエラー有無
- ランタイムログでのエラー有無

## デプロイ失敗時の対処

### よくある原因

1. **Composerエラー**
   - 依存関係の競合
   - PHPバージョンの不一致

2. **npm/Viteエラー**
   - パッケージの互換性問題
   - ビルドスクリプトのエラー

3. **環境変数の不足**
   - 必要な環境変数がRenderに設定されていない

### トラブルシューティング手順

1. `mcp__render__list_logs` でログを確認
2. エラーメッセージを特定
3. ローカルで再現・修正
4. 再プッシュ

## 環境変数の管理

Render Dashboard または MCP で設定:
```
mcp__render__update_environment_variables
```

### 必須の環境変数
- `APP_KEY`
- `APP_ENV=production`
- `DB_CONNECTION=pgsql`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

## 本番環境の注意事項

- `APP_DEBUG=false` を確認
- `APP_ENV=production` を確認
- マイグレーションは `--force` オプションで実行
- キャッシュのクリア: `php artisan config:clear`
