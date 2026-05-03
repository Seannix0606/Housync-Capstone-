<?php

namespace App\Contracts\Landlord;

use App\Models\Property;
use App\Models\Unit;
use Illuminate\Support\Collection;

/**
 * Landlord-facing unit creation from modal flows (single + bulk).
 */
interface LandlordUnitCreationServiceContract
{
    /**
     * @param  array{unit_number: string, rent_amount: numeric-string|float|int, status: string}  $payload
     */
    public function createSingleUnit(Property $property, array $payload): Unit;

    /**
     * @param  array{unit_count: int, naming_pattern: string, default_rent: numeric-string|float|int, default_status: string}  $payload
     * @return Collection<int, Unit>
     */
    public function createBulkUnits(Property $property, array $payload): Collection;
}
