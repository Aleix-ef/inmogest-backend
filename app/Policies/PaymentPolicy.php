<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['manager', 'owner'], true);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->canAccessPayment($user, $payment);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['manager', 'owner'], true);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->canAccessPayment($user, $payment);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->canAccessPayment($user, $payment);
    }

    private function canAccessPayment(User $user, Payment $payment): bool
    {
        if ($user->role === 'manager') {
            return true;
        }

        return $user->role === 'owner'
            && $user->owner
            && $payment->contract
            && $user->owner->properties()->whereKey($payment->contract->property_id)->exists();
    }
}
