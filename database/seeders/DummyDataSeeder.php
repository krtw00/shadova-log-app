<?php

namespace Database\Seeders;

use App\Models\Battle;
use App\Models\Deck;
use App\Models\ShareLink;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    /**
     * 開発用ダミーデータを投入
     */
    public function run(): void
    {
        $this->command->info('ダミーデータを作成中...');

        // マスタデータが存在することを確認
        $this->call([
            LeaderClassSeeder::class,
            GameModeSeeder::class,
            RankSeeder::class,
            GroupSeeder::class,
        ]);

        // テストユーザーを取得または作成
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'username' => 'testuser',
                'password' => bcrypt('password'),
            ]
        );

        // ユーザー設定を作成
        UserSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'theme' => 'dark',
                'default_game_mode_id' => 1,
                'per_page' => 20,
            ]
        );

        $this->command->info("テストユーザー: {$user->email} (パスワード: password)");

        // 各クラスのデッキを作成（2-3個ずつ）
        $decks = collect();
        for ($classId = 1; $classId <= 7; $classId++) {
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $deck = Deck::factory()
                    ->forUser($user)
                    ->forClass($classId)
                    ->create();
                $decks->push($deck);
            }
        }

        $this->command->info("デッキを {$decks->count()} 個作成しました");

        // 対戦記録を作成（過去30日分、各デッキに10-30戦）
        $battleCount = 0;

        foreach ($decks as $deck) {
            $count = rand(10, 30);

            // ランクマッチ（70%）
            $rankMatchCount = (int) ($count * 0.7);
            Battle::factory()
                ->count($rankMatchCount)
                ->forDeck($deck)
                ->rankMatch()
                ->create();
            $battleCount += $rankMatchCount;

            // フリーマッチ（30%）
            $freeMatchCount = $count - $rankMatchCount;
            Battle::factory()
                ->count($freeMatchCount)
                ->forDeck($deck)
                ->freeMatch()
                ->create();
            $battleCount += $freeMatchCount;
        }

        // 2Pick用の対戦記録も追加（20-50戦）
        $twoPickCount = rand(20, 50);
        Battle::factory()
            ->count($twoPickCount)
            ->forUser($user)
            ->twoPick()
            ->create();
        $battleCount += $twoPickCount;

        $this->command->info("対戦記録を {$battleCount} 件作成しました");

        // 今日の対戦を追加で作成（統計表示用）
        $todayDecks = $decks->random(min(3, $decks->count()));
        $todayBattleCount = 0;

        foreach ($todayDecks as $deck) {
            $count = rand(5, 15);
            Battle::factory()
                ->count($count)
                ->forDeck($deck)
                ->rankMatch()
                ->today()
                ->create();
            $todayBattleCount += $count;
        }

        $this->command->info("今日の対戦記録を {$todayBattleCount} 件追加しました");

        // 共有リンクを作成
        $shareLink = ShareLink::create([
            'user_id' => $user->id,
            'name' => 'グラマス到達記録',
            'slug' => 'grandmaster-' . Str::random(6),
            'start_date' => now()->subDays(30),
            'end_date' => now(),
            'is_active' => true,
        ]);

        $this->command->info("共有リンクを作成しました: /u/{$user->username}/{$shareLink->slug}");

        $this->command->newLine();
        $this->command->info('=== ダミーデータ作成完了 ===');
        $this->command->info("ログイン: {$user->email} / password");
    }
}
