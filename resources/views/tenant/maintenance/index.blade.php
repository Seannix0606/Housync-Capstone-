@extends('layouts.app')

@section('title', 'My Maintenance Requests')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/maintenance.css') }}">
@endpush

@section('content')
<div class="maintenance-container">
    <!-- Header Section -->
    <div class="maintenance-header">
        <div class="header-title">
            <h1><i class="fas fa-tools"></i> My Maintenance Requests</h1>
            <p class="subtitle">View and manage your maintenance requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('tenant.maintenance.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Request
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-details">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Requests</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3>{{ $stats['pending'] }}</h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon in-progress">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="stat-details">
                <h3>{{ $stats['in_progress'] }}</h3>
                <p>In Progress</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon completed">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3>{{ $stats['completed'] }}</h3>
                <p>Completed</p>
            </div>
        </div>
    </div>

    <!-- Current Assignment Info -->
    <div class="assignment-info-card">
        <div class="assignment-header">
            <h3><i class="fas fa-building"></i> Your Current Unit</h3>
        </div>
        <div class="assignment-body">
            <div class="assignment-details">
                <div class="detail-item">
                    <span class="detail-label">Property:</span>
                    <span class="detail-value">{{ $activeAssignment->unit->apartment->name }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Unit:</span>
                    <span class="detail-value">Unit {{ $activeAssignment->unit->unit_number }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Landlord:</span>
                    <span class="detail-value">{{ $activeAssignment->landlord->name }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <form method="GET" action="{{ route('tenant.maintenance.index') }}" class="filters-form">
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> Status</label>
                <select name="status">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
                <a href="{{ route('tenant.maintenance.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Maintenance Requests List -->
    <div class="maintenance-list-container">
        @if($maintenanceRequests->count() > 0)
            <div class="requests-grid">
                @foreach($maintenanceRequests as $request)
                    <div class="request-card {{ $request->priority == 'urgent' ? 'urgent-card' : '' }}">
                        <div class="request-card-header">
                            <div class="request-title-row">
                                <h3>{{ $request->title }}</h3>
                                <span class="request-id">#{{ $request->id }}</span>
                            </div>
                            <div class="request-badges">
                                <span class="priority-badge priority-{{ $request->priority }}">
                                    {{ ucfirst($request->priority) }}
                                </span>
                                <span class="status-badge status-{{ str_replace('_', '-', $request->status) }}">
                                    {{ ucwords(str_replace('_', ' ', $request->status)) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="request-card-body">
                            <div class="request-description">
                                <p>{{ Str::limit($request->description, 120) }}</p>
                            </div>
                            
                            <div class="request-meta">
                                <div class="meta-item">
                                    <i class="fas fa-tag"></i>
                                    <span class="category-badge category-{{ $request->category }}">
                                        {{ ucfirst($request->category) }}
                                    </span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ $request->requested_date->format('M d, Y') }}</span>
                                </div>
                                @if($request->assignedStaff)
                                <div class="meta-item">
                                    <i class="fas fa-user-cog"></i>
                                    <span>{{ $request->assignedStaff->staffProfile->name ?? $request->assignedStaff->email }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="request-card-footer">
                            <a href="{{ route('tenant.maintenance.show', $request->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            @if(!in_array($request->status, ['completed', 'cancelled']))
                                <form method="POST" action="{{ route('tenant.maintenance.cancel', $request->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this request?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                {{ $maintenanceRequests->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>No Maintenance Requests</h3>
                <p>You haven't submitted any maintenance requests yet.</p>
                <a href="{{ route('tenant.maintenance.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Your First Request
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

