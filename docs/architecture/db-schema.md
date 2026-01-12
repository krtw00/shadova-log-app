# データベーススキーマ定義

このドキュメントは、Shadova Log App のデータベーススキーマについて記述します。

---

## テーブル一覧

| テーブル名 | 説明 |
|-----------|------|
| `users` | ユーザー情報 |
| `decks` | デッキ情報（ソフトデリート対応） |
| `battles` | 対戦履歴 |
| `leader_classes` | クラス（リーダー）マスタ |
| `game_modes` | ゲームモードマスタ |
| `ranks` | ランクマスタ |
| `groups` | グループマスタ |
| `share_links` | 共有リンク |
| `user_settings` | ユーザー設定 |
| `streamer_sessions` | 配信セッション |
| `sessions` | Laravelセッション |
| `cache` | キャッシュ |
| `jobs` | キュー |

---

## ER図

```
┌──────────────┐      ┌──────────────┐      ┌────────────────┐
│    users     │      │    decks     │      │ leader_classes │
├──────────────┤      ├──────────────┤      ├────────────────┤
│ id (PK)      │◄──┐  │ id (PK)      │  ┌──►│ id (PK)        │
│ email        │   │  │ user_id (FK) │──┘   │ name           │
│ username     │   │  │ name         │      │ name_en        │
│ password     │   │  │ leader_class │──────┤                │
│ supabase_id  │   │  │ deleted_at   │      └────────────────┘
│ created_at   │   │  │ created_at   │
│ updated_at   │   │  │ updated_at   │
└──────────────┘   │  └──────────────┘
       │           │         │
       │           │         │
       ▼           │         ▼
┌──────────────────┤  ┌─────────────────────┐
│  user_settings   │  │      battles        │
├──────────────────┤  ├─────────────────────┤
│ user_id (PK,FK)  │  │ id (PK)             │
│ theme            │  │ user_id (FK)        │──┘
│ per_page         │  │ deck_id (FK)        │───────────────┐
│ default_game_mode│  │ opponent_class (FK) │───────────────┤
│ streamer_mode    │  │ my_class_id (FK)    │───────────────┤
│ overlay_*        │  │ game_mode_id (FK)   │──┐            │
└──────────────────┘  │ rank_id (FK)        │──┤            │
                      │ group_id (FK)       │──┤            │
       │              │ result              │  │            │
       │              │ is_first            │  │    ┌───────┴────────┐
       ▼              │ played_at           │  │    │ leader_classes │
┌──────────────────┐  │ notes               │  │    └────────────────┘
│ streamer_sessions│  │ created_at          │  │
├──────────────────┤  │ updated_at          │  │    ┌────────────────┐
│ id (PK)          │  └─────────────────────┘  ├───►│   game_modes   │
│ user_id (FK)     │──┘                        │    ├────────────────┤
│ name             │       ┌───────────────────┤    │ id (PK)        │
│ started_at       │       │                   │    │ code           │
│ ended_at         │       │                   │    │ name           │
│ is_active        │       ▼                   │    └────────────────┘
│ streak_start     │  ┌────────────┐           │
└──────────────────┘  │   ranks    │           │    ┌────────────────┐
                      ├────────────┤           └───►│    groups      │
       │              │ id (PK)    │                ├────────────────┤
       ▼              │ name       │                │ id (PK)        │
┌──────────────────┐  │ tier       │                │ name           │
│   share_links    │  │ level      │                │ code           │
├──────────────────┤  │ sort_order │                │ sort_order     │
│ id (PK)          │  └────────────┘                └────────────────┘
│ user_id (FK)     │──┘
│ slug             │
│ name             │
│ start_date       │
│ end_date         │
│ is_active        │
│ created_at       │
│ updated_at       │
└──────────────────┘
```

---

## テーブル定義

### `users` テーブル

ユーザーアカウント情報を管理します。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `bigint` | **PK**, Auto Increment | ユーザーID |
| `email` | `varchar(255)` | **Unique**, **Not Null** | メールアドレス |
| `username` | `varchar(255)` | **Unique**, Nullable | ユーザー名（公開用） |
| `password` | `varchar(255)` | **Not Null** | パスワード（Bcryptハッシュ） |
| `supabase_id` | `varchar(255)` | **Unique**, Nullable | Supabase Auth ID（レガシー） |
| `email_verified_at` | `timestamp` | Nullable | メール認証日時 |
| `remember_token` | `varchar(100)` | Nullable | ログイン維持トークン |
| `created_at` | `timestamp` | Nullable | 作成日時 |
| `updated_at` | `timestamp` | Nullable | 更新日時 |

---

### `leader_classes` テーブル

シャドウバース ワールズビヨンドのクラス（リーダー）マスタ。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `smallint` | **PK** | クラスID |
| `name` | `varchar(20)` | **Not Null** | クラス名（日本語） |
| `name_en` | `varchar(20)` | **Not Null** | クラス名（英語） |

**初期データ（7クラス）:**

| id | name | name_en |
|----|------|---------|
| 1 | エルフ | Elf |
| 2 | ロイヤル | Royale |
| 3 | ウィッチ | Witch |
| 4 | ドラゴン | Dragon |
| 5 | ナイトメア | Nightmare |
| 6 | ビショップ | Bishop |
| 7 | ネメシス | Nemesis |

---

### `game_modes` テーブル

対戦形式（ゲームモード）マスタ。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `smallint` | **PK** | モードID |
| `code` | `varchar(10)` | **Unique**, **Not Null** | モードコード |
| `name` | `varchar(20)` | **Not Null** | モード名 |

**初期データ（5モード）:**

| id | code | name |
|----|------|------|
| 1 | RANK | ランクマッチ |
| 2 | FREE | フリーマッチ |
| 3 | ROOM | ルームマッチ |
| 4 | GP | グランプリ |
| 5 | 2PICK | 2Pick |

---

### `ranks` テーブル

ランクマッチのランク情報マスタ。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `bigint` | **PK**, Auto Increment | ランクID |
| `name` | `varchar(255)` | **Not Null** | ランク名 |
| `tier` | `varchar(255)` | **Not Null** | ティア（Master, Grandmaster等） |
| `level` | `int` | Nullable | レベル（1, 2, 3等） |
| `sort_order` | `int` | Default: 0 | 表示順 |
| `created_at` | `timestamp` | Nullable | 作成日時 |
| `updated_at` | `timestamp` | Nullable | 更新日時 |

---

### `groups` テーブル

グランプリのグループ情報マスタ。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `bigint` | **PK**, Auto Increment | グループID |
| `name` | `varchar(255)` | **Not Null** | グループ名 |
| `code` | `varchar(255)` | **Unique**, **Not Null** | グループコード |
| `sort_order` | `int` | Default: 0 | 表示順 |
| `created_at` | `timestamp` | Nullable | 作成日時 |
| `updated_at` | `timestamp` | Nullable | 更新日時 |

---

### `decks` テーブル

ユーザーが登録したデッキ情報。ソフトデリート対応。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `bigint` | **PK**, Auto Increment | デッキID |
| `user_id` | `bigint` | **FK (users.id)**, **Not Null** | ユーザーID |
| `name` | `varchar(100)` | **Not Null** | デッキ名 |
| `leader_class_id` | `smallint` | **FK (leader_classes.id)**, **Not Null** | クラスID |
| `deleted_at` | `timestamp` | Nullable | 削除日時（ソフトデリート） |
| `created_at` | `timestamp` | Nullable | 作成日時 |
| `updated_at` | `timestamp` | Nullable | 更新日時 |

**インデックス:**
- `idx_decks_user_id` on `user_id`
- `idx_decks_leader_class_id` on `leader_class_id`

---

### `battles` テーブル

対戦履歴を管理します。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `bigint` | **PK**, Auto Increment | 対戦ID |
| `user_id` | `bigint` | **FK (users.id)**, **Not Null** | ユーザーID |
| `deck_id` | `bigint` | **FK (decks.id)**, Nullable | 使用デッキID（2Pick時はnull） |
| `my_class_id` | `smallint` | **FK (leader_classes.id)**, Nullable | 自分のクラス（2Pick用） |
| `opponent_class_id` | `smallint` | **FK (leader_classes.id)**, **Not Null** | 相手クラスID |
| `game_mode_id` | `smallint` | **FK (game_modes.id)**, **Not Null** | ゲームモードID |
| `rank_id` | `bigint` | **FK (ranks.id)**, Nullable | ランクID |
| `group_id` | `bigint` | **FK (groups.id)**, Nullable | グループID |
| `result` | `boolean` | **Not Null** | 結果（true: 勝利, false: 敗北） |
| `is_first` | `boolean` | **Not Null** | 先攻かどうか |
| `played_at` | `timestamp` | **Not Null** | 対戦日時 |
| `notes` | `text` | Nullable | メモ |
| `created_at` | `timestamp` | Nullable | 作成日時 |
| `updated_at` | `timestamp` | Nullable | 更新日時 |

**インデックス:**
- `idx_battles_user_id` on `user_id`
- `idx_battles_deck_id` on `deck_id`
- `idx_battles_played_at` on `played_at`
- `idx_battles_game_mode_id` on `game_mode_id`

---

### `share_links` テーブル

公開プロフィール用の共有リンク。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `bigint` | **PK**, Auto Increment | リンクID |
| `user_id` | `bigint` | **FK (users.id)**, **Not Null** | ユーザーID |
| `slug` | `varchar(255)` | **Not Null** | URLスラッグ |
| `name` | `varchar(255)` | **Not Null** | リンク名 |
| `start_date` | `date` | Nullable | 期間開始日 |
| `end_date` | `date` | Nullable | 期間終了日 |
| `is_active` | `boolean` | Default: true | 有効/無効 |
| `created_at` | `timestamp` | Nullable | 作成日時 |
| `updated_at` | `timestamp` | Nullable | 更新日時 |

**ユニーク制約:**
- `(user_id, slug)` - 同一ユーザー内でスラッグは一意

---

### `user_settings` テーブル

ユーザーごとの設定情報。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `user_id` | `bigint` | **PK**, **FK (users.id)** | ユーザーID |
| `theme` | `varchar(255)` | Default: 'dark' | テーマ（dark/light） |
| `per_page` | `int` | Default: 20 | 一覧表示件数 |
| `default_game_mode_id` | `smallint` | **FK (game_modes.id)**, Nullable | デフォルトゲームモード |
| `streamer_mode_enabled` | `boolean` | Default: false | 配信者モード有効 |
| `overlay_background_color` | `varchar(255)` | Default: '#1f2937' | オーバーレイ背景色 |
| `overlay_text_color` | `varchar(255)` | Default: '#ffffff' | オーバーレイ文字色 |
| `overlay_accent_color` | `varchar(255)` | Default: '#3b82f6' | オーバーレイアクセント色 |
| `overlay_font_size` | `varchar(255)` | Default: 'medium' | オーバーレイフォントサイズ |
| `overlay_opacity` | `int` | Default: 100 | オーバーレイ透明度 |
| `overlay_show_streak` | `boolean` | Default: true | 連勝表示 |
| `overlay_show_winrate` | `boolean` | Default: true | 勝率表示 |
| `overlay_show_record` | `boolean` | Default: true | 戦績表示 |
| `created_at` | `timestamp` | Nullable | 作成日時 |
| `updated_at` | `timestamp` | Nullable | 更新日時 |

---

### `streamer_sessions` テーブル

配信者モードのセッション管理。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `bigint` | **PK**, Auto Increment | セッションID |
| `user_id` | `bigint` | **FK (users.id)**, **Not Null** | ユーザーID |
| `name` | `varchar(255)` | Nullable | セッション名 |
| `started_at` | `timestamp` | **Not Null** | 開始日時 |
| `ended_at` | `timestamp` | Nullable | 終了日時 |
| `is_active` | `boolean` | Default: true | アクティブ状態 |
| `streak_start` | `timestamp` | Nullable | 連勝カウント開始日時 |
| `created_at` | `timestamp` | Nullable | 作成日時 |
| `updated_at` | `timestamp` | Nullable | 更新日時 |

---

### `sessions` テーブル

Laravelセッション管理（データベースドライバ使用）。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `varchar(255)` | **PK** | セッションID |
| `user_id` | `bigint` | Nullable | ユーザーID |
| `ip_address` | `varchar(45)` | Nullable | IPアドレス |
| `user_agent` | `text` | Nullable | ユーザーエージェント |
| `payload` | `longtext` | **Not Null** | セッションデータ |
| `last_activity` | `int` | **Not Null** | 最終アクティビティ |

---

### `cache` テーブル

キャッシュ管理（データベースドライバ使用）。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `key` | `varchar(255)` | **PK** | キャッシュキー |
| `value` | `mediumtext` | **Not Null** | キャッシュ値 |
| `expiration` | `int` | **Not Null** | 有効期限 |

---

### `jobs` テーブル

キュー管理（データベースドライバ使用）。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `bigint` | **PK**, Auto Increment | ジョブID |
| `queue` | `varchar(255)` | **Not Null** | キュー名 |
| `payload` | `longtext` | **Not Null** | ジョブデータ |
| `attempts` | `tinyint` | **Not Null** | 試行回数 |
| `reserved_at` | `int` | Nullable | 予約日時 |
| `available_at` | `int` | **Not Null** | 実行可能日時 |
| `created_at` | `int` | **Not Null** | 作成日時 |

---

## リレーションシップ

| 関係 | 説明 |
|------|------|
| `User` 1 → N `Deck` | ユーザーは複数のデッキを持つ |
| `User` 1 → N `Battle` | ユーザーは複数の対戦履歴を持つ |
| `User` 1 → N `ShareLink` | ユーザーは複数の共有リンクを持つ |
| `User` 1 → 1 `UserSetting` | ユーザーは1つの設定を持つ |
| `User` 1 → N `StreamerSession` | ユーザーは複数の配信セッションを持つ |
| `Deck` 1 → N `Battle` | デッキは複数の対戦で使用される |
| `LeaderClass` 1 → N `Deck` | クラスは複数のデッキに紐づく |
| `LeaderClass` 1 → N `Battle` | クラスは複数の対戦（相手・自分）に紐づく |
| `GameMode` 1 → N `Battle` | ゲームモードは複数の対戦に紐づく |
| `Rank` 1 → N `Battle` | ランクは複数の対戦に紐づく |
| `Group` 1 → N `Battle` | グループは複数の対戦に紐づく |

---

## Eloquentモデル関係

```php
// User.php
public function decks() { return $this->hasMany(Deck::class); }
public function battles() { return $this->hasMany(Battle::class); }
public function shareLinks() { return $this->hasMany(ShareLink::class); }
public function setting() { return $this->hasOne(UserSetting::class); }
public function streamerSessions() { return $this->hasMany(StreamerSession::class); }

// Deck.php (SoftDeletes)
public function user() { return $this->belongsTo(User::class); }
public function leaderClass() { return $this->belongsTo(LeaderClass::class); }
public function battles() { return $this->hasMany(Battle::class); }

// Battle.php
public function user() { return $this->belongsTo(User::class); }
public function deck() { return $this->belongsTo(Deck::class)->withTrashed(); }
public function opponentClass() { return $this->belongsTo(LeaderClass::class, 'opponent_class_id'); }
public function myClass() { return $this->belongsTo(LeaderClass::class, 'my_class_id'); }
public function gameMode() { return $this->belongsTo(GameMode::class); }
public function rank() { return $this->belongsTo(Rank::class); }
public function group() { return $this->belongsTo(Group::class); }
```

---

## マイグレーション一覧

| No | マイグレーション | 説明 |
|----|-----------------|------|
| 1 | `create_users_table` | ユーザーテーブル作成 |
| 2 | `create_cache_table` | キャッシュテーブル作成 |
| 3 | `create_jobs_table` | キューテーブル作成 |
| 4 | `create_leader_classes_table` | クラスマスタ作成 |
| 5 | `create_game_modes_table` | ゲームモードマスタ作成 |
| 6 | `add_supabase_fields_to_users_table` | Supabase ID追加 |
| 7 | `create_decks_table` | デッキテーブル作成 |
| 8 | `create_battles_table` | 対戦記録テーブル作成 |
| 9 | `modify_decks_for_soft_delete` | ソフトデリート対応 |
| 10 | `add_username_to_users_table` | ユーザー名追加 |
| 11 | `create_share_links_table` | 共有リンク作成 |
| 12 | `create_ranks_table` | ランクマスタ作成 |
| 13 | `add_rank_id_to_battles_table` | ランク紐付け追加 |
| 14 | `add_my_class_id_to_battles_table` | 自クラス追加 |
| 15 | `create_groups_table` | グループマスタ作成 |
| 16 | `add_group_id_to_battles_table` | グループ紐付け追加 |
| 17 | `create_user_settings_table` | ユーザー設定作成 |
| 18 | `make_deck_id_nullable_in_battles_table` | deck_id nullable化 |
| 19 | `add_streamer_mode_to_user_settings_table` | 配信者モード追加 |
| 20 | `create_streamer_sessions_table` | 配信セッション作成 |
