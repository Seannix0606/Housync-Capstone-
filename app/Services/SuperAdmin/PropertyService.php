<?php

namespace App\Services\SuperAdmin;

use App\Models\Property;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PropertyService
{
    public function getPaginatedProperties(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Property::query()
            ->with(['landlord', 'units'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%')
                        ->orWhereHas('landlord', function ($landlordQuery) use ($search) {
                            $landlordQuery->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($filters['landlord'] ?? null, function ($query, $landlordId) {
                $query->where('landlord_id', $landlordId);
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function getPropertyDetails(int $propertyId): Property
    {
        return Property::query()
            ->with('landlord')
            ->withCount('units')
            ->withCount([
                'units as occupied_units_count' => fn ($query) => $query->where('status', 'occupied'),
                'units as available_units_count' => fn ($query) => $query->where('status', 'available'),
            ])
            ->findOrFail($propertyId);
    }

    public function getPropertyWithUnits(int $propertyId): Property
    {
        return Property::query()
            ->with([
                'landlord',
                'units' => fn ($query) => $query
                    ->with(['tenantAssignment.tenant'])
                    ->orderBy('floor_number')
                    ->orderBy('unit_number'),
            ])
            ->withCount([
                'units as occupied_units_count' => fn ($query) => $query->where('status', 'occupied'),
                'units as available_units_count' => fn ($query) => $query->where('status', 'available'),
            ])
            ->findOrFail($propertyId);
    }

    public function getPropertyStats(): array
    {
        return [
            'total_properties' => Property::count(),
            'total_units' => \App\Models\Unit::count(),
            'occupied_units' => \App\Models\Unit::where('status', 'occupied')->count(),
            'active_landlords' => User::approvedLandlords()->count(),
        ];
    }

    public function getApprovedLandlords(): Collection
    {
        return User::approvedLandlords()->orderBy('id')->get();
    }
}
