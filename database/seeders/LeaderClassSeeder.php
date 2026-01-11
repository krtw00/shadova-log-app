<?php

namespace Database\Seeders;

use App\Models\LeaderClass;
use Illuminate\Database\Seeder;

class LeaderClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            ['id' => 1, 'name' => 'エルフ', 'name_en' => 'Elf'],
            ['id' => 2, 'name' => 'ロイヤル', 'name_en' => 'Royal'],
            ['id' => 3, 'name' => 'ウィッチ', 'name_en' => 'Witch'],
            ['id' => 4, 'name' => 'ドラゴン', 'name_en' => 'Dragon'],
            ['id' => 5, 'name' => 'ナイトメア', 'name_en' => 'Nightmare'],
            ['id' => 6, 'name' => 'ビショップ', 'name_en' => 'Bishop'],
            ['id' => 7, 'name' => 'ネメシス', 'name_en' => 'Nemesis'],
        ];

        foreach ($classes as $class) {
            LeaderClass::updateOrCreate(['id' => $class['id']], $class);
        }
    }
}
