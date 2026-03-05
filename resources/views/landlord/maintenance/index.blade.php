@extends('layouts.landlord-app')

@section('title', 'Maintenance Requests')

@push('styles')
@php
    $cssPath = public_path('css/maintenance.css');
    $version = file_exists($cssPath) ? filemtime($cssPath) : time();
@endphp
<link rel="stylesheet" href="{{ url('/css/maintenance.css') }}?v={{ $version }}">
@endpush

@section('content')
<div class="maintenance-container">
    <!-- Header Section -->
    <div class="maintenance-header">
        <div class="header-title">
            <h1><i class="fas fa-tools"></i> Maintenance Requests</h1>
            <p class="subtitle">Manage and track maintenance tickets from your tenants</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
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
            <div class="stat-icon urgent">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-details">
                <h3>{{ $stats['urgent'] }}</h3>
                <p>Urgent</p>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
        <form method="GET" action="{{ route('landlord.maintenance.index') }}" class="filters-form">
            <div class="filter-group">
                <label><i class="fas fa-search"></i> Search</label>
                <input type="text" name="search" placeholder="Search by title or description..." value="{{ request('search') }}">
            </div>
            
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
            
            <div class="filter-group">
                <label><i class="fas fa-exclamation-circle"></i> Priority</label>
                <select name="priority">
                    <option value="all" {{ request('priority') == 'all' ? 'selected' : '' }}>All Priorities</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-tag"></i> Category</label>
                <select name="category">
                    <option value="all" {{ request('category') == 'all' ? 'selected' : '' }}>All Categories</option>
                    <option value="plumbing" {{ request('category') == 'plumbing' ? 'selected' : '' }}>Plumbing</option>
                    <option value="electrical" {{ request('category') == 'electrical' ? 'selected' : '' }}>Electrical</option>
                    <option value="hvac" {{ request('category') == 'hvac' ? 'selected' : '' }}>HVAC</option>
                    <option value="appliance" {{ request('category') == 'appliance' ? 'selected' : '' }}>Appliance</option>
                    <option value="structural" {{ request('category') == 'structural' ? 'selected' : '' }}>Structural</option>
                    <option value="cleaning" {{ request('category') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                    <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                <a href="{{ route('landlord.maintenance.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                <a href="{{ route('landlord.maintenance.create') }}" class="btn btn-success" style="margin-left: auto;">
                    <i class="fas fa-plus"></i> Create Maintenance Request
                </a>
            </div>
        </form>
    </div>

    <!-- Maintenance Requests Table -->
    <div class="maintenance-table-container">
        @if($maintenanceRequests->count() > 0)
            <table class="maintenance-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Property/Unit</th>
                        <th>Tenant</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Requested Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($maintenanceRequests as $request)
                        <tr class="request-row {{ $request->priority == 'urgent' ? 'urgent-row' : '' }}">
                            <td>#{{ $request->id }}</td>
                            <td>
                                <div class="request-title">{{ $request->title }}</div>
                                <div class="request-description-preview">{{ Str::limit($request->description, 50) }}</div>
                            </td>
                            <td>
                                <div class="unit-info">
                                    <strong>{{ $request->unit->apartment->name ?? 'N/A' }}</strong>
                                    <span>Unit {{ $request->unit->unit_number ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="tenant-info">
                                    @if($request->tenant)
                                        {{ $request->tenant->tenantProfile->name ?? $request->tenant->email }}
                                    @else
                                        <span class="text-muted"><i>Landlord</i></span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="category-badge category-{{ $request->category }}">
                                    <i class="fas fa-{{ $request->category == 'plumbing' ? 'water' : ($request->category == 'electrical' ? 'bolt' : ($request->category == 'hvac' ? 'wind' : 'tools')) }}"></i>
                                    {{ ucfirst($request->category) }}
                                </span>
                            </td>
                            <td>
                                <span class="priority-badge priority-{{ $request->priority }}">
                                    {{ ucfirst($request->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ str_replace('_', '-', $request->status) }}">
                                    {{ ucwords(str_replace('_', ' ', $request->status)) }}
                                </span>
                            </td>
                                            <td>
                                                @if($request->assignedStaff)
                                                    <div class="staff-info">
                                                        <i class="fas fa-user"></i> {{ $request->assignedStaff->staffProfile->name ?? $request->assignedStaff->email }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">Not assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $request->requested_date->format('M d, Y') }}</div>
                                                @if($request->expected_completion_date)
                                                    <small class="text-muted">Due: {{ $request->expected_completion_date->format('M d, Y') }}</small>
                                                    @if($request->expected_completion_date->isPast() && $request->status != 'completed')
                                                        <br><span class="priority-badge priority-urgent" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">Overdue</span>
                                                    @endif
                                                @endif
                                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('landlord.maintenance.show', $request->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <button onclick="deleteMaintenanceRequest({{ $request->id }}, '{{ addslashes($request->title) }}')" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-container">
                {{ $maintenanceRequests->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>No Maintenance Requests</h3>
                <p>There are no maintenance requests matching your filters.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function deleteMaintenanceRequest(requestId, requestTitle) {
    if (confirm(`Are you sure you want to delete the maintenance request "${requestTitle}"?\n\nThis action cannot be undone.`)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/landlord/maintenance/${requestId}`;
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Add method spoofing for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Append to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

@endsection

