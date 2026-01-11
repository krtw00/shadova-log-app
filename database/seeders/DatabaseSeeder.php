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
        ]);

        // テストユーザーを作成
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
