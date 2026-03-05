@extends('layouts.landlord-app')

@section('title', 'Landlord Dashboard')

@section('content')
<!-- Header -->
<div class="content-header mb-4">
    <h1 class="fw-bold">Landlord Portal</h1>
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif

<!-- Welcome Section -->
<div class="welcome-section mb-4">
    @php 
        $user = auth()->user();
        // Query profile directly from database to ensure fresh data
        $landlordProfile = \App\Models\LandlordProfile::where('user_id', $user->id)->first();
        // Get name directly from profile, checking if it exists and is not empty or "User" or "New User"
        $profileName = $landlordProfile ? trim($landlordProfile->name ?? '') : '';
        $landlordName = (!empty($profileName) && $profileName !== 'User' && $profileName !== 'New User') 
            ? $profileName 
            : ($user->email ?? 'Landlord');
        $firstName = trim(explode(' ', $landlordName)[0] ?? $landlordName);
    @endphp
    <h2>Welcome back, {{ $firstName }}!</h2>
    <p>Here's an overview of your property portfolio</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_properties'] ?? 0 }}</div>
        <div class="stat-label">Total Properties</div>
        <div class="stat-sublabel">In your portfolio</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_units'] ?? 0 }}</div>
        <div class="stat-label">Total Units</div>
        <div class="stat-sublabel">Available for rent</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['occupied_units'] ?? 0 }}</div>
        <div class="stat-label">Occupied Units</div>
        <div class="stat-sublabel">Currently rented</div>
    </div>
    <div class="stat-card revenue-card">
        <div class="stat-value revenue-value">₱{{ number_format($stats['total_revenue'] ?? 0, 0) }}</div>
        <div class="stat-label">Monthly Revenue</div>
        <div class="stat-sublabel">From occupied units</div>
    </div>
</div>

<!-- Occupancy Rate Summary -->
@if(($stats['total_units'] ?? 0) > 0)
    <div class="property-summary mb-4">
        <div class="occupancy-rate d-flex align-items-center justify-content-between">
            <div>
                <div class="occupancy-percentage">{{ round((($stats['occupied_units'] ?? 0) / $stats['total_units']) * 100) }}%</div>
                <div class="occupancy-label">Occupancy Rate</div>
            </div>
            <div class="text-end">
                <div class="mb-1" style="font-size: 0.875rem; color: #64748b;">{{ $stats['occupied_units'] ?? 0 }} of {{ $stats['total_units'] }} units occupied</div>
                <div class="" style="font-size: 0.75rem; color: #94a3b8;">{{ $stats['available_units'] ?? 0 }} units available</div>
            </div>
        </div>
    </div>
@endif

<!-- Content Grid -->
<div class="content-grid">
    <!-- Recent Units -->
    <div class="activity-section">
        <div class="section-header mb-3 d-flex justify-content-between align-items-center">
            <h3 class="section-title">Recent Units</h3>
            <a href="{{ route('landlord.units') }}" class="btn btn-primary"><i class="fas fa-eye"></i> View All</a>
        </div>
        <table class="activity-table w-100">
            <thead>
                <tr><th>Unit</th><th>Property</th><th>Status</th><th>Rent</th></tr>
            </thead>
            <tbody>
            @if(isset($recentUnits) && count($recentUnits) > 0)
                @foreach($recentUnits->take(5) as $unit)
                <tr>
                    <td>{{ $unit->unit_number }}</td>
                    <td>{{ $unit->apartment->name ?? 'N/A' }}</td>
                    <td>
                        @if($unit->status === 'available')
                            <span class="status-badge status-available">Available</span>
                        @elseif($unit->status === 'occupied')
                            <span class="status-badge status-occupied">Occupied</span>
                        @else
                            <span class="status-badge status-maintenance">Maintenance</span>
                        @endif
                    </td>
                    <td>₱{{ number_format($unit->rent_amount ?? 0, 0) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="text-center text-muted p-4">
                        No units found. <a href="{{ route('landlord.create-apartment') }}" style="color: #f97316;">Add your first property</a>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3 class="section-title mb-4">Quick Actions</h3>
        <a href="{{ route('landlord.create-apartment') }}" class="action-btn"><i class="fas fa-plus-circle"></i> Add New Property</a>
        <a href="{{ route('landlord.apartments') }}" class="action-btn"><i class="fas fa-building"></i> Manage Properties</a>
        <a href="{{ route('landlord.units') }}" class="action-btn"><i class="fas fa-door-open"></i> Manage Units</a>
        <a href="#" class="action-btn"><i class="fas fa-users"></i> Tenant Directory</a>
        @if(($stats['total_units'] ?? 0) > 0)
        <div class="mt-4">
            <h4 class="mb-3" style="font-size: 0.88rem; font-weight: 600; color: #1e293b;">Portfolio Summary</h4>
            <div class="p-2 bg-info bg-opacity-10 rounded mb-2 border-start border-4 border-info">
                <span style="font-size: 0.78rem; color: #0369a1;">Available Units</span> <span class="float-end fw-bold" style="color: #0369a1;">{{ $stats['available_units'] ?? 0 }}</span>
            </div>
            <div class="p-2 bg-success bg-opacity-10 rounded mb-2 border-start border-4 border-success">
                <span style="font-size: 0.78rem; color: #047857;">Occupied Units</span> <span class="float-end fw-bold" style="color: #047857;">{{ $stats['occupied_units'] ?? 0 }}</span>
            </div>
            @if(($stats['total_units'] - $stats['occupied_units'] - $stats['available_units']) > 0)
            <div class="p-2 bg-warning bg-opacity-25 rounded border-start border-4 border-warning">
                <span style="font-size: 0.78rem; color: #d97706;">Maintenance</span> <span class="float-end fw-bold" style="color: #d97706;">{{ $stats['total_units'] - $stats['occupied_units'] - $stats['available_units'] }}</span>
            </div>
            @endif
        </div>
        @else
        <div class="mt-4 p-2 bg-warning bg-opacity-25 rounded border-start border-4 border-warning">
            <h4 class="mb-2" style="color: #d97706; font-size: .88rem; font-weight:600;"><i class="fas fa-info-circle"></i> Get Started</h4>
            <p class="mb-0" style="color: #92400e; font-size: .75rem;">Add your first property to start managing your rental portfolio.</p>
        </div>
        @endif
    </div>
</div>
@endsection 