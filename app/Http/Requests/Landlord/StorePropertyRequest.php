<?php

namespace App\Http\Requests\Landlord;

use App\Contracts\Landlord\PropertyTypeUnitRulesContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class StorePropertyRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'property_type' => [
                'required',
                'string',
                Rule::in(['apartment', 'condominium', 'townhouse', 'house', 'duplex', 'others']),
            ],
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|regex:/^[0-9]+$/|max:20',
            'contact_email' => 'nullable|email|max:255',
            'amenities' => 'nullable|array',
            'property_cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'property_gallery' => 'nullable|array|max:12',
            'property_gallery.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'unit_media' => 'nullable|array',
            'unit_media.*.cover' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'unit_media.*.gallery' => 'nullable|array|max:12',
            'unit_media.*.gallery.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'unit_cover_image' => 'prohibited',
            'unit_gallery' => 'prohibited',
            'unit_gallery.*' => 'prohibited',
            'cover_image' => 'prohibited',
            'gallery' => 'prohibited',
            'gallery.*' => 'prohibited',
            'floors' => 'nullable|integer|min:1',
            'unit_count' => 'nullable|integer|min:1',
            'bedrooms' => 'nullable|integer|min:1|prohibited_if:property_type,duplex',
            'building_floors' => 'nullable|integer|min:1|max:200',
            'unit_bedrooms' => 'nullable|array',
            'unit_bedrooms.*' => 'nullable|integer|min:0|max:50',
            'unit_stories' => 'nullable|array',
            'unit_stories.*' => 'nullable|integer|min:1|max:50',
            'year_built' => 'nullable|integer|min:1900|max:'.date('Y'),
            'parking_spaces' => 'nullable|integer|min:0',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'property_type.required' => 'Please choose a property type.',
            'property_type.in' => 'The selected property type is not valid. Choose apartment, condominium, townhouse, single family house, duplex, or other.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $type = $this->input('property_type');
            $effectiveCount = $this->effectiveUnitCountFromInput();

            if ($type === 'house') {
                if ($effectiveCount !== null && $effectiveCount !== 1) {
                    $field = $this->filled('floors') ? 'floors' : 'unit_count';
                    $validator->errors()->add(
                        $field,
                        'A single family house has exactly one unit. Leave units/floors blank or set the value to 1.'
                    );
                }
            } elseif ($type === 'duplex') {
                if ($effectiveCount !== null && $effectiveCount !== 2) {
                    $field = $this->filled('floors') ? 'floors' : 'unit_count';
                    $validator->errors()->add(
                        $field,
                        'A duplex has exactly two units. Leave this blank or enter 2.'
                    );
                }
            } elseif (in_array($type, ['apartment', 'condominium'], true)) {
                if ($effectiveCount === null) {
                    $validator->errors()->add(
                        'floors',
                        'Enter how many units this building has (minimum 2).'
                    );
                } elseif ($effectiveCount < 2) {
                    $validator->errors()->add(
                        'floors',
                        'Apartment and condominium properties must have at least 2 units.'
                    );
                }
            } elseif ($type === 'townhouse') {
                if ($effectiveCount !== null && $effectiveCount !== 1) {
                    $field = $this->filled('floors') ? 'floors' : 'unit_count';
                    $validator->errors()->add(
                        $field,
                        'A townhouse is one dwelling with exactly one unit. Leave unit count blank or enter 1.'
                    );
                }
            } elseif ($type === 'others') {
                if ($effectiveCount !== null && $effectiveCount < 1) {
                    $field = $this->filled('floors') ? 'floors' : 'unit_count';
                    $validator->errors()->add(
                        $field,
                        'This property type requires at least one unit when a unit count is provided.'
                    );
                }
            }

            $this->validateDwellingBedroomsAndBuildingFloors($validator);
            $this->validateUnitMediaIndicesWithinUnitCount($validator);
        });
    }

    /**
     * Physical stories + typical bedrooms for single-dwelling-focused property types.
     */
    protected function validateDwellingBedroomsAndBuildingFloors(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $type = $this->input('property_type');
        if (! in_array($type, ['house', 'duplex', 'townhouse'], true)) {
            return;
        }

        $unitStoriesInput = $this->input('unit_stories');
        if ($type !== 'duplex' && is_array($unitStoriesInput)) {
            foreach ($unitStoriesInput as $slotValue) {
                if ($slotValue !== null && $slotValue !== '') {
                    $validator->errors()->add(
                        'unit_stories',
                        'Per-unit story counts on this form apply only to duplex properties.'
                    );
                    break;
                }
            }
        }

        if ($type !== 'duplex' && ! $this->filled('building_floors')) {
            $validator->errors()->add(
                'building_floors',
                'Enter how many above-grade stories (floors/levels) the building has.'
            );
        }

        if ($type === 'duplex') {
            foreach ([0, 1] as $idx) {
                $v = $this->input("unit_bedrooms.{$idx}", '__missing__');
                if ($v === '__missing__' || $v === '' || $v === null || ! is_numeric($v)) {
                    $validator->errors()->add(
                        "unit_bedrooms.{$idx}",
                        'Enter the number of bedrooms for unit '.($idx + 1).'.'
                    );
                }
                $sv = $this->input("unit_stories.{$idx}", '__missing__');
                if ($sv === '__missing__' || $sv === '' || $sv === null || ! is_numeric($sv)) {
                    $validator->errors()->add(
                        "unit_stories.{$idx}",
                        'Enter how many interior stories (levels) unit '.($idx + 1).' has.'
                    );
                } elseif ((int) $sv < 1) {
                    $validator->errors()->add(
                        "unit_stories.{$idx}",
                        'Unit '.($idx + 1).' must have at least one interior story.'
                    );
                }
            }

            return;
        }

        if ($this->has('unit_bedrooms') && is_array($this->input('unit_bedrooms')) && $this->input('unit_bedrooms') !== []) {
            $validator->errors()->add(
                'unit_bedrooms',
                'Per-unit bedroom counts on this form apply only to duplex properties.'
            );
        }

        if (! $this->filled('bedrooms')) {
            $validator->errors()->add(
                'bedrooms',
                'Enter the typical number of bedrooms for each dwelling (use per-unit details in My Units if units differ).'
            );
        }
    }

    /**
     * Ensure unit_media slots reference only units that will exist after create (0 … count-1).
     */
    protected function validateUnitMediaIndicesWithinUnitCount(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $unitMedia = $this->input('unit_media');
        if (! is_array($unitMedia) || $unitMedia === []) {
            return;
        }

        $expected = $this->resolveExpectedInitialUnitCountForMediaValidation();
        if ($expected === null) {
            return;
        }

        foreach (array_keys($unitMedia) as $key) {
            $index = filter_var($key, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($index === false) {
                $validator->errors()->add('unit_media', 'Invalid unit photo slot.');

                continue;
            }
            if ($index >= $expected) {
                $validator->errors()->add(
                    "unit_media.{$key}",
                    'Unit photos are only available for units being created with this property ('.$expected.' unit'.($expected === 1 ? '' : 's').').'
                );
            }
        }
    }

    protected function resolveExpectedInitialUnitCountForMediaValidation(): ?int
    {
        $type = $this->input('property_type');
        if (! is_string($type) || $type === '') {
            return null;
        }

        $fromFloors = $this->filled('floors') ? (int) $this->input('floors') : null;
        $fromUnitCount = $this->filled('unit_count') ? (int) $this->input('unit_count') : null;

        try {
            return app(PropertyTypeUnitRulesContract::class)->resolveInitialUnitCount($type, $fromFloors, $fromUnitCount);
        } catch (ValidationException) {
            return null;
        }
    }

    /**
     * Mirrors PropertyService: floors take precedence over unit_count when both are set.
     */
    protected function effectiveUnitCountFromInput(): ?int
    {
        if ($this->filled('floors')) {
            return (int) $this->input('floors');
        }

        if ($this->filled('unit_count')) {
            return (int) $this->input('unit_count');
        }

        return null;
    }
}
