# デプロイメントガイド

このドキュメントは、Shadova Log App の現行本番構成と運用手順をまとめたものです。

## 本番環境構成

| コンポーネント | サービス | 説明 |
|---------------|---------|------|
| アプリケーション | Google Cloud Run | Docker コンテナをデプロイ |
| CI/CD | GitHub Actions | `main` / `staging` から自動デプロイ |
| データベース | Supabase PostgreSQL | アプリ本体の永続データ |
| コンテナレジストリ | Artifact Registry | Cloud Run 用イメージ保管 |
| DNS | カスタムドメイン | `APP_URL` と OAuth callback を合わせる |

## デプロイの流れ

1. GitHub Actions が `.github/workflows/deploy-google.yml` を実行します。
2. workflow がランタイム環境変数ファイルを生成します。
3. `scripts/deploy-cloudrun-shadova.sh` が Cloud Build で Docker イメージを作成します。
4. 同スクリプトが Cloud Run へデプロイします。

本番反映トリガー:

- `main` push: 本番サービスへデプロイ
- `staging` push: `ENABLE_STAGING=true` の場合のみ staging へデプロイ
- `workflow_dispatch`: 手動実行

## 必要な GitHub Variables / Secrets

### Variables

- `GOOGLE_CLOUD_PROJECT`
- `GOOGLE_CLOUD_REGION`
- `ARTIFACT_REGISTRY_REPOSITORY`
- `CLOUD_RUN_SERVICE`
- `STAGING_CLOUD_RUN_SERVICE`
- `GCP_WORKLOAD_IDENTITY_PROVIDER`
- `GCP_SERVICE_ACCOUNT`
- `ENABLE_STAGING`（任意）

### Secrets

- `SHADOVA_RUNTIME_ENV`
- `SHADOVA_STAGING_RUNTIME_ENV`（staging を使う場合）

`SHADOVA_RUNTIME_ENV` の雛形は [`deploy/google/shadova.runtime.env.example`](../../deploy/google/shadova.runtime.env.example) を使います。

## ランタイム環境変数

Cloud Run では少なくとも以下を設定します。

```env
APP_NAME="Shadova Log"
APP_ENV=production
APP_KEY=base64:replace-me
APP_DEBUG=false
APP_URL=https://your-domain.example

LOG_CHANNEL=stderr
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=db.your-project.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.your-project
DB_PASSWORD=replace-me
DB_SSLMODE=require

SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=file
QUEUE_CONNECTION=sync

GOOGLE_CLIENT_ID=replace-me
GOOGLE_CLIENT_SECRET=replace-me
GOOGLE_REDIRECT_URI=https://your-domain.example/auth/google/callback

DISCORD_CLIENT_ID=replace-me
DISCORD_CLIENT_SECRET=replace-me
DISCORD_REDIRECT_URI=https://your-domain.example/auth/discord/callback

GITHUB_TOKEN=replace-me
GITHUB_OWNER=krtw00
GITHUB_REPO=shadova-log-app
```

運用上の注意:

- `APP_URL` と OAuth provider 側の callback URL は完全一致させる
- Cloud Run では `SESSION_DRIVER=cookie` を前提にする
- OAuth callback が壊れる場合は、まず custom domain と redirect URI の不一致を疑う

## 手動デプロイ

GitHub Actions を使わずに手動で反映する場合は、`gcloud` 認証済み環境で次を実行します。

```bash
cp deploy/google/shadova.runtime.env.example deploy/google/shadova.runtime.env
# 値を埋める

bash scripts/deploy-cloudrun-shadova.sh
```

利用する主な環境変数:

- `GOOGLE_CLOUD_PROJECT`
- `GOOGLE_CLOUD_REGION`
- `ARTIFACT_REGISTRY_REPOSITORY`
- `CLOUD_RUN_SERVICE`
- `RUNTIME_ENV_FILE`

## OAuth 運用メモ

Cloud Run は HTTPS 終端がプロキシ側になるため、アプリ側で `X-Forwarded-*` を信頼する設定が必要です。現在は [`bootstrap/app.php`](../../bootstrap/app.php) で production 時に trusted proxies を有効化しています。

確認ポイント:

- Google / Discord の callback URL が現在の `APP_URL` と一致しているか
- Cloud Run の環境変数に OAuth client id / secret が入っているか
- `APP_KEY` が想定せず変わっておらず、セッションが失効していないか
- Cloud Run ログに `OAuth callback failed` / `OAuth user sync failed` が出ていないか

## 監視とログ確認

### Cloud Run

```bash
gcloud run services describe shadova-log \
  --region asia-northeast1 \
  --project "$GOOGLE_CLOUD_PROJECT"

gcloud logging read \
  'resource.type="cloud_run_revision" AND resource.labels.service_name="shadova-log"' \
  --limit 100 \
  --format json
```

### アプリログ

- Laravel は `stderr` に出力
- OAuth 失敗時は `OAuth callback failed` または `OAuth user sync failed` を確認
- GitHub Issue 連携失敗時は `GitHubService:` で検索

## 利用状況の確認

ツール継続か終了判断をする前に、最低限以下を確認します。

### まず見る数字

- 総ユーザー数
- 直近 30 日に対戦記録したユニークユーザー数
- 直近 7 日に対戦記録したユニークユーザー数
- 直近 30 日の新規登録数

### Supabase SQL 例

```sql
select count(*) as total_users from users;

select count(*) as users_created_last_30d
from users
where created_at >= now() - interval '30 days';

select count(distinct user_id) as active_recorders_last_30d
from battles
where played_at >= now() - interval '30 days';

select count(distinct user_id) as active_recorders_last_7d
from battles
where played_at >= now() - interval '7 days';
```

終了判断の目安:

- active user が継続的に極小で、問い合わせもなく、OAuth 修正後も再利用が見込めない
- 逆に少人数でも定着利用があるなら、最低限 OAuth / deploy / backup だけ維持する

## GitHub Issue 運用

[`FeedbackController`](../../app/Http/Controllers/FeedbackController.php) から GitHub Issue を作成します。現状は `krtw00/shadova-log-app` に投げる実装です。

確認事項:

- `GITHUB_TOKEN` が有効か
- `bug`, `enhancement`, `question`, `user-reported` ラベルが存在するか
- テスト投稿 issue のみ残っていないか定期的に確認する

## ローカル開発

Docker 開発環境を使います。

```bash
docker compose up -d db app
docker compose exec app php artisan migrate
docker compose exec app php artisan test
```
