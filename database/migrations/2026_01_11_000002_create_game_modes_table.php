<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_modes', function (Blueprint $table) {
            $table->smallInteger('id')->primary();
            $table->string('code', 10)->unique();
            $table->string('name', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_modes');
    }
};
