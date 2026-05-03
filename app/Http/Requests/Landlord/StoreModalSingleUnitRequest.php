<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModalSingleUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'landlord';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $propertyId = $this->input('property_id');

        return [
            'property_id' => [
                'required',
                'integer',
                Rule::exists('properties', 'id')->where(fn ($query) => $query->where('landlord_id', auth()->id())),
            ],
            'unit_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'unit_number')->where(fn ($query) => $query->where('property_id', $propertyId)),
            ],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['available', 'maintenance'])],
        ];
    }
}
