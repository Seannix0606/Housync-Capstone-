<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_number' => fake()->unique()->bothify('###??'),
            'property_id' => Property::factory(),
            'unit_type' => 'Studio',
            'rent_amount' => fake()->randomFloat(2, 500, 5000),
            'status' => 'available',
            'leasing_type' => 'separate',
            'tenant_count' => 0,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'is_furnished' => false,
        ];
    }
}
