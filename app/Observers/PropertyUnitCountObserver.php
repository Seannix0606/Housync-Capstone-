<?php

namespace App\Observers;

use App\Models\Property;
use App\Models\Unit;

/**
 * Keeps properties.total_units aligned with the number of related units.
 * (Bulk unit insert uses DB::table and updates the count in the same transaction.)
 */
class PropertyUnitCountObserver
{
    public function created(Unit $unit): void
    {
        $this->syncPropertyTotalUnits($unit->property_id);
    }

    public function deleted(Unit $unit): void
    {
        $this->syncPropertyTotalUnits($unit->property_id);
    }

    public function restored(Unit $unit): void
    {
        $this->syncPropertyTotalUnits($unit->property_id);
    }

    protected function syncPropertyTotalUnits(?int $propertyId): void
    {
        if ($propertyId === null) {
            return;
        }

        $property = Property::query()->find($propertyId);
        if ($property === null) {
            return;
        }

        $count = $property->units()->count();
        if ((int) $property->total_units !== $count) {
            $property->updateQuietly(['total_units' => $count]);
        }
    }
}
