<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LandlordVerificationService
{
    /**
     * Landlords whose profile application is still pending (single source of truth for listings).
     */
    public function pendingLandlordsQuery(): Builder
    {
        return User::query()
            ->where('role', 'landlord')
            ->whereHas('landlordProfile', fn ($q) => $q->where('status', 'pending'));
    }

    public function pendingLandlordsPaginated(int $perPage = 15)
    {
        return $this->pendingLandlordsQuery()
            ->with(['landlordProfile', 'approvedBy', 'landlordDocuments'])
            ->latest('users.created_at')
            ->paginate($perPage);
    }

    /**
     * Recent pending landlords for dashboards (non-paginated).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function recentPendingLandlords(int $limit = 5)
    {
        return $this->pendingLandlordsQuery()
            ->with('landlordProfile')
            ->latest('users.created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Approve a pending landlord. Idempotent: already-approved returns without error state for the UI.
     *
     * @return 'approved'|'already_approved'
     */
    public function approvePendingLandlord(User $landlord, int $actingAdminUserId): string
    {
        $landlord->load('landlordProfile');
        $profile = $landlord->landlordProfile;

        if (! $profile) {
            throw new \RuntimeException('Landlord profile not found.');
        }

        if ($profile->status === 'approved') {
            return 'already_approved';
        }

        if ($profile->status !== 'pending') {
            throw new \RuntimeException('This landlord application is not pending approval.');
        }

        DB::transaction(function () use ($landlord, $actingAdminUserId) {
            $landlord->approve($actingAdminUserId);
        });

        $landlord->unsetRelation('landlordProfile');
        $landlord->load('landlordProfile');

        return 'approved';
    }

    /**
     * Reject a pending landlord application.
     *
     * @return 'rejected'|'already_rejected'
     */
    public function rejectPendingLandlord(User $landlord, int $actingAdminUserId, string $reason): string
    {
        $landlord->load('landlordProfile');
        $profile = $landlord->landlordProfile;

        if (! $profile) {
            throw new \RuntimeException('Landlord profile not found.');
        }

        if ($profile->status === 'rejected') {
            return 'already_rejected';
        }

        if ($profile->status !== 'pending') {
            throw new \RuntimeException('This landlord application is not pending approval.');
        }

        DB::transaction(function () use ($landlord, $actingAdminUserId, $reason) {
            $landlord->reject($actingAdminUserId, $reason);
        });

        $landlord->unsetRelation('landlordProfile');
        $landlord->load('landlordProfile');

        return 'rejected';
    }
}
