<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'landlord']);
    }

    public function view(User $user, Unit $unit): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isLandlord()) {
            return $unit->property && $unit->property->landlord_id === $user->id;
        }

        if ($user->isTenant()) {
            return $user->tenantAssignments()
                ->where('unit_id', $unit->id)
                ->where('status', 'active')
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isLandlord() || $user->isSuperAdmin();
    }

    public function update(User $user, Unit $unit): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLandlord() && $unit->property && $unit->property->landlord_id === $user->id;
    }

    public function delete(User $user, Unit $unit): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLandlord() && $unit->property && $unit->property->landlord_id === $user->id;
    }
}
