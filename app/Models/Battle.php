<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Battle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deck_id',
        'my_class_id',
        'opponent_class_id',
        'game_mode_id',
        'rank_id',
        'group_id',
        'result',
        'is_first',
        'played_at',
        'notes',
    ];

    protected $casts = [
        'result' => 'boolean',
        'is_first' => 'boolean',
        'played_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function myClass(): BelongsTo
    {
        return $this->belongsTo(LeaderClass::class, 'my_class_id');
    }

    public function opponentClass(): BelongsTo
    {
        return $this->belongsTo(LeaderClass::class, 'opponent_class_id');
    }

    public function gameMode(): BelongsTo
    {
        return $this->belongsTo(GameMode::class, 'game_mode_id');
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function scopeWins($query)
    {
        return $query->whereRaw('result is true');
    }

    public function scopeLosses($query)
    {
        return $query->whereRaw('result is false');
    }

    public function scopeFirst($query)
    {
        return $query->whereRaw('is_first is true');
    }

    public function scopeSecond($query)
    {
        return $query->whereRaw('is_first is false');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('played_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('played_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('played_at', now()->month)->whereYear('played_at', now()->year);
    }
}
