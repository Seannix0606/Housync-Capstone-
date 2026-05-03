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
        $count = (int) $payload['unit_count'];
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

        return DB::transaction(function () use ($property, $labels, $payload) {
            $created = collect();

            foreach ($labels as $label) {
                $attrs = array_merge($this->minimalDefaultAttributes(), [
                    'unit_number' => $label,
                    'name' => $label,
                    'rent_amount' => $payload['default_rent'],
                    'price' => $payload['default_rent'],
                    'status' => $payload['default_status'],
                ]);
                $created->push($property->units()->create($attrs));
            }

            return $created;
        });
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
