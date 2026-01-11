<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('deck_id')->constrained()->onDelete('cascade');
            $table->smallInteger('opponent_class_id');
            $table->smallInteger('game_mode_id');
            $table->boolean('result');
            $table->boolean('is_first');
            $table->timestamp('played_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('opponent_class_id')->references('id')->on('leader_classes');
            $table->foreign('game_mode_id')->references('id')->on('game_modes');
            $table->index('user_id');
            $table->index('deck_id');
            $table->index('played_at');
            $table->index('game_mode_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battles');
    }
};
