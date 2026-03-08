@extends('layouts.super-admin-app')

@section('title', 'Properties')

@push('styles')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .content-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }


        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid #3b82f6;
            text-align: center;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .page-section {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .section-subtitle {
            color: #64748b;
            font-size: 1rem;
            margin-top: 0.25rem;
        }

        .filters-section {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .property-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .property-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #3b82f6;
        }

        .property-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .property-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .property-landlord {
            font-size: 0.875rem;
            color: #64748b;
        }

        .property-status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #d1fae5;
            color: #059669;
        }

        .property-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .info-item i {
            width: 16px;
            text-align: center;
            color: #3b82f6;
        }

        .property-stats {
            display: flex;
            justify-content: space-between;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-item-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .stat-item-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #047857;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 4rem;
            color: #94a3b8;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .empty-text {
            color: #64748b;
            margin-bottom: 2rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            text-decoration: none;
            color: #374151;
            font-size: 0.875rem;
        }

        .pagination a:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .pagination .active span {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }

        /* Dark Mode Styles */
        body.dark-mode .content-header h1 {
            color: #f1f5f9 !important;
        }

        body.dark-mode .stat-card {
            background: #1e293b !important;
            color: #e2e8f0;
        }

        body.dark-mode .stat-value {
            color: #f1f5f9 !important;
        }

        body.dark-mode .stat-label {
            color: #94a3b8 !important;
        }

        body.dark-mode .page-section {
            background: #1e293b !important;
            color: #e2e8f0;
        }

        body.dark-mode .section-title {
            color: #f1f5f9 !important;
        }

        body.dark-mode .section-subtitle {
            color: #94a3b8 !important;
        }

        body.dark-mode .form-label {
            color: #e2e8f0 !important;
        }

        body.dark-mode .form-control {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .form-control:focus {
            border-color: #3b82f6 !important;
            background: #0f172a !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .form-control::placeholder {
            color: #64748b !important;
        }

        body.dark-mode .property-card {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0;
        }

        body.dark-mode .property-card:hover {
            border-color: #3b82f6 !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        body.dark-mode .property-title {
            color: #f1f5f9 !important;
        }

        body.dark-mode .property-landlord {
            color: #94a3b8 !important;
        }

        body.dark-mode .info-item {
            color: #94a3b8 !important;
        }

        body.dark-mode .info-item i {
            color: #60a5fa !important;
        }

        body.dark-mode .property-stats {
            background: #0f172a !important;
        }

        body.dark-mode .stat-item-value {
            color: #f1f5f9 !important;
        }

        body.dark-mode .stat-item-label {
            color: #94a3b8 !important;
        }

        body.dark-mode .status-active {
            background: #064e3b !important;
            color: #6ee7b7 !important;
        }

        body.dark-mode .empty-icon {
            color: #475569 !important;
        }

        body.dark-mode .empty-title {
            color: #f1f5f9 !important;
        }

        body.dark-mode .empty-text {
            color: #94a3b8 !important;
        }

        body.dark-mode .alert-success {
            background: #064e3b !important;
            border-color: #065f46 !important;
            color: #6ee7b7 !important;
        }

        body.dark-mode .pagination a,
        body.dark-mode .pagination span {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        body.dark-mode .pagination a:hover {
            background: #334155 !important;
            border-color: #475569 !important;
        }
    </style>
@endpush

@section('content')
            <!-- Header -->
            <div class="content-header">
                <div>
                    <h1>Property Management</h1>
                    <p style="color: #64748b; margin-top: 0.5rem;">View and manage all properties in the system</p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ \App\Models\Property::count() }}</div>
                    <div class="stat-label">Total Properties</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ \App\Models\Unit::count() }}</div>
                    <div class="stat-label">Total Units</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ \App\Models\Unit::where('status', 'occupied')->count() }}</div>
                    <div class="stat-label">Occupied Units</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ \App\Models\User::approvedLandlords()->count() }}</div>
                    <div class="stat-label">Active Landlords</div>
                </div>
            </div>

            <!-- Properties Section -->
            <div class="page-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">All Properties</h2>
                        <p class="section-subtitle">View properties from all landlords in the system</p>
                    </div>
                </div>

                <!-- Search and Filters -->
                <form method="GET" action="{{ route('super-admin.apartments') }}">
                    <div class="filters-section">
                        <div class="form-group">
                            <label class="form-label">Search Properties</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by property name, address, or landlord..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Landlord</label>
                            <select name="landlord" class="form-control">
                                <option value="">All Landlords</option>
                                @foreach(\App\Models\User::approvedLandlords()->get() as $landlord)
                                    <option value="{{ $landlord->id }}" {{ request('landlord') == $landlord->id ? 'selected' : '' }}>
                                        {{ $landlord->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>

                @php
                    $apartments = \App\Models\Property::with(['landlord', 'units'])
                        ->when(request('search'), function($query) {
                            $search = request('search');
                            $query->where(function($query) use ($search) {
                                $query->where('name', 'like', '%' . $search . '%')
                                  ->orWhere('address', 'like', '%' . $search . '%')
                                  ->orWhereHas('landlord', function($subQuery) use ($search) {
                                      $subQuery->where('name', 'like', '%' . $search . '%');
                                  });
                            });
                        })
                        ->when(request('landlord'), function($query) {
                            $query->where('landlord_id', request('landlord'));
                        })
                        ->latest()
                        ->paginate(12);
                @endphp

                @if($apartments->count() > 0)
                    <div class="properties-grid">
                        @foreach($apartments as $apartment)
                            <div class="property-card">
                                <div class="property-header">
                                    <div>
                                        <h3 class="property-title">{{ $apartment->name }}</h3>
                                        <p class="property-landlord">
                                            <i class="fas fa-user"></i> {{ $apartment->landlord->name ?? 'Unknown Landlord' }}
                                        </p>
                                    </div>
                                    <span class="property-status status-active">Active</span>
                                </div>

                                <div class="property-info">
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span title="{{ $apartment->address }}">{{ Str::limit($apartment->address ?? 'No address', 30) }}</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-door-open"></i>
                                        <span>{{ $apartment->units->count() }} Units</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>{{ $apartment->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-users"></i>
                                        <span>{{ $apartment->units->where('status', 'occupied')->count() }} Occupied</span>
                                    </div>
                                </div>

                                <div class="property-stats">
                                    <div class="stat-item">
                                        <div class="stat-item-value">{{ $apartment->units->count() }}</div>
                                        <div class="stat-item-label">Total Units</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-item-value">{{ $apartment->units->where('status', 'occupied')->count() }}</div>
                                        <div class="stat-item-label">Occupied</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-item-value">
                                            @if($apartment->units->count() > 0)
                                                {{ round(($apartment->units->where('status', 'occupied')->count() / $apartment->units->count()) * 100) }}%
                                            @else
                                                0%
                                            @endif
                                        </div>
                                        <div class="stat-item-label">Occupancy</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-item-value">₱{{ number_format($apartment->units->where('status', 'occupied')->sum('rent_amount') ?? 0, 0) }}</div>
                                        <div class="stat-item-label">Revenue</div>
                                    </div>
                                </div>

                                <div class="btn-group">
                                    <a href="#" class="btn btn-primary btn-sm" onclick="viewProperty({{ $apartment->id }})">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <a href="#" class="btn btn-secondary btn-sm" onclick="viewUnits({{ $apartment->id }})">
                                        <i class="fas fa-door-open"></i> View Units
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($apartments->hasPages())
                        <div class="pagination">
                            {{ $apartments->links() }}
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3 class="empty-title">No Properties Found</h3>
                        <p class="empty-text">
                            @if(request()->hasAny(['search', 'landlord']))
                                No properties match your search criteria. Try adjusting your filters.
                            @else
                                No properties have been added to the system yet.
                            @endif
                        </p>
                        @if(request()->hasAny(['search', 'landlord']))
                            <a href="{{ route('super-admin.apartments') }}" class="btn btn-primary">
                                <i class="fas fa-refresh"></i> Clear Filters
                            </a>
                        @endif
                    </div>
                @endif
            </div>
    <script>
        function viewProperty(apartmentId) {
            // You can implement property details modal or redirect to detail page
            alert('Property details for ID: ' + apartmentId + '\nThis would show detailed property information.');
        }

        function viewUnits(apartmentId) {
            // You can implement units view modal or redirect to units page
            alert('Units view for property ID: ' + apartmentId + '\nThis would show all units in this property.');
        }
    </script>
@endsection