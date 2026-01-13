# テスト規約

## テスト実行

```bash
# 全テスト実行
composer test
# または
php artisan test

# 特定のテストファイルを実行
php artisan test tests/Feature/BattleTest.php

# 特定のメソッドを実行
php artisan test --filter=test_user_can_create_battle
```

## ディレクトリ構造

```
tests/
├── Feature/           # 機能テスト（HTTP、DB操作含む）
│   ├── BattleTest.php
│   ├── DeckTest.php
│   └── AuthTest.php
├── Unit/              # 単体テスト（ロジックのみ）
│   └── ExampleTest.php
└── TestCase.php       # 基底クラス
```

## テストの書き方

### Feature Test（機能テスト）
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Battle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BattleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_battles(): void
    {
        $user = User::factory()->create();
        Battle::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get('/battles');

        $response->assertStatus(200);
        $response->assertViewHas('battles');
    }

    public function test_user_can_create_battle(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/battles', [
                'opponent_class_id' => 1,
                'result' => 'win',
                'is_first' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('battles', [
            'user_id' => $user->id,
            'result' => 'win',
        ]);
    }

    public function test_guest_cannot_access_battles(): void
    {
        $response = $this->get('/battles');

        $response->assertRedirect('/login');
    }
}
```

### Unit Test（単体テスト）
```php
<?php

namespace Tests\Unit;

use App\Models\Battle;
use PHPUnit\Framework\TestCase;

class BattleTest extends TestCase
{
    public function test_win_rate_calculation(): void
    {
        // 純粋なロジックのテスト
        $wins = 7;
        $total = 10;
        $winRate = ($total > 0) ? round(($wins / $total) * 100, 1) : 0;

        $this->assertEquals(70.0, $winRate);
    }
}
```

## テストのベストプラクティス

### 命名規則
- `test_`プレフィックス + 説明的な名前
- 日本語コメントで補足可

```php
public function test_user_can_delete_own_battle(): void
{
    // ユーザーは自分の対戦記録を削除できる
}

public function test_user_cannot_delete_others_battle(): void
{
    // 他人の対戦記録は削除できない
}
```

### アサーション
- 1テスト1目的
- 必要なアサーションのみ

### データベース
- `RefreshDatabase`トレイトで各テスト後にリセット
- Factoryでテストデータ生成

### 認証テスト
- `actingAs($user)` で認証済みユーザーとしてリクエスト
- 未認証時のリダイレクト確認を含める
