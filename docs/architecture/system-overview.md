# システム概要

このドキュメントは、Shadova Log Appのシステム全体構成について記述します。

---

## 技術スタック

| レイヤー | 技術 | 説明 |
|---------|------|------|
| **バックエンド** | Laravel 11 (PHP 8.3+) | Webフレームワーク、ビジネスロジック |
| **フロントエンド** | Blade + Alpine.js | SSRベースのUI（検討中：Inertia.js + Vue 3） |
| **データベース** | Supabase (PostgreSQL) | マネージドDB、リアルタイム機能 |
| **認証** | Supabase Auth | OAuth、メール認証対応 |
| **ホスティング** | 検討中 | Vercel / Render / Railway |

---

## システム構成図

```
┌─────────────────────────────────────────────────────────────┐
│                        クライアント                          │
│                    (ブラウザ / モバイル)                      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      Laravel Application                     │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │   Routes    │  │ Controllers │  │      Services       │  │
│  │  (web.php)  │→ │             │→ │  (Business Logic)   │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
│                                              │               │
│  ┌─────────────┐  ┌─────────────┐            │               │
│  │    Views    │  │   Models    │←───────────┘               │
│  │   (Blade)   │  │ (Eloquent)  │                            │
│  └─────────────┘  └─────────────┘                            │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                         Supabase                             │
│  ┌──────────────────┐  ┌──────────────────────────────────┐ │
│  │   Supabase Auth  │  │        PostgreSQL Database       │ │
│  │  (認証・認可)     │  │   (users, decks, battles, etc)  │ │
│  └──────────────────┘  └──────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

## ディレクトリ構造（予定）

```
shadova-log-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/          # 認証関連
│   │   │   ├── BattleController.php
│   │   │   ├── DeckController.php
│   │   │   └── StatisticsController.php
│   │   ├── Middleware/
│   │   └── Requests/          # フォームリクエスト（バリデーション）
│   ├── Models/
│   │   ├── User.php
│   │   ├── Deck.php
│   │   ├── Battle.php
│   │   └── LeaderClass.php
│   └── Services/              # ビジネスロジック
│       ├── BattleService.php
│       ├── StatisticsService.php
│       └── DeckService.php
├── database/
│   └── migrations/            # マイグレーションファイル
├── resources/
│   └── views/                 # Bladeテンプレート
├── routes/
│   ├── web.php               # Webルート
│   └── api.php               # APIルート
├── config/
│   └── supabase.php          # Supabase設定
└── docs/                      # ドキュメント
```

---

## 認証フロー

Supabase Authを使用した認証フローを採用します。

### ユーザー登録
1. ユーザーがメールアドレス・パスワードを入力
2. Supabase Authでユーザー作成
3. Laravelの`users`テーブルにも同期（Supabase user_idを保存）
4. セッション開始

### ログイン
1. ユーザーがメールアドレス・パスワードを入力
2. Supabase Authで認証
3. JWTトークンを取得
4. Laravelセッションに保存

### OAuth（検討中）
- Google
- Twitter
- Discord

---

## APIエンドポイント概要

| エンドポイント | メソッド | 説明 |
|---------------|----------|------|
| `/api/battles` | GET | 対戦履歴一覧取得 |
| `/api/battles` | POST | 対戦記録作成 |
| `/api/battles/{id}` | PUT | 対戦記録更新 |
| `/api/battles/{id}` | DELETE | 対戦記録削除 |
| `/api/decks` | GET | デッキ一覧取得 |
| `/api/decks` | POST | デッキ作成 |
| `/api/statistics` | GET | 統計情報取得 |

詳細は [api/api-reference.md](../api/api-reference.md) を参照。

---

## シャドウバース固有の定義

### クラス（リーダー）一覧

| ID | クラス名 | 英語名 |
|----|---------|--------|
| 1 | エルフ | Forestcraft |
| 2 | ロイヤル | Swordcraft |
| 3 | ウィッチ | Runecraft |
| 4 | ドラゴン | Dragoncraft |
| 5 | ネクロマンサー | Shadowcraft |
| 6 | ヴァンパイア | Bloodcraft |
| 7 | ビショップ | Havencraft |
| 8 | ネメシス | Portalcraft |

### 対戦形式（ゲームモード）

| ID | 形式名 | 説明 |
|----|--------|------|
| `RANK` | ランクマッチ | ランク戦 |
| `GP` | グランプリ | グランプリ大会 |
| `ROOM` | ルームマッチ | フレンド対戦 |
| `2PICK` | 2Pick | アリーナ（2Pickモード） |
| `OPEN6` | Open 6 | アリーナ（Open 6モード） |
| `FREE` | フリーマッチ | カジュアル対戦 |
