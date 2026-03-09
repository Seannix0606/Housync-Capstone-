<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;

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
                'contact_phone' => preg_replace('/[^0-9]/', '', $this->contact_phone)
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
            'property_type' => 'required|string|in:apartment,condominium,townhouse,house,duplex,others',
            'address' => 'required|string|max:500',
            'description' => 'nullable|string|max:1000',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|regex:/^[0-9]+$/|max:20',
            'contact_email' => 'nullable|email|max:255',
            'amenities' => 'nullable|array',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'floors' => 'nullable|integer|min:1',
            'bedrooms' => 'nullable|integer|min:1',
        ];
    }
}
