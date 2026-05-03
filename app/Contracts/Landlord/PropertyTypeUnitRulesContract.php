<?php

namespace App\Contracts\Landlord;

use App\Models\Property;
use App\Models\Unit;
use Illuminate\Validation\ValidationException;

/**
 * Encapsulates how many units a property type may have at creation and over its lifetime.
 */
interface PropertyTypeUnitRulesContract
{
    /**
     * How many units to create when the property is first saved.
     *
     * @param  ?int  $requestedFloors  Raw floors value if present.
     * @param  ?int  $requestedUnitCount  Raw unit_count if present.
     *
     * @throws ValidationException
     */
    public function resolveInitialUnitCount(string $propertyType, ?int $requestedFloors, ?int $requestedUnitCount): int;

    /**
     * Hard cap on units for this type, or null if there is no cap.
     */
    public function maximumUnitsForType(?string $propertyType): ?int;

    /**
     * @throws ValidationException
     */
    public function assertMayAddUnits(Property $property, int $unitsToAdd): void;

    /**
     * Validate update form values against existing units (e.g. duplex must stay at two units).
     *
     * @throws ValidationException
     */
    public function assertUpdateCompatibleWithExistingUnits(Property $property, string $newPropertyType, int $submittedTotalUnits): void;

    /**
     * @throws ValidationException
     */
    public function assertDeletingUnitAllowed(Unit $unit): void;
}
