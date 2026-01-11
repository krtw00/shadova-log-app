<?php

namespace App\Policies;

use App\Models\ShareLink;
use App\Models\User;

class ShareLinkPolicy
{
    public function update(User $user, ShareLink $shareLink): bool
    {
        return $user->id === $shareLink->user_id;
    }

    public function delete(User $user, ShareLink $shareLink): bool
    {
        return $user->id === $shareLink->user_id;
    }
}
