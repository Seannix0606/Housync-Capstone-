<?php

namespace App\Http\Controllers\Landlord;

use App\Contracts\Landlord\LandlordUnitCreationServiceContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StoreModalBulkUnitsRequest;
use App\Http\Requests\Landlord\StoreModalSingleUnitRequest;
use App\Models\Property;
use App\Models\Unit;
use App\Services\Landlord\LandlordUnitStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LandlordUnitModalController extends Controller
{
    public function __construct(
        protected LandlordUnitStatsService $unitStats
    ) {}

    public function storeSingle(
        StoreModalSingleUnitRequest $request,
        LandlordUnitCreationServiceContract $creation
    ): JsonResponse {
        $property = Property::query()->findOrFail($request->validated('property_id'));

        try {
            $unit = $creation->createSingleUnit($property, $request->validated());
            $unit->load('property');

            return response()->json([
                'success' => true,
                'message' => 'Unit created successfully.',
                'units' => [$this->serializeUnitRow($unit)],
                'stats' => $this->unitStats->statsForLandlord(Auth::user(), $this->statsScopePropertyId()),
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            Log::error('Modal single unit creation failed', ['exception' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create unit.',
            ], 500);
        }
    }

    public function storeBulk(
        StoreModalBulkUnitsRequest $request,
        LandlordUnitCreationServiceContract $creation
    ): JsonResponse {
        $property = Property::query()->findOrFail($request->validated('property_id'));

        try {
            $units = $creation->createBulkUnits($property, $request->validated());
            $units->load('property');

            return response()->json([
                'success' => true,
                'message' => $units->count().' units created successfully.',
                'units' => $units->map(fn ($unit) => $this->serializeUnitRow($unit))->values()->all(),
                'stats' => $this->unitStats->statsForLandlord(Auth::user(), $this->statsScopePropertyId()),
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            Log::error('Modal bulk unit creation failed', ['exception' => $exception]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create units.',
            ], 500);
        }
    }

    protected function serializeUnitRow(Unit $unit): array
    {
        return [
            'id' => $unit->id,
            'unit_number' => $unit->unit_number,
            'property_name' => $unit->property->name ?? '',
            'unit_type' => $unit->unit_type,
            'bedrooms' => $unit->bedrooms,
            'bathrooms' => $unit->bathrooms,
            'floor_number' => $unit->floor_number,
            'status' => $unit->status,
            'rent_amount' => $unit->rent_amount,
            'max_occupants' => $unit->max_occupants,
        ];
    }

    protected function statsScopePropertyId(): ?int
    {
        $fromBody = (int) request()->input('stats_scope_property_id', 0);
        if ($fromBody > 0) {
            return $fromBody;
        }

        $request = request();
        $fromQuery = (int) ($request->get('property') ?? $request->get('apartment') ?? 0);

        return $fromQuery > 0 ? $fromQuery : null;
    }
}
