<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreModalBulkUnitsRequest extends FormRequest
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
        return [
            'property_id' => [
                'required',
                'integer',
                Rule::exists('properties', 'id')->where(fn ($query) => $query->where('landlord_id', auth()->id())),
            ],
            'unit_count' => ['required', 'integer', 'min:1', 'max:200'],
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

            $pattern = trim((string) $this->input('naming_pattern'));
            $count = (int) $this->input('unit_count');

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
