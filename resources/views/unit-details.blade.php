<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $unit->property->name ?? 'Property' }} - Unit {{ $unit->unit_number }} - HouseSync</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }

        .property-header {
            background: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .property-image-main {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 12px;
        }

        .property-image-placeholder {
            width: 100%;
            height: 500px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .property-info-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .amenity-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f8fafc;
            border-radius: 8px;
            margin: 0.25rem;
        }

        .related-property-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .related-property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #f1f5ff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
        }

        .price-tag {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-apply {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
        }

        .btn-apply:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .status-badge.available {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.occupied {
            background: #fee2e2;
            color: #991b1b;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .gallery-item {
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    @php
        $property = $unit->property;
        $images = $unit->images ?? $property->gallery_images ?? [];
        $mainImage = count($images) > 0 ? $images[0] : null;
    @endphp

    <div class="property-header">
        <div class="container">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <a href="{{ route('explore') }}" class="btn btn-outline-primary mb-3">
                <i class="fas fa-arrow-left me-1"></i> Back to Explore
            </a>
            
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1>{{ $property->name ?? 'Property' }} - Unit {{ $unit->unit_number }}</h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        {{ $property->address ?? 'Location not specified' }}
                        @if($property->city)
                            , {{ $property->city }}
                        @endif
                    </p>
                </div>
                <span class="status-badge {{ $unit->status }}">
                    <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                    {{ ucfirst($unit->status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <div class="col-lg-8">
                <!-- Unit Image -->
                @if($mainImage)
                    <img src="{{ $mainImage }}" alt="{{ $property->name ?? 'Unit' }}" class="property-image-main mb-3">
                    
                    @if(count($images) > 1)
                        <div class="gallery-grid">
                            @foreach(array_slice($images, 1, 4) as $img)
                                <div class="gallery-item">
                                    <img src="{{ $img }}" alt="Gallery image">
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="property-image-placeholder mb-4">
                        <div class="text-center">
                            <i class="fas fa-home fa-5x mb-3"></i>
                            <h3>No Image Available</h3>
                        </div>
                    </div>
                @endif

                <!-- Description -->
                <div class="property-info-card mt-4">
                    <h3><i class="fas fa-info-circle me-2 text-primary"></i>Description</h3>
                    <p class="mb-0">{{ $unit->description ?? $property->description ?? 'No description available for this unit.' }}</p>
                </div>

                <!-- Unit Features -->
                <div class="property-info-card">
                    <h3><i class="fas fa-th-list me-2 text-primary"></i>Unit Features</h3>
                    <div class="row">
                        <div class="col-md-6">
                            @if($unit->bedrooms)
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-bed"></i></div>
                                    <div>
                                        <small class="text-muted">Bedrooms</small>
                                        <div class="fw-bold">{{ $unit->bedrooms }}</div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($unit->bathrooms)
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-bath"></i></div>
                                    <div>
                                        <small class="text-muted">Bathrooms</small>
                                        <div class="fw-bold">{{ $unit->bathrooms }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($unit->floor_area)
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-ruler-combined"></i></div>
                                    <div>
                                        <small class="text-muted">Floor Area</small>
                                        <div class="fw-bold">{{ number_format($unit->floor_area) }} m²</div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($unit->floor_number)
                                <div class="feature-item">
                                    <div class="feature-icon"><i class="fas fa-building"></i></div>
                                    <div>
                                        <small class="text-muted">Floor</small>
                                        <div class="fw-bold">{{ $unit->floor_number }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Property Info -->
                <div class="property-info-card">
                    <h3><i class="fas fa-building me-2 text-primary"></i>About the Property</h3>
                    <p class="text-muted mb-3">{{ $property->name ?? 'Property' }}</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fas fa-tag"></i></div>
                                <div>
                                    <small class="text-muted">Property Type</small>
                                    <div class="fw-bold">{{ ucfirst($property->property_type ?? $unit->unit_type ?? 'N/A') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fas fa-door-open"></i></div>
                                <div>
                                    <small class="text-muted">Total Units</small>
                                    <div class="fw-bold">{{ $property->units->count() ?? 1 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Price & Apply -->
                <div class="property-info-card" style="position: sticky; top: 2rem;">
                    <div class="text-center mb-4">
                        <div class="price-tag">₱{{ number_format($unit->rent_amount ?? 0, 2) }}</div>
                        <p class="text-muted mb-0">per month</p>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Unit Number</span>
                            <span class="fw-bold">{{ $unit->unit_number }}</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Type</span>
                            <span class="fw-bold">{{ ucfirst($unit->unit_type ?? $property->property_type ?? 'Unit') }}</span>
                        </div>
                    </div>

                    @if($unit->bedrooms)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Bedrooms</span>
                                <span class="fw-bold">{{ $unit->bedrooms }}</span>
                            </div>
                        </div>
                    @endif

                    @if($unit->bathrooms)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Bathrooms</span>
                                <span class="fw-bold">{{ $unit->bathrooms }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            <span class="status-badge {{ $unit->status }}">{{ ucfirst($unit->status) }}</span>
                        </div>
                    </div>

                    <hr>

                    @if($unit->status === 'available')
                        @auth
                            @if(Auth::user()->role === 'tenant')
                                <button class="btn btn-apply btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#applyTenantModal">
                                    <i class="fas fa-file-signature me-1"></i> Apply as Tenant
                                </button>
                            @endif
                        @else
                            <button class="btn btn-apply btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#loginRequiredModal">
                                <i class="fas fa-file-signature me-1"></i> Apply as Tenant
                            </button>
                        @endauth
                    @else
                        <button class="btn btn-secondary w-100 mb-2" disabled>
                            <i class="fas fa-times-circle me-1"></i> Not Available
                        </button>
                    @endif

                    <button class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-envelope me-1"></i> Contact Landlord
                    </button>
                    
                    <button class="btn btn-outline-secondary w-100">
                        <i class="fas fa-calendar me-1"></i> Schedule Viewing
                    </button>

                    @if($property->landlord)
                        <hr>
                        <div class="text-center">
                            <small class="text-muted">Listed by</small>
                            <div class="fw-bold">{{ $property->landlord->landlordProfile->name ?? $property->landlord->name ?? 'Landlord' }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Related Units -->
        @if($relatedUnits->count() > 0)
            <div class="mt-5">
                <h3 class="mb-4"><i class="fas fa-home me-2"></i>Other Units in this Property</h3>
                <div class="row">
                    @foreach($relatedUnits as $related)
                        @php
                            $relatedImages = $related->images ?? $related->property->gallery_images ?? [];
                            $relatedImage = count($relatedImages) > 0 ? $relatedImages[0] : null;
                        @endphp
                        <div class="col-md-3 mb-4">
                            <a href="{{ route('property.show', ($related->property->slug ?? $related->property_id) . '-unit-' . $related->id) }}" class="related-property-card">
                                @if($relatedImage)
                                    <img src="{{ $relatedImage }}" alt="Unit {{ $related->unit_number }}" style="width: 100%; height: 180px; object-fit: cover;">
                                @else
                                    <div style="width: 100%; height: 180px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white;">
                                        <div class="text-center">
                                            <i class="fas fa-door-open fa-2x mb-2"></i>
                                            <div>Unit {{ $related->unit_number }}</div>
                                        </div>
                                    </div>
                                @endif
                                <div class="p-3">
                                    <h6 class="mb-1">Unit {{ $related->unit_number }}</h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-bold">₱{{ number_format($related->rent_amount ?? 0, 2) }}</span>
                                        <span class="badge {{ $related->status === 'available' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($related->status) }}
                                        </span>
                                    </div>
                                    @if($related->bedrooms || $related->bathrooms)
                                        <small class="text-muted">
                                            @if($related->bedrooms){{ $related->bedrooms }} bed @endif
                                            @if($related->bedrooms && $related->bathrooms) · @endif
                                            @if($related->bathrooms){{ $related->bathrooms }} bath @endif
                                        </small>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Login Required Modal -->
    <div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title" id="loginRequiredModalLabel">
                        <i class="fas fa-user-lock me-2"></i>Login Required
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" style="padding: 2rem;">
                    <div class="mb-4">
                        <i class="fas fa-home fa-3x text-primary mb-3"></i>
                        <h4>Apply as Tenant</h4>
                        <p class="text-muted">To apply for this unit, you need to have an account. Please login or register to continue.</p>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus me-2"></i>Register as Tenant
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Apply as Tenant Modal -->
    @auth
        @if(Auth::user()->role === 'tenant')
            <div class="modal fade" id="applyTenantModal" tabindex="-1" aria-labelledby="applyTenantModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="applyTenantModalLabel">
                                <i class="fas fa-file-signature me-2"></i>Apply as Tenant
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="applyTenantForm" method="POST" action="{{ route('tenant.apply.unit', $unit->id) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Application for:</strong> {{ $property->name ?? 'Property' }} - Unit {{ $unit->unit_number }} <br>
                                    <strong>Monthly Rent:</strong> ₱{{ number_format($unit->rent_amount ?? 0, 2) }}
                                </div>

                                <!-- Personal Information -->
                                <h6 class="mb-3 fw-bold text-primary">
                                    <i class="fas fa-user me-2"></i>Personal Information
                                </h6>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="applicant_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="applicant_name" name="name" value="{{ Auth::user()->tenantProfile->name ?? Auth::user()->name ?? '' }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="applicant_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="applicant_phone" name="phone" value="{{ Auth::user()->tenantProfile->phone ?? '' }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="applicant_address" class="form-label">Current Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="applicant_address" name="address" value="{{ Auth::user()->tenantProfile->address ?? '' }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="applicant_occupation" class="form-label">Occupation <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="applicant_occupation" name="occupation" placeholder="e.g., Software Engineer" required>
                                </div>

                                <div class="mb-3">
                                    <label for="applicant_monthly_income" class="form-label">Monthly Income <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="applicant_monthly_income" name="monthly_income" placeholder="e.g., 50000" min="0" required>
                                    </div>
                                    <small class="text-muted">Your monthly income helps the landlord assess your application.</small>
                                </div>

                                <!-- Move-in Date -->
                                <div class="mb-3">
                                    <label for="move_in_date" class="form-label">Preferred Move-in Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="move_in_date" name="move_in_date" min="{{ date('Y-m-d') }}" required>
                                </div>

                                <!-- Additional Notes -->
                                <div class="mb-3">
                                    <label for="applicant_notes" class="form-label">Message to Landlord (Optional)</label>
                                    <textarea class="form-control" id="applicant_notes" name="notes" rows="3" placeholder="Tell the landlord why you're interested in this unit..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-1"></i>Submit Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


