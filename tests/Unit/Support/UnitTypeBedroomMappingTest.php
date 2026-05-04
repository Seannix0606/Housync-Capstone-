<?php

namespace Tests\Unit\Support;

use App\Support\UnitTypeBedroomMapping;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UnitTypeBedroomMappingTest extends TestCase
{
    #[Test]
    public function default_bedrooms_for_known_slugs(): void
    {
        $this->assertSame(0, UnitTypeBedroomMapping::defaultBedroomsFor('studio'));
        $this->assertSame(1, UnitTypeBedroomMapping::defaultBedroomsFor('one_bedroom'));
        $this->assertSame(2, UnitTypeBedroomMapping::defaultBedroomsFor('two_bedroom'));
        $this->assertSame(3, UnitTypeBedroomMapping::defaultBedroomsFor('three_bedroom'));
        $this->assertSame(3, UnitTypeBedroomMapping::defaultBedroomsFor('penthouse'));
    }

    #[Test]
    public function default_bedrooms_for_unknown_type_is_null(): void
    {
        $this->assertNull(UnitTypeBedroomMapping::defaultBedroomsFor('custom_loft'));
    }

    #[Test]
    public function soft_match_allows_unknown_types_and_exact_known_pairs(): void
    {
        $this->assertTrue(UnitTypeBedroomMapping::bedroomsMatchConfiguredDefaults('custom_loft', 99));
        $this->assertTrue(UnitTypeBedroomMapping::bedroomsMatchConfiguredDefaults('studio', 0));
        $this->assertFalse(UnitTypeBedroomMapping::bedroomsMatchConfiguredDefaults('studio', 2));
    }
}
