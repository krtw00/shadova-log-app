<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deck extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'leader_class_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaderClass(): BelongsTo
    {
        return $this->belongsTo(LeaderClass::class, 'leader_class_id');
    }

    public function battles(): HasMany
    {
        return $this->hasMany(Battle::class);
    }

    public function winRate(): float
    {
        $total = $this->battles()->count();
        if ($total === 0) {
            return 0;
        }
        $wins = $this->battles()->whereRaw('result is true')->count();
        return round(($wins / $total) * 100, 1);
    }

    public function record(): array
    {
        $wins = $this->battles()->whereRaw('result is true')->count();
        $losses = $this->battles()->whereRaw('result is false')->count();
        return ['wins' => $wins, 'losses' => $losses];
    }
}
