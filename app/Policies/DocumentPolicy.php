<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['manager', 'owner'], true);
    }

    public function view(User $user, Document $document): bool
    {
        if ($user->role === 'manager') {
            return true;
        }

        if ($user->role !== 'owner' || ! $user->owner) {
            return false;
        }

        $owner = $user->owner;

        return ($document->property_id && $owner->properties()->whereKey($document->property_id)->exists())
            || ($document->contract && $owner->properties()->whereKey($document->contract->property_id)->exists())
            || ($document->tenant && $document->tenant->properties()
                ->whereHas('owners', fn ($query) => $query->where('owners.id', $owner->id))
                ->exists());
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['manager', 'owner'], true);
    }

    public function update(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }

    public function delete(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }
}
