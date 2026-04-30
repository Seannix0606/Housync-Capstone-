<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $property->name ?? 'Property Details' }} - HouseSync</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .property-header { background: white; padding: 2rem 0; margin-bottom: 2rem; }
        .property-image-main { width: 100%; height: 420px; object-fit: cover; border-radius: 12px; }
        .property-image-placeholder { width: 100%; height: 420px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; }
        .card-shell { background: white; border-radius: 12px; padding: 1.25rem; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08); margin-bottom: 1.25rem; }
        .unit-card { border: 1px solid #e2e8f0; }
        .unit-image { width: 100%; height: 180px; object-fit: cover; }
        .unit-image-placeholder { width: 100%; height: 180px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .unit-card-col { width: 20%; }
        @media (max-width: 1199.98px) { .unit-card-col { width: 25%; } }
        @media (max-width: 991.98px) { .unit-card-col { width: 33.3333%; } }
        @media (max-width: 767.98px) { .unit-card-col { width: 50%; } }
        @media (max-width: 575.98px) { .unit-card-col { width: 100%; } }
        .property-page-container {
            width: 96%;
            max-width: 1700px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    @php
        $propertyType = strtolower((string) ($property->property_type ?? ''));
        $floorGroups = $floorGroups ?? [];
        $hasAvailableUnits = count($floorGroups) > 0;
        $minAvailableRent = $property->min_available_rent;
        $allGroupedUnits = collect($floorGroups)->flatMap(fn ($group) => $group['units'] ?? [])->values();
    @endphp

    <div class="property-header">
        <div class="property-page-container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <a href="{{ route('explore') }}" class="btn btn-outline-primary mb-3">
                <i class="fas fa-arrow-left me-1"></i> Back to Explore
            </a>
            <h1 class="mb-1">{{ $property->name ?? 'Untitled Property' }}</h1>
            <p class="text-muted mb-0">
                <i class="fas fa-map-marker-alt me-1"></i>
                {{ trim(($property->address ?? '').' '.($property->city ?? '')) ?: 'Location not specified' }}
            </p>
        </div>
    </div>

    <div class="property-page-container mb-5">
        <div class="row">
            <div class="col-lg-8">
                @include('partials.property-hero', ['property' => $property, 'propertyHero' => $propertyHero])

                <div class="card-shell">
                    <h4>Property Overview</h4>
                    <p class="mb-0">{{ $property->description ?: 'No description available.' }}</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-shell">
                    <h3 class="text-primary mb-1">
                        @if(!is_null($minAvailableRent))
                            From ₱{{ number_format((float) $minAvailableRent, 2) }}
                        @else
                            Price on inquiry
                        @endif
                    </h3>
                    <p class="text-muted mb-3">per month</p>
                    <div class="mb-2"><strong>Type:</strong> {{ ucfirst($property->property_type ?? 'N/A') }}</div>
                    <div class="mb-2"><strong>Available units:</strong> {{ $property->available_units_count ?? 0 }}</div>
                    @if($property->landlord)
                        <div class="mb-2"><strong>Landlord:</strong> {{ $property->landlord->name }}</div>
                    @endif
                    <hr>

                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-envelope me-1"></i> Login to Contact Landlord
                        </a>
                    @endguest
                    @auth
                        @if(Auth::user()->role === 'tenant' && $property->landlord_id)
                            <form action="{{ route('tenant.chat.start-from-listing') }}" method="post" class="mb-2">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $property->id }}">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-envelope me-1"></i> Contact Landlord
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-primary w-100 mb-2" disabled>
                                <i class="fas fa-envelope me-1"></i> Contact Landlord
                            </button>
                        @endif
                    @endauth

                    @if($hasAvailableUnits && auth()->check() && auth()->user()->role === 'tenant')
                        <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#applyPropertyModal">
                            <i class="fas fa-file-signature me-1"></i> Apply (Auto-assign Available Unit)
                        </button>
                    @elseif($propertyType === 'house')
                        <button class="btn btn-secondary w-100" disabled>
                            <i class="fas fa-file-signature me-1"></i> Apply Unavailable (Unit Setup Pending)
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                @if($hasAvailableUnits)
                    @include('partials.property-floor-nav', ['floorGroups' => $floorGroups])
                    @include('partials.property-units-by-floor', ['floorGroups' => $floorGroups, 'displayTerm' => $displayTerm])
                @elseif($propertyType === 'house')
                    <div class="card-shell">
                        <div class="alert alert-warning mb-0">
                            <strong>House listing is available, but no unit record is configured yet.</strong><br>
                            You can still contact the landlord now. Applications are temporarily disabled until a unit slot is configured.
                        </div>
                    </div>
                @else
                    <div class="card-shell">
                        <div class="alert alert-secondary mb-0">
                            No available units at the moment for this property.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($relatedProperties->count() > 0)
            <div class="mt-4">
                <h4 class="mb-3">Similar Properties</h4>
                <div class="row">
                    @foreach($relatedProperties as $related)
                        @php
                            $relatedImage = $related->cover_image_url ?: (($related->gallery_urls ?? [])[0] ?? null);
                        @endphp
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('property.show', $related->slug ?? $related->id) }}" class="text-decoration-none">
                                <div class="card h-100">
                                    @if($relatedImage)
                                        <img src="{{ $relatedImage }}" alt="{{ $related->name }}" class="card-img-top" style="height: 160px; object-fit: cover;">
                                    @else
                                        <div class="card-img-top d-flex align-items-center justify-content-center text-white" style="height: 160px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">No Image</div>
                                    @endif
                                    <div class="card-body">
                                        <div class="fw-semibold">{{ Str::limit($related->name ?? 'Property', 40) }}</div>
                                        <small class="text-muted">{{ ucfirst($related->property_type ?? 'property') }}</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @auth
        @if(Auth::user()->role === 'tenant' && $hasAvailableUnits)
            <div class="modal fade" id="applyPropertyModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">Apply to {{ $property->name }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('tenant.apply', $property->id) }}">
                            @csrf
                            <div class="modal-body">
                                <input type="text" class="form-control mb-2" name="name" value="{{ Auth::user()->name }}" required>
                                <input type="text" class="form-control mb-2" name="phone" value="{{ Auth::user()->phone }}" required>
                                <input type="text" class="form-control mb-2" name="address" value="{{ Auth::user()->address }}" required>
                                <input type="text" class="form-control mb-2" name="occupation" placeholder="Occupation" required>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Optional note"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Submit Application</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @foreach($allGroupedUnits as $unitVm)
                <div class="modal fade" id="applyUnitModal{{ $unitVm['id'] }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Apply for {{ $unitVm['label'] }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('tenant.apply.unit', $unitVm['id']) }}">
                                @csrf
                                <div class="modal-body">
                                    <input type="text" class="form-control mb-2" name="name" value="{{ Auth::user()->name }}" required>
                                    <input type="text" class="form-control mb-2" name="phone" value="{{ Auth::user()->phone }}" required>
                                    <input type="text" class="form-control mb-2" name="address" value="{{ Auth::user()->address }}" required>
                                    <input type="text" class="form-control mb-2" name="occupation" placeholder="Occupation" required>
                                    <input type="date" class="form-control mb-2" name="move_in_date" required>
                                    <textarea class="form-control" name="notes" rows="3" placeholder="Optional note"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Submit Unit Application</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
