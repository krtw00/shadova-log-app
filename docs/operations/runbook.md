# 運用 Runbook

このドキュメントは、Shadova Log App の運用に関する手順書です。障害対応、監視、バックアップ/リストアなどの運用タスクについて記述します。

---

## 目次

1. [監視・アラート](#監視アラート)
2. [障害対応](#障害対応)
3. [バックアップ/リストア](#バックアップリストア)
4. [定期メンテナンス](#定期メンテナンス)
5. [スケーリング](#スケーリング)

---

## 監視・アラート

### Render Dashboard 監視項目

Render ダッシュボード（https://dashboard.render.com）で以下を確認:

| 監視項目 | 確認場所 | 正常値 | アラート閾値 |
|---------|---------|--------|-------------|
| CPU使用率 | Metrics → CPU | < 70% | > 80% 継続5分 |
| メモリ使用率 | Metrics → Memory | < 80% | > 90% 継続5分 |
| レスポンスタイム | Metrics → Response Time | < 500ms | > 2000ms |
| エラーレート | Logs → Error | < 1% | > 5% |
| デプロイ状態 | Events | Success | Failed |

### Supabase 監視項目

Supabase ダッシュボード（https://supabase.com/dashboard）で以下を確認:

| 監視項目 | 確認場所 | 正常値 | アラート閾値 |
|---------|---------|--------|-------------|
| DB接続数 | Database → Connections | < 50 | > 80 (無料枠上限100) |
| ストレージ使用量 | Settings → Usage | < 400MB | > 450MB (無料枠500MB) |
| APIリクエスト数 | Settings → Usage | 日次確認 | 月間上限の80% |
| DBサイズ | Database → Size | < 400MB | > 450MB (無料枠500MB) |

### ログ確認

#### Render ログ

```bash
# Render ダッシュボード → Logs タブで確認
# または Render CLI を使用
render logs --service shadova-log
```

#### Laravel ログ

本番環境では `LOG_CHANNEL=stack`、`LOG_LEVEL=error` に設定。

Render Shell で確認:
```bash
tail -f storage/logs/laravel.log
```

### アラート設定（推奨）

外部監視サービスとの連携を推奨:

- **UptimeRobot**: HTTPヘルスチェック（無料枠あり）
- **Better Stack**: ログ監視・アラート
- **Discord Webhook**: 障害通知

---

## 障害対応

### 障害対応フロー

```
1. 障害検知
   ↓
2. 影響範囲の確認
   ↓
3. 原因調査
   ↓
4. 復旧作業
   ↓
5. 再発防止策の検討
   ↓
6. ポストモーテム作成
```

### サービス停止時の対応

#### 症状: アプリケーションにアクセスできない

1. **Render ステータス確認**
   - https://status.render.com を確認
   - Render 側の障害の場合は復旧を待つ

2. **デプロイ状態確認**
   - Render ダッシュボード → Events で最新デプロイを確認
   - Failed の場合はログを確認し、前バージョンへロールバック

3. **アプリケーションログ確認**
   - Render ダッシュボード → Logs で直近のエラーを確認

#### 症状: 500 エラーが発生

1. **ログ確認**
   ```bash
   # Render Shell で実行
   tail -100 storage/logs/laravel.log
   ```

2. **一時的に DEBUG モード有効化**
   ```bash
   # 環境変数を一時変更（注意: 本番では必ず戻すこと）
   APP_DEBUG=true
   ```

3. **よくある原因と対策**
   - DB接続エラー → Supabase 接続情報確認
   - パーミッションエラー → storage/bootstrap/cache のパーミッション確認
   - 環境変数未設定 → 必須環境変数の確認

### DB接続エラー時の対応

#### 症状: `SQLSTATE[08006] could not connect to server`

1. **Supabase ステータス確認**
   - https://status.supabase.com を確認

2. **接続情報確認**
   ```bash
   # 環境変数確認
   echo $DB_HOST
   echo $DB_DATABASE
   ```

3. **接続テスト**
   ```bash
   psql "postgres://postgres:$DB_PASSWORD@$DB_HOST:5432/$DB_DATABASE"
   ```

4. **Supabase プロジェクト再起動**
   - Supabase ダッシュボード → Settings → General → Restart project

### 認証エラー時の対応

#### 症状: ログインできない / セッションが切れる

1. **セッションテーブル確認**
   ```sql
   SELECT COUNT(*) FROM sessions;
   ```

2. **セッションクリア**
   ```bash
   php artisan session:table
   php artisan migrate
   ```

3. **キャッシュクリア**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

---

## バックアップ/リストア

### データベースバックアップ

#### 自動バックアップ（Supabase）

Supabase Pro プランでは日次自動バックアップが有効。
無料プランでは手動バックアップが必要。

#### 手動バックアップ

```bash
# ローカルマシンで実行
pg_dump "postgres://postgres:${DB_PASSWORD}@${DB_HOST}:5432/postgres" \
  --format=custom \
  --file="backup_$(date +%Y%m%d_%H%M%S).dump"
```

#### バックアップスケジュール（推奨）

| 頻度 | 保持期間 | 用途 |
|-----|---------|------|
| 日次 | 7日 | 直近の復旧用 |
| 週次 | 4週 | 中期保存 |
| 月次 | 12ヶ月 | 長期アーカイブ |

### リストア手順

#### 本番環境へのリストア

```bash
# 1. メンテナンスモード有効化
# Render 環境変数で APP_ENV=maintenance に設定

# 2. リストア実行
pg_restore \
  --dbname="postgres://postgres:${DB_PASSWORD}@${DB_HOST}:5432/postgres" \
  --clean \
  --if-exists \
  backup_file.dump

# 3. マイグレーション実行（必要に応じて）
php artisan migrate --force

# 4. メンテナンスモード解除
# APP_ENV=production に戻す
```

#### 特定テーブルのみリストア

```bash
pg_restore \
  --dbname="postgres://..." \
  --table=battles \
  --data-only \
  backup_file.dump
```

---

## 定期メンテナンス

### 週次タスク

| タスク | 確認項目 | 対応 |
|-------|---------|------|
| ログ確認 | エラーログの傾向 | 繰り返しエラーの調査 |
| 使用量確認 | Supabase 使用量 | 閾値超え時は対策検討 |
| セキュリティ | 依存パッケージの脆弱性 | `composer audit` 実行 |

### 月次タスク

| タスク | 確認項目 | 対応 |
|-------|---------|------|
| 依存関係更新 | composer/npm パッケージ | セキュリティパッチ適用 |
| パフォーマンス分析 | レスポンスタイム傾向 | ボトルネック調査 |
| バックアップテスト | リストアテスト | 開発環境でリストア確認 |

### 依存関係の更新

```bash
# セキュリティ監査
composer audit
npm audit

# パッチ更新（マイナーバージョン）
composer update --prefer-stable
npm update

# メジャー更新は個別に検討
composer outdated
npm outdated
```

### セッションクリーンアップ

古いセッションデータの削除:

```bash
# 期限切れセッションの削除
php artisan session:gc

# または SQL で直接削除
DELETE FROM sessions WHERE last_activity < (EXTRACT(EPOCH FROM NOW()) - 86400 * 7);
```

---

## スケーリング

### 垂直スケーリング（Render）

現在の構成から以下の順でアップグレード:

| プラン | CPU | メモリ | 月額 | 推奨ユーザー数 |
|-------|-----|--------|------|---------------|
| Starter | 0.5 | 512MB | $7 | ~100 |
| Standard | 1 | 2GB | $25 | ~500 |
| Pro | 4 | 8GB | $85 | ~2000 |

### 水平スケーリング対応

現在はシングルインスタンス構成。水平スケーリングには以下の対応が必要:

1. **セッション管理**
   - `SESSION_DRIVER=database` または `redis` に変更（現在は database）

2. **キャッシュ**
   - 外部 Redis サービスの導入

3. **ファイルストレージ**
   - ローカルストレージから S3/Supabase Storage へ移行

---

## 緊急連絡先

| 役割 | 連絡先 | 備考 |
|-----|-------|------|
| 開発担当 | (設定してください) | 技術的な障害対応 |
| Render サポート | support@render.com | インフラ障害時 |
| Supabase サポート | support@supabase.io | DB障害時 |

---

## 関連ドキュメント

- [デプロイメントガイド](../deployment/deployment.md)
- [トラブルシューティング](../guides/troubleshooting.md)
- [システム概要](../architecture/system-overview.md)
