<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Passkey;
use App\Models\User;

class PasskeyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Passkey $passkey): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Passkey $passkey): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Passkey $passkey): bool
    {
        return $user->id === $passkey->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Passkey $passkey): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Passkey $passkey): bool
    {
        return false;
    }
}
