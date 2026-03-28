<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StorePropertyRequest;
use App\Http\Requests\Landlord\UpdatePropertyRequest;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PropertyController extends Controller
{
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
            'data' => $request->only(['name', 'property_type', 'address', 'floors', 'bedrooms']),
            'method' => $request->method(),
            'url' => $request->url(),
        ]);

        try {
            $coverPath = null;
            if ($request->hasFile('cover_image')) {
                try {
                    $supabase = new SupabaseService;
                    $filename = 'property-'.time().'-'.uniqid().'.'.$request->file('cover_image')->getClientOriginalExtension();
                    $path = 'properties/'.$filename;
                    $uploadResult = $supabase->uploadFile(config('services.supabase.bucket'), $path, $request->file('cover_image')->getRealPath());

                    if ($uploadResult['success']) {
                        $coverPath = $uploadResult['url'];
                    } else {
                        throw new \Exception($uploadResult['message'] ?? 'Supabase upload failed');
                    }
                } catch (\Exception $exception) {
                    Log::warning('Supabase upload failed, falling back to local storage', ['error' => $exception->getMessage()]);
                    $filename = 'apartment-'.time().'-'.uniqid().'.'.$request->file('cover_image')->getClientOriginalExtension();
                    $path = $request->file('cover_image')->storeAs('apartment-covers', $filename, 'public');
                    $coverPath = asset('storage/'.$path);
                }
            }

            $galleryPaths = [];
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $index => $file) {
                    try {
                        $supabase = $supabase ?? new SupabaseService;
                        $filename = 'property-gallery-'.time().'-'.$index.'-'.uniqid().'.'.$file->getClientOriginalExtension();
                        $path = 'properties/gallery/'.$filename;
                        $uploadResult = $supabase->uploadFile(config('services.supabase.bucket'), $path, $file->getRealPath());

                        if ($uploadResult['success']) {
                            $galleryPaths[] = $uploadResult['url'];
                        } else {
                            throw new \Exception($uploadResult['message'] ?? 'Supabase upload failed');
                        }
                    } catch (\Exception $exception) {
                        Log::warning('Supabase gallery upload failed, falling back to local', ['index' => $index]);
                        $filename = 'apartment-gallery-'.time().'-'.$index.'-'.uniqid().'.'.$file->getClientOriginalExtension();
                        $path = $file->storeAs('apartment-gallery', $filename, 'public');
                        $galleryPaths[] = asset('storage/'.$path);
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
        } catch (\Exception $exception) {
            Log::error('Error creating property: '.$exception->getMessage());

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

    public function updateApartment(UpdatePropertyRequest $request, $id)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($id);

        try {
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
                'total_units' => $request->total_units,
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
                $uploadResult = $supabase->uploadFile(config('services.supabase.bucket'), $path, $request->file('cover_image')->getRealPath());

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
                    $uploadResult = $supabase->uploadFile(config('services.supabase.bucket'), $path, $file->getRealPath());

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
        } catch (\Exception $exception) {
            Log::error('Error updating property: '.$exception->getMessage());

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
        } catch (\Exception $exception) {
            Log::error('Error deleting property', ['property_id' => $id, 'error' => $exception->getMessage()]);

            return back()->with('error', 'Failed to delete property. Please try again.');
        }
    }

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
}
