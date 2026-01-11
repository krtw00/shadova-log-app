<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);           // 表示名（例: "D3", "マスター", "グランドマスター0"）
            $table->string('tier', 20);           // 階層（Beginner, D, C, B, A, AA, Master, GrandMaster）
            $table->unsignedTinyInteger('level'); // サブレベル（0-3 or マスターレベル1-50）
            $table->unsignedSmallInteger('sort_order'); // ソート順
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
