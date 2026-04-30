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
        return User::query()->pendingLandlords();
    }

    public function pendingLandlordsPaginated(int $perPage = 15)
    {
        return $this->pendingLandlordsQuery()
            ->with(['landlordProfile'])
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
        $result = DB::transaction(function () use ($landlord, $actingAdminUserId) {
            $profile = $landlord->landlordProfile()->lockForUpdate()->first();

            if (! $profile) {
                throw new \RuntimeException('Landlord profile not found.');
            }

            if ($profile->status === 'approved') {
                return 'already_approved';
            }

            if ($profile->status !== 'pending') {
                throw new \RuntimeException('This landlord application is not pending approval.');
            }

            $profile->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $actingAdminUserId,
                'rejection_reason' => null,
            ]);

            return 'approved';
        });

        $landlord->unsetRelation('landlordProfile');
        $landlord->load('landlordProfile');

        return $result;
    }

    /**
     * Reject a pending landlord application.
     *
     * @return 'rejected'|'already_rejected'
     */
    public function rejectPendingLandlord(User $landlord, int $actingAdminUserId, string $reason): string
    {
        $result = DB::transaction(function () use ($landlord, $actingAdminUserId, $reason) {
            $profile = $landlord->landlordProfile()->lockForUpdate()->first();

            if (! $profile) {
                throw new \RuntimeException('Landlord profile not found.');
            }

            if ($profile->status === 'rejected') {
                return 'already_rejected';
            }

            if ($profile->status !== 'pending') {
                throw new \RuntimeException('This landlord application is not pending approval.');
            }

            $profile->update([
                'status' => 'rejected',
                'approved_at' => null,
                'approved_by' => $actingAdminUserId,
                'rejection_reason' => $reason,
            ]);

            return 'rejected';
        });

        $landlord->unsetRelation('landlordProfile');
        $landlord->load('landlordProfile');

        return $result;
    }
}
