<?php

namespace App\Http\Requests\Landlord;

use App\Contracts\Landlord\PropertyTypeUnitRulesContract;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Must be logged in and be a landlord
        return auth()->check() && auth()->user()->role === 'landlord';
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize phone number - remove all non-digit characters
        if ($this->has('contact_phone') && $this->contact_phone) {
            $this->merge([
                'contact_phone' => preg_replace('/[^0-9]/', '', $this->contact_phone),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $propertyId = $this->route('id');
        $currentUnitCount = 0;

        if ($propertyId) {
            $property = \App\Models\Property::find($propertyId);
            if ($property) {
                $currentUnitCount = $property->units()->count();
            }
        }

        $type = $this->input('property_type');
        $totalUnitsRules = ['required', 'integer'];
        if (in_array($type, ['house', 'townhouse'], true)) {
            $totalUnitsRules[] = 'in:1';
        } elseif ($type === 'duplex') {
            $totalUnitsRules[] = 'in:2';
        } else {
            $totalUnitsRules[] = 'min:'.max(1, $currentUnitCount);
        }

        return [
            'name' => 'required|string|max:255',
            'property_type' => 'required|string|in:apartment,condominium,townhouse,house,duplex,others',
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'total_units' => $totalUnitsRules,
            'floors' => 'nullable|integer|min:1',
            'bedrooms' => 'nullable|integer|min:1',
            'building_floors' => 'nullable|integer|min:1|max:200',
            'year_built' => 'nullable|integer|min:1900|max:'.date('Y'),
            'parking_spaces' => 'nullable|integer|min:0',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|regex:/^[0-9]+$/|max:20',
            'contact_email' => 'nullable|email|max:255',
            'amenities' => 'nullable|array',
            'status' => 'required|in:active,inactive,maintenance',
            'property_cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'property_gallery' => 'nullable|array|max:12',
            'property_gallery.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'unit_cover_image' => 'prohibited',
            'unit_gallery' => 'prohibited',
            'unit_gallery.*' => 'prohibited',
            'cover_image' => 'prohibited',
            'gallery' => 'prohibited',
            'gallery.*' => 'prohibited',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $propertyId = $this->route('id');
            if (! $propertyId) {
                return;
            }

            $property = Property::query()->find($propertyId);
            if (! $property) {
                return;
            }

            $rules = app(PropertyTypeUnitRulesContract::class);

            try {
                $rules->assertUpdateCompatibleWithExistingUnits(
                    $property,
                    (string) $this->input('property_type'),
                    (int) $this->input('total_units')
                );
            } catch (\Illuminate\Validation\ValidationException $exception) {
                foreach ($exception->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add($field, $message);
                    }
                }
            }
        });
    }
}
