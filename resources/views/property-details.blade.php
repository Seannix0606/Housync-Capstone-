<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $property->title }} - HouseSync</title>
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
    </style>
</head>
<body>
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
            <h1>{{ $property->title }}</h1>
            <p class="text-muted mb-0">
                <i class="fas fa-map-marker-alt me-1"></i>
                {{ $property->address ?? 'Location not specified' }}
            </p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <div class="col-lg-8">
                <!-- Property Image -->
                @php($img = $property->image_url)
                @if($img)
                    <img src="{{ $img }}" alt="{{ $property->title }}" class="property-image-main mb-4">
                @else
                    <div class="property-image-placeholder mb-4">
                        <div class="text-center">
                            <i class="fas fa-home fa-5x mb-3"></i>
                            <h3>No Image Available</h3>
                        </div>
                    </div>
                @endif

                <!-- Description -->
                <div class="property-info-card">
                    <h3>Description</h3>
                    <p>{{ $property->description ?? 'No description available.' }}</p>
                </div>

                <!-- Amenities -->
                @if($property->amenities->count() > 0)
                    <div class="property-info-card">
                        <h3>Amenities</h3>
                        <div>
                            @foreach($property->amenities as $amenity)
                                <span class="amenity-badge">
                                    <i class="{{ $amenity->icon }}"></i>
                                    {{ $amenity->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <!-- Price & Details -->
                <div class="property-info-card">
                    <h2 class="text-primary">₱{{ number_format($property->price, 2) }}</h2>
                    <p class="text-muted">per month</p>

                    <hr>

                    <div class="mb-3">
                        <strong>Type:</strong> {{ ucfirst($property->type) }}
                    </div>
                    <div class="mb-3">
                        <strong>Bedrooms:</strong> {{ $property->bedrooms }}
                    </div>
                    <div class="mb-3">
                        <strong>Bathrooms:</strong> {{ $property->bathrooms }}
                    </div>
                    @if($property->area)
                        <div class="mb-3">
                            <strong>Area:</strong> {{ number_format($property->area) }} m²
                        </div>
                    @endif
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge {{ $property->availability_status == 'available' ? 'bg-success' : 'bg-danger' }}">
                            {{ ucfirst($property->availability_status) }}
                        </span>
                    </div>

                    <hr>

                    <button class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-envelope me-1"></i> Contact Landlord
                    </button>
                    <button class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-calendar me-1"></i> Schedule Viewing
                    </button>
                    
                    @php
                        // Check if this property has a linked unit
                        $linkedUnit = $property->getUnit();
                        $hasAvailableUnit = $linkedUnit && $linkedUnit->status === 'available';
                    @endphp
                    
                    @if($property->availability_status === 'available')
                        @auth
                            @if(Auth::user()->role === 'tenant')
                                @if($hasAvailableUnit)
                                    <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#applyTenantModal">
                                        <i class="fas fa-file-signature me-1"></i> Apply as Tenant
                                    </button>
                                @else
                                    <button class="btn btn-secondary w-100" disabled title="This listing doesn't have units configured yet">
                                        <i class="fas fa-file-signature me-1"></i> Applications Not Available
                                    </button>
                                    <small class="text-muted d-block mt-2 text-center">
                                        <i class="fas fa-info-circle me-1"></i>Contact landlord to inquire
                                    </small>
                                @endif
                            @endif
                        @else
                            <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#loginRequiredModal">
                                <i class="fas fa-file-signature me-1"></i> Apply as Tenant
                            </button>
                        @endauth
                    @endif
                </div>
            </div>
        </div>

        <!-- Related Properties -->
        @if($relatedProperties->count() > 0)
            <div class="mt-5">
                <h3 class="mb-4">Similar Properties</h3>
                <div class="row">
                    @foreach($relatedProperties as $related)
                        <div class="col-md-3 mb-4">
                            <a href="{{ route('property.show', $related->slug) }}" class="related-property-card">
                                @php($relatedImg = $related->image_url)
                                @if($relatedImg)
                                    <img src="{{ $relatedImg }}" alt="{{ $related->title }}" style="width: 100%; height: 200px; object-fit: cover;">
                                @else
                                    <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white;">
                                        <div>No Image</div>
                                    </div>
                                @endif
                                <div class="p-3">
                                    <h5>{{ Str::limit($related->title, 30) }}</h5>
                                    <p class="text-primary mb-0 fw-bold">₱{{ number_format($related->price, 2) }}</p>
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
                        <p class="text-muted">To apply for this property, you need to have an account. Please login or register to continue.</p>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus me-2"></i>Register
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
                        <form id="applyTenantForm" method="POST" action="{{ route('tenant.apply', $property->id) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Application for:</strong> {{ $property->title }} <br>
                                    <strong>Monthly Rent:</strong> ₱{{ number_format($property->price, 2) }}
                                </div>

                                <!-- Personal Information -->
                                <h6 class="mb-3 fw-bold text-primary">
                                    <i class="fas fa-user me-2"></i>Personal Information
                                </h6>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="applicant_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="applicant_name" name="name" value="{{ Auth::user()->name }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="applicant_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="applicant_phone" name="phone" value="{{ Auth::user()->phone }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="applicant_address" class="form-label">Current Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="applicant_address" name="address" value="{{ Auth::user()->address }}" required>
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


                                <!-- Additional Notes -->
                                <div class="mb-3">
                                    <label for="applicant_notes" class="form-label">Message to Landlord (Optional)</label>
                                    <textarea class="form-control" id="applicant_notes" name="notes" rows="3" placeholder="Tell the landlord why you're interested in this property..."></textarea>
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

