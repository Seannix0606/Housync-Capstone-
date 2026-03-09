<?php

namespace App\Http\Controllers;

use App\Http\Requests\Landlord\StorePropertyRequest;
use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\TenantAssignment;
use App\Models\Unit;
use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Services\SupabaseService;

class LandlordController extends Controller
{
    public function dashboard()
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user()->load('landlordProfile');

        $stats = [
            'total_properties' => $landlord->properties()->count(),
            'total_units' => Unit::whereHas('property', function ($query) use ($landlord) {
                $query->where('landlord_id', $landlord->id);
            })->count(),
            'occupied_units' => Unit::whereHas('property', function ($query) use ($landlord) {
                $query->where('landlord_id', $landlord->id);
            })->where('status', 'occupied')->count(),
            'available_units' => Unit::whereHas('property', function ($query) use ($landlord) {
                $query->where('landlord_id', $landlord->id);
            })->where('status', 'available')->count(),
            'total_revenue' => Unit::whereHas('property', function ($query) use ($landlord) {
                $query->where('landlord_id', $landlord->id);
            })->where('status', 'occupied')->sum('rent_amount'),
        ];

        $properties = $landlord->properties()->with('units')->latest()->take(5)->get();
        $recentUnits = Unit::whereHas('property', function ($query) use ($landlord) {
            $query->where('landlord_id', $landlord->id);
        })->with('property')->latest()->take(10)->get();

        // Backward compatibility - pass as both names
        $apartments = $properties;

        return view('landlord.dashboard', compact('stats', 'properties', 'apartments', 'recentUnits'));
    }

    public function apartments(Request $request)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $query = $landlord->properties()->with('units');

        // Sorting
        $sortBy = $request->get('sort', 'name');

        switch ($sortBy) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'units':
                $query->withCount('units')->orderByDesc('units_count');
                break;
            case 'occupancy':
                $query->orderBy('name');
                break;
            case 'newest':
                $query->latest();
                break;
            default:
                $query->orderBy('name');
        }

        $properties = $query->paginate(15);

        // Calculate stats
        $totalUnits = $properties->sum(function ($prop) {
            return $prop->units->count();
        });
        $occupiedUnits = $properties->sum(function ($prop) {
            return $prop->units->where('status', 'occupied')->count();
        });
        $monthlyRevenue = $properties->sum(function ($prop) {
            return $prop->units->where('status', 'occupied')->sum('rent_amount');
        });

        // Backward compatibility
        $apartments = $properties;

        return view('landlord.apartments', compact('apartments', 'properties', 'totalUnits', 'occupiedUnits', 'monthlyRevenue'));
    }

    public function createApartment()
    {
        return view('landlord.create-apartment');
    }

    public function storeApartment(StorePropertyRequest $request)
    {
        Log::info('Property creation request received', [
            'data' => $request->all(),
            'method' => $request->method(),
            'url' => $request->url(),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'property_type' => 'required|string|in:apartment,condominium,townhouse,house,duplex,others',
            'address' => 'required|string|max:500',
            'description' => 'nullable|string|max:1000',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|regex:/^[0-9]+$/|max:20',
            'contact_email' => 'nullable|email|max:255',
            'amenities' => 'nullable|array',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'floors' => 'nullable|integer|min:1',
            'bedrooms' => 'nullable|integer|min:1',
        ]);

        try {
            $coverPath = null;
            if ($request->hasFile('cover_image')) {
                $supabase = new SupabaseService();
                $filename = 'property-' . time() . '-' . uniqid() . '.' . $request->file('cover_image')->getClientOriginalExtension();
                $path = 'properties/' . $filename;
                $uploadResult = $supabase->uploadFile('house-sync', $path, $request->file('cover_image')->getRealPath());
                
                if ($uploadResult['success']) {
                    $coverPath = $uploadResult['url'];
                } else {
                    Log::error('Failed to upload cover image', ['result' => $uploadResult]);
                    throw new \Exception('Failed to upload cover image: ' . ($uploadResult['message'] ?? 'Unknown error'));
                }
            }

            $galleryPaths = [];
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $index => $file) {
                    $supabase = new SupabaseService();
                    $filename = 'property-gallery-' . time() . '-' . $index . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = 'properties/gallery/' . $filename;
                    $uploadResult = $supabase->uploadFile('house-sync', $path, $file->getRealPath());
                    
                    if ($uploadResult['success']) {
                        $galleryPaths[] = $uploadResult['url'];
                    }
                }
            }

            /** @var \App\Models\User $landlord */
            $landlord = Auth::user();

            $floors = $request->property_type === 'house' ? null : $request->floors;
            $bedrooms = $request->property_type === 'house' ? $request->bedrooms : null;

            $property = $landlord->properties()->create([
                'name' => $request->name,
                'property_type' => $request->property_type,
                'address' => $request->address,
                'description' => $request->description,
                'total_units' => 0,
                'floors' => $floors,
                'bedrooms' => $bedrooms,
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
                'amenities' => $request->amenities ?? [],
                'status' => 'active',
                'cover_image' => $coverPath,
                'gallery' => $galleryPaths ?: null,
            ]);

            $successMessage = $request->property_type === 'house'
                ? "House created successfully! You can now add bedrooms as units from the 'My Units' page."
                : "Property created successfully! You can now add units from the 'My Units' page.";

            return redirect()->route('landlord.apartments')->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error('Error creating property: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create property. Please try again.');
        }
    }

    public function editApartment($id)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $apartment = $landlord->properties()->findOrFail($id);

        return view('landlord.edit-apartment', compact('apartment'));
    }

    public function updateApartment(\App\Http\Requests\Landlord\UpdatePropertyRequest $request, $id)
    {
        // Sanitize phone number - remove all non-digit characters
        if ($request->has('contact_phone') && $request->contact_phone) {
            $request->merge([
                'contact_phone' => preg_replace('/[^0-9]/', '', $request->contact_phone),
            ]);
        }

        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($id);

        try {
            $newTotalUnits = $request->total_units;

            $floors = $request->property_type === 'house' ? null : $request->floors;
            $bedrooms = $request->property_type === 'house' ? $request->bedrooms : null;

            $updateData = [
                'name' => $request->name,
                'property_type' => $request->property_type,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'description' => $request->description,
                'total_units' => $newTotalUnits,
                'floors' => $floors,
                'bedrooms' => $bedrooms,
                'year_built' => $request->year_built,
                'parking_spaces' => $request->parking_spaces,
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
                'amenities' => $request->amenities ?? [],
                'status' => $request->status,
            ];

            if ($request->hasFile('cover_image')) {
                $supabase = new SupabaseService;
                $filename = 'property-'.time().'-'.uniqid().'.'.$request->file('cover_image')->getClientOriginalExtension();
                $path = 'properties/'.$filename;
                $uploadResult = $supabase->uploadFile('house-sync', $path, $request->file('cover_image')->getRealPath());

                if ($uploadResult['success']) {
                    $updateData['cover_image'] = $uploadResult['url'];
                }
            }

            if ($request->hasFile('gallery')) {
                $supabase = new SupabaseService;
                $galleryPaths = $property->gallery ?? [];

                foreach ($request->file('gallery') as $index => $file) {
                    $filename = 'property-gallery-'.time().'-'.$index.'-'.uniqid().'.'.$file->getClientOriginalExtension();
                    $path = 'properties/gallery/'.$filename;
                    $uploadResult = $supabase->uploadFile('house-sync', $path, $file->getRealPath());

                    if ($uploadResult['success']) {
                        $galleryPaths[] = $uploadResult['url'];
                    }
                }

                $galleryPaths = array_slice($galleryPaths, 0, 12);
                $updateData['gallery'] = $galleryPaths;
            }

            $property->update($updateData);

            // Backward compatibility variable
            $apartment = $property;

            return redirect()->route('landlord.apartments')->with('success', 'Property updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating property: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update property. Please try again.');
        }
    }

    public function deleteApartment(Request $request, $id)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($id);

        try {
            $unitCount = $property->units()->count();
            $forceDelete = $request->boolean('force_delete');

            if ($forceDelete) {
                $request->validate(['password' => 'required|string']);

                if (! Hash::check($request->password, $landlord->password)) {
                    return back()->with('error', 'Incorrect password. Force delete cancelled.');
                }

                $activeTenantsCount = $property->units()
                    ->whereHas('tenantAssignments', function ($query) {
                        $query->whereIn('status', ['active', 'pending']);
                    })->count();

                if ($activeTenantsCount > 0) {
                    return back()->with('error', "Cannot force delete property with active tenant assignments. Found {$activeTenantsCount} unit(s) with active tenants.");
                }

                $propertyName = $property->name;

                foreach ($property->units as $unit) {
                    $unit->tenantAssignments()->delete();
                }

                $deletedUnitsCount = $property->units()->count();
                $property->units()->delete();
                $property->delete();

                return redirect()->route('landlord.apartments')->with('success', "Property '{$propertyName}' and {$deletedUnitsCount} unit(s) force deleted successfully.");
            }

            if ($unitCount > 0) {
                $activeTenantsCount = $property->units()
                    ->whereHas('tenantAssignments', function ($query) {
                        $query->whereIn('status', ['active', 'pending']);
                    })->count();

                if ($activeTenantsCount > 0) {
                    return back()->with('error', "Cannot delete property with active tenant assignments. Found {$activeTenantsCount} unit(s) with active tenants.");
                }

                return back()->with('error', "Cannot delete property with existing units. Found {$unitCount} unit(s). Please delete all units first, or use Force Delete.");
            }

            $property->rfidCards()->delete();

            $propertyName = $property->name;
            $property->delete();

            return redirect()->route('landlord.apartments')->with('success', "Property '{$propertyName}' deleted successfully.");
        } catch (\Exception $e) {
            Log::error('Error deleting property', ['property_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete property. Please try again.');
        }
    }

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
            $query = Unit::whereHas('property', function($q) use ($landlord) {
                $q->where('landlord_id', $landlord->id);
            })->with('property');
            $statsQuery = Unit::whereHas('property', function($q) use ($landlord) {
                $q->where('landlord_id', $landlord->id);
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

    public function storeUnit(\App\Http\Requests\Landlord\StoreUnitRequest $request, $propertyId)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($propertyId);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $supabase = new SupabaseService;
            $filename = 'unit-'.time().'-'.uniqid().'.'.$request->file('cover_image')->getClientOriginalExtension();
            $path = 'units/'.$filename;
            $uploadResult = $supabase->uploadFile('house-sync', $path, $request->file('cover_image')->getRealPath());

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
                $uploadResult = $supabase->uploadFile('house-sync', $path, $file->getRealPath());

                if ($uploadResult['success']) {
                    $galleryPaths[] = $uploadResult['url'];
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
            'units_per_floor' => 'nullable|integer|min:1|max:500',
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

        if (!$request->has('units') || empty($request->input('units'))) {
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

            foreach ($request->units as $unitData) {
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

            if (! empty($unitsToInsert)) {
                $chunks = array_chunk($unitsToInsert, 100);
                foreach ($chunks as $chunk) {
                    \DB::table('units')->insert($chunk);
                }
            }

            $property->update(['total_units' => $property->units()->count()]);
            session()->forget('bulk_creation_params');

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
            
        } catch (\Exception $e) {
            Log::error('Error finalizing bulk units: ' . $e->getMessage());
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
            }
            throw $e;
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
                $uploadResult = $supabase->uploadFile('house-sync', $path, $request->file('cover_image')->getRealPath());

                if ($uploadResult['success']) {
                    $updateData['cover_image'] = $uploadResult['url'];
                }
            }

            if ($request->hasFile('gallery')) {
                $supabase = new SupabaseService;
                $galleryPaths = $unit->gallery ?? [];

                foreach ($request->file('gallery') as $index => $file) {
                    $filename = 'unit-gallery-'.time().'-'.$index.'-'.uniqid().'.'.$file->getClientOriginalExtension();
                    $path = 'units/gallery/'.$filename;
                    $uploadResult = $supabase->uploadFile('house-sync', $path, $file->getRealPath());

                    if ($uploadResult['success']) {
                        $galleryPaths[] = $uploadResult['url'];
                    }
                }

                $updateData['gallery'] = array_slice($galleryPaths, 0, 12);
            }

            $unit->update($updateData);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Unit updated successfully.', 'unit' => $unit->fresh()]);
            }

            return redirect()->route('landlord.units')->with('success', 'Unit updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating unit: ' . $e->getMessage());
            
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
        } catch (\Exception $e) {
            Log::error('Error deleting unit: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete unit. Please try again.');
        }
    }

    public function register()
    {
        return view('landlord.register');
    }

    public function storeRegistration(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|regex:/^[0-9]+$/|max:20',
            'address' => 'required|string|max:500',
            'business_info' => 'required|string|max:1000',
            'documents' => 'required|array|min:1',
            'documents.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'document_types' => 'required|array',
            'document_types.*' => 'required|string|in:business_permit,mayors_permit,bir_certificate,barangay_clearance,lease_contract_sample,valid_id,other',
        ]);

        $landlord = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'landlord',
        ]);

        LandlordProfile::create([
            'user_id' => $landlord->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'business_info' => $request->business_info,
            'status' => 'pending',
        ]);

        foreach ($request->file('documents') as $index => $file) {
            $docType = $request->document_types[$index] ?? 'other';
            $supabase = new SupabaseService;

            $extension = $file->getClientOriginalExtension();
            $fileName = 'landlord-doc-'.$landlord->id.'-'.time().'-'.$index.'-'.uniqid().'.'.$extension;
            $path = 'landlord-documents/'.$fileName;

            $uploadResult = $supabase->uploadFile('house-sync', $path, $file->getRealPath());

            if ($uploadResult['success']) {
                LandlordDocument::create([
                    'landlord_id' => $landlord->id,
                    'document_type' => $docType,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $uploadResult['url'],
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now(),
                    'verification_status' => 'pending',
                ]);
            }
        }

        return redirect()->route('landlord.pending')->with('success', 'Registration submitted successfully. Please wait for admin approval.');
    }

    public function pending()
    {
        return view('landlord.pending');
    }

    public function rejected()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return view('landlord.rejected', compact('user'));
    }

    public function tenants()
    {
        $landlordId = Auth::id();
        $tenants = User::where('role', 'tenant')
            ->whereHas('tenantAssignments', function($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })->get();

        return view('landlord.tenants', compact('tenants'));
    }

    public function tenantHistory(Request $request)
    {
        $landlordId = Auth::id();

        $query = TenantAssignment::where('landlord_id', $landlordId)
            ->with(['tenant', 'unit.property']);

        if ($request->filled('property_id')) {
            $query->whereHas('unit.property', function($q) use ($request) {
                $q->where('id', $request->property_id);
            });
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('tenant_name')) {
            $searchTerm = $request->tenant_name;
            $query->whereHas('tenant', function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('lease_start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('lease_end_date', '<=', $request->date_to);
        }

        $assignments = $query->orderBy('lease_start_date', 'desc')->paginate(20);

        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $properties = $landlord->properties()->orderBy('name')->get();
        $apartments = $properties; // Backward compatibility
        
        $units = Unit::whereHas('property', function($q) use ($landlordId) {
            $q->where('landlord_id', $landlordId);
        })->with('property')->orderBy('unit_number')->get();

        $stats = [
            'total_assignments' => TenantAssignment::where('landlord_id', $landlordId)->count(),
            'active_assignments' => TenantAssignment::where('landlord_id', $landlordId)->where('status', 'active')->count(),
            'terminated_assignments' => TenantAssignment::where('landlord_id', $landlordId)->where('status', 'terminated')->count(),
            'total_revenue' => TenantAssignment::where('landlord_id', $landlordId)->where('status', 'active')->sum('rent_amount'),
        ];

        return view('landlord.tenant-history', compact('assignments', 'apartments', 'properties', 'units', 'stats'));
    }

    public function exportTenantHistoryCSV(Request $request)
    {
        $landlordId = Auth::id();

        $query = TenantAssignment::where('landlord_id', $landlordId)
            ->with(['tenant', 'unit.property']);

        // Apply same filters as tenantHistory
        if ($request->filled('property_id')) {
            $query->whereHas('unit.property', function($q) use ($request) {
                $q->where('id', $request->property_id);
            });
        }

        $assignments = $query->orderBy('lease_start_date', 'desc')->get();

        $filename = 'tenant-history-'.date('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($assignments) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Tenant Name', 'Email', 'Phone', 'Property Name', 'Unit Number',
                'Bedrooms', 'Move-in Date', 'Move-out Date', 'Rent Amount', 'Status',
            ]);

            foreach ($assignments as $assignment) {
                fputcsv($file, [
                    $assignment->tenant->name ?? 'N/A',
                    $assignment->tenant->email ?? 'N/A',
                    $assignment->tenant->phone ?? 'N/A',
                    $assignment->unit->property->name ?? 'N/A',
                    $assignment->unit->unit_number ?? 'N/A',
                    $assignment->unit->bedrooms ?? 'N/A',
                    $assignment->lease_start_date ? $assignment->lease_start_date->format('M d, Y') : 'N/A',
                    $assignment->lease_end_date ? $assignment->lease_end_date->format('M d, Y') : 'N/A',
                    '₱'.number_format($assignment->rent_amount, 2),
                    ucfirst($assignment->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // API endpoints
    public function getApartmentDetails($id)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->with('units')->findOrFail($id);

        return response()->json([
            'id' => $property->id,
            'name' => $property->name,
            'total_units' => $property->units->count(),
            'occupied_units' => $property->getOccupiedUnitsCount(),
            'available_units' => $property->getAvailableUnitsCount(),
            'maintenance_units' => $property->getMaintenanceUnitsCount(),
            'occupancy_rate' => $property->getOccupancyRate(),
            'total_revenue' => $property->getTotalRevenue(),
        ]);
    }

    public function getApartmentUnits($id)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($id);
        $units = $property->units()->orderBy('unit_number')->get();

        return response()->json([
            'units' => $units->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'unit_number' => $unit->unit_number,
                    'unit_type' => $unit->unit_type,
                    'rent_amount' => $unit->rent_amount,
                    'status' => $unit->status,
                    'bedrooms' => $unit->bedrooms,
                    'bathrooms' => $unit->bathrooms,
                    'max_occupants' => $unit->max_occupants ?? $unit->tenant_count,
                    'floor_number' => $unit->floor_number ?? 1,
                    'floor_area' => $unit->floor_area,
                    'amenities' => $unit->amenities,
                    'description' => $unit->description,
                ];
            }),
        ]);
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
        } catch (\Exception $e) {
            Log::error('Error creating unit: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create unit.'], 500);
        }
    }
}
