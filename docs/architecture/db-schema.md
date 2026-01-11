# データベーススキーマ定義

このドキュメントは、Shadova Log Appのデータベーススキーマについて記述します。

---

## テーブル一覧

- `users`: ユーザー情報を格納するテーブル
- `decks`: デッキ情報を格納するテーブル
- `battles`: 対戦履歴を格納するテーブル
- `leader_classes`: クラス（リーダー）マスタテーブル
- `game_modes`: ゲームモードマスタテーブル

---

## ER図

```
┌─────────────┐       ┌─────────────┐       ┌────────────────┐
│    users    │       │    decks    │       │ leader_classes │
├─────────────┤       ├─────────────┤       ├────────────────┤
│ id (PK)     │──┐    │ id (PK)     │   ┌──│ id (PK)        │
│ supabase_id │  │    │ user_id (FK)│───┘  │ name           │
│ username    │  │    │ name        │      │ name_en        │
│ email       │  │    │ leader_id   │──────│                │
│ created_at  │  │    │ active      │      └────────────────┘
│ updated_at  │  │    │ created_at  │
└─────────────┘  │    │ updated_at  │
                 │    └─────────────┘
                 │
                 │    ┌─────────────────┐   ┌────────────────┐
                 │    │     battles     │   │   game_modes   │
                 │    ├─────────────────┤   ├────────────────┤
                 └───→│ id (PK)         │   │ id (PK)        │
                      │ user_id (FK)    │   │ code           │
                      │ deck_id (FK)    │───│ name           │
                      │ opponent_class  │   └────────────────┘
                      │ game_mode (FK)  │
                      │ result          │
                      │ is_first        │
                      │ played_at       │
                      │ notes           │
                      │ created_at      │
                      │ updated_at      │
                      └─────────────────┘
```

---

## テーブル定義

### `users` テーブル

ユーザーアカウント情報を管理します。Supabase Authと連携します。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `bigint` | **Primary Key**, Auto Increment | ユーザーID |
| `supabase_id` | `uuid` | **Unique**, **Not Null** | Supabase Auth のユーザーID |
| `username` | `varchar(50)` | **Unique**, **Not Null** | ユーザー名（表示名） |
| `email` | `varchar(255)` | **Unique**, **Not Null** | メールアドレス |
| `theme_preference` | `varchar(10)` | Default: `'dark'` | テーマ設定（'dark' / 'light'） |
| `created_at` | `timestamp` | **Not Null**, Default: `now()` | 作成日時 |
| `updated_at` | `timestamp` | **Not Null**, Default: `now()` | 更新日時 |

### `leader_classes` テーブル

シャドウバースのクラス（リーダー）マスタデータ。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `tinyint` | **Primary Key** | クラスID |
| `name` | `varchar(20)` | **Not Null** | クラス名（日本語） |
| `name_en` | `varchar(20)` | **Not Null** | クラス名（英語） |

**初期データ:**

| id | name | name_en |
|----|------|---------|
| 1 | エルフ | Forestcraft |
| 2 | ロイヤル | Swordcraft |
| 3 | ウィッチ | Runecraft |
| 4 | ドラゴン | Dragoncraft |
| 5 | ネクロマンサー | Shadowcraft |
| 6 | ヴァンパイア | Bloodcraft |
| 7 | ビショップ | Havencraft |
| 8 | ネメシス | Portalcraft |

### `game_modes` テーブル

対戦形式（ゲームモード）マスタデータ。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `tinyint` | **Primary Key** | モードID |
| `code` | `varchar(10)` | **Unique**, **Not Null** | モードコード |
| `name` | `varchar(20)` | **Not Null** | モード名 |

**初期データ:**

| id | code | name |
|----|------|------|
| 1 | RANK | ランクマッチ |
| 2 | GP | グランプリ |
| 3 | ROOM | ルームマッチ |
| 4 | 2PICK | 2Pick |
| 5 | OPEN6 | Open 6 |
| 6 | FREE | フリーマッチ |

### `decks` テーブル

ユーザーが登録したデッキ情報を管理します。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `bigint` | **Primary Key**, Auto Increment | デッキID |
| `user_id` | `bigint` | **Foreign Key (users.id)**, **Not Null** | ユーザーID |
| `name` | `varchar(100)` | **Not Null** | デッキ名 |
| `leader_class_id` | `tinyint` | **Foreign Key (leader_classes.id)**, **Not Null** | クラスID |
| `active` | `boolean` | **Not Null**, Default: `true` | アクティブかどうか |
| `created_at` | `timestamp` | Default: `now()` | 作成日時 |
| `updated_at` | `timestamp` | Default: `now()` | 更新日時 |

**インデックス:**
- `idx_decks_user_id` on `user_id`
- `idx_decks_leader_class_id` on `leader_class_id`

### `battles` テーブル

対戦履歴を管理します。

| カラム名 | データ型 | 制約 | 説明 |
|:---|:---|:---|:---|
| `id` | `bigint` | **Primary Key**, Auto Increment | 対戦ID |
| `user_id` | `bigint` | **Foreign Key (users.id)**, **Not Null** | ユーザーID |
| `deck_id` | `bigint` | **Foreign Key (decks.id)**, **Not Null** | 使用デッキID |
| `opponent_class_id` | `tinyint` | **Foreign Key (leader_classes.id)**, **Not Null** | 相手クラスID |
| `game_mode_id` | `tinyint` | **Foreign Key (game_modes.id)**, **Not Null** | ゲームモードID |
| `result` | `boolean` | **Not Null** | 結果（true: 勝利, false: 敗北） |
| `is_first` | `boolean` | **Not Null** | 先攻かどうか（true: 先攻, false: 後攻） |
| `played_at` | `timestamp` | **Not Null** | 対戦日時 |
| `notes` | `text` | Nullable | メモ |
| `created_at` | `timestamp` | Default: `now()` | 作成日時 |
| `updated_at` | `timestamp` | Default: `now()` | 更新日時 |

**インデックス:**
- `idx_battles_user_id` on `user_id`
- `idx_battles_deck_id` on `deck_id`
- `idx_battles_played_at` on `played_at`
- `idx_battles_game_mode_id` on `game_mode_id`

---

## リレーションシップ

| 関係 | 説明 |
|------|------|
| `User` 1 → N `Deck` | ユーザーは複数のデッキを持つ |
| `User` 1 → N `Battle` | ユーザーは複数の対戦履歴を持つ |
| `Deck` 1 → N `Battle` | デッキは複数の対戦で使用される |
| `LeaderClass` 1 → N `Deck` | クラスは複数のデッキに紐づく |
| `LeaderClass` 1 → N `Battle` | クラスは複数の対戦（相手クラス）に紐づく |
| `GameMode` 1 → N `Battle` | ゲームモードは複数の対戦に紐づく |

---

## Supabase固有の設定

### Row Level Security (RLS)

Supabaseを使用するため、RLS（行レベルセキュリティ）を設定します。

```sql
-- usersテーブル: 自分のデータのみアクセス可能
ALTER TABLE users ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Users can view own data" ON users
  FOR SELECT USING (auth.uid() = supabase_id);

CREATE POLICY "Users can update own data" ON users
  FOR UPDATE USING (auth.uid() = supabase_id);

-- decksテーブル
ALTER TABLE decks ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Users can CRUD own decks" ON decks
  FOR ALL USING (
    user_id IN (SELECT id FROM users WHERE supabase_id = auth.uid())
  );

-- battlesテーブル
ALTER TABLE battles ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Users can CRUD own battles" ON battles
  FOR ALL USING (
    user_id IN (SELECT id FROM users WHERE supabase_id = auth.uid())
  );
```
