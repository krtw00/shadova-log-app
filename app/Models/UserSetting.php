<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'default_game_mode_id',
        'theme',
        'per_page',
        // 配信者モード設定
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
    ];

    protected $casts = [
        'streamer_mode_enabled' => 'boolean',
        'overlay_bg_transparent' => 'boolean',
        'overlay_show_winrate' => 'boolean',
        'overlay_show_record' => 'boolean',
        'overlay_show_streak' => 'boolean',
        'overlay_show_deck' => 'boolean',
        'overlay_show_log' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function defaultGameMode(): BelongsTo
    {
        return $this->belongsTo(GameMode::class, 'default_game_mode_id');
    }
}
