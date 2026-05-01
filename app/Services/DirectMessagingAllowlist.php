<?php

namespace App\Services;

use App\Models\StaffAssignment;
use App\Models\TenantAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

class DirectMessagingAllowlist
{
    /**
     * Roles that can be selected in the directory and receive DMs from tenants/landlords.
     */
    public static function messageableRoles(): array
    {
        return ['tenant', 'landlord', 'staff'];
    }

    public static function isMessageableTarget(?User $user): bool
    {
        return $user && in_array($user->role, self::messageableRoles(), true);
    }

    /**
     * Tenants and landlords may start DMs; targets are other messaging-capable users.
     */
    public static function canStartDirectMessage(User $from, ?User $to): bool
    {
        if (! $to || $from->id === $to->id) {
            return false;
        }

        if (! in_array($from->role, ['tenant', 'landlord'], true)) {
            return false;
        }

        return self::isMessageableTarget($to);
    }

    /**
     * Search users by name or email (min length enforced by caller).
     *
     * @return Collection<int, array{id: int, name: string, role: string, subtitle: string}>
     */
    public static function searchDirectoryUsers(User $viewer, string $query, int $limit = 25): Collection
    {
        $query = trim($query);
        if (mb_strlen($query) < 2) {
            return collect();
        }

        $like = '%'.addcslashes($query, '%_\\').'%';

        return User::query()
            ->whereIn('role', self::messageableRoles())
            ->where('id', '!=', $viewer->id)
            ->where(function ($q) use ($like) {
                $q->where('email', 'like', $like)
                    ->orWhereHas('tenantProfile', fn ($q) => $q->where('name', 'like', $like))
                    ->orWhereHas('landlordProfile', fn ($q) => $q->where('name', 'like', $like))
                    ->orWhereHas('staffProfile', fn ($q) => $q->where('name', 'like', $like));
            })
            ->with(['tenantProfile', 'landlordProfile', 'staffProfile'])
            ->orderBy('role')
            ->limit($limit)
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'role' => $u->role,
                'subtitle' => self::directorySubtitle($viewer, $u),
            ])
            ->values();
    }

    /**
     * Prefer a shared property when assignments link both users; otherwise platform-wide (null).
     */
    public static function resolvePropertyScopedApartmentId(User $from, User $to): ?int
    {
        if ($from->isTenant()) {
            return self::resolveFromTenantPerspective($from, $to);
        }

        if ($from->isLandlord()) {
            return self::resolveFromLandlordPerspective($from, $to);
        }

        return null;
    }

    /**
     * @return Collection<int, array{id: int, name: string, role: string, subtitle: string}>
     */
    public static function suggestedContactsForTenant(User $tenant): Collection
    {
        $byId = [];

        $statusRank = fn (string $status) => match ($status) {
            'active' => 1,
            'pending_approval' => 2,
            'terminated' => 3,
            'pending' => 4,
            default => 5,
        };

        $assignments = TenantAssignment::where('tenant_id', $tenant->id)
            ->with(['landlord', 'unit.property'])
            ->get()
            ->sortBy(fn ($a) => $statusRank($a->status));

        foreach ($assignments as $assignment) {
            if (! $assignment->landlord || $assignment->landlord_id === $tenant->id) {
                continue;
            }
            $lid = $assignment->landlord_id;
            if (isset($byId[$lid])) {
                continue;
            }
            $propName = $assignment->unit?->property?->name;
            $subtitle = match ($assignment->status) {
                'active' => $propName ? 'Your landlord · '.$propName : 'Your landlord',
                'pending_approval' => $propName ? 'Application pending · '.$propName : 'Application pending',
                'terminated' => $propName ? 'Former landlord · '.$propName : 'Former landlord',
                default => $propName ? 'Past lease · '.$propName : 'Past lease',
            };
            $byId[$lid] = [
                'id' => $lid,
                'name' => $assignment->landlord->name,
                'role' => 'landlord',
                'subtitle' => $subtitle,
            ];
        }

        $propertyIds = $assignments->pluck('unit.property_id')->unique()->filter()->values();
        if ($propertyIds->isNotEmpty()) {
            self::mergePropertyScopedTenantPeers($tenant, $propertyIds, $byId);
            self::mergePropertyScopedStaff($tenant, $propertyIds, $byId);
        }

        return self::sortContactRows(collect($byId)->values());
    }

    /**
     * @return Collection<int, array{id: int, name: string, role: string, subtitle: string}>
     */
    public static function suggestedContactsForLandlord(User $landlord): Collection
    {
        $byId = [];

        $statusRank = fn (string $status) => match ($status) {
            'active' => 1,
            'pending_approval' => 2,
            'pending' => 3,
            'terminated' => 4,
            default => 5,
        };

        $assignments = TenantAssignment::where('landlord_id', $landlord->id)
            ->with(['tenant', 'unit.property'])
            ->get()
            ->sortBy(fn ($a) => $statusRank($a->status));

        foreach ($assignments as $assignment) {
            if (! $assignment->tenant || $assignment->tenant_id === $landlord->id) {
                continue;
            }
            $tid = $assignment->tenant_id;
            if (isset($byId[$tid])) {
                continue;
            }
            $unitLabel = $assignment->unit?->unit_number ?? '—';
            $subtitle = match ($assignment->status) {
                'active' => 'Tenant · Unit '.$unitLabel,
                'pending_approval' => 'Applicant · Unit '.$unitLabel,
                'terminated' => 'Former tenant · Unit '.$unitLabel,
                default => 'Past · Unit '.$unitLabel,
            };
            $byId[$tid] = [
                'id' => $tid,
                'name' => $assignment->tenant->name,
                'role' => 'tenant',
                'subtitle' => $subtitle,
            ];
        }

        $staffAssignments = StaffAssignment::where('landlord_id', $landlord->id)
            ->where('status', 'active')
            ->with(['staff', 'unit'])
            ->get();

        foreach ($staffAssignments as $sa) {
            if (! $sa->staff || $sa->staff_id === $landlord->id || isset($byId[$sa->staff_id])) {
                continue;
            }
            $byId[$sa->staff_id] = [
                'id' => $sa->staff_id,
                'name' => $sa->staff->name,
                'role' => 'staff',
                'subtitle' => $sa->staff_type_display.' · Unit '.($sa->unit?->unit_number ?? '—'),
            ];
        }

        return self::sortContactRows(collect($byId)->values());
    }

    private static function directorySubtitle(User $viewer, User $u): string
    {
        $roleLabel = match ($u->role) {
            'landlord' => 'Landlord',
            'tenant' => 'Tenant',
            'staff' => 'Staff',
            default => ucfirst((string) $u->role),
        };

        $extra = '';
        if ($u->role === 'staff' && $u->staffProfile?->staff_type) {
            $extra = ' · '.ucwords(str_replace('_', ' ', $u->staffProfile->staff_type));
        }

        $subtitle = $roleLabel.$extra;
        if (self::canRevealDirectoryEmail($viewer, $u)) {
            return $subtitle.' · '.$u->email;
        }

        return $subtitle;
    }

    private static function canRevealDirectoryEmail(User $viewer, User $target): bool
    {
        if ($viewer->id === $target->id) {
            return true;
        }

        // Keep this opt-in: only reveal emails when an explicit ability is granted.
        if (method_exists($viewer, 'can')) {
            return $viewer->can('users.view-email')
                || $viewer->can('users.view_email')
                || $viewer->can('view user email')
                || $viewer->can('view-email');
        }

        return false;
    }

    /**
     * @param  array<int, array{id: int, name: string, role: string, subtitle: string}>  $byId
     */
    private static function mergePropertyScopedTenantPeers(User $tenant, Collection $propertyIds, array &$byId): void
    {
        $otherAssignments = TenantAssignment::where('status', 'active')
            ->where('tenant_id', '!=', $tenant->id)
            ->whereHas('unit', fn ($q) => $q->whereIn('property_id', $propertyIds))
            ->with(['tenant', 'unit'])
            ->get();

        foreach ($otherAssignments as $oa) {
            if (! $oa->tenant || isset($byId[$oa->tenant_id])) {
                continue;
            }
            $byId[$oa->tenant_id] = [
                'id' => $oa->tenant_id,
                'name' => $oa->tenant->name,
                'role' => 'tenant',
                'subtitle' => 'Tenant · Unit '.($oa->unit->unit_number ?? '—'),
            ];
        }
    }

    /**
     * @param  array<int, array{id: int, name: string, role: string, subtitle: string}>  $byId
     */
    private static function mergePropertyScopedStaff(User $tenant, Collection $propertyIds, array &$byId): void
    {
        $staffAssignments = StaffAssignment::where('status', 'active')
            ->whereHas('unit', fn ($q) => $q->whereIn('property_id', $propertyIds))
            ->with(['staff', 'unit'])
            ->get();

        foreach ($staffAssignments as $sa) {
            if (! $sa->staff || $sa->staff_id === $tenant->id || isset($byId[$sa->staff_id])) {
                continue;
            }
            $byId[$sa->staff_id] = [
                'id' => $sa->staff_id,
                'name' => $sa->staff->name,
                'role' => 'staff',
                'subtitle' => $sa->staff_type_display.' · Unit '.($sa->unit->unit_number ?? '—'),
            ];
        }
    }

    /**
     * @param  Collection<int, array{id: int, name: string, role: string, subtitle: string}>  $rows
     * @return Collection<int, array{id: int, name: string, role: string, subtitle: string}>
     */
    private static function sortContactRows(Collection $rows): Collection
    {
        $order = ['landlord' => 0, 'staff' => 1, 'tenant' => 2];

        return $rows
            ->sortBy([
                fn (array $c) => $order[$c['role']] ?? 99,
                fn (array $c) => mb_strtolower($c['name']),
            ])
            ->values();
    }

    private static function resolveFromTenantPerspective(User $tenant, User $to): ?int
    {
        $assignment = TenantAssignment::where('tenant_id', $tenant->id)
            ->where('landlord_id', $to->id)
            ->with('unit')
            ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'pending_approval' THEN 2 WHEN 'pending' THEN 3 WHEN 'terminated' THEN 4 ELSE 5 END")
            ->first();

        if ($assignment && $assignment->unit) {
            return (int) $assignment->unit->property_id;
        }

        if ($to->isTenant()) {
            return self::sharedPropertyBetweenTenants($tenant->id, $to->id);
        }

        if ($to->isStaff()) {
            return self::tenantToStaffSharedProperty($tenant, $to->id);
        }

        return null;
    }

    private static function resolveFromLandlordPerspective(User $landlord, User $to): ?int
    {
        if ($to->isTenant()) {
            $assignment = TenantAssignment::where('landlord_id', $landlord->id)
                ->where('tenant_id', $to->id)
                ->with('unit')
                ->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'pending_approval' THEN 2 WHEN 'pending' THEN 3 WHEN 'terminated' THEN 4 ELSE 5 END")
                ->first();

            if ($assignment && $assignment->unit) {
                return (int) $assignment->unit->property_id;
            }
        }

        if ($to->isStaff()) {
            $sa = StaffAssignment::where('landlord_id', $landlord->id)
                ->where('staff_id', $to->id)
                ->where('status', 'active')
                ->with('unit')
                ->first();

            if ($sa && $sa->unit) {
                return (int) $sa->unit->property_id;
            }
        }

        return null;
    }

    private static function sharedPropertyBetweenTenants(int $tenantAId, int $tenantBId): ?int
    {
        $propsA = TenantAssignment::where('tenant_id', $tenantAId)
            ->whereHas('unit')
            ->with('unit')
            ->get()
            ->pluck('unit.property_id')
            ->unique()
            ->filter()
            ->values();

        if ($propsA->isEmpty()) {
            return null;
        }

        $propsB = TenantAssignment::where('tenant_id', $tenantBId)
            ->whereHas('unit')
            ->with('unit')
            ->get()
            ->pluck('unit.property_id')
            ->unique()
            ->filter();

        $shared = $propsA->intersect($propsB)->first();

        return $shared ? (int) $shared : null;
    }

    private static function tenantToStaffSharedProperty(User $tenant, int $staffId): ?int
    {
        $propertyIds = TenantAssignment::where('tenant_id', $tenant->id)
            ->whereHas('unit')
            ->with('unit')
            ->get()
            ->pluck('unit.property_id')
            ->unique()
            ->filter()
            ->values();

        if ($propertyIds->isEmpty()) {
            return null;
        }

        $sa = StaffAssignment::where('staff_id', $staffId)
            ->where('status', 'active')
            ->whereHas('unit', fn ($q) => $q->whereIn('property_id', $propertyIds))
            ->with('unit')
            ->first();

        if ($sa && $sa->unit) {
            return (int) $sa->unit->property_id;
        }

        return null;
    }
}
