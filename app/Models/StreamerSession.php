<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamerSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'started_at',
        'ended_at',
        'is_active',
        'streak_start',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * セッション中の対戦を取得
     */
    public function battles()
    {
        return Battle::where('user_id', $this->user_id)
            ->where('played_at', '>=', $this->started_at)
            ->when($this->ended_at, function ($query) {
                $query->where('played_at', '<=', $this->ended_at);
            });
    }

    /**
     * セッション中の統計を取得
     */
    public function getStats(): array
    {
        $battles = $this->battles();
        $total = $battles->count();
        $wins = (clone $battles)->where('result', true)->count();
        $losses = $total - $wins;
        $winRate = $total > 0 ? round(($wins / $total) * 100, 1) : 0;

        // 連勝計算
        $recentBattles = (clone $battles)->orderBy('played_at', 'desc')->get();
        $streak = 0;
        foreach ($recentBattles as $battle) {
            if ($battle->result) {
                $streak++;
            } else {
                break;
            }
        }

        return [
            'total' => $total,
            'wins' => $wins,
            'losses' => $losses,
            'win_rate' => $winRate,
            'streak' => $streak + $this->streak_start,
        ];
    }

    /**
     * アクティブなセッションを取得
     */
    public static function getActiveSession($userId): ?self
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }
}
