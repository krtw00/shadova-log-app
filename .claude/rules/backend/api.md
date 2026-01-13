---
paths:
  - "app/Http/Controllers/**/*.php"
  - "routes/*.php"
---

# API/コントローラー設計ガイド

## ルート設計

### RESTfulなルート
```php
// リソースルート（CRUD）
Route::resource('battles', BattleController::class);

// 個別定義の場合
Route::get('/battles', [BattleController::class, 'index']);
Route::post('/battles', [BattleController::class, 'store']);
Route::put('/battles/{battle}', [BattleController::class, 'update']);
Route::delete('/battles/{battle}', [BattleController::class, 'destroy']);
```

### ルートグループ
```php
Route::middleware(['auth'])->group(function () {
    // 認証が必要なルート
});

Route::prefix('api/v1')->group(function () {
    // APIルート
});
```

## コントローラー設計

### 基本構造
```php
<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BattleController extends Controller
{
    /**
     * 対戦記録一覧を表示
     */
    public function index(Request $request): View
    {
        $battles = Battle::query()
            ->where('user_id', $request->user()->id)
            ->with(['deck', 'rank', 'myClass', 'opponentClass'])
            ->latest()
            ->paginate(20);

        return view('battles.index', compact('battles'));
    }

    /**
     * 対戦記録を保存
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opponent_class_id' => 'required|exists:leader_classes,id',
            'result' => 'required|in:win,lose,draw',
            'is_first' => 'required|boolean',
        ]);

        $request->user()->battles()->create($validated);

        return redirect()
            ->route('battles.index')
            ->with('success', '対戦記録を追加しました');
    }

    /**
     * 対戦記録を更新
     */
    public function update(Request $request, Battle $battle): RedirectResponse
    {
        $this->authorize('update', $battle);

        $validated = $request->validate([
            'result' => 'required|in:win,lose,draw',
        ]);

        $battle->update($validated);

        return redirect()
            ->route('battles.index')
            ->with('success', '対戦記録を更新しました');
    }

    /**
     * 対戦記録を削除
     */
    public function destroy(Battle $battle): RedirectResponse
    {
        $this->authorize('delete', $battle);

        $battle->delete();

        return redirect()
            ->route('battles.index')
            ->with('success', '対戦記録を削除しました');
    }
}
```

## レスポンス形式

### Webレスポンス
- 成功時: リダイレクト + フラッシュメッセージ
- エラー時: バリデーションエラー自動表示

### JSONレスポンス（Ajax用）
```php
public function store(Request $request): JsonResponse
{
    $battle = $request->user()->battles()->create($validated);

    return response()->json([
        'success' => true,
        'message' => '対戦記録を追加しました',
        'data' => $battle,
    ], 201);
}
```

## エラーハンドリング

### バリデーションエラー
- Laravelが自動的に422レスポンスを返す
- Bladeでは`@error`ディレクティブで表示

### 認可エラー
- `$this->authorize()`が自動的に403を返す

### カスタムエラー
```php
if ($condition) {
    abort(404, 'リソースが見つかりません');
}
```
