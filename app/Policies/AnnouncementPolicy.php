<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Announcement $announcement): bool
    {
        if ($user->isSuperAdmin() || $user->isLandlord()) {
            return true;
        }

        // Tenants and staff can view published announcements for their properties
        return $announcement->published_at && $announcement->published_at->lte(now());
    }

    public function create(User $user): bool
    {
        return $user->isLandlord() || $user->isSuperAdmin();
    }

    public function update(User $user, Announcement $announcement): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLandlord() && $announcement->user_id === $user->id;
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLandlord() && $announcement->user_id === $user->id;
    }
}
