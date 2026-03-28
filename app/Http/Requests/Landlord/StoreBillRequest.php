<?php

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'landlord';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tenant_assignment_id' => [
                'required',
                Rule::exists('tenant_assignments', 'id')
                    ->where('landlord_id', Auth::id())
                    ->where('status', 'active'),
            ],
            'type' => 'required|in:rent,electricity,water,other',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date|after_or_equal:today',
            'billing_period_start' => 'nullable|date',
            'billing_period_end' => 'nullable|date|after_or_equal:billing_period_start',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
