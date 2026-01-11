<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            // 配信者モード有効/無効
            $table->boolean('streamer_mode_enabled')->default(false);

            // オーバーレイ設定
            $table->boolean('overlay_bg_transparent')->default(true);
            $table->string('overlay_font_size', 10)->default('medium'); // small, medium, large, xlarge
            $table->string('overlay_color_theme', 20)->default('dark'); // dark, light, custom
            $table->string('overlay_custom_bg_color', 20)->nullable();
            $table->string('overlay_custom_text_color', 20)->nullable();

            // 表示項目設定
            $table->boolean('overlay_show_winrate')->default(true);
            $table->boolean('overlay_show_record')->default(true);
            $table->boolean('overlay_show_streak')->default(true);
            $table->boolean('overlay_show_deck')->default(true);
            $table->boolean('overlay_show_log')->default(true);
            $table->integer('overlay_log_count')->default(5);
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn([
                'streamer_mode_enabled',
                'overlay_bg_transparent',
                'overlay_font_size',
                'overlay_color_theme',
                'overlay_custom_bg_color',
                'overlay_custom_text_color',
                'overlay_show_winrate',
                'overlay_show_record',
                'overlay_show_streak',
                'overlay_show_deck',
                'overlay_show_log',
                'overlay_log_count',
            ]);
        });
    }
};
