<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Apartments',
            'slug' => fake()->slug(),
            'property_type' => 'apartment',
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'description' => fake()->paragraph(),
            'landlord_id' => User::factory()->create(['role' => 'landlord'])->id,
            'total_units' => fake()->numberBetween(5, 50),
            'status' => 'active',
            'is_active' => true,
        ];
    }
}
