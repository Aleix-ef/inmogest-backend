<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['manager', 'owner'], true);
    }

    public function view(User $user, Property $property): bool
    {
        return $this->canAccessProperty($user, $property);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['manager', 'owner'], true);
    }

    public function update(User $user, Property $property): bool
    {
        return $this->canAccessProperty($user, $property);
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->canAccessProperty($user, $property);
    }

    private function canAccessProperty(User $user, Property $property): bool
    {
        if ($user->role === 'manager') {
            return true;
        }

        return $user->role === 'owner'
            && $user->owner
            && $user->owner->properties()->whereKey($property->id)->exists();
    }
}
