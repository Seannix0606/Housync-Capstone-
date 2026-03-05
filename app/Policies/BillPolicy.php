<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'landlord', 'tenant']);
    }

    public function view(User $user, Bill $bill): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isLandlord()) {
            return $bill->landlord_id === $user->id;
        }

        if ($user->isTenant()) {
            return $bill->tenant_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isLandlord() || $user->isSuperAdmin();
    }

    public function update(User $user, Bill $bill): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLandlord() && $bill->landlord_id === $user->id;
    }

    public function delete(User $user, Bill $bill): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLandlord() && $bill->landlord_id === $user->id && $bill->amount_paid <= 0;
    }

    public function recordPayment(User $user, Bill $bill): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLandlord() && $bill->landlord_id === $user->id;
    }

    public function submitPaymentProof(User $user, Bill $bill): bool
    {
        return $user->isTenant() && $bill->tenant_id === $user->id && $bill->status !== 'paid';
    }
}
