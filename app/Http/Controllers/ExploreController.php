<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\Unit;
use App\Services\Explore\PropertyHeroPresentationService;
use App\Services\Explore\PropertyUnitPresentationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
            return $this->filterProperties($request);
        }

        $properties = $this->buildPropertyQuery($request)->paginate(12);

        $propertyTypes = ['apartment', 'condominium', 'townhouse', 'house', 'duplex'];

        // If the user is an authenticated tenant, render within the tenant layout
        if (auth()->check() && auth()->user()->role === 'tenant') {
            return view('tenant.explore', compact('properties', 'amenities', 'propertyTypes'));
        }

        return view('explore', compact('properties', 'amenities', 'propertyTypes'));
    }

    /**
     * Filter properties based on request parameters.
     */
    public function filterProperties(Request $request)
    {
        $properties = $this->buildPropertyQuery($request)->paginate(12);

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('partials.property-cards', compact('properties'))->render(),
                'pagination' => $properties->links('pagination::bootstrap-5')->toHtml(),
                'count' => $properties->total(),
            ]);
        }

        return view('explore', compact('properties'));
    }

    /**
     * Build property-first explore query with availability-based filters.
     */
    private function buildPropertyQuery(Request $request)
    {
        $propertyTableHasAvailableFrom = Schema::hasColumn('properties', 'available_from');
        $propertyTableHasAvailableTo = Schema::hasColumn('properties', 'available_to');
        $propertyTableHasIsFeatured = Schema::hasColumn('properties', 'is_featured');
        $propertyTableHasAmenities = Schema::hasColumn('properties', 'amenities');

        $query = Property::query()
            ->with(['landlord', 'units' => function ($unitQuery) {
                $unitQuery->where('status', 'available')->orderBy('rent_amount');
            }])
            ->withCount([
                'units as available_units_count' => function ($unitQuery) {
                    $unitQuery->where('status', 'available');
                },
            ])
            ->withMin([
                'units as min_available_rent' => function ($unitQuery) {
                    $unitQuery->where('status', 'available');
                },
            ], 'rent_amount')
            ->active();

        if ($request->filled('type')) {
            $query->where('property_type', $request->string('type'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($propertyQuery) use ($search) {
                $propertyQuery->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('address', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhereHas('landlord', function ($landlordQuery) use ($search) {
                        $landlordQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('availability') && $request->availability === 'occupied') {
            $query->whereDoesntHave('units', function ($unitQuery) {
                $unitQuery->where('status', 'available');
            });
        } else {
            $query->whereHas('units', function ($unitQuery) {
                $unitQuery->where('status', 'available');
            });
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('units', function ($unitQuery) use ($request) {
                $unitQuery->where('status', 'available');

                if ($request->filled('min_price')) {
                    $unitQuery->where('rent_amount', '>=', (float) $request->min_price);
                }

                if ($request->filled('max_price')) {
                    $unitQuery->where('rent_amount', '<=', (float) $request->max_price);
                }
            });
        }

        // Amenity filter from Explore modal.
        // Supports both legacy amenity IDs and string labels stored in the JSON column.
        $selectedAmenities = array_values(array_filter((array) $request->input('amenities', []), static function ($value) {
            return $value !== null && $value !== '';
        }));
        if ($propertyTableHasAmenities && count($selectedAmenities) > 0) {
            $amenityNamesById = Amenity::whereIn('id', $selectedAmenities)
                ->pluck('name', 'id')
                ->map(fn ($name) => mb_strtolower((string) $name))
                ->all();

            foreach ($selectedAmenities as $amenityId) {
                $amenityName = $amenityNamesById[(int) $amenityId] ?? $amenityNamesById[(string) $amenityId] ?? null;

                $query->where(function ($amenityQuery) use ($amenityId, $amenityName) {
                    $amenityQuery->whereJsonContains('amenities', (string) $amenityId)
                        ->orWhereJsonContains('amenities', (int) $amenityId);

                    if ($amenityName !== null && $amenityName !== '') {
                        $amenityQuery->orWhereJsonContains('amenities', $amenityName)
                            ->orWhereJsonContains('amenities', ucfirst($amenityName));
                    }
                });
            }
        }

        // Optional date filters (legacy property listing fields).
        if ($propertyTableHasAvailableFrom && $request->filled('available_from')) {
            $query->whereDate('available_from', '>=', $request->date('available_from')->toDateString());
        }

        if ($propertyTableHasAvailableTo && $request->filled('available_to')) {
            $query->whereDate('available_to', '<=', $request->date('available_to')->toDateString());
        }

        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderByRaw('min_available_rent IS NULL, min_available_rent ASC');
                break;
            case 'price_high':
                $query->orderByRaw('min_available_rent IS NULL, min_available_rent DESC');
                break;
            case 'featured':
                if ($propertyTableHasIsFeatured) {
                    $query->orderByDesc('is_featured')->orderByDesc('created_at');
                } else {
                    $query->orderByDesc('created_at');
                }
                break;
            default:
                $query->orderByDesc('created_at');
                break;
        }

        return $query;
    }

    /**
     * Show single unit details
     */
    public function show($slug)
    {
        // Try to find by unit ID in slug (format: property-name-unit-number-{id})
        if (preg_match('/-unit-(\d+)$/', $slug, $matches)) {
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

        // Backward-compatible fallback: plain property slug routes to property details.
        return $this->showProperty(
            $slug,
            app(PropertyHeroPresentationService::class),
            app(PropertyUnitPresentationService::class)
        );
    }

    /**
     * Show property details with all available units
     */
    public function showProperty(
        $slug,
        PropertyHeroPresentationService $propertyHeroPresentationService,
        PropertyUnitPresentationService $propertyUnitPresentationService
    )
    {
        $property = Property::with([
            'units' => function ($query) {
                $query->select([
                    'id',
                    'property_id',
                    'unit_number',
                    'unit_type',
                    'rent_amount',
                    'status',
                    'floor_number',
                    'bedrooms',
                    'bathrooms',
                    'floor_area',
                    'cover_image',
                    'gallery',
                ])->where('status', 'available');
            },
            'landlord',
        ])->withCount([
            'units as available_units_count' => function ($query) {
                $query->where('status', 'available');
            },
        ])->withMin([
            'units as min_available_rent' => function ($query) {
                $query->where('status', 'available');
            },
        ], 'rent_amount')->where(function ($query) use ($slug) {
            $query->where('slug', $slug);
            if (is_numeric($slug)) {
                $query->orWhere('id', (int) $slug);
            }
        })->firstOrFail();

        $relatedProperties = Property::with(['units' => function ($query) {
            $query->where('status', 'available');
        }])
            ->where('id', '!=', $property->id)
            ->where('status', 'active')
            ->where('is_active', true)
            ->whereHas('units', function ($query) {
                $query->where('status', 'available');
            })
            ->limit(4)
            ->get();

        $heroPresentation = $propertyHeroPresentationService->build($property);
        $unitPresentation = $propertyUnitPresentationService->build($property);

        return view('property-details', [
            'property' => $property,
            'relatedProperties' => $relatedProperties,
            'propertyHero' => $heroPresentation,
            'displayTerm' => $unitPresentation['displayTerm'],
            'floorGroups' => $unitPresentation['floorGroups'],
        ]);
    }
}
