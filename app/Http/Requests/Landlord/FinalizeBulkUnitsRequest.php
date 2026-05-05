<?php

namespace App\Http\Requests\Landlord;

use App\Support\UnitTypeBedroomMapping;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class FinalizeBulkUnitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! auth()->check() || auth()->user()->role !== 'landlord') {
            return false;
        }

        $propertyId = $this->route('apartmentId');

        return $propertyId !== null
            && auth()->user()->properties()->whereKey($propertyId)->exists();
    }

    protected function prepareForValidation(): void
    {
        $units = [];

        $payload = $this->input('units_payload');
        if (is_string($payload) && $payload !== '') {
            try {
                $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $units = $decoded;
                }
            } catch (\JsonException) {
                // Leave empty; validation reports missing/invalid units.
            }
        }

        if ($units === [] && is_array($this->input('units'))) {
            $units = $this->input('units');
        }

        $this->merge(['units' => $units]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $allowedTypes = UnitTypeBedroomMapping::allowedUnitTypeKeys();

        return [
            'units_expected_count' => 'nullable|integer|min:0',
            'units' => 'required|array|min:1',
            'units.*.unit_number' => 'required|string|max:50',
            'units.*.unit_type' => ['required', 'string', 'max:100', Rule::in($allowedTypes)],
            'units.*.rent_amount' => 'required|numeric|min:0',
            'units.*.bedrooms' => 'required|integer|min:0',
            'units.*.bathrooms' => 'required|integer|min:1',
            'units.*.status' => 'required|in:available,maintenance',
            'units.*.leasing_type' => 'required|in:separate,inclusive',
            'units.*.max_occupants' => 'required|integer|min:1',
            'units.*.floor_number' => 'required|integer|min:1',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'units.required' => 'No units data received. Please try again.',
            'units.min' => 'No units data received. Please try again.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $expected = (int) $this->input('units_expected_count', 0);
            /** @var array<int, mixed> $units */
            $units = $this->input('units', []);
            if ($expected > 0 && count($units) < $expected) {
                $validator->errors()->add(
                    'units',
                    'Only '.count($units)." of {$expected} units were received. Please retry; this usually means form data was truncated."
                );
            }
        });
    }
}
