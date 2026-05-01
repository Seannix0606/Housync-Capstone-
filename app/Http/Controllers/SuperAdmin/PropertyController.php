<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(private readonly PropertyService $propertyService) {}

    public function apartments(Request $request)
    {
        $filters = $request->only(['search', 'status', 'landlord']);
        $properties = $this->propertyService->getPaginatedProperties($filters);
        $stats = $this->propertyService->getPropertyStats();
        $landlords = $this->propertyService->getApprovedLandlords();

        return view('super-admin.apartments', compact('properties', 'stats', 'landlords'));
    }

    public function show(int $id)
    {
        $property = $this->propertyService->getPropertyDetails($id);

        return view('super-admin.property-details', compact('property'));
    }

    public function units(int $id)
    {
        $property = $this->propertyService->getPropertyWithUnits($id);

        return view('super-admin.property-units', compact('property'));
    }
}
