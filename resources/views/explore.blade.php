<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Explore Properties - HouseSync</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }

        /* Header */
        .explore-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0 2rem;
        }

        .explore-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .explore-header p {
            font-size: 1.125rem;
            opacity: 0.95;
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .filter-bar .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
        }

        .filter-bar .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .filter-bar #quickSearch {
            border: 2px solid #e2e8f0;
            padding: 0.6rem 1rem;
        }

        .filter-bar #quickSearch:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Filter Modal Styles */
        .modal-content {
            border-radius: 16px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 1.25rem 1.5rem;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }

        .filter-group {
            margin-bottom: 1rem;
        }

        .filter-group label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
            display: block;
            font-size: 0.875rem;
        }

        .filter-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .filter-pill {
            padding: 0.5rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .filter-pill:hover {
            border-color: #667eea;
            background: #f1f5ff;
        }

        .filter-pill.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .amenity-checkbox {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .amenity-checkbox:hover {
            border-color: #667eea;
            background: #f8fafc;
        }

        .amenity-checkbox input[type="checkbox"] {
            margin-right: 0.75rem;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .amenity-checkbox.checked {
            border-color: #667eea;
            background: #f1f5ff;
        }

        /* Property Cards */
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .property-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .property-image-link {
            display: block;
            text-decoration: none;
            color: inherit;
        }

        .property-title-link {
            color: #1e293b;
            text-decoration: none;
            transition: color 0.2s;
        }

        .property-title-link:hover {
            color: #667eea;
        }

        .property-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: #e2e8f0;
        }

        .property-image-placeholder {
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            font-weight: 600;
        }

        /* Image Carousel Styles */
        .property-image-carousel {
            position: relative;
            width: 100%;
            height: 220px;
            overflow: hidden;
            background: #e2e8f0;
        }

        .carousel-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
        }

        .carousel-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .carousel-slide.active {
            opacity: 1;
            position: relative;
        }

        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .carousel-controls {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 0.5rem;
            pointer-events: none;
        }

        .carousel-btn {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            pointer-events: all;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .carousel-btn:hover {
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transform: scale(1.1);
        }

        .carousel-btn i {
            color: #667eea;
            font-size: 0.875rem;
        }

        .carousel-indicators {
            position: absolute;
            bottom: 0.75rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.5rem;
            pointer-events: none;
        }

        .carousel-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            pointer-events: all;
            transition: all 0.2s;
        }

        .carousel-dot.active {
            background: white;
            width: 24px;
            border-radius: 4px;
        }

        .property-content {
            padding: 1.25rem;
        }

        .property-type {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #f1f5ff;
            color: #667eea;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }

        .property-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .property-address {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .property-features {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            color: #64748b;
            font-size: 0.875rem;
        }

        .property-feature {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .property-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .property-availability {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .property-availability.available {
            background: #d1fae5;
            color: #065f46;
        }

        .property-availability.occupied {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Loading */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #64748b;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .explore-header h1 {
                font-size: 2rem;
            }

            .properties-grid {
                grid-template-columns: 1fr;
            }

            .filter-section {
                margin-top: 0;
            }
        }
        /* Navbar */
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-custom .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #667eea;
        }

        .navbar-custom .nav-link {
            color: #475569;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }

        .navbar-custom .nav-link:hover {
            color: #667eea;
        }

        .navbar-custom .nav-link .fa-user-circle {
            font-size: 1.1rem;
        }

        .navbar-custom a.nav-link:hover {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 8px;
        }

        .navbar-custom .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('login') }}">
                <i class="fas fa-home"></i> HouSync
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        @if(auth()->user()->role === 'tenant')
                            <li class="nav-item">
                                @if(auth()->user()->tenantAssignments()->exists())
                                    <a class="nav-link" href="{{ route('tenant.dashboard') }}" style="cursor: pointer;">
                                        <i class="fas fa-user-circle me-1"></i> {{ auth()->user()->name }}
                                    </a>
                                @else
                                    <a class="nav-link" href="{{ route('tenant.profile') }}" style="cursor: pointer;">
                                        <i class="fas fa-user-circle me-1"></i> {{ auth()->user()->name }}
                                    </a>
                                @endif
                            </li>
                        @endif
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('register') }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="explore-header">
        <div class="container">
            <h1><i class="fas fa-search me-2"></i>Explore Properties</h1>
            <p>Find your perfect place to live</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container" style="margin-top: -2rem; padding-bottom: 3rem;">
        <!-- Tenant Info Banner -->
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show mb-3" role="alert" style="margin-top: 1rem; border-radius: 12px; border-left: 4px solid #0dcaf0;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Welcome!</strong> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @auth
            @if(auth()->user()->role === 'tenant' && !auth()->user()->tenantAssignments()->exists())
                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert" style="margin-top: 1rem; border-radius: 12px; border-left: 4px solid #0dcaf0;">
                    <i class="fas fa-home me-2"></i>
                    <strong>Welcome, {{ auth()->user()->name }}!</strong> You're browsing as a prospect tenant. Contact landlords for the properties you're interested in to get assigned to a unit.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        @endauth
        
        <!-- Compact Filter Bar -->
        <div class="filter-bar">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fas fa-filter me-2"></i> Filters
                        <span class="badge bg-light text-primary ms-2" id="activeFiltersCount" style="display: none;">0</span>
                    </button>
                    <div class="flex-grow-1" style="max-width: 400px;">
                        <input type="text" id="quickSearch" class="form-control" placeholder="Quick search...">
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <select id="quickSort" class="form-select" style="width: auto;">
                        <option value="latest">Latest</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="featured">Featured</option>
                    </select>
                    <h6 class="mb-0 text-muted">
                        <span id="resultsCount">{{ $units->total() }}</span> found
                    </h6>
                </div>
            </div>
        </div>

        <!-- Filter Modal -->
        <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="filterModalLabel">
                            <i class="fas fa-sliders-h me-2"></i> Filter Properties
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="filterForm">
                            <div class="row g-3">
                                <!-- Search -->
                                <div class="col-12">
                                    <div class="filter-group">
                                        <label><i class="fas fa-search me-1"></i> Search</label>
                                        <input type="text" name="search" id="search" class="form-control" placeholder="Search properties...">
                                    </div>
                                </div>

                                <!-- Property Type -->
                                <div class="col-12 col-md-6">
                                    <div class="filter-group">
                                        <label><i class="fas fa-building me-1"></i> Property Type</label>
                                        <select name="type" id="type" class="form-select">
                                            <option value="">All Types</option>
                                            @foreach($propertyTypes as $type)
                                                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Availability -->
                                <div class="col-12 col-md-6">
                                    <div class="filter-group">
                                        <label><i class="fas fa-calendar-check me-1"></i> Availability</label>
                                        <select name="availability" id="availability" class="form-select">
                                            <option value="">All</option>
                                            <option value="available">Available</option>
                                            <option value="occupied">Occupied</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Price Range -->
                                <div class="col-12 col-md-6">
                                    <div class="filter-group">
                                        <label><i class="fas fa-dollar-sign me-1"></i> Min Price</label>
                                        <input type="number" name="min_price" id="min_price" class="form-control" placeholder="Min ₱">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="filter-group">
                                        <label><i class="fas fa-dollar-sign me-1"></i> Max Price</label>
                                        <input type="number" name="max_price" id="max_price" class="form-control" placeholder="Max ₱">
                                    </div>
                                </div>

                                <!-- Date Range -->
                                <div class="col-12 col-md-6">
                                    <div class="filter-group">
                                        <label><i class="fas fa-calendar me-1"></i> Available From</label>
                                        <input type="date" name="available_from" id="available_from" class="form-control">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="filter-group">
                                        <label><i class="fas fa-calendar me-1"></i> Available To</label>
                                        <input type="date" name="available_to" id="available_to" class="form-control">
                                    </div>
                                </div>

                                <!-- Amenities -->
                                <div class="col-12">
                                    <div class="filter-group">
                                        <label><i class="fas fa-star me-1"></i> Amenities</label>
                                        <div class="row g-2">
                                            @foreach($amenities as $amenity)
                                                <div class="col-6 col-md-4">
                                                    <label class="amenity-checkbox">
                                                        <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" class="amenity-input">
                                                        <span>
                                                            <i class="{{ $amenity->icon }} me-1"></i>
                                                            {{ $amenity->name }}
                                                        </span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Sort -->
                                <div class="col-12">
                                    <div class="filter-group">
                                        <label><i class="fas fa-sort me-1"></i> Sort By</label>
                                        <select name="sort_by" id="sort_by" class="form-select">
                                            <option value="latest">Latest</option>
                                            <option value="price_low">Price: Low to High</option>
                                            <option value="price_high">Price: High to Low</option>
                                            <option value="featured">Featured</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="clearFilters">
                            <i class="fas fa-times me-1"></i> Clear All
                        </button>
                        <button type="button" class="btn btn-primary" id="applyFilters">
                            <i class="fas fa-check me-1"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Properties Grid -->
        <div id="propertiesContainer">
            @include('partials.unit-cards', ['units' => $units])
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="d-flex justify-content-center">
            {{ $units->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Login Required Modal -->
    <div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title" id="loginRequiredModalLabel">
                        <i class="fas fa-user-lock me-2"></i>Login Required
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Setup CSRF token for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Sync quick search with modal search
            $('#quickSearch').on('input', function() {
                $('#search').val($(this).val());
            });

            // Sync quick sort with modal sort
            $('#quickSort').on('change', function() {
                $('#sort_by').val($(this).val());
                applyFilters();
            });

            // Quick search on Enter key
            $('#quickSearch').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    applyFilters();
                }
            });

            // Amenity checkbox styling
            $('.amenity-checkbox input').on('change', function() {
                if ($(this).is(':checked')) {
                    $(this).closest('.amenity-checkbox').addClass('checked');
                } else {
                    $(this).closest('.amenity-checkbox').removeClass('checked');
                }
                updateActiveFiltersCount();
            });

            // Update active filters count
            function updateActiveFiltersCount() {
                let count = 0;
                
                if ($('#search').val()) count++;
                if ($('#type').val()) count++;
                if ($('#availability').val()) count++;
                if ($('#min_price').val()) count++;
                if ($('#max_price').val()) count++;
                if ($('#available_from').val()) count++;
                if ($('#available_to').val()) count++;
                count += $('input[name="amenities[]"]:checked').length;

                if (count > 0) {
                    $('#activeFiltersCount').text(count).show();
                } else {
                    $('#activeFiltersCount').hide();
                }
            }

            // Update count when inputs change
            $('#filterForm input, #filterForm select').on('change input', updateActiveFiltersCount);

            // Apply filters
            function applyFilters(page = 1) {
                const formData = {
                    search: $('#search').val(),
                    type: $('#type').val(),
                    availability: $('#availability').val(),
                    min_price: $('#min_price').val(),
                    max_price: $('#max_price').val(),
                    available_from: $('#available_from').val(),
                    available_to: $('#available_to').val(),
                    sort_by: $('#sort_by').val(),
                    amenities: $('input[name="amenities[]"]:checked').map(function() {
                        return $(this).val();
                    }).get(),
                    page: page
                };

                // Sync quick sort
                $('#quickSort').val(formData.sort_by);

                // Show loading
                $('#loadingOverlay').addClass('active');

                $.ajax({
                    url: '{{ route("explore") }}',
                    method: 'GET',
                    data: formData,
                    success: function(response) {
                        $('#propertiesContainer').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#resultsCount').text(response.count);
                        
                        // Save filters to localStorage
                        localStorage.setItem('exploreFilters', JSON.stringify(formData));
                        
                        // Smooth scroll to top
                        $('html, body').animate({ scrollTop: 0 }, 300);
                    },
                    error: function(xhr) {
                        console.error('Filter error:', xhr);
                        alert('An error occurred while filtering properties.');
                    },
                    complete: function() {
                        $('#loadingOverlay').removeClass('active');
                    }
                });
            }

            // Apply filters button
            $('#applyFilters').on('click', function() {
                applyFilters();
                // Close modal
                const filterModal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
                if (filterModal) {
                    filterModal.hide();
                }
                updateActiveFiltersCount();
            });

            // Apply filters on Enter key in modal
            $('#filterForm input, #filterForm select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#applyFilters').click();
                }
            });

            // Clear filters
            $('#clearFilters').on('click', function() {
                $('#filterForm')[0].reset();
                $('#quickSearch').val('');
                $('#quickSort').val('latest');
                $('.amenity-checkbox').removeClass('checked');
                localStorage.removeItem('exploreFilters');
                applyFilters();
                updateActiveFiltersCount();
                // Close modal
                const filterModal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
                if (filterModal) {
                    filterModal.hide();
                }
            });

            // Handle pagination clicks
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const page = new URL(url).searchParams.get('page');
                applyFilters(page);
            });

            // Restore filters from localStorage
            const savedFilters = localStorage.getItem('exploreFilters');
            if (savedFilters) {
                const filters = JSON.parse(savedFilters);
                $('#search').val(filters.search || '');
                $('#quickSearch').val(filters.search || '');
                $('#type').val(filters.type || '');
                $('#availability').val(filters.availability || '');
                $('#min_price').val(filters.min_price || '');
                $('#max_price').val(filters.max_price || '');
                $('#available_from').val(filters.available_from || '');
                $('#available_to').val(filters.available_to || '');
                $('#sort_by').val(filters.sort_by || 'latest');
                $('#quickSort').val(filters.sort_by || 'latest');
                
                if (filters.amenities && filters.amenities.length > 0) {
                    filters.amenities.forEach(function(amenityId) {
                        $('input[name="amenities[]"][value="' + amenityId + '"]')
                            .prop('checked', true)
                            .closest('.amenity-checkbox').addClass('checked');
                    });
                }
            }

            // Update filter count on page load
            updateActiveFiltersCount();

            // Auto-apply filters on select change
            $('#type, #availability, #sort_by').on('change', function() {
                applyFilters();
            });
        });

        // Carousel functions
        function slideCarousel(carouselId, direction) {
            const carousel = document.querySelector(`[data-carousel-id="${carouselId}"]`);
            if (!carousel) return;

            const slides = carousel.querySelectorAll('.carousel-slide');
            const dots = carousel.querySelectorAll('.carousel-dot');
            const totalSlides = slides.length;

            if (totalSlides <= 1) return;

            let currentIndex = 0;
            slides.forEach((slide, index) => {
                if (slide.classList.contains('active')) {
                    currentIndex = index;
                }
            });

            // Calculate new index
            let newIndex = currentIndex + direction;
            if (newIndex < 0) {
                newIndex = totalSlides - 1;
            } else if (newIndex >= totalSlides) {
                newIndex = 0;
            }

            // Update slides
            slides[currentIndex].classList.remove('active');
            slides[newIndex].classList.add('active');

            // Update dots
            dots[currentIndex].classList.remove('active');
            dots[newIndex].classList.add('active');
        }

        function goToSlide(carouselId, index) {
            const carousel = document.querySelector(`[data-carousel-id="${carouselId}"]`);
            if (!carousel) return;

            const slides = carousel.querySelectorAll('.carousel-slide');
            const dots = carousel.querySelectorAll('.carousel-dot');

            // Remove active from all
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));

            // Add active to selected
            if (slides[index]) slides[index].classList.add('active');
            if (dots[index]) dots[index].classList.add('active');
        }
    </script>
</body>
</html>
