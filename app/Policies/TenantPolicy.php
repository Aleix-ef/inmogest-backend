<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['manager', 'owner'], true);
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $this->canAccessTenant($user, $tenant);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['manager', 'owner'], true);
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $this->canAccessTenant($user, $tenant);
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $this->canAccessTenant($user, $tenant);
    }

    private function canAccessTenant(User $user, Tenant $tenant): bool
    {
        if ($user->role === 'manager') {
            return true;
        }

        $owner = $user->owner;

        if ($user->role !== 'owner' || ! $owner) {
            return false;
        }

        return (int) $tenant->owner_id === (int) $owner->id
            || $tenant->properties()
                ->whereHas('owners', fn ($query) => $query->where('owners.id', $owner->id))
                ->exists();
    }
}
