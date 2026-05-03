<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Keep in sync with {@see PropertySeeder} slugs — only these get auto-generated units.
     *
     * @var list<string>
     */
    private const DEMO_PROPERTY_SLUGS = [
        'sunrise-apartments',
        'green-valley-condos',
        'demo-townhouse-oak',
        'demo-house-elm',
        'demo-duplex-maple',
        'demo-others-pine',
    ];

    /**
     * Ensure each demo property has the expected number of units (see PropertySeeder totals).
     */
    public function run(): void
    {
        if (! User::where('role', 'landlord')->exists()) {
            $this->command->warn('No landlord users found. Skipping unit seeding.');

            return;
        }

        $propertyCount = 0;

        foreach (Property::query()->whereIn('slug', self::DEMO_PROPERTY_SLUGS)->orderBy('id')->cursor() as $property) {
            $this->syncUnitsForProperty($property);
            $propertyCount++;
        }

        $this->command->info("Synced demo units across {$propertyCount} seeded properties.");
    }

    private function syncUnitsForProperty(Property $property): void
    {
        $type = (string) ($property->property_type ?? '');
        $target = max(1, (int) $property->total_units);

        if (in_array($type, ['apartment', 'condominium'], true)) {
            $target = max(2, $target);
        }

        $floors = max(1, (int) ($property->building_floors ?? $property->floors ?? 1));

        for ($i = 1; $i <= $target; $i++) {
            $unitNumber = $this->unitNumberFor($type, $i);
            $floorNumber = $this->floorNumberFor($type, $floors, $i, $target);

            $attrs = $this->unitAttributesTemplate($property, $i, $floorNumber);

            Unit::updateOrCreate(
                [
                    'property_id' => $property->id,
                    'unit_number' => $unitNumber,
                ],
                array_merge($attrs, [
                    'property_id' => $property->id,
                    'unit_number' => $unitNumber,
                    'floor_number' => $floorNumber,
                ])
            );
        }

        $property->update(['total_units' => $property->units()->count()]);
    }

    private function unitNumberFor(string $propertyType, int $index): string
    {
        return match ($propertyType) {
            'duplex' => $index === 1 ? 'East' : 'West',
            'house', 'townhouse' => 'Main',
            default => 'Unit '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
        };
    }

    private function floorNumberFor(string $propertyType, int $floors, int $index, int $total): int
    {
        if (in_array($propertyType, ['house', 'townhouse'], true)) {
            return 1;
        }

        if ($propertyType === 'duplex') {
            return min($floors, $index);
        }

        // Apartment-style: spread units across floors in rotation
        if ($total <= 1) {
            return 1;
        }

        return (($index - 1) % $floors) + 1;
    }

    /**
     * @return array<string, mixed>
     */
    private function unitAttributesTemplate(Property $property, int $index, int $floorNumber): array
    {
        $type = (string) ($property->property_type ?? '');
        $variants = [
            ['label' => 'Studio', 'bedrooms' => 0, 'rent' => 5500],
            ['label' => '1 Bedroom', 'bedrooms' => 1, 'rent' => 7500],
            ['label' => '2 Bedroom', 'bedrooms' => 2, 'rent' => 11000],
        ];
        $pick = $variants[($index - 1) % count($variants)];

        if ($type === 'duplex') {
            $pick = $index === 1
                ? ['label' => '2 Bedroom', 'bedrooms' => 2, 'rent' => 14000]
                : ['label' => '3 Bedroom', 'bedrooms' => 3, 'rent' => 16500];
        }

        if (in_array($type, ['house', 'townhouse'], true)) {
            $pick = ['label' => '3 Bedroom', 'bedrooms' => (int) ($property->bedrooms ?? 3), 'rent' => 28000];
        }

        $bedrooms = min(10, max(0, (int) $pick['bedrooms']));
        $maxOccupants = max(1, $bedrooms === 0 ? 2 : $bedrooms + 1);

        return [
            'unit_type' => $pick['label'],
            'rent_amount' => (float) $pick['rent'],
            'status' => 'available',
            'leasing_type' => 'separate',
            'tenant_count' => 0,
            'max_occupants' => $maxOccupants,
            'description' => match ($type) {
                'duplex' => 'Duplex side '.$index.' — demo seed.',
                'house', 'townhouse' => 'Primary dwelling — demo seed.',
                default => "Unit on floor {$floorNumber} — demo seed.",
            },
            'floor_area' => 28.0 + ($index * 4.5),
            'bedrooms' => $bedrooms,
            'bathrooms' => max(1, min(3, (int) ceil(($bedrooms + 1) / 2))),
            'is_furnished' => $index % 2 === 1,
            'amenities' => ['WiFi'],
            'notes' => 'Seeded for '.$property->name,
        ];
    }
}
