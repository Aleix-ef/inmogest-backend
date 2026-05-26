<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['manager', 'owner'], true);
    }

    public function view(User $user, Contract $contract): bool
    {
        return $this->canAccessContract($user, $contract);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['manager', 'owner'], true);
    }

    public function update(User $user, Contract $contract): bool
    {
        return $this->canAccessContract($user, $contract);
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $this->canAccessContract($user, $contract);
    }

    private function canAccessContract(User $user, Contract $contract): bool
    {
        if ($user->role === 'manager') {
            return true;
        }

        return $user->role === 'owner'
            && $user->owner
            && $contract->property
            && $user->owner->properties()->whereKey($contract->property_id)->exists();
    }
}
