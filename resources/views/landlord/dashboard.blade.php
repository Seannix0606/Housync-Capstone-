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

@php
    $tu = (int) ($stats['total_units'] ?? 0);
    $ou = (int) ($stats['occupied_units'] ?? 0);
    $occPct = $tu > 0 ? (int) round(($ou / $tu) * 100) : 0;
    $occDeg = $occPct * 3.6;
    $barHeights = [42, 68, 52, 88, 61, 74];
@endphp

<!-- Stats Grid -->
<div class="stats-grid saas-stats-grid mb-4">
    <div class="stat-card saas-stat-card">
        <div class="saas-stat-icon saas-stat-icon-blue"><i class="fas fa-building"></i></div>
        <div class="stat-value">{{ $stats['total_properties'] ?? 0 }}</div>
        <div class="stat-label">Total Properties</div>
        <div class="stat-sublabel">In your portfolio</div>
    </div>
    <div class="stat-card saas-stat-card">
        <div class="saas-stat-icon saas-stat-icon-orange"><i class="fas fa-door-open"></i></div>
        <div class="stat-value">{{ $stats['total_units'] ?? 0 }}</div>
        <div class="stat-label">Total Units</div>
        <div class="stat-sublabel">Across all properties</div>
    </div>
    <div class="stat-card saas-stat-card">
        <div class="saas-stat-icon saas-stat-icon-green"><i class="fas fa-user-check"></i></div>
        <div class="stat-value">{{ $stats['occupied_units'] ?? 0 }}</div>
        <div class="stat-label">Occupied Units</div>
        <div class="stat-sublabel">Currently rented</div>
    </div>
    <div class="stat-card saas-stat-card stat-card-feature">
        <div class="saas-stat-icon" style="background:rgba(255,255,255,0.2);color:#fff;"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-value revenue-value">₱{{ number_format($stats['total_revenue'] ?? 0, 0) }}</div>
        <div class="stat-label">Monthly Revenue</div>
        <div class="stat-sublabel">From occupied units</div>
    </div>
</div>

<!-- Charts row -->
<div class="saas-charts-row mb-4">
    <div class="saas-widget-card">
        <h3 class="saas-widget-title">Portfolio activity</h3>
        <p class="text-muted small mb-3 mb-md-0" style="font-size:0.8rem;">Illustrative trend — last six periods</p>
        <div class="saas-bar-chart" role="img" aria-label="Bar chart placeholder">
            @foreach($barHeights as $h)
                <div class="saas-bar" style="height: {{ $h }}%;"></div>
            @endforeach
        </div>
    </div>
    <div class="saas-widget-card">
        <h3 class="saas-widget-title">Occupancy split</h3>
        <div class="saas-donut-wrap">
            <div class="saas-donut" style="background: conic-gradient(#2563eb 0deg {{ $occDeg }}deg, #93c5fd {{ $occDeg }}deg 360deg);">
                <div class="saas-donut-label">
                    <span class="saas-donut-pct">{{ $occPct }}%</span>
                    <span class="saas-donut-caption">Occupied</span>
                </div>
            </div>
            <div class="saas-donut-legend">
                <div><span class="saas-legend-dot blue"></span>Occupied ({{ $ou }})</div>
                <div><span class="saas-legend-dot orange"></span>Other ({{ max(0, $tu - $ou) }})</div>
            </div>
        </div>
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
                        No units found. <a href="{{ route('landlord.create-apartment') }}" style="color: #3b82f6;">Add your first property</a>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
</div>
@endsection 