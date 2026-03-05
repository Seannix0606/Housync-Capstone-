@extends('layouts.landlord-app')

@section('title', 'Maintenance Request Details')

@push('styles')
@php
    $cssPath = public_path('css/maintenance.css');
    $version = file_exists($cssPath) ? filemtime($cssPath) : time();
@endphp
<link rel="stylesheet" href="{{ url('/css/maintenance.css') }}?v={{ $version }}">
@endpush

@section('content')
<div class="maintenance-container">
    <!-- Header with Back Button -->
    <div class="maintenance-header">
        <div class="header-title">
            <a href="{{ route('landlord.maintenance.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Maintenance
            </a>
            <h1><i class="fas fa-tools"></i> Maintenance Request #{{ $maintenanceRequest->id }}</h1>
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

    <div class="maintenance-details-grid">
        <!-- Main Information Card -->
        <div class="details-card main-card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Request Information</h2>
                <div class="header-badges">
                    <span class="priority-badge priority-{{ $maintenanceRequest->priority }}">
                        {{ ucfirst($maintenanceRequest->priority) }} Priority
                    </span>
                    <span class="status-badge status-{{ str_replace('_', '-', $maintenanceRequest->status) }}">
                        {{ ucwords(str_replace('_', ' ', $maintenanceRequest->status)) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <label><i class="fas fa-heading"></i> Title:</label>
                    <div class="info-value">{{ $maintenanceRequest->title }}</div>
                </div>
                
                <div class="info-row">
                    <label><i class="fas fa-align-left"></i> Description:</label>
                    <div class="info-value description-box">{{ $maintenanceRequest->description }}</div>
                </div>
                
                <div class="info-row">
                    <label><i class="fas fa-tag"></i> Category:</label>
                    <div class="info-value">
                        <span class="category-badge category-{{ $maintenanceRequest->category }}">
                            <i class="fas fa-{{ $maintenanceRequest->category == 'plumbing' ? 'water' : ($maintenanceRequest->category == 'electrical' ? 'bolt' : ($maintenanceRequest->category == 'hvac' ? 'wind' : 'tools')) }}"></i>
                            {{ ucfirst($maintenanceRequest->category) }}
                        </span>
                    </div>
                </div>
                
                <div class="info-row">
                    <label><i class="fas fa-calendar"></i> Requested Date:</label>
                    <div class="info-value">{{ $maintenanceRequest->requested_date->format('F d, Y') }}</div>
                </div>
                
                @if($maintenanceRequest->expected_completion_date)
                <div class="info-row">
                    <label><i class="fas fa-calendar-alt"></i> Expected Completion:</label>
                    <div class="info-value">
                        {{ $maintenanceRequest->expected_completion_date->format('F d, Y') }}
                        @if($maintenanceRequest->status != 'completed' && $maintenanceRequest->expected_completion_date->isPast())
                            <span class="priority-badge priority-urgent" style="margin-left: 0.5rem;">Overdue</span>
                        @endif
                    </div>
                </div>
                @endif
                
                @if($maintenanceRequest->completed_date)
                <div class="info-row">
                    <label><i class="fas fa-calendar-check"></i> Completed Date:</label>
                    <div class="info-value">{{ $maintenanceRequest->completed_date->format('F d, Y') }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Property and Tenant Information -->
        <div class="details-card">
            <div class="card-header">
                <h2><i class="fas fa-building"></i> Property & Tenant</h2>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <label><i class="fas fa-building"></i> Property:</label>
                    <div class="info-value">{{ $maintenanceRequest->unit->apartment->name ?? 'N/A' }}</div>
                </div>
                
                <div class="info-row">
                    <label><i class="fas fa-door-open"></i> Unit:</label>
                    <div class="info-value">Unit {{ $maintenanceRequest->unit->unit_number ?? 'N/A' }}</div>
                </div>
                
                <div class="info-row">
                    <label><i class="fas fa-user"></i> Tenant:</label>
                    <div class="info-value">
                        @if($maintenanceRequest->tenant)
                            {{ $maintenanceRequest->tenant->tenantProfile->name ?? $maintenanceRequest->tenant->email }}
                        @else
                            <span class="text-muted"><i class="fas fa-user-tie"></i> Landlord-created (Vacant/Managed Unit)</span>
                        @endif
                    </div>
                </div>
                
                @if($maintenanceRequest->tenant && $maintenanceRequest->tenant->email)
                <div class="info-row">
                    <label><i class="fas fa-envelope"></i> Email:</label>
                    <div class="info-value">{{ $maintenanceRequest->tenant->email }}</div>
                </div>
                @endif
                
                @if($maintenanceRequest->tenant && $maintenanceRequest->tenant->tenantProfile && $maintenanceRequest->tenant->tenantProfile->phone)
                <div class="info-row">
                    <label><i class="fas fa-phone"></i> Phone:</label>
                    <div class="info-value">{{ $maintenanceRequest->tenant->tenantProfile->phone }}</div>
                </div>
                @endif

                @if($maintenanceRequest->tenant_notes)
                <div class="info-row">
                    <label><i class="fas fa-comment"></i> {{ $maintenanceRequest->tenant ? 'Tenant Notes' : 'Notes' }}:</label>
                    <div class="info-value notes-box">{{ $maintenanceRequest->tenant_notes }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Staff Assignment Card -->
        <div class="details-card">
            <div class="card-header">
                <h2><i class="fas fa-user-cog"></i> Staff Assignment</h2>
            </div>
            <div class="card-body">
                @if($maintenanceRequest->assignedStaff)
                    <div class="assigned-staff-info">
                        <div class="staff-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="staff-details">
                            <h3>{{ $maintenanceRequest->assignedStaff->staffProfile->name ?? $maintenanceRequest->assignedStaff->email }}</h3>
                            @if($maintenanceRequest->assignedStaff->staffProfile)
                                <p><strong>Type:</strong> {{ ucwords(str_replace('_', ' ', $maintenanceRequest->assignedStaff->staffProfile->staff_type ?? 'N/A')) }}</p>
                                @if($maintenanceRequest->assignedStaff->staffProfile->phone)
                                    <p><strong>Phone:</strong> {{ $maintenanceRequest->assignedStaff->staffProfile->phone }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    @if($maintenanceRequest->status != 'completed' && $maintenanceRequest->status != 'cancelled')
                    <form method="POST" action="{{ route('landlord.maintenance.assign-staff', $maintenanceRequest->id) }}" class="reassign-form">
                        @csrf
                        <label>Filter by Staff Type (Optional):</label>
                        <select id="filterStaffType" class="filter-select" onchange="filterStaffByType()">
                            <option value="">All Staff Types</option>
                            <option value="maintenance_worker">Maintenance Worker</option>
                            <option value="plumber">Plumber</option>
                            <option value="electrician">Electrician</option>
                            <option value="cleaner">Cleaner</option>
                            <option value="painter">Painter</option>
                            <option value="carpenter">Carpenter</option>
                            <option value="security_guard">Security Guard</option>
                            <option value="gardener">Gardener</option>
                            <option value="other">Other</option>
                        </select>
                        
                        <label>Reassign to Different Staff:</label>
                        <select name="staff_id" id="staffSelect" required>
                            <option value="">Select Staff Member</option>
                            @foreach($availableStaff as $staff)
                                <option value="{{ $staff->id }}" 
                                        data-staff-type="{{ $staff->staffProfile->staff_type ?? 'other' }}"
                                        {{ $staff->id == $maintenanceRequest->assigned_staff_id ? 'selected' : '' }}>
                                    {{ $staff->staffProfile->name ?? $staff->email }} 
                                    @if($staff->staffProfile && $staff->staffProfile->staff_type)
                                        ({{ ucwords(str_replace('_', ' ', $staff->staffProfile->staff_type)) }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        
                        <label>Expected Completion Date (Optional):</label>
                        <input type="date" name="expected_completion_date" class="form-control" value="{{ $maintenanceRequest->expected_completion_date?->format('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                        <small class="form-text">Set a deadline for this maintenance task</small>
                        
                        <button type="submit" class="btn btn-primary"><i class="fas fa-user-check"></i> Reassign</button>
                    </form>
                    @endif
                @else
                    <div class="no-assignment">
                        <i class="fas fa-user-slash"></i>
                        <p>No staff assigned yet</p>
                    </div>
                    
                    @if($availableStaff->count() > 0)
                    <form method="POST" action="{{ route('landlord.maintenance.assign-staff', $maintenanceRequest->id) }}" class="assign-form">
                        @csrf
                        <label>Filter by Staff Type (Optional):</label>
                        <select id="filterStaffTypeAssign" class="filter-select" onchange="filterStaffByTypeAssign()">
                            <option value="">All Staff Types</option>
                            <option value="maintenance_worker">Maintenance Worker</option>
                            <option value="plumber">Plumber</option>
                            <option value="electrician">Electrician</option>
                            <option value="cleaner">Cleaner</option>
                            <option value="painter">Painter</option>
                            <option value="carpenter">Carpenter</option>
                            <option value="security_guard">Security Guard</option>
                            <option value="gardener">Gardener</option>
                            <option value="other">Other</option>
                        </select>
                        
                        <label>Assign Staff Member:</label>
                        <select name="staff_id" id="staffSelectAssign" required>
                            <option value="">Select Staff Member</option>
                            @foreach($availableStaff as $staff)
                                <option value="{{ $staff->id }}" data-staff-type="{{ $staff->staffProfile->staff_type ?? 'other' }}">
                                    {{ $staff->staffProfile->name ?? $staff->email }} 
                                    @if($staff->staffProfile && $staff->staffProfile->staff_type)
                                        ({{ ucwords(str_replace('_', ' ', $staff->staffProfile->staff_type)) }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        
                        <label>Expected Completion Date (Optional):</label>
                        <input type="date" name="expected_completion_date" class="form-control" min="{{ date('Y-m-d') }}">
                        <small class="form-text">Set a deadline for this maintenance task</small>
                        
                        <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Assign Staff</button>
                    </form>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No active staff available. Please add staff members first.
                    </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Status Management Card -->
        <div class="details-card">
            <div class="card-header">
                <h2><i class="fas fa-tasks"></i> Status Management</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('landlord.maintenance.update-status', $maintenanceRequest->id) }}" class="status-form">
                    @csrf
                    <label>Update Status:</label>
                    <select name="status" required>
                        <option value="pending" {{ $maintenanceRequest->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="assigned" {{ $maintenanceRequest->status == 'assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="in_progress" {{ $maintenanceRequest->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $maintenanceRequest->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $maintenanceRequest->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Status</button>
                </form>

                @if($maintenanceRequest->status != 'completed' && $maintenanceRequest->status != 'cancelled')
                <form method="POST" action="{{ route('landlord.maintenance.cancel', $maintenanceRequest->id) }}" class="cancel-form" onsubmit="return confirm('Are you sure you want to cancel this maintenance request?');">
                    @csrf
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Cancel Request</button>
                </form>
                @endif
            </div>
        </div>

        <!-- Staff Notes Card -->
        <div class="details-card full-width">
            <div class="card-header">
                <h2><i class="fas fa-sticky-note"></i> Staff Notes</h2>
            </div>
            <div class="card-body">
                @if($maintenanceRequest->staff_notes)
                    <div class="current-notes">
                        <p>{{ $maintenanceRequest->staff_notes }}</p>
                    </div>
                @endif
                
                @if($maintenanceRequest->status != 'completed' && $maintenanceRequest->status != 'cancelled')
                <form method="POST" action="{{ route('landlord.maintenance.update-notes', $maintenanceRequest->id) }}" class="notes-form">
                    @csrf
                    <textarea name="staff_notes" rows="4" placeholder="Add notes about the maintenance request...">{{ $maintenanceRequest->staff_notes }}</textarea>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Notes</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.filter-select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.95rem;
    margin-bottom: 1rem;
    transition: border-color 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.assign-form label,
.reassign-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.assign-form select:not(.filter-select),
.reassign-form select:not(.filter-select) {
    margin-bottom: 1rem;
}
</style>

<script>
// Filter staff by type for reassign form
function filterStaffByType() {
    const filterSelect = document.getElementById('filterStaffType');
    const staffSelect = document.getElementById('staffSelect');
    const selectedType = filterSelect.value.toLowerCase();
    
    // Get all options except the first one (placeholder)
    const options = Array.from(staffSelect.options);
    
    options.forEach(option => {
        if (option.value === '') {
            // Always show the placeholder
            option.style.display = '';
            return;
        }
        
        const staffType = option.getAttribute('data-staff-type');
        
        if (selectedType === '' || staffType === selectedType) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Reset selection if current selection is now hidden
    if (staffSelect.selectedIndex > 0 && staffSelect.options[staffSelect.selectedIndex].style.display === 'none') {
        staffSelect.selectedIndex = 0;
    }
}

// Filter staff by type for assign form
function filterStaffByTypeAssign() {
    const filterSelect = document.getElementById('filterStaffTypeAssign');
    const staffSelect = document.getElementById('staffSelectAssign');
    const selectedType = filterSelect.value.toLowerCase();
    
    // Get all options except the first one (placeholder)
    const options = Array.from(staffSelect.options);
    
    options.forEach(option => {
        if (option.value === '') {
            // Always show the placeholder
            option.style.display = '';
            return;
        }
        
        const staffType = option.getAttribute('data-staff-type');
        
        if (selectedType === '' || staffType === selectedType) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
    
    // Reset selection if current selection is now hidden
    if (staffSelect.selectedIndex > 0 && staffSelect.options[staffSelect.selectedIndex].style.display === 'none') {
        staffSelect.selectedIndex = 0;
    }
}
</script>
@endsection

