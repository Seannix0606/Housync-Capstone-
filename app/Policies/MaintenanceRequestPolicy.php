<?php

namespace App\Policies;

use App\Models\MaintenanceRequest;
use App\Models\User;

class MaintenanceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'landlord', 'tenant', 'staff']);
    }

    public function view(User $user, MaintenanceRequest $request): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isLandlord()) {
            return $request->landlord_id === $user->id;
        }

        if ($user->isTenant()) {
            return $request->tenant_id === $user->id;
        }

        if ($user->isStaff()) {
            return $request->assigned_staff_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['landlord', 'tenant']);
    }

    public function update(User $user, MaintenanceRequest $request): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isLandlord()) {
            return $request->landlord_id === $user->id;
        }

        if ($user->isStaff()) {
            return $request->assigned_staff_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, MaintenanceRequest $request): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLandlord() && $request->landlord_id === $user->id;
    }

    public function addComment(User $user, MaintenanceRequest $request): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isLandlord()) {
            return $request->landlord_id === $user->id;
        }

        if ($user->isTenant()) {
            return $request->tenant_id === $user->id;
        }

        if ($user->isStaff()) {
            return $request->assigned_staff_id === $user->id;
        }

        return false;
    }

    public function rate(User $user, MaintenanceRequest $request): bool
    {
        return $user->isTenant()
            && $request->tenant_id === $user->id
            && $request->status === 'completed'
            && is_null($request->rating);
    }
}
