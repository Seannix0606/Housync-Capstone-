<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StoreUnitRequest;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UnitController extends Controller
{
    public function units(Request $request, $propertyId = null)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();

        $sortBy = $request->get('sort', 'property_unit');

        if ($propertyId) {
            $property = $landlord->properties()->findOrFail($propertyId);
            $query = $property->units()->with('property');
            $statsQuery = $property->units();
        } else {
            $query = Unit::whereHas('property', function ($query) use ($landlord) {
                $query->where('landlord_id', $landlord->id);
            })->with('property');
            $statsQuery = Unit::whereHas('property', function ($query) use ($landlord) {
                $query->where('landlord_id', $landlord->id);
            });
        }

        // Filter by property from dropdown
        if ($request->filled('apartment') || $request->filled('property')) {
            $selectedPropertyId = (int) ($request->get('property') ?? $request->get('apartment'));
            $query->where('property_id', $selectedPropertyId);
            $statsQuery->where('property_id', $selectedPropertyId);
            $propertyId = $selectedPropertyId;
        }

        $stats = [
            'total_units' => $statsQuery->count(),
            'available_units' => (clone $statsQuery)->where('status', 'available')->count(),
            'occupied_units' => (clone $statsQuery)->where('status', 'occupied')->count(),
            'monthly_revenue' => (clone $statsQuery)->where('status', 'occupied')->sum('rent_amount') ?? 0,
        ];

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
        $properties = $landlord->properties()->orderBy('name')->get();

        // Backward compatibility
        $apartments = $properties;
        $apartmentId = $propertyId;

        return view('landlord.units', compact('units', 'apartments', 'properties', 'apartmentId', 'propertyId', 'stats'));
    }

    public function createUnit($propertyId = null)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        if ($propertyId) {
            $property = $landlord->properties()->findOrFail($propertyId);
            $apartment = $property; // Backward compatibility

            return view('landlord.create-unit', compact('apartment', 'property'));
        } else {
            $properties = $landlord->properties()->get();
            $apartments = $properties;

            return view('landlord.select-property-for-unit', compact('apartments', 'properties'));
        }
    }

    public function storeUnit(StoreUnitRequest $request, $propertyId)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $supabase = new SupabaseService;
            $filename = 'unit-'.time().'-'.uniqid().'.'.$request->file('cover_image')->getClientOriginalExtension();
            $path = 'units/'.$filename;
            $uploadResult = $supabase->uploadFile(config('services.supabase.bucket'), $path, $request->file('cover_image')->getRealPath());

            if ($uploadResult['success']) {
                $coverPath = $uploadResult['url'];
            } else {
                return back()->withInput()->with('error', 'Failed to upload cover image.');
            }
        }

        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $index => $file) {
                $supabase = new SupabaseService;
                $filename = 'unit-gallery-'.time().'-'.$index.'-'.uniqid().'.'.$file->getClientOriginalExtension();
                $path = 'units/gallery/'.$filename;
                $uploadResult = $supabase->uploadFile(config('services.supabase.bucket'), $path, $file->getRealPath());

                if ($uploadResult['success']) {
                    $galleryPaths[] = $uploadResult['url'];
                } else {
                    return back()->withInput()->with('error', 'Failed to upload gallery image '.($index + 1).': '.($uploadResult['message'] ?? 'Unknown error'));
                }
            }
        }

        $property->units()->create([
            'unit_number' => $request->unit_number,
            'unit_type' => $request->unit_type,
            'rent_amount' => $request->rent_amount,
            'status' => $request->status,
            'leasing_type' => $request->leasing_type,
            'description' => $request->description,
            'floor_area' => $request->floor_area,
            'floor_number' => $request->floor_number ?? 1,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'is_furnished' => $request->boolean('is_furnished'),
            'amenities' => $request->amenities ?? [],
            'notes' => $request->notes,
            'cover_image' => $coverPath,
            'gallery' => $galleryPaths ?: null,
        ]);

        return redirect()->route('landlord.units', $propertyId)->with('success', 'Unit created successfully.');
    }

    public function storeBulkUnits(Request $request, $propertyId)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);

        $request->validate([
            // No hard max here; user can choose any number.
            // Note: very large numbers may impact performance, but we intentionally do not cap it.
            'units_per_floor' => 'nullable|integer|min:1',
            'create_all_bedrooms' => 'nullable|boolean',
            'default_unit_type' => 'required|string|max:100',
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

    public function createMultipleUnits($propertyId)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);
        $apartment = $property; // Backward compatibility

        return view('landlord.create-multiple-units', compact('apartment', 'property'));
    }

    public function bulkEditUnits($propertyId)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);
        $apartment = $property;
        $bulkParams = session('bulk_creation_params', []);
        $existingUnitsCount = $property->units()->count();

        return view('landlord.bulk-edit-units', compact('apartment', 'property', 'bulkParams', 'existingUnitsCount'));
    }

    public function finalizeBulkUnits(Request $request, $propertyId)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);

        // Debug: Log the number of units received
        $unitsReceived = $request->input('units', []);
        Log::info('Bulk units received', [
            'property_id' => $propertyId,
            'units_count' => is_array($unitsReceived) ? count($unitsReceived) : 0,
            'post_data_size' => strlen(serialize($request->all())),
        ]);

        if (! $request->has('units') || empty($request->input('units'))) {
            return back()->with('error', 'No units data received. Please try again.');
        }

        $request->validate([
            'units' => 'required|array',
            'units.*.unit_number' => 'required|string|max:50',
            'units.*.unit_type' => 'required|string|max:100',
            'units.*.rent_amount' => 'required|numeric|min:0',
            'units.*.bedrooms' => 'required|integer|min:0',
            'units.*.bathrooms' => 'required|integer|min:1',
            'units.*.status' => 'required|in:available,maintenance',
            'units.*.leasing_type' => 'required|in:separate,inclusive',
            'units.*.max_occupants' => 'required|integer|min:1',
            'units.*.floor_number' => 'required|integer|min:1',
        ]);

        try {
            $unitsCreated = 0;
            $unitsSkipped = 0;
            $skippedUnitNumbers = [];
            $existingUnitNumbers = $property->units()->pluck('unit_number')->toArray();
            $unitsToInsert = [];
            $now = now();

            $dedupedUnits = collect($request->units)->unique('unit_number')->values()->all();

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

            \Illuminate\Support\Facades\DB::transaction(function () use ($unitsToInsert, $property) {
                if (! empty($unitsToInsert)) {
                    $chunks = array_chunk($unitsToInsert, 100);
                    foreach ($chunks as $chunk) {
                        \DB::table('units')->insert($chunk);
                    }
                }

                $property->update(['total_units' => $property->units()->count()]);
                session()->forget('bulk_creation_params');
            });

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

        try {
            $request->validate([
                'unit_number' => 'required|string|max:50|unique:units,unit_number,'.$unit->id.',id,property_id,'.$unit->property_id,
                'unit_type' => 'required|string|max:100',
                'rent_amount' => 'required|numeric|min:0',
                'status' => 'required|in:available,occupied,maintenance',
                'leasing_type' => 'required|in:separate,inclusive',
                'description' => 'nullable|string|max:1000',
                'floor_area' => 'nullable|numeric|min:0',
                'bedrooms' => 'required|integer|min:0',
                'bathrooms' => 'required|integer|min:1',
                'is_furnished' => 'nullable|boolean',
                'amenities' => 'nullable|array',
                'notes' => 'nullable|string|max:1000',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
                'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
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

            if ($request->hasFile('cover_image')) {
                $supabase = new SupabaseService;
                $filename = 'unit-'.time().'-'.uniqid().'.'.$request->file('cover_image')->getClientOriginalExtension();
                $path = 'units/'.$filename;
                $uploadResult = $supabase->uploadFile(config('services.supabase.bucket'), $path, $request->file('cover_image')->getRealPath());

                if ($uploadResult['success']) {
                    $updateData['cover_image'] = $uploadResult['url'];
                } else {
                    throw new \Exception('Failed to upload cover image: '.($uploadResult['message'] ?? 'Unknown error'));
                }
            }

            if ($request->hasFile('gallery')) {
                $supabase = new SupabaseService;
                $galleryPaths = $unit->gallery ?? [];

                foreach ($request->file('gallery') as $index => $file) {
                    $filename = 'unit-gallery-'.time().'-'.$index.'-'.uniqid().'.'.$file->getClientOriginalExtension();
                    $path = 'units/gallery/'.$filename;
                    $uploadResult = $supabase->uploadFile(config('services.supabase.bucket'), $path, $file->getRealPath());

                    if ($uploadResult['success']) {
                        $galleryPaths[] = $uploadResult['url'];
                    } else {
                        throw new \Exception('Failed to upload gallery image '.($index + 1).': '.($uploadResult['message'] ?? 'Unknown error'));
                    }
                }

                $updateData['gallery'] = array_slice($galleryPaths, 0, 12);
            }

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

    public function deleteUnit($id)
    {
        $unit = Unit::whereHas('property', function ($query) {
            $query->where('landlord_id', Auth::id());
        })->findOrFail($id);

        try {
            $activeAssignments = $unit->tenantAssignments()->whereIn('status', ['active', 'pending'])->count();

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

    public function storeApartmentUnit(Request $request, $propertyId)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);

        $request->validate([
            'unit_number' => 'required|string|max:50|unique:units,unit_number,NULL,id,property_id,'.$propertyId,
            'unit_type' => 'required|string|max:100',
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
            $unit = $property->units()->create([
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

            return response()->json(['success' => true, 'message' => 'Unit created successfully.', 'unit' => $unit]);
        } catch (\Exception $exception) {
            Log::error('Error creating unit: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to create unit.'], 500);
        }
    }
}
