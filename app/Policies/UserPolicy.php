<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $currentUser, User $user): bool
    {
        return $currentUser->id === $user->id;
    }
}
