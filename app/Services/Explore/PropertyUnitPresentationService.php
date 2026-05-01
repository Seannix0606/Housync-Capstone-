<?php

namespace App\Services\Explore;

use App\Models\Property;
use App\Models\Unit;
use Illuminate\Support\Collection;

class PropertyUnitPresentationService
{
    /**
     * Build floor-grouped view model for available units.
     */
    public function build(Property $property): array
    {
        $displayTerm = $this->getDisplayTerm($property);
        $units = $property->units instanceof Collection ? $property->units : collect();

        $sortedUnits = $units
            ->sort(function (Unit $left, Unit $right) {
                $leftRent = (float) ($left->rent_amount ?? 0);
                $rightRent = (float) ($right->rent_amount ?? 0);

                if ($leftRent === $rightRent) {
                    return strnatcasecmp((string) ($left->unit_number ?? ''), (string) ($right->unit_number ?? ''));
                }

                return $leftRent <=> $rightRent;
            })
            ->values();

        $grouped = $sortedUnits->groupBy(function (Unit $unit) {
            $floorNumber = is_null($unit->floor_number) ? null : (int) $unit->floor_number;

            if (is_null($floorNumber) || $floorNumber <= 0) {
                return 'unspecified';
            }

            return $floorNumber;
        });

        $floorGroups = $grouped
            ->map(function (Collection $floorUnits, $floorKey) use ($property, $displayTerm) {
                return [
                    'floor_key' => (string) $floorKey,
                    'floor_label' => $this->formatFloorLabel($floorKey),
                    'available_count' => $floorUnits->filter(fn (Unit $unit) => $unit->is_available)->count(),
                    'units' => $floorUnits->map(function (Unit $unit) use ($property, $displayTerm) {
                        $unitLabel = trim(($displayTerm.' '.($unit->unit_number ?? $unit->id)));
                        $propertySlugOrId = ! empty($property->slug) ? $property->slug : $property->id;
                        $rentDisplay = is_null($unit->rent_amount)
                            ? 'Inquire'
                            : '₱'.number_format((float) $unit->rent_amount, 2).'/month';

                        return [
                            'id' => $unit->id,
                            'label' => $unitLabel,
                            'details_url' => route('property.show', $propertySlugOrId.'-unit-'.$unit->id),
                            'rent_display' => $rentDisplay,
                            'status' => ucfirst((string) ($unit->status ?? 'available')),
                            'unit_type' => ucfirst((string) ($unit->unit_type ?? $property->property_type ?? 'Unit')),
                            'bedrooms' => $unit->bedrooms,
                            'bathrooms' => $unit->bathrooms,
                            'floor_area' => $unit->floor_area,
                            'image_url' => $this->resolveUnitImageUrl($unit, $property),
                        ];
                    })->values()->all(),
                ];
            })
            ->sort(function (array $left, array $right) {
                $leftKey = $left['floor_key'];
                $rightKey = $right['floor_key'];

                if ($leftKey === 'unspecified') {
                    return 1;
                }
                if ($rightKey === 'unspecified') {
                    return -1;
                }

                return ((int) $leftKey) <=> ((int) $rightKey);
            })
            ->values()
            ->all();

        return [
            'displayTerm' => $displayTerm,
            'floorGroups' => $floorGroups,
        ];
    }

    private function getDisplayTerm(Property $property): string
    {
        $type = strtolower((string) ($property->property_type ?? ''));

        return in_array($type, ['apartment', 'house'], true) ? 'Room' : 'Unit';
    }

    private function formatFloorLabel(mixed $floorKey): string
    {
        if ($floorKey === 'unspecified') {
            return 'Unspecified Floor';
        }

        return 'Floor '.(int) $floorKey;
    }

    private function resolveUnitImageUrl(Unit $unit, Property $property): ?string
    {
        if (! empty($unit->cover_image_url)) {
            return $unit->cover_image_url;
        }

        $unitGallery = $unit->gallery_urls ?? [];
        if (is_array($unitGallery) && ! empty($unitGallery[0])) {
            return $unitGallery[0];
        }

        if (! empty($property->cover_image_url)) {
            return $property->cover_image_url;
        }

        $propertyGallery = $property->gallery_urls ?? [];
        if (is_array($propertyGallery) && ! empty($propertyGallery[0])) {
            return $propertyGallery[0];
        }

        return null;
    }
}
