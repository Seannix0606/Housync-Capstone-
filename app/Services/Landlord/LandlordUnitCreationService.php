<?php

namespace App\Services\Landlord;

use App\Contracts\Landlord\LandlordUnitCreationServiceContract;
use App\Contracts\Landlord\PropertyTypeUnitRulesContract;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LandlordUnitCreationService implements LandlordUnitCreationServiceContract
{
    public function __construct(
        protected PropertyTypeUnitRulesContract $unitRules
    ) {}

    public function createSingleUnit(Property $property, array $payload): Unit
    {
        $this->unitRules->assertMayAddUnits($property, 1);

        return DB::transaction(function () use ($property, $payload) {
            $attrs = array_merge($this->minimalDefaultAttributes(), [
                'unit_number' => $payload['unit_number'],
                'name' => $payload['unit_number'],
                'rent_amount' => $payload['rent_amount'],
                'price' => $payload['rent_amount'],
                'status' => $payload['status'],
            ]);

            return $property->units()->create($attrs);
        });
    }

    public function createBulkUnits(Property $property, array $payload): Collection
    {
        $unitsPerFloor = isset($payload['units_per_floor']) && $payload['units_per_floor'] !== '' && $payload['units_per_floor'] !== null
            ? (int) $payload['units_per_floor']
            : null;

        if ($unitsPerFloor !== null) {
            $floors = max(1, (int) ($property->building_floors ?? $property->floors ?? 1));
            $count = $unitsPerFloor * $floors;
            $floorForIndex = $this->floorNumbersSequence($floors, $unitsPerFloor);
        } else {
            $count = (int) ($payload['unit_count'] ?? 0);
            $floorForIndex = array_fill(0, max(0, $count), 1);
        }

        if ($count < 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'unit_count' => ['Invalid unit count.'],
            ]);
        }

        $this->unitRules->assertMayAddUnits($property, $count);

        $labels = $this->generateSequentialLabels($payload['naming_pattern'], $count);
        $existing = $property->units()->pluck('unit_number')->all();

        foreach ($labels as $label) {
            if (in_array($label, $existing, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'naming_pattern' => ["The generated unit name \"{$label}\" already exists for this property."],
                ]);
            }
        }

        return DB::transaction(function () use ($property, $labels, $payload, $floorForIndex) {
            $created = collect();

            foreach ($labels as $idx => $label) {
                $floorNumber = $floorForIndex[$idx] ?? 1;
                $attrs = array_merge($this->minimalDefaultAttributes(), [
                    'unit_number' => $label,
                    'name' => $label,
                    'rent_amount' => $payload['default_rent'],
                    'price' => $payload['default_rent'],
                    'status' => $payload['default_status'],
                    'floor_number' => $floorNumber,
                ]);
                $created->push($property->units()->create($attrs));
            }

            return $created;
        });
    }

    /**
     * One floor number per generated unit index (row-major: fill floor 1, then floor 2, …).
     *
     * @return list<int>
     */
    protected function floorNumbersSequence(int $floors, int $unitsPerFloor): array
    {
        $out = [];
        for ($f = 1; $f <= $floors; $f++) {
            for ($u = 1; $u <= $unitsPerFloor; $u++) {
                $out[] = $f;
            }
        }

        return $out;
    }

    /**
     * Shared defaults for quick-create flows (aligned with storeApartmentUnit / listing expectations).
     *
     * @return array<string, mixed>
     */
    protected function minimalDefaultAttributes(): array
    {
        return [
            'unit_type' => 'studio',
            'leasing_type' => 'separate',
            'tenant_count' => 0,
            'bedrooms' => 0,
            'bathrooms' => 1,
            'max_occupants' => 1,
            'floor_number' => 1,
            'floor_area' => null,
            'unit_stories' => null,
            'description' => null,
            'amenities' => [],
            'is_furnished' => false,
            'notes' => null,
        ];
    }

    /**
     * @return list<string>
     */
    protected function generateSequentialLabels(string $pattern, int $count): array
    {
        $pattern = trim($pattern);
        $labels = [];

        for ($i = 1; $i <= $count; $i++) {
            if (str_contains($pattern, '{n}')) {
                $labels[] = str_replace('{n}', (string) $i, $pattern);
            } else {
                $labels[] = trim($pattern.' '.$i);
            }
        }

        return $labels;
    }
}
