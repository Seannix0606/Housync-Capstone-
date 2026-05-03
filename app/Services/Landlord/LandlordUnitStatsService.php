<?php

namespace App\Services\Landlord;

use App\Models\Unit;
use App\Models\User;

class LandlordUnitStatsService
{
    /**
     * Aggregate counts for landlord-owned units, optionally scoped to one property.
     *
     * @return array{total_units: int, available_units: int, occupied_units: int, monthly_revenue: float|int}
     */
    public function statsForLandlord(User $landlord, ?int $propertyId = null): array
    {
        $statsQuery = Unit::whereHas('property', function ($query) use ($landlord) {
            $query->where('landlord_id', $landlord->id);
        });

        if ($propertyId !== null) {
            $statsQuery->where('property_id', $propertyId);
        }

        return [
            'total_units' => (clone $statsQuery)->count(),
            'available_units' => (clone $statsQuery)->where('status', 'available')->count(),
            'occupied_units' => (clone $statsQuery)->where('status', 'occupied')->count(),
            'monthly_revenue' => (clone $statsQuery)->where('status', 'occupied')->sum('rent_amount') ?? 0,
        ];
    }
}
