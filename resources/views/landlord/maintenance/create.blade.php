@extends('layouts.landlord-app')

@section('title', 'Create Maintenance Request')

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
            <h1><i class="fas fa-plus-circle"></i> Create Maintenance Request</h1>
            <p class="subtitle">Create a maintenance task for your vacant units or preventive maintenance</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
            <ul style="margin: 0.5rem 0 0 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('landlord.maintenance.store') }}" id="createMaintenanceForm">
                @csrf
                
                <!-- Unit Selection -->
                <div class="form-section">
                    <h3><i class="fas fa-building"></i> Select Unit</h3>
                    
                    <div class="form-group">
                        <label for="unit_id">Unit <span class="required">*</span></label>
                        <select name="unit_id" id="unit_id" class="form-control" required>
                            <option value="">Select a unit...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->apartment->name }} - Unit {{ $unit->unit_number }}
                                    @if($unit->status == 'vacant')
                                        (Vacant)
                                    @elseif($unit->status == 'occupied')
                                        (Occupied by {{ $unit->currentTenant->tenantProfile->name ?? $unit->currentTenant->email }})
                                    @else
                                        ({{ ucfirst($unit->status) }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text">Select which unit needs maintenance work</small>
                    </div>
                </div>

                <!-- Request Details -->
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Request Details</h3>
                    
                    <div class="form-group">
                        <label for="title">Title <span class="required">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" 
                               value="{{ old('title') }}" placeholder="e.g., Replace broken window" required maxlength="255">
                        <small class="form-text">Brief description of the maintenance needed</small>
                    </div>

                    <div class="form-group">
                        <label for="description">Description <span class="required">*</span></label>
                        <textarea name="description" id="description" class="form-control" rows="4" 
                                  placeholder="Provide detailed information about the maintenance work needed..." required>{{ old('description') }}</textarea>
                        <small class="form-text">Detailed description of the issue and what needs to be done</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category <span class="required">*</span></label>
                            <select name="category" id="category" class="form-control" required>
                                <option value="">Select category...</option>
                                <option value="plumbing" {{ old('category') == 'plumbing' ? 'selected' : '' }}>
                                    <i class="fas fa-water"></i> Plumbing
                                </option>
                                <option value="electrical" {{ old('category') == 'electrical' ? 'selected' : '' }}>
                                    <i class="fas fa-bolt"></i> Electrical
                                </option>
                                <option value="hvac" {{ old('category') == 'hvac' ? 'selected' : '' }}>
                                    <i class="fas fa-wind"></i> HVAC
                                </option>
                                <option value="appliance" {{ old('category') == 'appliance' ? 'selected' : '' }}>
                                    <i class="fas fa-blender"></i> Appliance
                                </option>
                                <option value="structural" {{ old('category') == 'structural' ? 'selected' : '' }}>
                                    <i class="fas fa-home"></i> Structural
                                </option>
                                <option value="cleaning" {{ old('category') == 'cleaning' ? 'selected' : '' }}>
                                    <i class="fas fa-broom"></i> Cleaning
                                </option>
                                <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>
                                    <i class="fas fa-tools"></i> Other
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="priority">Priority <span class="required">*</span></label>
                            <select name="priority" id="priority" class="form-control" required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Staff Assignment (Optional) -->
                <div class="form-section">
                    <h3><i class="fas fa-user-cog"></i> Assign Staff (Optional)</h3>
                    
                    <div class="form-group">
                        <label for="assigned_staff_id">Assign to Staff Member</label>
                        <select name="assigned_staff_id" id="assigned_staff_id" class="form-control">
                            <option value="">Assign later...</option>
                            @foreach($availableStaff as $staff)
                                <option value="{{ $staff->id }}" {{ old('assigned_staff_id') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->staffProfile->name ?? $staff->email }}
                                    @if($staff->staffProfile && $staff->staffProfile->staff_type)
                                        ({{ ucwords(str_replace('_', ' ', $staff->staffProfile->staff_type)) }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text">You can assign staff now or later from the request details page</small>
                    </div>

                    <div class="form-group">
                        <label for="expected_completion_date">Expected Completion Date</label>
                        <input type="date" name="expected_completion_date" id="expected_completion_date" 
                               class="form-control" value="{{ old('expected_completion_date') }}" 
                               min="{{ date('Y-m-d') }}">
                        <small class="form-text">Set a deadline for completing this maintenance task</small>
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-section">
                    <h3><i class="fas fa-sticky-note"></i> Additional Notes</h3>
                    
                    <div class="form-group">
                        <label for="staff_notes">Notes for Staff</label>
                        <textarea name="staff_notes" id="staff_notes" class="form-control" rows="3" 
                                  placeholder="Any special instructions or notes for the staff member...">{{ old('staff_notes') }}</textarea>
                        <small class="form-text">Instructions, special requirements, or important information</small>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Create Maintenance Request
                    </button>
                    <a href="{{ route('landlord.maintenance.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Form validation
    document.getElementById('createMaintenanceForm').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const unitId = document.getElementById('unit_id').value;
        const category = document.getElementById('category').value;
        const priority = document.getElementById('priority').value;
        
        if (!title || !description || !unitId || !category || !priority) {
            e.preventDefault();
            alert('Please fill in all required fields marked with *');
            return false;
        }
        
        if (title.length < 5) {
            e.preventDefault();
            alert('Title must be at least 5 characters long');
            return false;
        }
        
        if (description.length < 10) {
            e.preventDefault();
            alert('Description must be at least 10 characters long');
            return false;
        }
    });
</script>
@endpush


