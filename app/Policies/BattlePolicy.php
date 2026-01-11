<?php

namespace App\Policies;

use App\Models\Battle;
use App\Models\User;

class BattlePolicy
{
    public function update(User $user, Battle $battle): bool
    {
        return $user->id === $battle->user_id;
    }

    public function delete(User $user, Battle $battle): bool
    {
        return $user->id === $battle->user_id;
    }
}
