<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaderClass extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'name_en',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function decks(): HasMany
    {
        return $this->hasMany(Deck::class, 'leader_class_id');
    }

    public function battlesAsOpponent(): HasMany
    {
        return $this->hasMany(Battle::class, 'opponent_class_id');
    }
}
