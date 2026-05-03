<?php

namespace Tests\Unit\Services\Landlord;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Services\Landlord\PropertyCreationUnitMediaApplicator;
use App\Services\Media\UnitMediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class PropertyCreationUnitMediaApplicatorTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_applies_uploads_to_units_in_creation_order(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $unitA = Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'Unit 1']);
        $unitB = Unit::factory()->create(['property_id' => $property->id, 'unit_number' => 'Unit 2']);

        $cover0 = UploadedFile::fake()->create('u0.jpg', 100);
        $cover1 = UploadedFile::fake()->create('u1.jpg', 100);

        $mock = Mockery::mock(UnitMediaService::class);
        $mock->shouldReceive('uploadForUnit')
            ->once()
            ->with($unitA->id, Mockery::on(fn ($f) => $f instanceof UploadedFile), [])
            ->andReturn(['cover_image' => 'path/a']);
        $mock->shouldReceive('uploadForUnit')
            ->once()
            ->with($unitB->id, Mockery::on(fn ($f) => $f instanceof UploadedFile), [])
            ->andReturn(['cover_image' => 'path/b']);
        $this->app->instance(UnitMediaService::class, $mock);

        $request = Request::create('/test', 'POST', [], [], [
            'unit_media' => [
                0 => ['cover' => $cover0],
                1 => ['cover' => $cover1],
            ],
        ]);

        $applicator = $this->app->make(PropertyCreationUnitMediaApplicator::class);
        $applicator->applyFromRequest($property->fresh(), $request);

        $this->assertSame('path/a', $unitA->fresh()->cover_image);
        $this->assertSame('path/b', $unitB->fresh()->cover_image);
    }
}
