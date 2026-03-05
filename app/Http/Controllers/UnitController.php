<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class UnitController extends Controller
{
    /**
     * Display a listing of units with filtering
     */
    public function index(Request $request): View
    {
        $query = Unit::query();

        // Apply filters if they exist
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('unit_type')) {
            $query->where('unit_type', $request->unit_type);
        }

        if ($request->filled('min_rent')) {
            $query->where('rent_amount', '>=', $request->min_rent);
        }

        if ($request->filled('max_rent')) {
            $query->where('rent_amount', '<=', $request->max_rent);
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', $request->bedrooms);
        }

        if ($request->filled('is_furnished')) {
            $query->where('is_furnished', $request->boolean('is_furnished'));
        }

        if ($request->filled('leasing_type')) {
            $query->where('leasing_type', $request->leasing_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('unit_number', 'like', "%{$search}%")
                  ->orWhere('unit_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Get filtered units
        $units = $query->orderBy('unit_number')->get();

        // Calculate summary statistics
        $totalUnits = Unit::count();
        $occupiedUnits = Unit::occupied()->count();
        $availableUnits = Unit::available()->count();
        $maintenanceUnits = Unit::underMaintenance()->count();

        // Get unique unit types for filter dropdown
        $unitTypes = Unit::distinct()->pluck('unit_type')->sort()->values();

        return view('units', compact(
            'units', 
            'totalUnits', 
            'occupiedUnits', 
            'availableUnits', 
            'maintenanceUnits',
            'unitTypes'
        ));
    }

    /**
     * Store a newly created unit
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'unit_number' => 'required|string|unique:units,unit_number,NULL,id,apartment_id,' . $request->apartment_id,
            'apartment_id' => 'required|exists:apartments,id',
            'unit_type' => 'required|string|max:255',
            'rent_amount' => 'required|numeric|min:0',
            'status' => 'required|in:available,maintenance',
            'leasing_type' => 'required|in:separate,inclusive',
            'tenant_count' => 'required|integer|min:0',
            'max_occupants' => 'nullable|integer|min:1',
            'floor_number' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'floor_area' => 'nullable|numeric|min:0',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:1',
            'is_furnished' => 'boolean',
            'amenities' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        try {
            $unit = Unit::create($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Unit created successfully!',
                'unit' => $unit
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get filtered units as JSON (for AJAX requests)
     */
    public function filter(Request $request): JsonResponse
    {
        $query = Unit::query();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('unit_type')) {
            $query->where('unit_type', $request->unit_type);
        }

        if ($request->filled('min_rent')) {
            $query->where('rent_amount', '>=', $request->min_rent);
        }

        if ($request->filled('max_rent')) {
            $query->where('rent_amount', '<=', $request->max_rent);
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', $request->bedrooms);
        }

        if ($request->filled('is_furnished')) {
            $query->where('is_furnished', $request->boolean('is_furnished'));
        }

        if ($request->filled('leasing_type')) {
            $query->where('leasing_type', $request->leasing_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('unit_number', 'like', "%{$search}%")
                  ->orWhere('unit_type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $units = $query->orderBy('unit_number')->get();

        return response()->json([
            'success' => true,
            'units' => $units
        ]);
    }

    /**
     * Get unit statistics
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total' => Unit::count(),
            'occupied' => Unit::occupied()->count(),
            'available' => Unit::available()->count(),
            'maintenance' => Unit::underMaintenance()->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get unit types for filter dropdown
     */
    public function getUnitTypes(): JsonResponse
    {
        $unitTypes = Unit::distinct()->pluck('unit_type')->sort()->values();
        return response()->json($unitTypes);
    }
}
