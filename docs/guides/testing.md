# テストガイド

このドキュメントは、Shadova Log App のテスト戦略、実行方法、ベストプラクティスについて記述します。

---

## 目次

1. [テスト戦略](#テスト戦略)
2. [テスト実行方法](#テスト実行方法)
3. [テストの書き方](#テストの書き方)
4. [カバレッジ目標](#カバレッジ目標)
5. [CI/CD 連携](#cicd-連携)
6. [ベストプラクティス](#ベストプラクティス)

---

## テスト戦略

### テストピラミッド

```
        /\
       /  \
      / E2E \        <- 少数（手動 or 将来的に自動化）
     /------\
    / Feature \      <- 主要機能をカバー（HTTP、DB操作）
   /------------\
  /    Unit      \   <- ビジネスロジックをカバー
 /----------------\
```

### テスト種別

| 種別 | 目的 | ツール | 実行頻度 |
|-----|------|-------|---------|
| Unit Test | ビジネスロジックの検証 | PHPUnit | 毎コミット |
| Feature Test | 機能全体の検証（HTTP、DB） | PHPUnit + Laravel | 毎コミット |
| E2E Test | ユーザー視点での検証 | （将来的に導入） | リリース前 |

### テスト対象

| レイヤー | テスト対象 | 優先度 |
|---------|-----------|--------|
| Controller | HTTPリクエスト/レスポンス、認可 | 高 |
| Service | ビジネスロジック | 高 |
| Model | リレーション、スコープ、アクセサ | 中 |
| Policy | 認可ルール | 高 |
| Middleware | 認証、CSRF | 中 |

---

## テスト実行方法

### 基本コマンド

```bash
# 全テスト実行
composer test
# または
php artisan test

# 詳細出力
php artisan test --verbose
```

### 特定のテスト実行

```bash
# 特定のテストファイル
php artisan test tests/Feature/BattleTest.php

# 特定のテストメソッド
php artisan test --filter=test_user_can_create_battle

# 特定のクラス
php artisan test --filter=BattleTest

# 複数フィルタ
php artisan test --filter="test_user_can"
```

### テストカバレッジ

```bash
# カバレッジレポート生成（HTML）
XDEBUG_MODE=coverage php artisan test --coverage-html=coverage

# カバレッジ閾値チェック
php artisan test --coverage --min=60
```

### 並列実行

```bash
# 並列実行（高速化）
php artisan test --parallel

# プロセス数指定
php artisan test --parallel --processes=4
```

---

## テストの書き方

### ディレクトリ構造

```
tests/
├── Feature/              # 機能テスト（HTTP、DB操作含む）
│   ├── Auth/
│   │   └── LoginTest.php
│   ├── Battle/
│   │   ├── CreateBattleTest.php
│   │   ├── UpdateBattleTest.php
│   │   └── DeleteBattleTest.php
│   ├── Deck/
│   │   └── DeckTest.php
│   └── Statistics/
│       └── StatisticsTest.php
├── Unit/                 # 単体テスト（ロジックのみ）
│   ├── Models/
│   │   └── BattleTest.php
│   └── Services/
│       └── StatisticsServiceTest.php
├── TestCase.php          # 基底クラス
└── CreatesApplication.php
```

### Feature Test（機能テスト）

```php
<?php

namespace Tests\Feature\Battle;

use App\Models\User;
use App\Models\Battle;
use App\Models\Deck;
use App\Models\LeaderClass;
use App\Models\GameMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateBattleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーでマスタデータを投入
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
    }

    public function test_authenticated_user_can_create_battle(): void
    {
        // Arrange
        $user = User::factory()->create();
        $deck = Deck::factory()->create(['user_id' => $user->id]);
        $gameMode = GameMode::first();
        $opponentClass = LeaderClass::first();

        // Act
        $response = $this->actingAs($user)->post('/battles', [
            'deck_id' => $deck->id,
            'opponent_class_id' => $opponentClass->id,
            'game_mode_id' => $gameMode->id,
            'result' => true,
            'is_first' => true,
        ]);

        // Assert
        $response->assertRedirect('/battles');
        $this->assertDatabaseHas('battles', [
            'user_id' => $user->id,
            'deck_id' => $deck->id,
            'result' => true,
        ]);
    }

    public function test_guest_cannot_create_battle(): void
    {
        $response = $this->post('/battles', [
            'opponent_class_id' => 1,
            'result' => true,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_validation_error_when_required_fields_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/battles', []);

        $response->assertSessionHasErrors([
            'opponent_class_id',
            'game_mode_id',
            'result',
        ]);
    }
}
```

### Unit Test（単体テスト）

```php
<?php

namespace Tests\Unit\Services;

use App\Services\StatisticsService;
use PHPUnit\Framework\TestCase;

class StatisticsServiceTest extends TestCase
{
    public function test_calculate_win_rate_with_valid_data(): void
    {
        $wins = 7;
        $total = 10;

        $winRate = StatisticsService::calculateWinRate($wins, $total);

        $this->assertEquals(70.0, $winRate);
    }

    public function test_calculate_win_rate_with_zero_total(): void
    {
        $wins = 0;
        $total = 0;

        $winRate = StatisticsService::calculateWinRate($wins, $total);

        $this->assertEquals(0.0, $winRate);
    }

    public function test_calculate_streak(): void
    {
        $results = [true, true, true, false, true, true];

        $streak = StatisticsService::calculateStreak($results);

        $this->assertEquals(2, $streak); // 最新から2連勝
    }
}
```

### Policy テスト

```php
<?php

namespace Tests\Feature\Policy;

use App\Models\User;
use App\Models\Battle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BattlePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_own_battle(): void
    {
        $user = User::factory()->create();
        $battle = Battle::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/battles/{$battle->id}", [
            'result' => false,
        ]);

        $response->assertRedirect();
    }

    public function test_user_cannot_update_others_battle(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $battle = Battle::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->put("/battles/{$battle->id}", [
            'result' => false,
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_battle(): void
    {
        $user = User::factory()->create();
        $battle = Battle::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/battles/{$battle->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('battles', ['id' => $battle->id]);
    }
}
```

---

## カバレッジ目標

### 全体目標

| 対象 | 目標カバレッジ | 優先度 |
|-----|--------------|--------|
| 全体 | 60% 以上 | - |
| Controllers | 80% 以上 | 高 |
| Services | 90% 以上 | 高 |
| Models | 70% 以上 | 中 |
| Policies | 100% | 高 |

### 重点テスト対象

必ずテストすべき機能:

- [ ] ユーザー認証（ログイン/ログアウト/登録）
- [ ] 対戦記録 CRUD
- [ ] デッキ CRUD
- [ ] 認可（自分のデータのみ操作可能）
- [ ] 統計計算
- [ ] 共有リンク機能

---

## CI/CD 連携

### GitHub Actions 設定

`.github/workflows/test.yml`:

```yaml
name: Tests

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_USER: postgres
          POSTGRES_PASSWORD: password
          POSTGRES_DB: testing
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: pgsql, pdo_pgsql, mbstring, xml
          coverage: xdebug

      - name: Install Composer dependencies
        run: composer install --prefer-dist --no-progress

      - name: Copy .env
        run: cp .env.example .env

      - name: Generate key
        run: php artisan key:generate

      - name: Run tests
        env:
          DB_CONNECTION: pgsql
          DB_HOST: 127.0.0.1
          DB_PORT: 5432
          DB_DATABASE: testing
          DB_USERNAME: postgres
          DB_PASSWORD: password
        run: php artisan test --coverage --min=60

      - name: Run Pint
        run: ./vendor/bin/pint --test
```

### ローカル開発での実行

```bash
# テスト前にコードスタイルチェック
./vendor/bin/pint --test

# テスト実行
composer test

# 全チェック
composer test && ./vendor/bin/pint --test
```

---

## ベストプラクティス

### 命名規則

```php
// 良い例: 説明的な名前
public function test_user_can_create_battle_with_valid_data(): void
public function test_guest_is_redirected_to_login(): void
public function test_validation_fails_when_opponent_class_is_missing(): void

// 悪い例: 曖昧な名前
public function test_battle(): void
public function test_create(): void
```

### AAA パターン

```php
public function test_example(): void
{
    // Arrange（準備）
    $user = User::factory()->create();
    $deck = Deck::factory()->create(['user_id' => $user->id]);

    // Act（実行）
    $response = $this->actingAs($user)->post('/battles', [
        'deck_id' => $deck->id,
        // ...
    ]);

    // Assert（検証）
    $response->assertRedirect();
    $this->assertDatabaseHas('battles', ['deck_id' => $deck->id]);
}
```

### Factory の活用

```php
// database/factories/BattleFactory.php
public function definition(): array
{
    return [
        'user_id' => User::factory(),
        'deck_id' => Deck::factory(),
        'opponent_class_id' => LeaderClass::factory(),
        'game_mode_id' => GameMode::factory(),
        'result' => $this->faker->boolean(),
        'is_first' => $this->faker->boolean(),
        'played_at' => $this->faker->dateTimeThisMonth(),
    ];
}

// 状態メソッド
public function win(): static
{
    return $this->state(fn (array $attributes) => [
        'result' => true,
    ]);
}

public function loss(): static
{
    return $this->state(fn (array $attributes) => [
        'result' => false,
    ]);
}
```

### テストの独立性

```php
// 良い例: 各テストが独立
public function test_create_battle(): void
{
    $user = User::factory()->create();
    // このテスト専用のデータを作成
}

public function test_delete_battle(): void
{
    $user = User::factory()->create();
    $battle = Battle::factory()->create(['user_id' => $user->id]);
    // このテスト専用のデータを作成
}

// 悪い例: テスト間で依存
private static $user;

public function test_create_then_delete(): void
{
    // 前のテストに依存
}
```

### アサーションの使い分け

```php
// HTTP レスポンス
$response->assertStatus(200);
$response->assertRedirect('/battles');
$response->assertForbidden();
$response->assertNotFound();

// ビュー
$response->assertViewIs('battles.index');
$response->assertViewHas('battles');

// セッション
$response->assertSessionHas('success');
$response->assertSessionHasErrors(['email']);

// データベース
$this->assertDatabaseHas('battles', ['id' => 1]);
$this->assertDatabaseMissing('battles', ['id' => 1]);
$this->assertDatabaseCount('battles', 5);

// モデル
$this->assertModelExists($battle);
$this->assertSoftDeleted($battle);
```

---

## 関連ドキュメント

- [環境構築ガイド](./environment-setup.md)
- [セキュリティガイドライン](../../.claude/rules/security.md)
- [コードスタイルガイドライン](../../.claude/rules/code-style.md)
