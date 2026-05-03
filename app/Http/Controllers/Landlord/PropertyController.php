<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StorePropertyRequest;
use App\Http\Requests\Landlord\UpdatePropertyRequest;
use App\Services\Landlord\PropertyCreationUnitMediaApplicator;
use App\Services\Landlord\PropertyService;
use App\Services\Media\PropertyMediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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

    public function storeApartment(
        StorePropertyRequest $request,
        PropertyService $propertyService,
        PropertyMediaService $propertyMediaService,
        PropertyCreationUnitMediaApplicator $propertyCreationUnitMediaApplicator
    ) {
        Log::info('Property creation request received', [
            'data' => $request->only(['name', 'property_type', 'address', 'floors', 'bedrooms', 'building_floors', 'unit_stories', 'dwelling_stories']),
            'method' => $request->method(),
            'url' => $request->url(),
        ]);

        try {
            /** @var \App\Models\User $landlord */
            $landlord = Auth::user();

            $property = DB::transaction(function () use ($request, $landlord, $propertyService, $propertyMediaService, $propertyCreationUnitMediaApplicator) {
                $property = $propertyService->createPropertyWithUnits([
                    'landlord_id' => $landlord->id,
                    'name' => $request->name,
                    'property_type' => $request->property_type,
                    'address' => $request->address,
                    'description' => $request->description,
                    'floors' => $request->floors,
                    'unit_count' => $request->input('unit_count'),
                    'bedrooms' => $request->bedrooms,
                    'building_floors' => $request->input('building_floors'),
                    'unit_bedrooms' => $request->input('unit_bedrooms'),
                    'unit_stories' => $request->input('unit_stories'),
                    'dwelling_stories' => $request->input('dwelling_stories'),
                    'contact_person' => $request->contact_person,
                    'contact_phone' => $request->contact_phone,
                    'contact_email' => $request->contact_email,
                    'amenities' => $request->amenities ?? [],
                    'status' => 'active',
                ]);

                $mediaPayload = $propertyMediaService->uploadForProperty(
                    $property->id,
                    $request->file('property_cover_image'),
                    $request->file('property_gallery', [])
                );

                if (! empty($mediaPayload)) {
                    $property->update($mediaPayload);
                }

                $propertyCreationUnitMediaApplicator->applyFromRequest($property->fresh(), $request);

                return $property->fresh(['units']);
            });

            $unitCount = $property->units->count();
            $successMessage = $unitCount === 1
                ? 'Property created successfully with 1 unit.'
                : "Property created successfully with {$unitCount} units.";

            return redirect()->route('landlord.apartments')->with('success', $successMessage);
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
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

    public function updateApartment(UpdatePropertyRequest $request, $id, PropertyMediaService $propertyMediaService)
    {
        /** @var \App\Models\User $landlord */
        $landlord = Auth::user();
        $property = $landlord->properties()->findOrFail($id);

        try {
            $floorsColumn = match ($request->property_type) {
                'house', 'duplex', 'townhouse' => null,
                default => $request->floors,
            };
            $dwellingTypes = ['house', 'duplex', 'townhouse'];
            $bedrooms = in_array($request->property_type, $dwellingTypes, true) ? $request->bedrooms : null;
            $buildingFloors = in_array($request->property_type, $dwellingTypes, true) ? $request->building_floors : null;
            $totalUnits = match ($request->property_type) {
                'duplex' => 2,
                'house', 'townhouse' => 1,
                default => $request->total_units,
            };

            $updateData = [
                'name' => $request->name,
                'property_type' => $request->property_type,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'description' => $request->description,
                'total_units' => $totalUnits,
                'floors' => $floorsColumn,
                'building_floors' => $buildingFloors,
                'bedrooms' => $bedrooms,
                'year_built' => $request->year_built,
                'parking_spaces' => $request->parking_spaces,
                'contact_person' => $request->contact_person,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
                'amenities' => $request->amenities ?? [],
                'status' => $request->status,
            ];

            $mediaPayload = $propertyMediaService->uploadForProperty(
                $property->id,
                $request->file('property_cover_image'),
                $request->file('property_gallery', [])
            );

            $updateData = array_merge($updateData, $mediaPayload);
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
                        $query->whereIn('status', ['active', 'pending', 'pending_approval']);
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
                        $query->whereIn('status', ['active', 'pending', 'pending_approval']);
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
