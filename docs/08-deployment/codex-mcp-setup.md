# Codex MCP セットアップ（Render / Supabase）

このリポジトリには、Codex が自動読み込みできる MCP 設定として `.mcp.json` を同梱しています。

## 1) 事前準備（環境変数）

### Render

- `RENDER_API_KEY`（必須）: Render の Account Settings で発行した API Key
- `RENDER_API_BASE_URL`（任意）: デフォルトは `https://api.render.com/v1`

永続化したい場合は、このリポジトリの `.env`（または `.env.local`）に追加してください（`mcp/render-server.mjs` が自動で読み込みます）。

例（`.env`）:

```dotenv
RENDER_API_KEY="xxxxxxxx"
```

例（一時的にこのシェルだけで設定する場合）:

```bash
export RENDER_API_KEY="xxxxxxxx"
```

### Supabase

`.mcp.json` の `supabase` は Supabase 公式の Hosted MCP を参照します。認証が必要な場合は、Supabase 側の案内に従ってアクセストークンを設定してください。

## 2) Codex から使う

このリポジトリのルートで Codex を起動すると、`.mcp.json` の `mcpServers` が読み込まれます。

## 3) ローカル Render MCP の概要

- 実装: `mcp/render-server.mjs`
- 利用 API: Render REST API (`https://api.render.com/v1`)
- 提供ツール: `render_list_services`, `render_get_service`, `render_create_deploy`, `render_list_deploys`, `render_get_deploy`, `render_request`

動作確認（MCP 経由ではなく直接起動するだけの簡易チェック）:

```bash
RENDER_API_KEY="..." node mcp/render-server.mjs
```
