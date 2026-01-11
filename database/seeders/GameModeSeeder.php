<?php

namespace Database\Seeders;

use App\Models\GameMode;
use Illuminate\Database\Seeder;

class GameModeSeeder extends Seeder
{
    public function run(): void
    {
        $modes = [
            ['id' => 1, 'code' => 'RANK', 'name' => 'ランクマッチ'],
            ['id' => 2, 'code' => 'FREE', 'name' => 'フリーマッチ'],
            ['id' => 3, 'code' => 'ROOM', 'name' => 'ルームマッチ'],
            ['id' => 4, 'code' => 'GP', 'name' => 'グランプリ'],
            ['id' => 5, 'code' => '2PICK', 'name' => '2Pick'],
        ];

        foreach ($modes as $mode) {
            GameMode::updateOrCreate(['id' => $mode['id']], $mode);
        }
    }
}
