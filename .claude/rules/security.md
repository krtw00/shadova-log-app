# セキュリティガイドライン

## 認可（Authorization）

### Policy使用
全てのリソースアクセスはPolicyで制御する。

```php
// app/Policies/BattlePolicy.php
public function update(User $user, Battle $battle): bool
{
    return $user->id === $battle->user_id;
}

public function delete(User $user, Battle $battle): bool
{
    return $user->id === $battle->user_id;
}
```

### コントローラーでの使用
```php
public function update(Request $request, Battle $battle)
{
    $this->authorize('update', $battle);
    // ...
}

// または
public function destroy(Battle $battle)
{
    Gate::authorize('delete', $battle);
    // ...
}
```

## バリデーション

### 入力値の検証
```php
$validated = $request->validate([
    'opponent_class_id' => 'required|exists:leader_classes,id',
    'result' => 'required|in:win,lose,draw',
    'is_first' => 'required|boolean',
    'deck_id' => 'nullable|exists:decks,id',
    'memo' => 'nullable|string|max:500',
]);
```

### XSS対策
- Bladeテンプレートでは`{{ }}`でエスケープ（デフォルト）
- 生HTMLが必要な場合のみ`{!! !!}`を使用（要注意）

```blade
{{-- 安全: エスケープされる --}}
{{ $battle->memo }}

{{-- 危険: 信頼できるデータのみ使用 --}}
{!! $trustedHtml !!}
```

## データベースセキュリティ

### SQLインジェクション対策
- Eloquentまたはクエリビルダーを使用
- 生SQLは避ける（必要な場合はプレースホルダー使用）

```php
// 良い例: Eloquent
Battle::where('user_id', $userId)->get();

// 良い例: プレースホルダー
DB::select('SELECT * FROM battles WHERE user_id = ?', [$userId]);

// 悪い例: 危険！
DB::select("SELECT * FROM battles WHERE user_id = $userId");
```

### マスアサインメント対策
モデルの`$fillable`で許可するフィールドを明示。

```php
class Battle extends Model
{
    protected $fillable = [
        'user_id',
        'deck_id',
        'opponent_class_id',
        'result',
        'is_first',
        'memo',
    ];
}
```

## 認証セキュリティ

### パスワード
- `Hash::make()`でハッシュ化
- 平文パスワードは絶対に保存しない

### セッション
- HTTPSを使用（本番環境）
- セッションの適切な有効期限設定

## 環境変数

### 機密情報の管理
```bash
# 絶対にコミットしてはいけないもの
DB_PASSWORD=xxxxx
SUPABASE_SERVICE_KEY=xxxxx
APP_KEY=xxxxx

# .gitignoreに必ず含める
.env
.env.local
.env.*.local
```

### 本番/開発の分離
- 開発用と本番用で異なるSupabaseプロジェクトを使用
- 本番のService Keyは厳重に管理

## CSRF対策

Bladeフォームには必ず`@csrf`を含める。

```blade
<form method="POST" action="{{ route('battles.store') }}">
    @csrf
    <!-- フォームフィールド -->
</form>
```

## レート制限

必要に応じてルートにレート制限を設定。

```php
Route::middleware(['throttle:60,1'])->group(function () {
    // 1分間に60リクエストまで
});
```
