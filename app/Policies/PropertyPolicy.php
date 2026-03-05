<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'landlord']);
    }

    public function view(User $user, Property $property): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLandlord() && $property->landlord_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isLandlord() || $user->isSuperAdmin();
    }

    public function update(User $user, Property $property): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLandlord() && $property->landlord_id === $user->id;
    }

    public function delete(User $user, Property $property): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLandlord() && $property->landlord_id === $user->id;
    }
}
