# トラブルシューティングガイド

このドキュメントは、Shadova Log App の開発・運用で発生しやすい問題と解決策をまとめたものです。

---

## 目次

1. [開発環境の問題](#開発環境の問題)
2. [データベース関連](#データベース関連)
3. [認証関連](#認証関連)
4. [デプロイ関連](#デプロイ関連)
5. [フロントエンド関連](#フロントエンド関連)
6. [パフォーマンス関連](#パフォーマンス関連)

---

## 開発環境の問題

### Composer install が失敗する

#### エラー例
```
Your requirements could not be resolved to an installable set of packages.
```

#### 解決策
```bash
# 1. Composer キャッシュをクリア
composer clear-cache

# 2. vendor ディレクトリを削除して再インストール
rm -rf vendor
composer install --no-cache

# 3. PHP バージョンを確認（8.2 以上が必要）
php -v
```

---

### npm install が失敗する

#### エラー例
```
npm ERR! ERESOLVE unable to resolve dependency tree
```

#### 解決策
```bash
# 1. node_modules を削除
rm -rf node_modules package-lock.json

# 2. npm キャッシュをクリア
npm cache clean --force

# 3. 再インストール
npm install
```

---

### PHP 拡張機能が見つからない

#### エラー例
```
PHP extension pgsql is missing
```

#### 解決策
```bash
# Ubuntu/WSL
sudo apt install php8.3-pgsql php8.3-mbstring php8.3-xml php8.3-curl

# macOS
brew install php@8.3

# 拡張確認
php -m | grep pgsql
```

---

### Vite 開発サーバーが起動しない

#### エラー例
```
Error: listen EADDRINUSE: address already in use :::5173
```

#### 解決策
```bash
# 使用中のポートを確認
lsof -i :5173

# プロセスを終了
kill -9 <PID>

# または別のポートを使用
npm run dev -- --port 5174
```

---

## データベース関連

### 接続エラー

#### エラー例
```
SQLSTATE[08006] [7] could not connect to server: Connection refused
```

#### チェックリスト
1. **Supabase プロジェクトが起動しているか確認**
   - Supabase ダッシュボードでステータスを確認
   - 「Paused」の場合は「Restore」をクリック

2. **接続情報が正しいか確認**
   ```bash
   # .env の確認
   cat .env | grep DB_
   ```

3. **接続テスト**
   ```bash
   psql "postgres://postgres:${DB_PASSWORD}@${DB_HOST}:5432/postgres"
   ```

4. **ファイアウォール/VPN**
   - 社内ネットワークからの接続制限がないか確認
   - VPN を使用している場合は接続を確認

---

### マイグレーションエラー

#### エラー例
```
SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "users" does not exist
```

#### 解決策
```bash
# マイグレーションをリセット
php artisan migrate:fresh

# シーダーも実行
php artisan migrate:fresh --seed

# 特定のマイグレーションのみ実行
php artisan migrate --path=database/migrations/2024_01_01_000000_create_xxx_table.php
```

---

### テーブルが既に存在する

#### エラー例
```
SQLSTATE[42P07]: Duplicate table: 7 ERROR: relation "battles" already exists
```

#### 解決策
```bash
# 1. マイグレーション状態を確認
php artisan migrate:status

# 2. 特定のマイグレーションをスキップ
# migrations テーブルに手動で追加
php artisan tinker
>>> DB::table('migrations')->insert(['migration' => '2024_01_01_000000_create_battles_table', 'batch' => 1]);
```

---

## 認証関連

### ログインできない

#### チェックリスト
1. **メールアドレス/パスワードが正しいか確認**

2. **セッション設定を確認**
   ```bash
   # .env
   SESSION_DRIVER=database  # または file
   ```

3. **sessions テーブルが存在するか確認**
   ```bash
   php artisan session:table
   php artisan migrate
   ```

4. **ブラウザの Cookie を確認**
   - Cookie がブロックされていないか
   - プライベートブラウジングモードでテスト

---

### セッションが保持されない

#### エラー例
- ページ遷移のたびにログアウトされる
- 「419 Page Expired」エラー

#### 解決策
```php
// 1. CSRF トークンを確認
// Blade テンプレートに @csrf があるか

// 2. APP_URL が正しいか確認
// .env
APP_URL=http://localhost:3000

// 3. Cookie ドメインを確認
// config/session.php
'domain' => env('SESSION_DOMAIN', null),
```

---

### OAuth ログインが失敗する

#### エラー例
```
Client error: 401 Unauthorized
```

#### チェックリスト
1. **OAuth クライアント設定**
   - Client ID / Client Secret が正しいか
   - リダイレクト URI が正確に一致しているか

2. **環境変数**
   ```bash
   GOOGLE_CLIENT_ID=xxx
   GOOGLE_CLIENT_SECRET=xxx
   GOOGLE_REDIRECT_URI=https://your-app.com/auth/google/callback
   ```

3. **HTTPS 設定**
   - OAuth プロバイダーによっては HTTPS が必須

---

## デプロイ関連

### Render デプロイが失敗する

#### エラー例
```
Build failed
```

#### チェックリスト
1. **ビルドログを確認**
   - Render ダッシュボード → Events → 該当デプロイ → Logs

2. **よくある原因**
   - `composer.lock` / `package-lock.json` がコミットされていない
   - PHP/Node.js バージョンの不一致
   - メモリ不足（Instance Type をアップグレード）

3. **ローカルでビルドテスト**
   ```bash
   docker build -t shadova-log .
   ```

---

### 500 Internal Server Error（本番環境）

#### チェックリスト
1. **ログを確認**
   ```bash
   # Render Shell
   tail -100 storage/logs/laravel.log
   ```

2. **一時的に DEBUG モードを有効化**
   ```bash
   # Render 環境変数
   APP_DEBUG=true
   ```
   ※ 確認後は必ず `false` に戻すこと

3. **よくある原因**
   - 環境変数の設定漏れ
   - マイグレーション未実行
   - パーミッションエラー

---

### Mixed Content エラー

#### エラー例
```
Mixed Content: The page was loaded over HTTPS, but requested an insecure resource
```

#### 解決策
```php
// 1. APP_URL を HTTPS に設定
// .env
APP_URL=https://your-app.onrender.com

// 2. HTTPS を強制（AppServiceProvider）
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    if (config('app.env') === 'production') {
        URL::forceScheme('https');
    }
}

// 3. TrustProxies ミドルウェアを確認
// app/Http/Middleware/TrustProxies.php
protected $proxies = '*';
```

---

## フロントエンド関連

### CSS/JS が反映されない

#### 解決策
```bash
# 1. キャッシュをクリア
php artisan view:clear
php artisan cache:clear

# 2. アセットを再ビルド
npm run build

# 3. ブラウザキャッシュをクリア
# Ctrl+Shift+R (ハードリフレッシュ)
```

---

### Alpine.js が動作しない

#### チェックリスト
1. **スクリプトが読み込まれているか**
   - ブラウザの開発者ツール → Console でエラー確認

2. **x-data が正しいか**
   ```html
   <!-- 良い例 -->
   <div x-data="{ open: false }">

   <!-- 悪い例（シンタックスエラー） -->
   <div x-data="{ open: false">
   ```

3. **初期化順序**
   - Alpine.js は `app.js` で正しく初期化されているか

---

### Tailwind CSS クラスが効かない

#### 解決策
```bash
# 1. ビルドを実行
npm run dev  # 開発
npm run build  # 本番

# 2. tailwind.config.js の content を確認
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    // ...
}
```

---

## パフォーマンス関連

### ページ読み込みが遅い

#### 診断
```bash
# 1. クエリログを有効化
# .env
DB_DEBUG=true

# 2. Laravel Debugbar をインストール（開発環境のみ）
composer require barryvdh/laravel-debugbar --dev
```

#### よくある原因と対策

| 原因 | 対策 |
|-----|------|
| N+1 クエリ | Eager Loading (`with()`) を使用 |
| 大量データの取得 | ページネーション導入 |
| 重い集計処理 | キャッシュ導入 |

```php
// N+1 問題の解決例
// 悪い例
$battles = Battle::all();
foreach ($battles as $battle) {
    echo $battle->deck->name; // 毎回クエリ発行
}

// 良い例
$battles = Battle::with(['deck', 'opponentClass'])->get();
```

---

### メモリ不足

#### エラー例
```
Allowed memory size of 134217728 bytes exhausted
```

#### 解決策
```php
// 1. php.ini で上限を増やす
memory_limit = 256M

// 2. 大量データ処理時は chunk を使用
Battle::chunk(100, function ($battles) {
    foreach ($battles as $battle) {
        // 処理
    }
});

// 3. 不要なデータを読み込まない
Battle::select(['id', 'result', 'played_at'])->get();
```

---

## ヘルプを求める

上記で解決しない場合:

1. **エラーログを収集**
   ```bash
   tail -100 storage/logs/laravel.log > error_log.txt
   ```

2. **再現手順を整理**
   - 何をしようとしたか
   - どのようなエラーが出たか
   - 試した解決策

3. **GitHub Issue を作成**
   - エラーログ、再現手順を添付
   - 環境情報（PHP/Node.js バージョン、OS など）を記載

---

## 関連ドキュメント

- [環境構築ガイド](./environment-setup.md)
- [デプロイメントガイド](../deployment/deployment.md)
- [運用 Runbook](../operations/runbook.md)
