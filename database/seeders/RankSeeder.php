<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        $ranks = [
            // ビギナー
            ['name' => 'ビギナー0', 'tier' => 'Beginner', 'level' => 0, 'sort_order' => 1],
            ['name' => 'ビギナー1', 'tier' => 'Beginner', 'level' => 1, 'sort_order' => 2],
            ['name' => 'ビギナー2', 'tier' => 'Beginner', 'level' => 2, 'sort_order' => 3],
            ['name' => 'ビギナー3', 'tier' => 'Beginner', 'level' => 3, 'sort_order' => 4],

            // D ランク
            ['name' => 'D0', 'tier' => 'D', 'level' => 0, 'sort_order' => 10],
            ['name' => 'D1', 'tier' => 'D', 'level' => 1, 'sort_order' => 11],
            ['name' => 'D2', 'tier' => 'D', 'level' => 2, 'sort_order' => 12],
            ['name' => 'D3', 'tier' => 'D', 'level' => 3, 'sort_order' => 13],

            // C ランク
            ['name' => 'C0', 'tier' => 'C', 'level' => 0, 'sort_order' => 20],
            ['name' => 'C1', 'tier' => 'C', 'level' => 1, 'sort_order' => 21],
            ['name' => 'C2', 'tier' => 'C', 'level' => 2, 'sort_order' => 22],
            ['name' => 'C3', 'tier' => 'C', 'level' => 3, 'sort_order' => 23],

            // B ランク
            ['name' => 'B0', 'tier' => 'B', 'level' => 0, 'sort_order' => 30],
            ['name' => 'B1', 'tier' => 'B', 'level' => 1, 'sort_order' => 31],
            ['name' => 'B2', 'tier' => 'B', 'level' => 2, 'sort_order' => 32],
            ['name' => 'B3', 'tier' => 'B', 'level' => 3, 'sort_order' => 33],

            // A ランク
            ['name' => 'A0', 'tier' => 'A', 'level' => 0, 'sort_order' => 40],
            ['name' => 'A1', 'tier' => 'A', 'level' => 1, 'sort_order' => 41],
            ['name' => 'A2', 'tier' => 'A', 'level' => 2, 'sort_order' => 42],
            ['name' => 'A3', 'tier' => 'A', 'level' => 3, 'sort_order' => 43],

            // AA ランク
            ['name' => 'AA0', 'tier' => 'AA', 'level' => 0, 'sort_order' => 50],
            ['name' => 'AA1', 'tier' => 'AA', 'level' => 1, 'sort_order' => 51],
            ['name' => 'AA2', 'tier' => 'AA', 'level' => 2, 'sort_order' => 52],
            ['name' => 'AA3', 'tier' => 'AA', 'level' => 3, 'sort_order' => 53],

            // マスター（1つにまとめ）
            ['name' => 'マスター', 'tier' => 'Master', 'level' => 0, 'sort_order' => 100],

            // グランドマスター（1つにまとめ）
            ['name' => 'グランドマスター', 'tier' => 'GrandMaster', 'level' => 0, 'sort_order' => 200],
        ];

        foreach ($ranks as $rank) {
            Rank::updateOrCreate(
                ['tier' => $rank['tier'], 'level' => $rank['level']],
                $rank
            );
        }
    }
}
