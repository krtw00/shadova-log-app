<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['name' => 'エメラルド', 'code' => 'EMERALD', 'sort_order' => 1],
            ['name' => 'トパーズ', 'code' => 'TOPAZ', 'sort_order' => 2],
            ['name' => 'ルビー', 'code' => 'RUBY', 'sort_order' => 3],
            ['name' => 'サファイア', 'code' => 'SAPPHIRE', 'sort_order' => 4],
            ['name' => 'ダイヤモンド', 'code' => 'DIAMOND', 'sort_order' => 5],
        ];

        foreach ($groups as $group) {
            Group::updateOrCreate(
                ['code' => $group['code']],
                $group
            );
        }
    }
}
