# システム概要

このドキュメントは、Shadova Log App のシステム全体構成について記述します。

---

## プロジェクト概要

**Shadova Log App** は、シャドウバース ワールズビヨンドのプレイヤーが対戦成績を記録・分析するためのWebアプリケーションです。配信者向けのリアルタイムオーバーレイ機能も備えています。

---

## 技術スタック

| レイヤー | 技術 | バージョン | 説明 |
|---------|------|-----------|------|
| **バックエンド** | Laravel | 11.x | PHP Webフレームワーク |
| **言語** | PHP | 8.2+ | サーバーサイド言語 |
| **フロントエンド** | Blade + Alpine.js | 3.15.x | SSRベースのリアクティブUI |
| **CSS** | Tailwind CSS | 4.0 | ユーティリティファーストCSS |
| **ビルドツール** | Vite | 7.0.x | フロントエンドビルド |
| **HTTPクライアント** | Axios | 1.11.x | 非同期通信 |
| **データベース** | PostgreSQL | - | Supabase マネージド |
| **認証** | Laravel Sessions | - | セッションベース認証 |
| **ホスティング** | Render | - | コンテナホスティング |

---

## システム構成図

```
┌─────────────────────────────────────────────────────────────────┐
│                         クライアント                              │
│                      (ブラウザ / OBS等)                          │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                        Render (Docker)                          │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                   Laravel Application                      │  │
│  │                                                            │  │
│  │  ┌──────────┐  ┌─────────────┐  ┌───────────────────────┐ │  │
│  │  │  Routes  │  │ Controllers │  │   Business Logic      │ │  │
│  │  │ (web.php)│→ │             │→ │   (in Controllers)    │ │  │
│  │  └──────────┘  └─────────────┘  └───────────────────────┘ │  │
│  │        ↓              ↓                    ↓              │  │
│  │  ┌──────────┐  ┌─────────────┐  ┌───────────────────────┐ │  │
│  │  │  Views   │  │   Models    │  │      Policies         │ │  │
│  │  │ (Blade)  │  │ (Eloquent)  │  │   (Authorization)     │ │  │
│  │  └──────────┘  └─────────────┘  └───────────────────────┘ │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                          Supabase                               │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                   PostgreSQL Database                      │  │
│  │   users, battles, decks, share_links, user_settings, etc   │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## ディレクトリ構造

```
shadova-log-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php           # 認証処理
│   │       ├── BattleController.php         # 対戦記録 CRUD
│   │       ├── DeckController.php           # デッキ管理
│   │       ├── StatisticsController.php     # 統計・分析
│   │       ├── SettingsController.php       # ユーザー設定
│   │       ├── StreamerController.php       # 配信者モード
│   │       ├── ShareController.php          # 共有リンク管理
│   │       └── PublicProfileController.php  # 公開プロフィール
│   ├── Models/
│   │   ├── User.php                        # ユーザー
│   │   ├── Battle.php                      # 対戦記録
│   │   ├── Deck.php                        # デッキ (ソフトデリート対応)
│   │   ├── ShareLink.php                   # 共有リンク
│   │   ├── UserSetting.php                 # ユーザー設定
│   │   ├── StreamerSession.php             # 配信セッション
│   │   ├── LeaderClass.php                 # クラスマスタ
│   │   ├── GameMode.php                    # ゲームモードマスタ
│   │   ├── Rank.php                        # ランクマスタ
│   │   └── Group.php                       # グループマスタ
│   ├── Policies/
│   │   ├── BattlePolicy.php                # 対戦記録の認可
│   │   ├── DeckPolicy.php                  # デッキの認可
│   │   └── ShareLinkPolicy.php             # 共有リンクの認可
│   └── Notifications/
│       └── ResetPasswordNotification.php   # パスワードリセット通知
├── routes/
│   └── web.php                             # Webルート定義
├── database/
│   └── migrations/                         # 20個のマイグレーション
├── resources/
│   ├── views/                              # Bladeテンプレート
│   │   ├── auth/                          # 認証画面
│   │   ├── battles/                       # 対戦記録画面
│   │   ├── statistics/                    # 統計画面
│   │   ├── settings/                      # 設定画面
│   │   ├── streamer/                      # 配信者モード画面
│   │   ├── shares/                        # 公開プロフィール
│   │   └── components/layouts/            # レイアウト
│   ├── css/app.css                        # Tailwind CSS
│   └── js/app.js                          # Alpine.js エントリ
├── public/
│   └── build/                             # Viteビルド出力
├── config/                                # Laravel設定
├── storage/                               # ログ・ファイル
└── docs/                                  # ドキュメント
```

---

## 認証フロー

Laravel標準のセッションベース認証を採用しています。

### ユーザー登録
1. ユーザーがメールアドレス・パスワード・ユーザー名を入力
2. Laravel でユーザー作成（パスワードはBcryptでハッシュ化）
3. セッション開始、ホーム画面へリダイレクト

### ログイン
1. ユーザーがメールアドレス・パスワードを入力
2. Laravel Auth で認証
3. セッション作成（データベースドライバ使用）
4. 対戦記録画面へリダイレクト

### パスワードリセット
1. ユーザーがメールアドレスを入力
2. リセットトークンをメール送信
3. トークン付きURLから新パスワード設定

---

## 主要コントローラー

| コントローラー | 責務 |
|--------------|------|
| `AuthController` | ユーザー登録・ログイン・ログアウト・パスワードリセット |
| `BattleController` | 対戦記録のCRUD、統計計算、フィルタリング |
| `DeckController` | デッキのCRUD、デッキ別統計 |
| `StatisticsController` | 期間別統計、クラス別分析、相性表 |
| `SettingsController` | プロフィール・テーマ・データ管理 |
| `StreamerController` | 配信セッション管理、オーバーレイAPI |
| `ShareController` | 共有リンクのCRUD、有効/無効切替 |
| `PublicProfileController` | 公開プロフィール表示 |

---

## シャドウバース固有の定義

### クラス（リーダー）一覧

| ID | クラス名 | 英語名 |
|----|---------|--------|
| 1 | エルフ | Elf |
| 2 | ロイヤル | Royale |
| 3 | ウィッチ | Witch |
| 4 | ドラゴン | Dragon |
| 5 | ナイトメア | Nightmare |
| 6 | ビショップ | Bishop |
| 7 | ネメシス | Nemesis |

※旧シャドバの「ネクロマンサー」と「ヴァンパイア」は「ナイトメア」に統合

### 対戦形式（ゲームモード）

| ID | コード | 形式名 | 説明 |
|----|--------|--------|------|
| 1 | RANK | ランクマッチ | ランク戦 |
| 2 | FREE | フリーマッチ | カジュアル対戦 |
| 3 | ROOM | ルームマッチ | フレンド対戦 |
| 4 | GP | グランプリ | グランプリ大会 |
| 5 | 2PICK | 2Pick | アリーナモード |

---

## セキュリティ

### 認証・認可
- **セッション管理**: データベースドライバ使用
- **CSRF保護**: 全POSTリクエストにトークン必須
- **Policy認可**: リソース所有者のみアクセス可能

### パスワード
- **ハッシュ化**: Bcrypt（rounds: 12）
- **バリデーション**: 最小8文字

### その他
- **Mixed Content対策**: HTTPS強制（本番環境）
- **XSS対策**: Bladeのエスケープ機能

---

## 関連ドキュメント

- [データベーススキーマ](./db-schema.md)
- [フロントエンドアーキテクチャ](./frontend-architecture.md)
- [API仕様](../api/api-reference.md)
- [機能設計](../design/feature-design.md)
