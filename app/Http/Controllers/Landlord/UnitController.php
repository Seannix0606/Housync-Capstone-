<?php

namespace App\Http\Controllers\Landlord;

use App\Contracts\Landlord\PropertyTypeUnitRulesContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\FinalizeBulkUnitsRequest;
use App\Http\Requests\Landlord\StoreUnitRequest;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Services\Landlord\LandlordUnitStatsService;
use App\Services\Media\UnitMediaService;
use App\Support\UnitTypeBedroomMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UnitController extends Controller
{
    public function units(Request $request, LandlordUnitStatsService $unitStats, PropertyTypeUnitRulesContract $unitRules, $propertyId = null)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();

        $sortBy = $request->get('sort', 'property_unit');

        if ($propertyId) {
            $property = $landlord->properties()->findOrFail($propertyId);
            $query = $property->units()->with('property');
        } else {
            $query = Unit::whereHas('property', function ($query) use ($landlord) {
                $query->where('landlord_id', $landlord->id);
            })->with('property');
        }

        // Filter by property from dropdown
        if ($request->filled('apartment') || $request->filled('property')) {
            $selectedPropertyId = (int) ($request->get('property') ?? $request->get('apartment'));
            $query->where('property_id', $selectedPropertyId);
            $propertyId = $selectedPropertyId;
        }

        $statsScopePropertyId = null;
        if ($request->filled('apartment') || $request->filled('property')) {
            $statsScopePropertyId = (int) ($request->get('property') ?? $request->get('apartment'));
        } elseif ($propertyId) {
            $statsScopePropertyId = (int) $propertyId;
        }

        $stats = $unitStats->statsForLandlord($landlord, $statsScopePropertyId);

        switch ($sortBy) {
            case 'property_unit':
                $query->join('properties', 'units.property_id', '=', 'properties.id')
                    ->orderBy('properties.name')
                    ->orderBy('units.floor_number')
                    ->orderByRaw('CAST(units.unit_number AS UNSIGNED)')
                    ->orderBy('units.unit_number')
                    ->select('units.*');
                break;
            case 'property':
                $query->join('properties', 'units.property_id', '=', 'properties.id')
                    ->orderBy('properties.name')
                    ->select('units.*');
                break;
            case 'unit_number':
                $query->orderByRaw('CAST(unit_number AS UNSIGNED)')->orderBy('unit_number');
                break;
            case 'floor':
                $query->orderBy('floor_number')->orderByRaw('CAST(unit_number AS UNSIGNED)');
                break;
            case 'status':
                $query->orderByRaw("FIELD(status, 'available', 'occupied', 'maintenance')");
                break;
            case 'rent':
                $query->orderByDesc('rent_amount');
                break;
            case 'newest':
                $query->latest();
                break;
            default:
                $query->join('properties', 'units.property_id', '=', 'properties.id')
                    ->orderBy('properties.name')
                    ->orderByRaw('CAST(units.unit_number AS UNSIGNED)')
                    ->select('units.*');
        }

        $units = $query->paginate(20);
        $properties = $landlord->properties()->withCount('units')->orderBy('name')->get();

        // Backward compatibility
        $apartments = $properties;
        $apartmentId = $propertyId;

        $filterPropertyId = (int) ($request->get('property') ?? $request->get('apartment') ?? 0);
        if ($filterPropertyId === 0 && $propertyId !== null) {
            $filterPropertyId = (int) $propertyId;
        }

        $addUnitBlockedMessage = null;
        if ($filterPropertyId > 0) {
            $filtered = $properties->firstWhere('id', $filterPropertyId);
            if ($filtered !== null) {
                $maxUnits = $unitRules->maximumUnitsForType($filtered->property_type);
                if ($maxUnits !== null && $filtered->units_count >= $maxUnits) {
                    $addUnitBlockedMessage = match ($filtered->property_type) {
                        'duplex' => 'This duplex already has two units. Edit those units in My Units, or create another duplex property if you need an additional two-unit building.',
                        'townhouse' => 'A townhouse is one dwelling with a single unit. Edit that unit here or change the property type if this listing should include more rentals.',
                        'house' => 'A single family house has one unit. Edit that unit here or change the property type if this listing should include more rentals.',
                        default => 'This property has reached the maximum number of units for its type.',
                    };
                }
            }
        }

        return view('landlord.units', compact('units', 'apartments', 'properties', 'apartmentId', 'propertyId', 'stats', 'unitRules', 'addUnitBlockedMessage'));
    }

    public function createUnit(PropertyTypeUnitRulesContract $unitRules, $propertyId = null)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        if ($propertyId) {
            $property = $landlord->properties()->findOrFail($propertyId);
            try {
                $unitRules->assertMayAddUnits($property, 1);
            } catch (ValidationException $exception) {
                return redirect()
                    ->route('landlord.units', ['apartment' => $property->id])
                    ->withErrors($exception->errors());
            }
            $apartment = $property; // Backward compatibility

            return view('landlord.create-unit', compact('apartment', 'property'));
        } else {
            $properties = $landlord->properties()->withCount('units')->orderBy('name')->get();
            $apartments = $properties;

            return view('landlord.select-property-for-unit', compact('apartments', 'properties', 'unitRules'));
        }
    }

    public function storeUnit(StoreUnitRequest $request, $propertyId, UnitMediaService $unitMediaService, PropertyTypeUnitRulesContract $unitRules)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);

        try {
            $unit = DB::transaction(function () use ($request, $property, $unitMediaService, $unitRules) {
                $locked = Property::query()->whereKey($property->id)->lockForUpdate()->firstOrFail();
                $unitRules->assertMayAddUnits($locked, 1);

                $unit = $property->units()->create([
                    'unit_number' => $request->unit_number,
                    'unit_type' => $request->unit_type,
                    'rent_amount' => $request->rent_amount,
                    'status' => $request->status,
                    'leasing_type' => $request->leasing_type,
                    'description' => $request->description,
                    'floor_area' => $request->floor_area,
                    'floor_number' => $request->floor_number ?? 1,
                    'unit_stories' => $request->filled('unit_stories') ? (int) $request->input('unit_stories') : null,
                    'bedrooms' => $request->bedrooms,
                    'bathrooms' => $request->bathrooms,
                    'is_furnished' => $request->boolean('is_furnished'),
                    'amenities' => $request->amenities ?? [],
                    'notes' => $request->notes,
                ]);

                $mediaPayload = $unitMediaService->uploadForUnit(
                    $unit->id,
                    $request->file('unit_cover_image'),
                    $request->file('unit_gallery', [])
                );

                if (! empty($mediaPayload)) {
                    $unit->update($mediaPayload);
                }

                return $unit;
            });
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        return redirect()->route('landlord.units', $propertyId)->with('success', 'Unit created successfully.');
    }

    public function storeBulkUnits(Request $request, $propertyId, PropertyTypeUnitRulesContract $unitRules)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);

        try {
            $unitRules->assertMayAddUnits($property, 1);
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        $request->validate([
            // No hard max here; user can choose any number.
            // Note: very large numbers may impact performance, but we intentionally do not cap it.
            'units_per_floor' => 'nullable|integer|min:1',
            'create_all_bedrooms' => 'nullable|boolean',
            'default_unit_type' => ['required', 'string', 'max:100', Rule::in(UnitTypeBedroomMapping::allowedUnitTypeKeys())],
            'default_rent' => 'required|numeric|min:0',
            'default_bedrooms' => 'required|integer|min:0',
            'default_bathrooms' => 'required|integer|min:1',
        ]);

        session([
            'bulk_creation_params' => [
                'property_id' => $propertyId,
                'creation_type' => 'bulk',
                'units_per_floor' => $request->units_per_floor,
                'create_all_bedrooms' => $request->create_all_bedrooms,
                'default_unit_type' => $request->default_unit_type,
                'default_rent' => $request->default_rent,
                'default_bedrooms' => $request->default_bedrooms,
                'default_bathrooms' => $request->default_bathrooms,
            ],
        ]);

        return redirect()->route('landlord.bulk-edit-units', $propertyId);
    }

    public function createMultipleUnits($propertyId, PropertyTypeUnitRulesContract $unitRules)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);
        try {
            $unitRules->assertMayAddUnits($property, 1);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('landlord.units', ['apartment' => $property->id])
                ->withErrors($exception->errors());
        }
        $apartment = $property; // Backward compatibility

        return view('landlord.create-multiple-units', compact('apartment', 'property'));
    }

    public function bulkEditUnits($propertyId, PropertyTypeUnitRulesContract $unitRules)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->withCount('units')->with('units')->findOrFail($propertyId);
        $apartment = $property;
        $bulkParams = session('bulk_creation_params', []);
        $existingUnitsCount = $property->units_count;

        $maxUnits = $unitRules->maximumUnitsForType($property->property_type);
        $bulkNewUnitsRemaining = $maxUnits === null ? null : max(0, $maxUnits - $existingUnitsCount);

        return view('landlord.bulk-edit-units', compact('apartment', 'property', 'bulkParams', 'existingUnitsCount', 'bulkNewUnitsRemaining'));
    }

    public function finalizeBulkUnits(FinalizeBulkUnitsRequest $request, $propertyId, PropertyTypeUnitRulesContract $unitRules)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);

        /** @var array<int, array<string, mixed>> $units */
        $units = $request->validated('units');

        try {
            $unitsCreated = 0;
            $unitsSkipped = 0;
            $skippedUnitNumbers = [];
            $existingUnitNumbers = $property->units()->pluck('unit_number')->toArray();
            $unitsToInsert = [];
            $now = now();

            $dedupedUnits = collect($units)->unique('unit_number')->values()->all();

            foreach ($dedupedUnits as $unitData) {
                if (in_array($unitData['unit_number'], $existingUnitNumbers)) {
                    $unitsSkipped++;
                    $skippedUnitNumbers[] = $unitData['unit_number'];

                    continue;
                }

                $isFurnished = isset($unitData['is_furnished']) &&
                    ($unitData['is_furnished'] === 'true' || $unitData['is_furnished'] === true || $unitData['is_furnished'] === '1');

                $unitsToInsert[] = [
                    'property_id' => $propertyId,
                    'unit_number' => $unitData['unit_number'],
                    'unit_type' => $unitData['unit_type'],
                    'rent_amount' => $unitData['rent_amount'],
                    'status' => $unitData['status'],
                    'leasing_type' => $unitData['leasing_type'],
                    'bedrooms' => $unitData['bedrooms'],
                    'bathrooms' => $unitData['bathrooms'],
                    'tenant_count' => 0,
                    'max_occupants' => $unitData['max_occupants'],
                    'floor_number' => $unitData['floor_number'],
                    'description' => "Customized unit {$unitData['unit_number']}",
                    'amenities' => json_encode([]),
                    'is_furnished' => $isFurnished,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $unitsCreated++;
            }

            try {
                DB::transaction(function () use ($unitsToInsert, $propertyId, $unitRules) {
                    $locked = Property::query()->whereKey($propertyId)->lockForUpdate()->firstOrFail();
                    $unitRules->assertMayAddUnits($locked, count($unitsToInsert));

                    if (! empty($unitsToInsert)) {
                        $chunks = array_chunk($unitsToInsert, 100);
                        foreach ($chunks as $chunk) {
                            DB::table('units')->insert($chunk);
                        }
                    }

                    $locked->update(['total_units' => $locked->units()->count()]);
                    session()->forget('bulk_creation_params');
                });
            } catch (ValidationException $exception) {
                return back()->withInput()->withErrors($exception->errors());
            }

            // Build success message with skipped unit info
            $message = "Successfully created {$unitsCreated} units!";
            if ($unitsSkipped > 0) {
                $message .= " ({$unitsSkipped} units skipped - already exist: ".implode(', ', array_slice($skippedUnitNumbers, 0, 10));
                if (count($skippedUnitNumbers) > 10) {
                    $message .= '...';
                }
                $message .= ')';
            }

            return redirect()->route('landlord.units', $propertyId)->with('success', $message);

        } catch (\Exception $exception) {
            Log::error('Error finalizing bulk units: '.$exception->getMessage());

            return back()->withInput()->with('error', 'Failed to create units. Please try again.');
        }
    }

    public function updateUnit(Request $request, $id)
    {
        $unit = Unit::whereHas('property', function ($query) {
            $query->where('landlord_id', Auth::id());
        })->findOrFail($id);

        if ($request->has('unit_stories') && $request->input('unit_stories') === '') {
            $request->merge(['unit_stories' => null]);
        }

        try {
            $request->validate([
                'unit_number' => 'required|string|max:50|unique:units,unit_number,'.$unit->id.',id,property_id,'.$unit->property_id,
                'unit_type' => 'required|string|max:100',
                'rent_amount' => 'required|numeric|min:0',
                'status' => 'required|in:available,occupied,maintenance',
                'leasing_type' => 'required|in:separate,inclusive',
                'description' => 'nullable|string|max:1000',
                'floor_area' => 'nullable|numeric|min:0',
                'unit_stories' => 'nullable|integer|min:1|max:50',
                'bedrooms' => 'required|integer|min:0',
                'bathrooms' => 'required|integer|min:1',
                'is_furnished' => 'nullable|boolean',
                'amenities' => 'nullable|array',
                'notes' => 'nullable|string|max:1000',
                'unit_cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
                'unit_gallery' => 'nullable|array|max:12',
                'unit_gallery.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
                'property_cover_image' => 'prohibited',
                'property_gallery' => 'prohibited',
                'property_gallery.*' => 'prohibited',
                'cover_image' => 'prohibited',
                'gallery' => 'prohibited',
                'gallery.*' => 'prohibited',
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $exception->errors()], 422);
            }
            throw $exception;
        }

        try {
            $updateData = [
                'unit_number' => $request->unit_number,
                'unit_type' => $request->unit_type,
                'rent_amount' => $request->rent_amount,
                'status' => $request->status,
                'leasing_type' => $request->leasing_type,
                'description' => $request->description,
                'floor_area' => $request->floor_area,
                'bedrooms' => $request->bedrooms,
                'bathrooms' => $request->bathrooms,
                'is_furnished' => $request->boolean('is_furnished'),
                'amenities' => $request->amenities ?? [],
                'notes' => $request->notes,
            ];

            if ($request->has('unit_stories')) {
                $rawStories = $request->input('unit_stories');
                $updateData['unit_stories'] = ($rawStories === '' || $rawStories === null)
                    ? null
                    : (int) $rawStories;
            }

            $unitMediaService = app(UnitMediaService::class);
            $mediaPayload = $unitMediaService->uploadForUnit(
                $unit->id,
                $request->file('unit_cover_image'),
                $request->file('unit_gallery', [])
            );
            $updateData = array_merge($updateData, $mediaPayload);

            $unit->update($updateData);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Unit updated successfully.', 'unit' => $unit->fresh()]);
            }

            return redirect()->route('landlord.units')->with('success', 'Unit updated successfully.');
        } catch (\Exception $exception) {
            Log::error('Error updating unit: '.$exception->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to update unit.'], 500);
            }

            return back()->with('error', 'Failed to update unit. Please try again.');
        }
    }

    public function deleteUnit($id, PropertyTypeUnitRulesContract $unitRules)
    {
        $unit = Unit::whereHas('property', function ($query) {
            $query->where('landlord_id', Auth::id());
        })->findOrFail($id);

        try {
            $unitRules->assertDeletingUnitAllowed($unit);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        try {
            $activeAssignments = $unit->tenantAssignments()->whereIn('status', ['active', 'pending', 'pending_approval'])->count();

            if ($activeAssignments > 0) {
                return back()->with('error', 'Cannot delete unit with active tenant assignments.');
            }

            $unitNumber = $unit->unit_number;
            $unit->tenantAssignments()->delete();
            $unit->delete();

            return back()->with('success', "Unit '{$unitNumber}' deleted successfully.");
        } catch (\Exception $exception) {
            Log::error('Error deleting unit: '.$exception->getMessage());

            return back()->with('error', 'Failed to delete unit. Please try again.');
        }
    }

    public function getUnitDetails($id)
    {
        $unit = Unit::whereHas('property', function ($query) {
            $query->where('landlord_id', Auth::id());
        })->with(['property', 'tenantAssignments.tenant'])->findOrFail($id);

        $currentAssignment = $unit->tenantAssignments()->where('status', 'active')->with('tenant')->first();

        return response()->json([
            'id' => $unit->id,
            'unit_number' => $unit->unit_number,
            'property_name' => $unit->property->name,
            'property_id' => $unit->property->id,
            'unit_type' => $unit->unit_type,
            'rent_amount' => $unit->rent_amount,
            'status' => $unit->status,
            'leasing_type' => $unit->leasing_type,
            'bedrooms' => $unit->bedrooms,
            'bathrooms' => $unit->bathrooms,
            'max_occupants' => $unit->max_occupants,
            'floor_number' => $unit->floor_number,
            'unit_stories' => $unit->unit_stories,
            'floor_area' => $unit->floor_area,
            'is_furnished' => $unit->is_furnished,
            'amenities' => $unit->amenities ?? [],
            'description' => $unit->description,
            'notes' => $unit->notes,
            'cover_image_url' => $unit->cover_image_url,
            'gallery_urls' => $unit->gallery_urls ?? [],
            'current_tenant' => $currentAssignment ? [
                'id' => $currentAssignment->tenant->id,
                'name' => $currentAssignment->tenant->name,
                'email' => $currentAssignment->tenant->email,
            ] : null,
        ]);
    }

    public function storeApartmentUnit(Request $request, $propertyId, PropertyTypeUnitRulesContract $unitRules)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);

        $request->validate([
            'unit_number' => 'required|string|max:50|unique:units,unit_number,NULL,id,property_id,'.$propertyId,
            'unit_type' => ['required', 'string', 'max:100', Rule::in(UnitTypeBedroomMapping::allowedUnitTypeKeys())],
            'rent_amount' => 'required|numeric|min:0',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:1',
            'max_occupants' => 'required|integer|min:1',
            'floor_number' => 'nullable|integer|min:1',
            'floor_area' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'amenities' => 'nullable|array',
        ]);

        try {
            $unit = DB::transaction(function () use ($property, $request, $unitRules) {
                $locked = Property::query()->whereKey($property->id)->lockForUpdate()->firstOrFail();
                $unitRules->assertMayAddUnits($locked, 1);

                return $property->units()->create([
                    'unit_number' => $request->unit_number,
                    'unit_type' => $request->unit_type,
                    'rent_amount' => $request->rent_amount,
                    'status' => 'available',
                    'leasing_type' => 'separate',
                    'bedrooms' => $request->bedrooms,
                    'bathrooms' => $request->bathrooms,
                    'tenant_count' => 0,
                    'max_occupants' => $request->max_occupants,
                    'floor_number' => $request->floor_number ?? 1,
                    'floor_area' => $request->floor_area,
                    'description' => $request->description,
                    'amenities' => $request->amenities ?? [],
                    'is_furnished' => in_array('furnished', $request->amenities ?? []),
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Unit created successfully.', 'unit' => $unit]);
        } catch (ValidationException $exception) {
            return response()->json(['success' => false, 'message' => collect($exception->errors())->flatten()->first()], 422);
        } catch (\Exception $exception) {
            Log::error('Error creating unit: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to create unit.'], 500);
        }
    }
}
