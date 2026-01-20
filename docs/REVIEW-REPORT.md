# ドキュメントレビューレポート

**レビュー日時**: 2026-01-20
**レビュー方針**: 実装準拠（ドキュメントを実装に合わせて修正する方針）
**レビュー対象**: docs/ ディレクトリ内の全ファイル

---

## 概要

docs/ディレクトリ内の全ドキュメントをレビューし、CLAUDE.md、実装コード（app/Models/、database/migrations/、routes/web.php等）と照合した結果、以下の問題点を発見しました。

---

## 1. ドキュメント間の矛盾点

### 1.1 Laravelバージョンの不一致

| ドキュメント | 記載バージョン |
|-------------|---------------|
| CLAUDE.md | Laravel 12.x |
| docs/02-architecture/system-overview.md | Laravel 12.x |
| docs/02-architecture/frontend-architecture.md | Laravel 11 |

**修正**: frontend-architecture.md の「Blade: Laravel 11」を「Laravel 12」に修正する必要があります。

### 1.2 PHPバージョンの不一致

| ドキュメント | 記載バージョン |
|-------------|---------------|
| CLAUDE.md | PHP 8.2+ |
| README.md | PHP 8.2+ |
| docs/08-deployment/environment-setup.md | PHP 8.3+ |
| docs/08-deployment/deployment.md (Dockerfile) | PHP 8.4 |

**修正**: バージョン要件を統一する必要があります（実運用に合わせて8.2+または8.3+に統一推奨）。

### 1.3 関連ドキュメントへのリンクパスの不一致

以下のドキュメントで、存在しないパスへのリンクが記載されています：

| ドキュメント | 誤ったリンク | 正しいリンク |
|-------------|-------------|-------------|
| docs/02-architecture/system-overview.md | `../api/api-reference.md` | `../06-interfaces/api-reference.md` |
| docs/02-architecture/system-overview.md | `../design/feature-design.md` | `../05-features/feature-design.md` |
| docs/02-architecture/frontend-architecture.md | `../api/api-reference.md` | `../06-interfaces/api-reference.md` |
| docs/02-architecture/frontend-architecture.md | `../design/feature-design.md` | `../05-features/feature-design.md` |
| docs/05-features/feature-design.md | `../architecture/system-overview.md` | `../02-architecture/system-overview.md` |
| docs/05-features/feature-design.md | `../architecture/db-schema.md` | `../04-data/db-schema.md` |
| docs/05-features/feature-design.md | `../api/api-reference.md` | `../06-interfaces/api-reference.md` |
| docs/06-interfaces/api-reference.md | `../architecture/system-overview.md` | `../02-architecture/system-overview.md` |
| docs/06-interfaces/api-reference.md | `../architecture/db-schema.md` | `../04-data/db-schema.md` |
| docs/06-interfaces/api-reference.md | `../architecture/frontend-architecture.md` | `../02-architecture/frontend-architecture.md` |
| docs/08-deployment/deployment.md | `../guides/environment-setup.md` | `./environment-setup.md` |
| README.md | `./docs/architecture/system-overview.md` | `./docs/02-architecture/system-overview.md` |
| README.md | `./docs/architecture/db-schema.md` | `./docs/04-data/db-schema.md` |
| README.md | `./docs/api/api-reference.md` | `./docs/06-interfaces/api-reference.md` |
| README.md | `./docs/design/feature-design.md` | `./docs/05-features/feature-design.md` |
| README.md | `./docs/guides/environment-setup.md` | `./docs/08-deployment/environment-setup.md` |
| README.md | `./docs/deployment/deployment.md` | `./docs/08-deployment/deployment.md` |

---

## 2. 実装と乖離している記述

### 2.1 マイグレーション数の不一致

| 記載 | 実際 |
|------|------|
| docs/02-architecture/system-overview.md: 「20個のマイグレーション」 | 21個のマイグレーション |
| docs/04-data/db-schema.md: マイグレーション一覧が20件 | 21件存在 |

**不足しているマイグレーション**: `2026_01_11_182715_add_group_id_to_battles_table.php`

### 2.2 user_settings テーブルのカラム定義の乖離

#### docs/04-data/db-schema.md の記載（古い仕様）:
```
overlay_background_color, overlay_text_color, overlay_accent_color,
overlay_font_size, overlay_opacity, overlay_show_streak,
overlay_show_winrate, overlay_show_record
```

#### 実際の実装（app/Models/UserSetting.php + マイグレーション）:
```
overlay_bg_transparent, overlay_font_size, overlay_color_theme,
overlay_custom_bg_color, overlay_custom_text_color, overlay_show_winrate,
overlay_show_record, overlay_show_streak, overlay_show_deck,
overlay_show_log, overlay_log_count
```

**相違点**:
- `overlay_background_color` → `overlay_custom_bg_color` に変更
- `overlay_text_color` → `overlay_custom_text_color` に変更
- `overlay_accent_color` → 削除
- `overlay_opacity` → 削除
- `overlay_bg_transparent` → 追加
- `overlay_color_theme` → 追加
- `overlay_show_deck` → 追加
- `overlay_show_log` → 追加
- `overlay_log_count` → 追加

### 2.3 user_settings テーブルの主キー定義の乖離

| docs/04-data/db-schema.md の記載 | 実際の実装 |
|--------------------------------|-----------|
| `user_id` が主キー（PK） | `id` が主キー（Auto Increment）、`user_id` はユニーク制約のみ |

### 2.4 Userモデルのリレーション定義の不足

#### CLAUDE.md の記載:
```
User
├── hasMany: StreamerSession
```

#### 実際の実装 (app/Models/User.php):
`streamerSessions()` リレーションが定義されていません。

### 2.5 Battleモデルのdeck()リレーション

#### docs/04-data/db-schema.md の記載:
```php
public function deck() { return $this->belongsTo(Deck::class)->withTrashed(); }
```

#### 実際の実装 (app/Models/Battle.php):
```php
public function deck(): BelongsTo
{
    return $this->belongsTo(Deck::class);
}
```

**相違点**: 実装では `->withTrashed()` が付いていません。削除済みデッキとのリレーションを維持する場合は実装を修正するか、ドキュメントを実装に合わせる必要があります。

### 2.6 フィードバック機能がドキュメントに未記載

routes/web.php に以下のルートが存在しますが、どのドキュメントにも記載がありません：

```php
// フィードバック
Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
Route::post('/feedback/bug', [FeedbackController::class, 'storeBug'])->name('feedback.bug');
Route::post('/feedback/enhancement', [FeedbackController::class, 'storeEnhancement'])->name('feedback.enhancement');
Route::post('/feedback/contact', [FeedbackController::class, 'storeContact'])->name('feedback.contact');
```

### 2.7 オーバーレイAPI レスポンス形式の乖離

#### docs/06-interfaces/api-reference.md の記載:
```json
{
  "wins": 10,
  "losses": 5,
  "winRate": 66.7,
  "streak": 3,
  "settings": { ... }
}
```

#### 実際の実装 (StreamerController::overlayData()):
```json
{
  "session_name": "配信中",
  "stats": {
    "total": 15,
    "wins": 10,
    "losses": 5,
    "win_rate": 66.7,
    "streak": 3
  },
  "deck": {
    "name": "エルフアグロ",
    "class": "エルフ",
    "total": 10,
    "wins": 7,
    "losses": 3,
    "win_rate": 70.0
  },
  "log": [...]
}
```

**相違点**: レスポンス構造が大幅に異なります。

### 2.8 オーバーレイ設定更新 API の乖離

#### docs/06-interfaces/api-reference.md の記載:
- `overlay_background_color`, `overlay_text_color`, `overlay_accent_color`, `overlay_font_size`, `overlay_opacity`

#### 実際の実装 (StreamerController::updateOverlaySettings()):
- `overlay_bg_transparent`, `overlay_font_size`, `overlay_color_theme`, `overlay_custom_bg_color`, `overlay_custom_text_color`, `overlay_show_winrate`, `overlay_show_record`, `overlay_show_streak`, `overlay_show_deck`, `overlay_show_log`, `overlay_log_count`

---

## 3. 不足している情報

### 3.1 機能ドキュメントの不足

- **フィードバック機能**: GitHub Issue連携によるバグ報告・機能要望・お問い合わせ機能が実装されているが、どのドキュメントにも記載がありません。

### 3.2 APIリファレンスの不足

以下のルートがdocs/06-interfaces/api-reference.mdに記載されていません：

| ルート | 説明 |
|--------|------|
| `GET /feedback` | フィードバック画面表示 |
| `POST /feedback/bug` | バグ報告送信 |
| `POST /feedback/enhancement` | 機能要望送信 |
| `POST /feedback/contact` | お問い合わせ送信 |

### 3.3 環境変数の不足

docs/08-deployment/environment-setup.md に以下の環境変数が記載されていません：

- OAuth認証用の環境変数
  - `GOOGLE_CLIENT_ID`
  - `GOOGLE_CLIENT_SECRET`
  - `GOOGLE_REDIRECT_URI`
  - `DISCORD_CLIENT_ID`
  - `DISCORD_CLIENT_SECRET`
  - `DISCORD_REDIRECT_URI`
- GitHub Issue連携用の環境変数
  - `GITHUB_TOKEN`（推測）
  - `GITHUB_REPO`（推測）

### 3.4 ディレクトリ構造の不足

docs/02-architecture/system-overview.md のディレクトリ構造に以下が記載されていません：

- `app/Services/` ディレクトリ（GitHubService.php が存在）
- `app/Database/` ディレクトリ（PostgresConnection.php が存在）

### 3.5 コントローラー一覧の不足

docs/02-architecture/system-overview.md の主要コントローラー一覧に以下が記載されていません：

| コントローラー | 責務 |
|--------------|------|
| `FeedbackController` | バグ報告・機能要望・お問い合わせ（GitHub Issue連携） |

---

## 4. 改善すべき点

### 4.1 ドキュメント構成の整理

- README.md内のドキュメントリンクがすべて古いパス形式（番号プレフィックスなし）になっているため、現在のディレクトリ構造と不整合が発生しています。

### 4.2 バージョン情報の一元管理

- PHP、Laravel、各種ライブラリのバージョン情報が複数のドキュメントに散在しており、更新漏れが発生しやすい状態です。
- CLAUDE.md または README.md を正とし、他のドキュメントからは参照する形に変更することを推奨します。

### 4.3 データベーススキーマの自動生成検討

- スキーマ情報が実装と乖離しやすいため、マイグレーションやEloquentモデルから自動生成する仕組みの導入を検討してください。

### 4.4 オーバーレイ設定のドキュメント整備

- 配信者モードのオーバーレイ設定が大幅に変更されているため、docs/05-features/feature-design.md の「オーバーレイ設定」セクションも更新が必要です。

### 4.5 日時の更新

- docs/05-features/feature-design.md のサンプル日時が「2024-01-15」となっており、古い形式になっています。現在の年次（2026年）に更新するか、プレースホルダー形式に変更することを推奨します。

---

## 修正優先度

### 高優先度（機能理解に影響）

1. user_settings テーブルのカラム定義の修正（db-schema.md）
2. オーバーレイAPIレスポンス形式の修正（api-reference.md）
3. フィードバック機能のドキュメント追加
4. 関連ドキュメントへのリンクパス修正（全ドキュメント）

### 中優先度（整合性確保）

5. マイグレーション一覧の更新（db-schema.md）
6. Userモデルのリレーション記載更新（CLAUDE.md）
7. コントローラー一覧の更新（system-overview.md）
8. 環境変数の追記（environment-setup.md）

### 低優先度（品質向上）

9. PHPバージョンの統一
10. ディレクトリ構造の更新
11. 日時サンプルの更新

---

## まとめ

実装は正常に動作しているものと推測されますが、ドキュメントが実装の変更に追従できていない状態です。特に配信者モード（オーバーレイ設定）とフィードバック機能周りの乖離が顕著です。

上記の修正を行うことで、新規開発者のオンボーディングや保守性の向上が期待できます。
