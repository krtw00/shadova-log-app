# コードスタイルガイドライン

## PHP/Laravel

### フォーマット
- PSR-12準拠
- `php artisan pint` で自動フォーマット
- 最大行長: 120文字

### 型宣言
```php
// 良い例: 型宣言を明示
public function store(Request $request): JsonResponse
{
    // ...
}

// 悪い例: 型宣言なし
public function store($request)
{
    // ...
}
```

### Eloquent
- クエリビルダーよりEloquentを優先
- N+1問題を避けるため`with()`で事前読み込み
- スコープを活用して再利用可能なクエリを定義

```php
// 良い例
$battles = Battle::with(['deck', 'rank', 'group'])
    ->where('user_id', $userId)
    ->latest()
    ->get();

// 悪い例: N+1問題
$battles = Battle::where('user_id', $userId)->get();
foreach ($battles as $battle) {
    echo $battle->deck->name; // 毎回クエリ発行
}
```

### バリデーション
- FormRequestクラスを使用（複雑な場合）
- シンプルな場合はコントローラー内で`$request->validate()`

## JavaScript/Alpine.js

### Alpine.jsコンポーネント
```html
<!-- 良い例: 明確なデータ構造 -->
<div x-data="{
    isOpen: false,
    items: [],
    toggle() { this.isOpen = !this.isOpen }
}">
    <button @click="toggle">Toggle</button>
</div>

<!-- 複雑なロジックは別ファイルに -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('battleForm', () => ({
        // コンポーネントロジック
    }))
})
</script>
```

### イベントハンドリング
- `@click`, `@submit.prevent` などのディレクティブを使用
- フォーム送信時は`@submit.prevent`で標準動作を抑制

## Bladeテンプレート

### コンポーネント
- 再利用可能なUIは`resources/views/components/`に配置
- `<x-component-name>`で使用

### 条件分岐
```blade
{{-- 良い例: 読みやすい --}}
@if ($battles->isEmpty())
    <p>対戦記録がありません</p>
@else
    @foreach ($battles as $battle)
        ...
    @endforeach
@endif

{{-- 避けるべき: 複雑なネスト --}}
```

## CSS/Tailwind

### クラス順序（推奨）
1. レイアウト (flex, grid, position)
2. サイズ (w, h, p, m)
3. 外観 (bg, border, shadow)
4. タイポグラフィ (text, font)
5. 状態 (hover, focus, dark)

```html
<button class="flex items-center px-4 py-2 bg-blue-500 text-white hover:bg-blue-600">
    保存
</button>
```
