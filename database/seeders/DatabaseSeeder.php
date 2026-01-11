<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // マスタデータを投入
        $this->call([
            LeaderClassSeeder::class,
            GameModeSeeder::class,
            RankSeeder::class,
            GroupSeeder::class,
        ]);

        // 開発環境の場合はダミーデータも投入
        if (app()->environment('local', 'development')) {
            $this->call(DummyDataSeeder::class);
        } else {
            // 本番環境ではテストユーザーのみ作成
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
