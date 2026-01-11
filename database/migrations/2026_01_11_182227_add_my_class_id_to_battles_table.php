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
        Schema::table('battles', function (Blueprint $table) {
            // 2pick用：自分のクラス（デッキなしの場合に使用）
            $table->foreignId('my_class_id')->nullable()->after('deck_id')->constrained('leader_classes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battles', function (Blueprint $table) {
            $table->dropForeign(['my_class_id']);
            $table->dropColumn('my_class_id');
        });
    }
};
