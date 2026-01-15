# API リファレンス

このドキュメントは、Shadova Log App の全ルート・エンドポイントについて記述します。

---

## 概要

Shadova Log App は **SSR（Server-Side Rendering）** アーキテクチャを採用しており、従来のREST APIではなく、Laravelの標準的なWebルートを使用しています。フォーム送信はPOST/PUT/DELETEメソッドで処理され、レスポンスはリダイレクトまたはBladeビューのレンダリングとなります。

**ベースURL:** `https://your-domain.com`

**認証方式:** セッションベース認証（Cookie）

---

## 認証ルート

### ユーザー登録

**POST** `/register`

新規ユーザーを登録します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `username` | string | Yes | ユーザー名 |
| `email` | string | Yes | メールアドレス |
| `password` | string | Yes | パスワード（8文字以上） |
| `password_confirmation` | string | Yes | パスワード確認 |

**レスポンス:**
- 成功: `/battles` へリダイレクト
- 失敗: バリデーションエラーと共に登録画面へリダイレクト

---

### ログイン

**POST** `/login`

ユーザー認証を行います。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `email` | string | Yes | メールアドレス |
| `password` | string | Yes | パスワード |
| `remember` | boolean | No | ログイン状態を維持 |

**レスポンス:**
- 成功: `/battles` へリダイレクト
- 失敗: エラーメッセージと共にログイン画面へリダイレクト

---

### ログアウト

**POST** `/logout`

セッションを終了します。

**レスポンス:**
- `/login` へリダイレクト

---

### パスワードリセット申請

**POST** `/forgot-password`

パスワードリセット用のメールを送信します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `email` | string | Yes | 登録メールアドレス |

**レスポンス:**
- 成功: ステータスメッセージと共に同画面へリダイレクト
- 失敗: エラーメッセージと共に同画面へリダイレクト

---

### パスワードリセット実行

**POST** `/reset-password`

新しいパスワードを設定します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `token` | string | Yes | リセットトークン |
| `email` | string | Yes | メールアドレス |
| `password` | string | Yes | 新パスワード |
| `password_confirmation` | string | Yes | パスワード確認 |

**レスポンス:**
- 成功: `/login` へリダイレクト
- 失敗: エラーメッセージと共にリセット画面へリダイレクト

---

### OAuth認証開始

**GET** `/auth/{provider}`

OAuth認証プロバイダーへリダイレクトします。

**URLパラメータ:**
- `provider` - 認証プロバイダー（`google` または `discord`）

**レスポンス:**
- 成功: プロバイダーの認証画面へリダイレクト
- 失敗: 無効なプロバイダーの場合、エラーと共にログイン画面へリダイレクト

---

### OAuthコールバック

**GET** `/auth/{provider}/callback`

OAuth認証のコールバックを処理します。

**URLパラメータ:**
- `provider` - 認証プロバイダー（`google` または `discord`）

**処理フロー:**
1. プロバイダーIDで既存ユーザーを検索 → ログイン
2. メールアドレスで既存ユーザーを検索 → アカウント連携してログイン
3. 該当なし → 新規ユーザー作成してログイン

**レスポンス:**
- 成功: `/battles` へリダイレクト
- 失敗: エラーメッセージと共にログイン画面へリダイレクト

---

## 対戦記録ルート

### 対戦記録一覧

**GET** `/battles`

対戦記録一覧を表示します（メイン画面）。

**クエリパラメータ:**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `game_mode` | string | No | ゲームモードでフィルタ（RANK, FREE, ROOM, GP, 2PICK） |
| `page` | integer | No | ページ番号 |

**レスポンス:**
- Bladeビュー（battles/index）をレンダリング
- 含まれるデータ: 対戦一覧、デッキ一覧、統計情報、マスタデータ

---

### 対戦記録作成

**POST** `/battles`

新しい対戦記録を作成します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `deck_id` | integer | No* | デッキID（2Pick以外で必須） |
| `my_class_id` | integer | No* | 自分のクラスID（2Pick用） |
| `opponent_class_id` | integer | Yes | 相手クラスID |
| `game_mode_id` | integer | Yes | ゲームモードID |
| `rank_id` | integer | No | ランクID（ランクマッチ時） |
| `group_id` | integer | No | グループID（GP時） |
| `result` | boolean | Yes | 結果（1: 勝利, 0: 敗北） |
| `is_first` | boolean | Yes | 先攻（1: 先攻, 0: 後攻） |
| `played_at` | datetime | No | 対戦日時（デフォルト: 現在） |
| `notes` | string | No | メモ |

**レスポンス:**
- 成功: `/battles` へリダイレクトwithフラッシュメッセージ
- 失敗: バリデーションエラーと共に同画面へリダイレクト

---

### 対戦記録更新

**PUT** `/battles/{battle}`

対戦記録を更新します。

**URLパラメータ:**
- `battle` - 対戦記録ID

**リクエストボディ:** 対戦記録作成と同様

**認可:** 自分の対戦記録のみ更新可能

**レスポンス:**
- 成功: `/battles` へリダイレクトwithフラッシュメッセージ
- 失敗: バリデーションエラーまたは403エラー

---

### 対戦記録削除

**DELETE** `/battles/{battle}`

対戦記録を削除します。

**URLパラメータ:**
- `battle` - 対戦記録ID

**認可:** 自分の対戦記録のみ削除可能

**レスポンス:**
- 成功: `/battles` へリダイレクトwithフラッシュメッセージ
- 失敗: 403エラー

---

## デッキルート

### デッキ作成

**POST** `/decks`

新しいデッキを作成します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `name` | string | Yes | デッキ名（最大100文字） |
| `leader_class_id` | integer | Yes | クラスID |

**レスポンス:**
- 成功: `/battles` へリダイレクトwithフラッシュメッセージ

---

### デッキ更新

**PUT** `/decks/{deck}`

デッキを更新します。

**URLパラメータ:**
- `deck` - デッキID

**リクエストボディ:**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `name` | string | Yes | デッキ名 |

**認可:** 自分のデッキのみ更新可能

**レスポンス:**
- 成功: `/battles` へリダイレクトwithフラッシュメッセージ

---

### デッキ削除

**DELETE** `/decks/{deck}`

デッキを削除します（ソフトデリート）。

**URLパラメータ:**
- `deck` - デッキID

**認可:** 自分のデッキのみ削除可能

**注意:** 削除されたデッキに紐づく対戦記録は保持され、表示時は「（削除済み）」と表示されます。

**レスポンス:**
- 成功: `/battles` へリダイレクトwithフラッシュメッセージ

---

## 統計ルート

### 統計情報表示

**GET** `/statistics`

詳細な統計情報を表示します。

**クエリパラメータ:**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `period` | string | No | 期間（all, today, week, month） |

**レスポンス:**
- Bladeビュー（statistics/index）をレンダリング
- 含まれるデータ:
  - 総合統計（勝敗、勝率、最高連勝）
  - デッキ別統計
  - クラス別対戦成績
  - 先後別統計
  - 相性表（マッチアップマトリクス）

---

## 設定ルート

### 設定画面表示

**GET** `/settings`

設定画面を表示します。

**レスポンス:**
- Bladeビュー（settings/index）をレンダリング

---

### プロフィール更新

**PUT** `/settings/profile`

ユーザー名を更新します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `username` | string | Yes | 新しいユーザー名 |

---

### パスワード更新

**PUT** `/settings/password`

パスワードを変更します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `current_password` | string | Yes | 現在のパスワード |
| `password` | string | Yes | 新しいパスワード |
| `password_confirmation` | string | Yes | パスワード確認 |

---

### 表示設定更新

**PUT** `/settings/preferences`

テーマ・デフォルトゲームモードを更新します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `theme` | string | Yes | テーマ（dark, light） |
| `default_game_mode_id` | integer | No | デフォルトゲームモードID |

---

### 表示件数更新

**PUT** `/settings/per-page`

一覧の表示件数を更新します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `per_page` | integer | Yes | 表示件数（10, 20, 50, 100） |

---

### 配信者モード設定

**PUT** `/settings/streamer`

配信者モードを有効化/無効化します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `streamer_mode_enabled` | boolean | Yes | 有効/無効 |

---

### データエクスポート

**GET** `/settings/export`

対戦データをCSVとしてダウンロードします。

**レスポンス:**
- Content-Type: `text/csv`
- ダウンロードファイル名: `shadova-log-export-{date}.csv`

---

### 全データ削除

**DELETE** `/settings/data`

すべての対戦記録・デッキを削除します（アカウントは保持）。

**レスポンス:**
- 成功: `/settings` へリダイレクトwithフラッシュメッセージ

---

### アカウント削除

**DELETE** `/settings/account`

アカウントを完全に削除します。

**レスポンス:**
- 成功: `/login` へリダイレクト

---

## 共有リンクルート

### 共有リンク作成

**POST** `/shares`

新しい共有リンクを作成します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `slug` | string | Yes | URLスラッグ（英数字・ハイフン） |
| `name` | string | Yes | リンク名 |
| `start_date` | date | No | 期間開始日 |
| `end_date` | date | No | 期間終了日 |

**レスポンス:**
- 成功: `/settings` へリダイレクトwithフラッシュメッセージ

---

### 共有リンク更新

**PUT** `/shares/{shareLink}`

共有リンクを更新します。

**認可:** 自分の共有リンクのみ更新可能

---

### 共有リンク削除

**DELETE** `/shares/{shareLink}`

共有リンクを削除します。

**認可:** 自分の共有リンクのみ削除可能

---

### 共有リンク切り替え

**POST** `/shares/{shareLink}/toggle`

共有リンクの有効/無効を切り替えます。

**認可:** 自分の共有リンクのみ操作可能

---

### ユーザー名更新

**POST** `/profile/username`

ユーザー名を更新します（共有リンク用）。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `username` | string | Yes | 新しいユーザー名 |

---

## 公開プロフィールルート

### 公開プロフィール表示

**GET** `/u/{username}/{slug}`

公開プロフィールを表示します（認証不要）。

**URLパラメータ:**
- `username` - ユーザー名
- `slug` - 共有リンクのスラッグ

**レスポンス:**
- 有効な共有リンク: プロフィールページをレンダリング
- 無効または非公開: 404エラー

**表示される情報:**
- ユーザー名
- 期間内の対戦統計
- デッキ別統計
- クラス別対戦成績

---

## 配信者モードルート

### 配信者ダッシュボード

**GET** `/streamer`

配信者モードのダッシュボードを表示します。

**前提条件:** 配信者モードが有効であること

**レスポンス:**
- Bladeビュー（streamer/index）をレンダリング

---

### オーバーレイ表示

**GET** `/streamer/overlay`

OBS用オーバーレイウィンドウを表示します。

**レスポンス:**
- Bladeビュー（streamer/overlay）をレンダリング
- JavaScript により5秒間隔で `/streamer/overlay/data` を呼び出し

---

### オーバーレイデータ取得

**GET** `/streamer/overlay/data`

オーバーレイ用のリアルタイムデータをJSON形式で取得します。

**レスポンス:**

```json
{
  "wins": 10,
  "losses": 5,
  "winRate": 66.7,
  "streak": 3,
  "settings": {
    "backgroundColor": "#1f2937",
    "textColor": "#ffffff",
    "accentColor": "#3b82f6",
    "fontSize": "medium",
    "opacity": 100,
    "showStreak": true,
    "showWinrate": true,
    "showRecord": true
  }
}
```

---

### セッション開始

**POST** `/streamer/session/start`

新しい配信セッションを開始します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `name` | string | No | セッション名 |

**レスポンス:**
- 成功: `/streamer` へリダイレクト

---

### セッション終了

**POST** `/streamer/session/end`

現在の配信セッションを終了します。

**レスポンス:**
- 成功: `/streamer` へリダイレクト

---

### 連勝リセット

**POST** `/streamer/streak/reset`

連勝カウンターをリセットします。

**レスポンス:**
- 成功: `/streamer` へリダイレクト

---

### オーバーレイ設定更新

**PUT** `/streamer/overlay/settings`

オーバーレイの表示設定を更新します。

**リクエストボディ (form-data):**

| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| `overlay_background_color` | string | No | 背景色（HEX） |
| `overlay_text_color` | string | No | 文字色（HEX） |
| `overlay_accent_color` | string | No | アクセント色（HEX） |
| `overlay_font_size` | string | No | フォントサイズ（small, medium, large） |
| `overlay_opacity` | integer | No | 透明度（0-100） |
| `overlay_show_streak` | boolean | No | 連勝表示 |
| `overlay_show_winrate` | boolean | No | 勝率表示 |
| `overlay_show_record` | boolean | No | 戦績表示 |

---

## エラーレスポンス

### HTTPステータスコード一覧

| ステータスコード | 説明 | 発生条件 |
|-----------------|------|---------|
| 200 OK | 成功 | GET リクエスト成功時 |
| 302 Found | リダイレクト | POST/PUT/DELETE 成功時 |
| 401 Unauthorized | 未認証 | ログインが必要なルートへの未認証アクセス |
| 403 Forbidden | 認可エラー | 他ユーザーのリソースへのアクセス |
| 404 Not Found | リソース不在 | 存在しないリソースへのアクセス |
| 419 Page Expired | CSRFトークン無効 | CSRFトークンの期限切れ/不一致 |
| 422 Unprocessable Entity | バリデーションエラー | 入力値が不正 |
| 429 Too Many Requests | レート制限 | 短時間に過剰なリクエスト |
| 500 Internal Server Error | サーバーエラー | アプリケーション内部エラー |

---

### バリデーションエラー (422)

フォームバリデーションに失敗した場合、エラーメッセージと共に元の画面にリダイレクトされます。

Bladeテンプレートでは `$errors` 変数を通じてエラーメッセージにアクセスできます。

**Ajax リクエスト時のレスポンス例:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "opponent_class_id": [
            "相手クラスは必須です。"
        ],
        "result": [
            "結果は必須です。"
        ],
        "game_mode_id": [
            "ゲームモードは必須です。"
        ]
    }
}
```

**よくあるバリデーションエラー:**

| フィールド | エラーメッセージ | 原因 |
|-----------|----------------|------|
| `email` | "このメールアドレスは既に使用されています。" | 重複登録 |
| `password` | "パスワードは8文字以上必要です。" | 文字数不足 |
| `deck_id` | "選択されたデッキは無効です。" | 存在しないID |
| `slug` | "このスラッグは既に使用されています。" | 共有リンクの重複 |

---

### 認可エラー (403)

他のユーザーのリソースにアクセスしようとした場合、403 Forbiddenが返されます。

**レスポンス例:**

```json
{
    "message": "This action is unauthorized."
}
```

**発生するケース:**
- 他のユーザーの対戦記録を編集/削除しようとした
- 他のユーザーのデッキを編集/削除しようとした
- 他のユーザーの共有リンクを操作しようとした

---

### 認証エラー (401)

未認証の状態で認証が必要なルートにアクセスした場合、`/login` へリダイレクトされます。

**Ajax リクエスト時のレスポンス例:**

```json
{
    "message": "Unauthenticated."
}
```

---

### Not Found (404)

存在しないリソースにアクセスした場合、404エラーページが表示されます。

**Ajax リクエスト時のレスポンス例:**

```json
{
    "message": "Record not found."
}
```

---

### CSRFトークンエラー (419)

CSRFトークンが無効または期限切れの場合に発生します。

**レスポンス例:**

```json
{
    "message": "CSRF token mismatch."
}
```

**対処法:**
- ページをリロードして新しいCSRFトークンを取得
- `@csrf` ディレクティブがフォームに含まれているか確認

---

### レート制限エラー (429)

短時間に過剰なリクエストを送信した場合に発生します。

**レスポンス例:**

```json
{
    "message": "Too Many Attempts."
}
```

**レスポンスヘッダー:**

```
Retry-After: 60
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
```

---

### サーバーエラー (500)

アプリケーション内部でエラーが発生した場合に返されます。

**本番環境のレスポンス例:**

```json
{
    "message": "Server Error"
}
```

**開発環境のレスポンス例（APP_DEBUG=true）:**

```json
{
    "message": "SQLSTATE[42P01]: Undefined table...",
    "exception": "Illuminate\\Database\\QueryException",
    "file": "/var/www/html/app/Http/Controllers/BattleController.php",
    "line": 45,
    "trace": [...]
}
```

---

### エラーハンドリングのベストプラクティス

**JavaScript でのエラーハンドリング例:**

```javascript
async function createBattle(data) {
    try {
        const response = await fetch('/battles', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            const errorData = await response.json();

            switch (response.status) {
                case 422:
                    // バリデーションエラー
                    displayValidationErrors(errorData.errors);
                    break;
                case 403:
                    // 認可エラー
                    alert('この操作を行う権限がありません。');
                    break;
                case 419:
                    // CSRFトークンエラー
                    window.location.reload();
                    break;
                case 429:
                    // レート制限
                    alert('リクエストが多すぎます。しばらく待ってから再試行してください。');
                    break;
                default:
                    alert('エラーが発生しました。');
            }
            return;
        }

        // 成功時の処理
        window.location.href = '/battles';

    } catch (error) {
        console.error('Network error:', error);
        alert('通信エラーが発生しました。');
    }
}
```

---

## CSRF保護

すべてのPOST/PUT/DELETEリクエストにはCSRFトークンが必要です。

Bladeテンプレートでは `@csrf` ディレクティブを使用してトークンを含めます。

```html
<form method="POST" action="/battles">
    @csrf
    <!-- form fields -->
</form>
```

PUT/DELETEメソッドの場合は、`@method` ディレクティブも必要です。

```html
<form method="POST" action="/battles/1">
    @csrf
    @method('DELETE')
    <button type="submit">削除</button>
</form>
```

---

## 関連ドキュメント

- [システム概要](../architecture/system-overview.md)
- [データベーススキーマ](../architecture/db-schema.md)
- [フロントエンドアーキテクチャ](../architecture/frontend-architecture.md)
