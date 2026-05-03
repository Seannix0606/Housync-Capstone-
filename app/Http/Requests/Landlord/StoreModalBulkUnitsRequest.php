<?php

namespace App\Http\Requests\Landlord;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreModalBulkUnitsRequest extends FormRequest
{
    private const FIXED_DWELLING_TYPES = ['house', 'townhouse', 'duplex'];

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'landlord';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'property_id' => [
                'required',
                'integer',
                Rule::exists('properties', 'id')->where(fn ($query) => $query->where('landlord_id', auth()->id())),
            ],
            'unit_count' => ['nullable', 'integer', 'min:1', 'max:200'],
            'units_per_floor' => ['nullable', 'integer', 'min:1', 'max:200'],
            'naming_pattern' => ['required', 'string', 'max:120'],
            'default_rent' => ['required', 'numeric', 'min:0'],
            'default_status' => ['required', Rule::in(['available', 'maintenance'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $propertyId = (int) $this->input('property_id');
            $property = Property::query()->find($propertyId);
            if ($property === null) {
                return;
            }

            $hasPerFloor = $this->filled('units_per_floor');
            $hasFlatCount = $this->filled('unit_count');

            if ($hasPerFloor === $hasFlatCount) {
                $validator->errors()->add('unit_count', 'Provide either a number of units or units per floor—not both, and not neither.');

                return;
            }

            if ($hasPerFloor) {
                if (in_array($property->property_type, self::FIXED_DWELLING_TYPES, true)) {
                    $validator->errors()->add(
                        'units_per_floor',
                        'Floor-based bulk creation applies to multi-unit buildings (e.g. apartment, condominium). For houses, townhouses, and duplexes, units are defined on the property—use a flat unit count or single-unit mode.'
                    );

                    return;
                }

                $floors = max(1, (int) ($property->building_floors ?? $property->floors ?? 1));
                $count = (int) $this->input('units_per_floor') * $floors;
            } else {
                $count = (int) $this->input('unit_count');
            }

            if ($count < 1 || $count > 200) {
                $validator->errors()->add('unit_count', 'Total units must be between 1 and 200.');

                return;
            }

            $pattern = trim((string) $this->input('naming_pattern'));
            $labels = [];
            for ($i = 1; $i <= $count; $i++) {
                if (str_contains($pattern, '{n}')) {
                    $labels[] = str_replace('{n}', (string) $i, $pattern);
                } else {
                    $labels[] = trim($pattern.' '.$i);
                }
            }

            if (count($labels) !== count(array_unique($labels))) {
                $validator->errors()->add('naming_pattern', 'This naming pattern produces duplicate unit names. Adjust the pattern or count.');
            }
        });
    }
}
