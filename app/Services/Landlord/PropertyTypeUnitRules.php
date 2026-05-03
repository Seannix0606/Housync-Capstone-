<?php

namespace App\Services\Landlord;

use App\Contracts\Landlord\PropertyTypeUnitRulesContract;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Validation\ValidationException;

class PropertyTypeUnitRules implements PropertyTypeUnitRulesContract
{
    private const DUPLEX_UNIT_COUNT = 2;

    private const SINGLE_DWELLING_UNIT_COUNT = 1;

    public function resolveInitialUnitCount(string $propertyType, ?int $requestedFloors, ?int $requestedUnitCount): int
    {
        $requested = $requestedFloors ?? $requestedUnitCount;

        return match ($propertyType) {
            'house' => self::SINGLE_DWELLING_UNIT_COUNT,
            'duplex' => $this->resolveDuplexInitialCount($requested),
            'townhouse' => $this->resolveSingleDwellingHint($requested, 'townhouse'),
            'apartment', 'condominium' => $this->requireAtLeast(
                2,
                $requested,
                'Apartment and condominium properties require multiple units (at least 2).'
            ),
            default => max(1, $requested ?? 1),
        };
    }

    public function maximumUnitsForType(?string $propertyType): ?int
    {
        return match ($propertyType) {
            'house', 'townhouse' => self::SINGLE_DWELLING_UNIT_COUNT,
            'duplex' => self::DUPLEX_UNIT_COUNT,
            default => null,
        };
    }

    public function assertMayAddUnits(Property $property, int $unitsToAdd): void
    {
        if ($unitsToAdd < 1) {
            return;
        }

        $max = $this->maximumUnitsForType($property->property_type);
        if ($max === null) {
            return;
        }

        $locked = Property::query()->whereKey($property->getKey())->lockForUpdate()->firstOrFail();
        $current = $locked->units()->count();
        if ($current + $unitsToAdd > $max) {
            throw ValidationException::withMessages([
                'unit_number' => [
                    $this->maxUnitsExceededMessage($property->property_type, $max),
                ],
            ]);
        }
    }

    public function assertUpdateCompatibleWithExistingUnits(Property $property, string $newPropertyType, int $submittedTotalUnits): void
    {
        $actual = $property->units()->count();

        if ($newPropertyType === 'duplex') {
            if ($actual !== self::DUPLEX_UNIT_COUNT) {
                throw ValidationException::withMessages([
                    'property_type' => [
                        'A duplex must have exactly '.self::DUPLEX_UNIT_COUNT." units. This property currently has {$actual}.",
                    ],
                ]);
            }

            if ($submittedTotalUnits !== self::DUPLEX_UNIT_COUNT) {
                throw ValidationException::withMessages([
                    'total_units' => ['Total units must be '.self::DUPLEX_UNIT_COUNT.' for a duplex.'],
                ]);
            }

            return;
        }

        if (in_array($newPropertyType, ['house', 'townhouse'], true)) {
            if ($actual !== self::SINGLE_DWELLING_UNIT_COUNT) {
                $label = $newPropertyType === 'townhouse' ? 'townhouse' : 'single family house';
                throw ValidationException::withMessages([
                    'property_type' => [
                        "A {$label} has exactly one unit. This property currently has {$actual}.",
                    ],
                ]);
            }

            if ($submittedTotalUnits !== self::SINGLE_DWELLING_UNIT_COUNT) {
                throw ValidationException::withMessages([
                    'total_units' => ['Total units must be 1 for a single family house or townhouse.'],
                ]);
            }
        }
    }

    /**
     * Duplex: always two dwelling units; optional request hint must be blank or exactly 2.
     */
    private function resolveDuplexInitialCount(?int $requested): int
    {
        if ($requested !== null && $requested !== self::DUPLEX_UNIT_COUNT) {
            throw ValidationException::withMessages([
                'floors' => ['A duplex has exactly two units. Leave this blank or enter 2.'],
                'unit_count' => ['A duplex has exactly two units. Leave this blank or enter 2.'],
            ]);
        }

        return self::DUPLEX_UNIT_COUNT;
    }

    /**
     * One legal dwelling = one rentable unit (townhouse row is a single home).
     */
    private function resolveSingleDwellingHint(?int $requested, string $typeLabel): int
    {
        if ($requested !== null && $requested !== self::SINGLE_DWELLING_UNIT_COUNT) {
            throw ValidationException::withMessages([
                'floors' => ["A {$typeLabel} has exactly one unit. Leave this blank or enter 1."],
                'unit_count' => ["A {$typeLabel} has exactly one unit. Leave this blank or enter 1."],
            ]);
        }

        return self::SINGLE_DWELLING_UNIT_COUNT;
    }

    private function maxUnitsExceededMessage(string $propertyType, int $max): string
    {
        return match ($propertyType) {
            'duplex' => "A duplex has exactly {$max} units. Remove a unit or change the property type before adding more.",
            'house' => 'A single family house has exactly one unit. Edit it in My Units or change the property type instead of adding another.',
            'townhouse' => 'A townhouse listing is one dwelling with exactly one unit. Edit it in My Units or change the property type instead of adding another.',
            default => "This property type allows at most {$max} unit(s).",
        };
    }

    public function assertDeletingUnitAllowed(Unit $unit): void
    {
        $unit->loadMissing('property');
        $property = $unit->property;

        if ($property === null) {
            return;
        }

        if ($property->property_type === 'duplex') {
            throw ValidationException::withMessages([
                'unit' => [
                    'Duplex properties always have exactly two units. You cannot delete a unit; edit it in place or change the property type first.',
                ],
            ]);
        }

        if (in_array($property->property_type, ['house', 'townhouse'], true)) {
            if ($property->units()->count() <= 1) {
                throw ValidationException::withMessages([
                    'unit' => [
                        'This property is one dwelling with a single unit. You cannot delete that unit; edit it in place or delete the whole property.',
                    ],
                ]);
            }
        }
    }

    private function requireAtLeast(int $minimum, ?int $requested, string $message): int
    {
        if ($requested === null || $requested < $minimum) {
            throw ValidationException::withMessages([
                'floors' => [$message],
                'unit_count' => [$message],
            ]);
        }

        return $requested;
    }
}
