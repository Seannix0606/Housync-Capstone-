<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Unit;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /**
     * Create a booking, preferring unit_id. When only property_id is supplied (legacy),
     * assigns the first available unit on that property.
     *
     * @param  array{unit_id?: int|string|null, property_id?: int|string|null}  $data
     */
    public function createBooking(array $data): Booking
    {
        $unitId = $this->nullableInt($data['unit_id'] ?? null);
        $propertyId = $this->nullableInt($data['property_id'] ?? null);

        if ($unitId !== null) {
            $unit = Unit::query()->findOrFail($unitId);

            if ($propertyId !== null && (int) $unit->property_id !== $propertyId) {
                throw ValidationException::withMessages([
                    'unit_id' => ['The selected unit does not belong to the given property.'],
                    'property_id' => ['The selected unit does not belong to the given property.'],
                ]);
            }

            return Booking::query()->create([
                'property_id' => $unit->property_id,
                'unit_id' => $unit->id,
            ]);
        }

        if ($propertyId !== null) {
            $unit = Unit::query()
                ->where('property_id', $propertyId)
                ->where('status', 'available')
                ->orderBy('id')
                ->first();

            if ($unit === null) {
                throw ValidationException::withMessages([
                    'property_id' => ['No available unit was found for this property. Choose a specific unit or add an available unit first.'],
                ]);
            }

            return Booking::query()->create([
                'property_id' => $propertyId,
                'unit_id' => $unit->id,
            ]);
        }

        throw ValidationException::withMessages([
            'unit_id' => ['Provide a unit for this booking, or a property to pick the first available unit.'],
        ]);
    }

    protected function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
