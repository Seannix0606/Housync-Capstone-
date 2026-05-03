<?php

namespace App\Services\Landlord;

use App\Contracts\Landlord\PropertyTypeUnitRulesContract;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class PropertyService
{
    public function __construct(
        private PropertyTypeUnitRulesContract $propertyTypeUnitRules
    ) {}

    /**
     * Create a property and seed initial units from the payload.
     *
     * Expected keys (aligned with landlord property store flow):
     * - landlord_id (required), name, property_type, address
     * - description?, contact_person?, contact_phone?, contact_email?, amenities?, status?
     * - floors?, bedrooms? (house metadata), unit_count? (optional override where applicable)
     */
    public function createPropertyWithUnits(array $data): Property
    {
        return DB::transaction(function () use ($data) {
            $propertyType = $data['property_type'];

            $unitRows = $this->resolveInitialUnitRows($propertyType, $data);

            // `floors` column = unit count for multi-unit types only (not physical stories).
            $floorsColumn = match ($propertyType) {
                'house', 'duplex', 'townhouse' => null,
                default => $data['floors'] ?? null,
            };

            $dwellingTypes = ['house', 'duplex', 'townhouse'];
            $buildingFloors = null;
            $propertyBedrooms = null;
            if (in_array($propertyType, $dwellingTypes, true)) {
                if (array_key_exists('building_floors', $data) && $data['building_floors'] !== null && $data['building_floors'] !== '') {
                    $buildingFloors = (int) $data['building_floors'];
                }
                if ($propertyType === 'duplex' && ! empty($data['unit_bedrooms']) && is_array($data['unit_bedrooms'])) {
                    $perUnit = [];
                    foreach ([0, 1] as $idx) {
                        if (array_key_exists($idx, $data['unit_bedrooms']) && $data['unit_bedrooms'][$idx] !== null && $data['unit_bedrooms'][$idx] !== '') {
                            $perUnit[] = max(0, (int) $data['unit_bedrooms'][$idx]);
                        }
                    }
                    $propertyBedrooms = $perUnit !== [] ? max($perUnit) : null;
                } elseif (array_key_exists('bedrooms', $data) && $data['bedrooms'] !== null && $data['bedrooms'] !== '') {
                    $propertyBedrooms = (int) $data['bedrooms'];
                }
            }

            $property = Property::query()->create([
                'landlord_id' => $data['landlord_id'],
                'name' => $data['name'],
                'property_type' => $propertyType,
                'address' => $data['address'],
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'description' => $data['description'] ?? null,
                'total_units' => 0,
                'floors' => $floorsColumn,
                'building_floors' => $buildingFloors,
                'bedrooms' => $propertyBedrooms,
                'year_built' => $data['year_built'] ?? null,
                'parking_spaces' => $data['parking_spaces'] ?? null,
                'contact_person' => $data['contact_person'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'amenities' => $data['amenities'] ?? [],
                'status' => $data['status'] ?? 'active',
            ]);

            Unit::withoutEvents(function () use ($property, $unitRows) {
                foreach ($unitRows as $row) {
                    $property->units()->create($row);
                }
            });

            if ($unitRows !== []) {
                $property->update(['total_units' => count($unitRows)]);
            }

            return $property->fresh(['units']);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function resolveInitialUnitRows(string $propertyType, array $data): array
    {
        $count = $this->resolveUnitCount($propertyType, $data);

        return $this->makeNumberedUnits($count, $propertyType, $data);
    }

    protected function resolveUnitCount(string $propertyType, array $data): int
    {
        $fromFloors = array_key_exists('floors', $data) && $data['floors'] !== null && $data['floors'] !== ''
            ? (int) $data['floors']
            : null;
        $fromUnitCount = array_key_exists('unit_count', $data) && $data['unit_count'] !== null && $data['unit_count'] !== ''
            ? (int) $data['unit_count']
            : null;

        return $this->propertyTypeUnitRules->resolveInitialUnitCount($propertyType, $fromFloors, $fromUnitCount);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<string, mixed>>
     */
    protected function makeNumberedUnits(int $count, string $propertyType, array $data = []): array
    {
        if ($count < 1) {
            return [];
        }

        $units = [];
        $typeLabel = $this->unitTypeLabel($propertyType);
        $seedBedrooms = 0;
        if (in_array($propertyType, ['house', 'townhouse'], true)) {
            $seedBedrooms = isset($data['bedrooms']) && $data['bedrooms'] !== '' && $data['bedrooms'] !== null
                ? max(0, (int) $data['bedrooms']) : 0;
        }

        for ($i = 1; $i <= $count; $i++) {
            $bedroomsForUnit = $seedBedrooms;
            if ($propertyType === 'duplex' && ! empty($data['unit_bedrooms']) && is_array($data['unit_bedrooms'])) {
                $idx = $i - 1;
                if (array_key_exists($idx, $data['unit_bedrooms']) && $data['unit_bedrooms'][$idx] !== null && $data['unit_bedrooms'][$idx] !== '') {
                    $bedroomsForUnit = max(0, (int) $data['unit_bedrooms'][$idx]);
                }
            }

            $interiorStoriesForUnit = null;
            if ($propertyType === 'duplex' && ! empty($data['unit_stories']) && is_array($data['unit_stories'])) {
                $idx = $i - 1;
                if (array_key_exists($idx, $data['unit_stories']) && $data['unit_stories'][$idx] !== null && $data['unit_stories'][$idx] !== '') {
                    $interiorStoriesForUnit = max(1, (int) $data['unit_stories'][$idx]);
                }
            } elseif (in_array($propertyType, ['house', 'townhouse'], true)
                && $i === 1
                && array_key_exists('dwelling_stories', $data)
                && $data['dwelling_stories'] !== null
                && $data['dwelling_stories'] !== '') {
                $interiorStoriesForUnit = max(1, (int) $data['dwelling_stories']);
            }

            $units[] = [
                'unit_number' => 'Unit '.$i,
                'unit_type' => $typeLabel,
                'rent_amount' => 0,
                'status' => 'available',
                'leasing_type' => 'separate',
                'tenant_count' => 0,
                'bedrooms' => $bedroomsForUnit,
                'bathrooms' => 1,
                'floor_number' => $count === 1 ? 1 : $i,
                'unit_stories' => $interiorStoriesForUnit,
                'is_furnished' => false,
            ];
        }

        return $units;
    }

    protected function unitTypeLabel(string $propertyType): string
    {
        return match ($propertyType) {
            'house' => 'Single Family House',
            'townhouse' => 'Townhouse',
            'duplex' => 'Duplex',
            'apartment' => 'Apartment',
            'condominium' => 'Condominium',
            'others' => 'Other',
            default => 'Unit',
        };
    }
}
