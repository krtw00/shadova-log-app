---
paths:
  - "database/**/*.php"
  - "app/Models/**/*.php"
---

# データベース設計ガイド

## マイグレーション

### 命名規則
- ファイル名: `YYYY_MM_DD_HHMMSS_動詞_テーブル名.php`
- テーブル名: スネークケース複数形

```php
// 良い例
2026_01_11_000001_create_battles_table.php
2026_01_11_000002_add_rank_id_to_battles_table.php

// 悪い例
2026_01_11_000001_Battle.php
```

### マイグレーション作成
```bash
# テーブル作成
php artisan make:migration create_battles_table

# カラム追加
php artisan make:migration add_rank_id_to_battles_table
```

### マイグレーション構造
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deck_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('opponent_class_id')->constrained('leader_classes');
            $table->enum('result', ['win', 'lose', 'draw']);
            $table->boolean('is_first');
            $table->text('memo')->nullable();
            $table->timestamps();

            // インデックス
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battles');
    }
};
```

### 外部キー制約
- `cascadeOnDelete()`: 親削除時に子も削除
- `nullOnDelete()`: 親削除時にNULL設定
- `restrictOnDelete()`: 親削除を禁止

## モデル設計

### 基本構造
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Battle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deck_id',
        'opponent_class_id',
        'my_class_id',
        'rank_id',
        'group_id',
        'game_mode_id',
        'result',
        'is_first',
        'memo',
    ];

    protected $casts = [
        'is_first' => 'boolean',
    ];

    // リレーション
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function opponentClass(): BelongsTo
    {
        return $this->belongsTo(LeaderClass::class, 'opponent_class_id');
    }

    public function myClass(): BelongsTo
    {
        return $this->belongsTo(LeaderClass::class, 'my_class_id');
    }

    // スコープ
    public function scopeWins($query)
    {
        return $query->where('result', 'win');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
```

### キャスト
```php
protected $casts = [
    'is_first' => 'boolean',
    'is_active' => 'boolean',
    'settings' => 'array',
    'created_at' => 'datetime',
];
```

## クエリパフォーマンス

### N+1問題の回避
```php
// 良い例: Eager Loading
$battles = Battle::with(['deck', 'rank', 'myClass', 'opponentClass'])
    ->where('user_id', $userId)
    ->get();

// 悪い例: N+1問題
$battles = Battle::where('user_id', $userId)->get();
foreach ($battles as $battle) {
    echo $battle->deck->name; // 毎回クエリ発行
}
```

### インデックス設計
- 頻繁に検索するカラムにインデックス
- 複合インデックスは検索順序を考慮

```php
$table->index(['user_id', 'created_at']);
$table->index('opponent_class_id');
```

## Supabase固有の注意点

### PostgreSQL互換
- `enum`型はPostgreSQLでも使用可能
- `boolean`は`true`/`false`（文字列ではない）

### 接続設定
```php
// config/database.php
'pgsql' => [
    'driver' => 'pgsql',
    'host' => env('DB_HOST'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'postgres'),
    'username' => env('DB_USERNAME', 'postgres'),
    'password' => env('DB_PASSWORD'),
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
    'sslmode' => 'require',
],
```
