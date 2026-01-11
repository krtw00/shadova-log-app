<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameMode extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'name',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function battles(): HasMany
    {
        return $this->hasMany(Battle::class, 'game_mode_id');
    }
}
