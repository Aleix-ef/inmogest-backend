<?php

namespace App\Policies;

use App\Models\Owner;
use App\Models\User;

class OwnerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function view(User $user, Owner $owner): bool
    {
        return $user->role === 'manager';
    }

    public function create(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function update(User $user, Owner $owner): bool
    {
        return $user->role === 'manager';
    }

    public function delete(User $user, Owner $owner): bool
    {
        return $user->role === 'manager';
    }
}
