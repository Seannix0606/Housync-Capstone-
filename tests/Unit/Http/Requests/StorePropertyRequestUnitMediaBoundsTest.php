<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Landlord\StorePropertyRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePropertyRequestUnitMediaBoundsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unit_media_index_must_be_below_initial_unit_count(): void
    {
        User::factory()->create(['role' => 'landlord']);
        $this->be(User::query()->first());

        $request = new StorePropertyRequest;
        $request->merge([
            'name' => 'Test Prop',
            'property_type' => 'duplex',
            'address' => '123 St',
            'floors' => 2,
            'building_floors' => 2,
            'unit_bedrooms' => [0 => 2, 1 => 2],
            'unit_stories' => [0 => 1, 1 => 1],
            'unit_media' => [
                2 => ['_keep' => 1],
            ],
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('unit_media.2'));
    }
}
