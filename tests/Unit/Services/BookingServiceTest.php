<?php

namespace Tests\Unit\Services;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BookingService;
    }

    public function test_creates_booking_from_unit_id_and_sets_property_from_unit(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $unit = Unit::factory()->create([
            'property_id' => $property->id,
            'status' => 'available',
        ]);

        $booking = $this->service->createBooking(['unit_id' => $unit->id]);

        $this->assertSame($property->id, $booking->property_id);
        $this->assertSame($unit->id, $booking->unit_id);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_legacy_property_id_maps_to_first_available_unit(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => 'U-first',
            'status' => 'occupied',
        ]);
        $second = Unit::factory()->create([
            'property_id' => $property->id,
            'unit_number' => 'U-second',
            'status' => 'available',
        ]);

        $booking = $this->service->createBooking(['property_id' => $property->id]);

        $this->assertSame($second->id, $booking->unit_id);
        $this->assertSame($property->id, $booking->property_id);
    }

    public function test_property_id_picks_first_available_unit_by_id(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $firstAvailable = Unit::factory()->create([
            'property_id' => $property->id,
            'status' => 'available',
        ]);
        Unit::factory()->create([
            'property_id' => $property->id,
            'status' => 'available',
        ]);

        $booking = $this->service->createBooking(['property_id' => $property->id]);

        $this->assertSame($firstAvailable->id, $booking->unit_id);
    }

    public function test_mismatched_unit_and_property_throws(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $propertyA = Property::factory()->create(['landlord_id' => $landlord->id]);
        $propertyB = Property::factory()->create(['landlord_id' => $landlord->id]);
        $unit = Unit::factory()->create(['property_id' => $propertyA->id]);

        $this->expectException(ValidationException::class);

        $this->service->createBooking([
            'unit_id' => $unit->id,
            'property_id' => $propertyB->id,
        ]);
    }

    public function test_property_without_available_unit_throws(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        Unit::factory()->create([
            'property_id' => $property->id,
            'status' => 'occupied',
        ]);

        $this->expectException(ValidationException::class);

        $this->service->createBooking(['property_id' => $property->id]);
    }
}
