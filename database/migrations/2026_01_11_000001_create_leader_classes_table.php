<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leader_classes', function (Blueprint $table) {
            $table->smallInteger('id')->primary();
            $table->string('name', 20);
            $table->string('name_en', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leader_classes');
    }
};
