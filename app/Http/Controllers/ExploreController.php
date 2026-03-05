<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Property;
use App\Models\Amenity;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    /**
     * Display the explore page with available units
     */
    public function index(Request $request)
    {
        $amenities = Amenity::orderBy('name')->get();
        
        // If this is an AJAX request for filtering
        if ($request->ajax()) {
            return $this->filterUnits($request);
        }

        // Get available units with their properties
        $units = Unit::with(['property.landlord'])
            ->where('status', 'available')
            ->whereHas('property', function($q) {
                $q->where('status', 'active')->where('is_active', true);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $propertyTypes = ['apartment', 'condominium', 'townhouse', 'house', 'duplex'];
        
        // If the user is an authenticated tenant, render within the tenant layout
        if (auth()->check() && auth()->user()->role === 'tenant') {
            return view('tenant.explore', compact('units', 'amenities', 'propertyTypes'));
        }

        return view('explore', compact('units', 'amenities', 'propertyTypes'));
    }

    /**
     * Filter units based on request parameters
     */
    public function filterUnits(Request $request)
    {
        $query = Unit::with(['property.landlord'])
            ->whereHas('property', function($q) {
                $q->where('status', 'active')->where('is_active', true);
            });

        // Apply filters
        if ($request->filled('type')) {
            $query->where('unit_type', 'LIKE', '%' . $request->type . '%');
        }

        if ($request->filled('availability')) {
            $query->where('status', $request->availability);
        } else {
            $query->where('status', 'available');
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        if ($request->filled('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            if ($request->filled('min_price')) {
                $query->where('rent_amount', '>=', $request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('rent_amount', '<=', $request->max_price);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('unit_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('property', function($pq) use ($search) {
                      $pq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('address', 'LIKE', "%{$search}%")
                         ->orWhere('city', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('rent_amount', 'asc');
                break;
            case 'price_high':
                $query->orderBy('rent_amount', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $units = $query->paginate(12);

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('partials.unit-cards', compact('units'))->render(),
                'pagination' => $units->links('pagination::bootstrap-5')->toHtml(),
                'count' => $units->total(),
            ]);
        }

        return view('explore', compact('units'));
    }

    /**
     * Show single unit details
     */
    public function show($slug)
    {
        // Try to find by unit ID in slug (format: property-name-unit-number-{id})
        if (preg_match('/-(\d+)$/', $slug, $matches)) {
            $unitId = (int) $matches[1];
            $unit = Unit::with(['property.landlord'])->find($unitId);
            
            if ($unit) {
                $relatedUnits = Unit::with(['property'])
                    ->where('property_id', $unit->property_id)
                    ->where('id', '!=', $unit->id)
                    ->where('status', 'available')
                    ->limit(4)
                    ->get();

                return view('unit-details', compact('unit', 'relatedUnits'));
            }
        }
        
        // Fallback: try to find a property with this slug
        $property = Property::with(['units' => function($q) {
            $q->where('status', 'available');
        }, 'landlord'])->where('slug', $slug)->first();
        
        if ($property && $property->units->count() > 0) {
            $unit = $property->units->first();
            $relatedUnits = $property->units->where('id', '!=', $unit->id)->take(4);
            
            return view('unit-details', compact('unit', 'relatedUnits', 'property'));
        }
        
        abort(404, 'Unit not found');
    }

    /**
     * Show property details with all available units
     */
    public function showProperty($slug)
    {
        $property = Property::with(['units' => function($q) {
            $q->where('status', 'available')->orderBy('rent_amount');
        }, 'landlord'])->where('slug', $slug)->firstOrFail();

        $relatedProperties = Property::with(['units' => function($q) {
            $q->where('status', 'available');
        }])
            ->where('id', '!=', $property->id)
            ->where('status', 'active')
            ->whereHas('units', function($q) {
                $q->where('status', 'available');
            })
            ->limit(4)
            ->get();

        return view('property-details', compact('property', 'relatedProperties'));
    }
}
