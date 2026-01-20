# フロントエンドアーキテクチャ

このドキュメントは、Shadova Log App のフロントエンド構成について記述します。

---

## 技術スタック

| 技術 | バージョン | 用途 |
|------|-----------|------|
| **Blade** | Laravel 12.x | サーバーサイドテンプレート |
| **Alpine.js** | 3.15.x | リアクティブUI |
| **Tailwind CSS** | 4.0 | スタイリング |
| **Vite** | 7.0.x | ビルド・HMR |
| **Axios** | 1.11.x | HTTP通信 |

---

## アーキテクチャ概要

```
┌─────────────────────────────────────────────────────────────┐
│                      Blade Templates                         │
│  ┌────────────────────────────────────────────────────────┐ │
│  │                  layouts/app.blade.php                  │ │
│  │  ┌──────────────────────────────────────────────────┐  │ │
│  │  │              Alpine.js Components                │  │ │
│  │  │  - x-data (state management)                     │  │ │
│  │  │  - x-model (two-way binding)                     │  │ │
│  │  │  - x-on (event handling)                         │  │ │
│  │  │  - x-show/x-if (conditional rendering)           │  │ │
│  │  └──────────────────────────────────────────────────┘  │ │
│  │  ┌──────────────────────────────────────────────────┐  │ │
│  │  │              Tailwind CSS Classes                │  │ │
│  │  │  - Utility-first styling                         │  │ │
│  │  │  - Dark mode support                             │  │ │
│  │  │  - Responsive design                             │  │ │
│  │  └──────────────────────────────────────────────────┘  │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                        Vite Build                            │
│  resources/js/app.js  →  public/build/assets/*.js           │
│  resources/css/app.css →  public/build/assets/*.css         │
└─────────────────────────────────────────────────────────────┘
```

---

## ディレクトリ構造

```
resources/
├── views/
│   ├── components/
│   │   └── layouts/
│   │       ├── app.blade.php        # メインレイアウト（認証後）
│   │       └── auth.blade.php       # 認証画面レイアウト
│   ├── auth/
│   │   ├── login.blade.php          # ログイン画面
│   │   ├── register.blade.php       # 登録画面
│   │   ├── forgot-password.blade.php
│   │   └── reset-password.blade.php
│   ├── battles/
│   │   └── index.blade.php          # 対戦記録一覧（メイン画面）
│   ├── statistics/
│   │   └── index.blade.php          # 統計分析画面
│   ├── settings/
│   │   └── index.blade.php          # 設定画面
│   ├── streamer/
│   │   ├── index.blade.php          # 配信者ダッシュボード
│   │   └── overlay.blade.php        # オーバーレイ（ポップアップ）
│   └── shares/
│       └── public.blade.php         # 公開プロフィール
├── css/
│   └── app.css                      # Tailwind CSS エントリ
└── js/
    ├── app.js                       # Alpine.js 初期化
    └── bootstrap.js                 # Axios 設定
```

---

## レイアウト構成

### メインレイアウト (`layouts/app.blade.php`)

認証済みユーザー向けのメインレイアウト。

```
┌─────────────────────────────────────────────────────────────┐
│  Header                                                      │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  Logo    Navigation Tabs    User Menu                 │  │
│  └───────────────────────────────────────────────────────┘  │
├─────────────────────────────────────────────────────────────┤
│  Main Content                                                │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                                                       │  │
│  │              {{ $slot }}                              │  │
│  │                                                       │  │
│  └───────────────────────────────────────────────────────┘  │
├─────────────────────────────────────────────────────────────┤
│  Footer (minimal)                                           │
└─────────────────────────────────────────────────────────────┘
```

**ナビゲーションタブ:**
- 対戦記録
- 統計
- 設定
- 配信者モード（有効時のみ表示）

### 認証レイアウト (`layouts/auth.blade.php`)

ログイン・登録画面用のシンプルなレイアウト。

---

## Alpine.js パターン

### 基本的な状態管理

```html
<div x-data="{
    isOpen: false,
    selectedItem: null,
    items: @json($items)
}">
    <button @click="isOpen = !isOpen">Toggle</button>
    <div x-show="isOpen" x-transition>
        <!-- Content -->
    </div>
</div>
```

### フォーム送信パターン

```html
<form x-data="{ submitting: false }"
      @submit="submitting = true">
    <button :disabled="submitting">
        <span x-show="!submitting">送信</span>
        <span x-show="submitting">送信中...</span>
    </button>
</form>
```

### モーダルパターン

```html
<div x-data="{ showModal: false }">
    <button @click="showModal = true">開く</button>

    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:leave="transition ease-in duration-150"
         class="fixed inset-0 z-50">
        <div class="bg-black bg-opacity-50" @click="showModal = false"></div>
        <div class="modal-content">
            <!-- Modal content -->
        </div>
    </div>
</div>
```

---

## 主要画面の構成

### 対戦記録画面 (`battles/index.blade.php`)

メインの操作画面。対戦の記録・閲覧を行う。

```
┌─────────────────────────────────────────────────────────────┐
│  Game Mode Tabs                                              │
│  [ランクマッチ] [フリー] [ルーム] [GP] [2Pick]               │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────────┐  ┌───────────────────────────────┐ │
│  │  Today's Stats      │  │  Battle Entry Form            │ │
│  │  - Win/Loss         │  │  - Deck selector              │ │
│  │  - Win Rate         │  │  - Opponent class             │ │
│  │  - Current Streak   │  │  - Result (Win/Lose)          │ │
│  └─────────────────────┘  │  - First/Second               │ │
│                           │  - Rank (if RANK mode)        │ │
│                           │  - Group (if GP mode)         │ │
│                           │  - Notes                      │ │
│                           └───────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│  Battle History Table                                        │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ Date | Deck | vs Class | Result | First | Actions    │  │
│  │ ...  | ...  | ...      | ...    | ...   | Edit/Del   │  │
│  └───────────────────────────────────────────────────────┘  │
│  Pagination                                                  │
└─────────────────────────────────────────────────────────────┘
```

### 統計画面 (`statistics/index.blade.php`)

詳細な統計分析を表示。

```
┌─────────────────────────────────────────────────────────────┐
│  Period Selector                                             │
│  [全期間] [今日] [今週] [今月]                               │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐        │
│  │ Total Games  │ │ Win Rate     │ │ Best Streak  │        │
│  │    150       │ │   62.5%      │ │     12       │        │
│  └──────────────┘ └──────────────┘ └──────────────┘        │
├─────────────────────────────────────────────────────────────┤
│  Deck Stats                          Class Matchup          │
│  ┌────────────────────────┐  ┌────────────────────────────┐ │
│  │ Deck Name | W | L | %  │  │ vs Class | W | L | Rate   │ │
│  │ ...       | . | . | .  │  │ エルフ   | . | . | .      │ │
│  └────────────────────────┘  └────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│  Matchup Matrix (自クラス × 相手クラス)                      │
│  ┌───────────────────────────────────────────────────────┐  │
│  │     | Elf | Roy | Wit | Dra | Nig | Bis | Nem        │  │
│  │ Elf |  -  | 55% | 48% | ... | ... | ... | ...        │  │
│  │ ... | ... | ... | ... | ... | ... | ... | ...        │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### 設定画面 (`settings/index.blade.php`)

ユーザー設定を管理。

```
┌─────────────────────────────────────────────────────────────┐
│  Profile Section                                             │
│  - Username                                                  │
│  - Email                                                     │
├─────────────────────────────────────────────────────────────┤
│  Password Section                                            │
│  - Current Password                                          │
│  - New Password                                              │
├─────────────────────────────────────────────────────────────┤
│  Preferences                                                 │
│  - Theme (Dark/Light)                                        │
│  - Default Game Mode                                         │
│  - Items per page                                            │
├─────────────────────────────────────────────────────────────┤
│  Streamer Mode                                               │
│  - Enable/Disable                                            │
├─────────────────────────────────────────────────────────────┤
│  Share Links                                                 │
│  - Create/Edit/Delete share links                            │
├─────────────────────────────────────────────────────────────┤
│  Data Management                                             │
│  - Export Data                                               │
│  - Delete All Data                                           │
│  - Delete Account                                            │
└─────────────────────────────────────────────────────────────┘
```

### 配信者モード画面 (`streamer/index.blade.php`)

配信者向けの特別な機能を提供。

```
┌─────────────────────────────────────────────────────────────┐
│  Session Control                                             │
│  [セッション開始] or [セッション終了]                        │
│  Current Session: "配信 2024/01/15" (Started: 14:00)        │
├─────────────────────────────────────────────────────────────┤
│  Session Stats                                               │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐        │
│  │ Session W/L  │ │ Win Rate     │ │ Streak       │        │
│  │   10W - 5L   │ │   66.7%      │ │ 3連勝中      │        │
│  └──────────────┘ └──────────────┘ └──────────────┘        │
│  [連勝リセット]                                              │
├─────────────────────────────────────────────────────────────┤
│  Overlay Preview                                             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  [Preview of overlay with current settings]           │  │
│  └───────────────────────────────────────────────────────┘  │
│  [オーバーレイを開く] - Opens popup window                   │
├─────────────────────────────────────────────────────────────┤
│  Overlay Settings                                            │
│  - Background Color                                          │
│  - Text Color                                                │
│  - Accent Color                                              │
│  - Font Size                                                 │
│  - Opacity                                                   │
│  - Show/Hide: Streak, Win Rate, Record                       │
└─────────────────────────────────────────────────────────────┘
```

### オーバーレイ (`streamer/overlay.blade.php`)

OBSなどで使用するポップアップウィンドウ。

```
┌───────────────────────────────────┐
│  Shadova Log Overlay              │
│  ┌─────────────────────────────┐  │
│  │  10勝 - 5敗 (66.7%)        │  │
│  │  3連勝中                    │  │
│  └─────────────────────────────┘  │
└───────────────────────────────────┘
```

**特徴:**
- 5秒間隔で自動更新（JSON API呼び出し）
- カスタマイズ可能な外観
- 透過背景対応

---

## スタイリング

### Tailwind CSS 設定

`resources/css/app.css`:
```css
@import "tailwindcss";
```

### ダークモード対応

```html
<html class="dark">
    <!-- Dark mode is default -->
</html>
```

### カラーパレット

| 用途 | ダークモード | ライトモード |
|------|-------------|-------------|
| 背景 | `bg-gray-900` | `bg-white` |
| カード | `bg-gray-800` | `bg-gray-100` |
| テキスト | `text-white` | `text-gray-900` |
| アクセント | `bg-blue-600` | `bg-blue-500` |
| 勝利 | `text-green-500` | `text-green-600` |
| 敗北 | `text-red-500` | `text-red-600` |

---

## JavaScript エントリポイント

### `resources/js/app.js`

```javascript
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

### `resources/js/bootstrap.js`

```javascript
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

---

## ビルド設定

### Vite 設定 (`vite.config.js`)

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

### ビルドコマンド

```bash
# 開発（HMR）
npm run dev

# 本番ビルド
npm run build
```

---

## レスポンシブ対応

| ブレークポイント | 対応 |
|-----------------|------|
| `sm` (640px+) | モバイル対応 |
| `md` (768px+) | タブレット |
| `lg` (1024px+) | デスクトップ |

主にデスクトップでの使用を想定しているが、基本的なレスポンシブ対応は実装済み。

---

## 関連ドキュメント

- [システム概要](./system-overview.md)
- [API仕様](../06-interfaces/api-reference.md)
- [機能設計](../05-features/feature-design.md)
