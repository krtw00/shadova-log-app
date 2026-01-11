<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->smallInteger('leader_class_id');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('leader_class_id')->references('id')->on('leader_classes');
            $table->index('user_id');
            $table->index('leader_class_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decks');
    }
};
