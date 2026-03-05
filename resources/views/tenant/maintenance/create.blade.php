@extends('layouts.app')

@section('title', 'Submit Maintenance Request')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/maintenance.css') }}">
<style>
.maintenance-form-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.form-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 2rem;
}

.form-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.form-header h2 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-header p {
    color: #7f8c8d;
    margin: 0;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-group label i {
    color: #3498db;
    margin-right: 0.5rem;
}

.form-group .required {
    color: #e74c3c;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.95rem;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-control.error {
    border-color: #e74c3c;
}

.form-text {
    font-size: 0.85rem;
    color: #7f8c8d;
    margin-top: 0.25rem;
}

.invalid-feedback {
    color: #e74c3c;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
    font-family: inherit;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.priority-options,
.category-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}

.radio-card {
    position: relative;
}

.radio-card input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.radio-card label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    border: 2px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    min-height: 80px;
    font-weight: 500;
}

.radio-card label i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.radio-card input:checked + label {
    border-color: #3498db;
    background: #e3f2fd;
    color: #1976d2;
}

.radio-card.priority-low input:checked + label {
    border-color: #2e7d32;
    background: #e8f5e9;
    color: #2e7d32;
}

.radio-card.priority-medium input:checked + label {
    border-color: #e65100;
    background: #fff3e0;
    color: #e65100;
}

.radio-card.priority-high input:checked + label {
    border-color: #bf360c;
    background: #ffe0b2;
    color: #bf360c;
}

.radio-card.priority-urgent input:checked + label {
    border-color: #c62828;
    background: #ffebee;
    color: #c62828;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e9ecef;
}

.unit-info-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.unit-info-banner h3 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
}

.unit-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.unit-info-item {
    display: flex;
    flex-direction: column;
}

.unit-info-item .label {
    font-size: 0.85rem;
    opacity: 0.9;
    margin-bottom: 0.25rem;
}

.unit-info-item .value {
    font-weight: 600;
    font-size: 1rem;
}
</style>
@endpush

@section('content')
<div class="maintenance-form-container">
    <div class="maintenance-header">
        <a href="{{ route('tenant.maintenance.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Requests
        </a>
    </div>

    <!-- Unit Info Banner -->
    <div class="unit-info-banner">
        <h3><i class="fas fa-info-circle"></i> Request for Your Unit</h3>
        <div class="unit-info-grid">
            <div class="unit-info-item">
                <span class="label">Property</span>
                <span class="value">{{ $activeAssignment->unit->apartment->name }}</span>
            </div>
            <div class="unit-info-item">
                <span class="label">Unit Number</span>
                <span class="value">Unit {{ $activeAssignment->unit->unit_number }}</span>
            </div>
            <div class="unit-info-item">
                <span class="label">Landlord</span>
                <span class="value">{{ $activeAssignment->landlord->name }}</span>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-header">
            <h2><i class="fas fa-tools"></i> Submit Maintenance Request</h2>
            <p>Please provide detailed information about the maintenance issue you're experiencing.</p>
        </div>

        <form method="POST" action="{{ route('tenant.maintenance.store') }}">
            @csrf

            <!-- Title -->
            <div class="form-group">
                <label for="title">
                    <i class="fas fa-heading"></i> Request Title <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-control @error('title') error @enderror" 
                    placeholder="e.g., Leaking faucet in kitchen"
                    value="{{ old('title') }}"
                    required
                >
                <small class="form-text">Brief description of the issue</small>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description">
                    <i class="fas fa-align-left"></i> Detailed Description <span class="required">*</span>
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    class="form-control @error('description') error @enderror"
                    placeholder="Please describe the issue in detail. Include when it started, how often it occurs, and any other relevant information..."
                    required
                >{{ old('description') }}</textarea>
                <small class="form-text">The more details you provide, the faster we can resolve the issue</small>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Priority -->
            <div class="form-group">
                <label>
                    <i class="fas fa-exclamation-circle"></i> Priority Level <span class="required">*</span>
                </label>
                <div class="priority-options">
                    <div class="radio-card priority-low">
                        <input type="radio" id="priority-low" name="priority" value="low" {{ old('priority') == 'low' ? 'checked' : '' }}>
                        <label for="priority-low">
                            <i class="fas fa-circle"></i>
                            Low
                        </label>
                    </div>
                    <div class="radio-card priority-medium">
                        <input type="radio" id="priority-medium" name="priority" value="medium" {{ old('priority') == 'medium' || !old('priority') ? 'checked' : '' }}>
                        <label for="priority-medium">
                            <i class="fas fa-circle"></i>
                            Medium
                        </label>
                    </div>
                    <div class="radio-card priority-high">
                        <input type="radio" id="priority-high" name="priority" value="high" {{ old('priority') == 'high' ? 'checked' : '' }}>
                        <label for="priority-high">
                            <i class="fas fa-exclamation-circle"></i>
                            High
                        </label>
                    </div>
                    <div class="radio-card priority-urgent">
                        <input type="radio" id="priority-urgent" name="priority" value="urgent" {{ old('priority') == 'urgent' ? 'checked' : '' }}>
                        <label for="priority-urgent">
                            <i class="fas fa-exclamation-triangle"></i>
                            Urgent
                        </label>
                    </div>
                </div>
                <small class="form-text">
                    <strong>Urgent:</strong> Safety hazard or no heat/water<br>
                    <strong>High:</strong> Major inconvenience affecting daily life<br>
                    <strong>Medium:</strong> Needs attention soon<br>
                    <strong>Low:</strong> Minor issue, not urgent
                </small>
                @error('priority')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Category -->
            <div class="form-group">
                <label>
                    <i class="fas fa-tag"></i> Category <span class="required">*</span>
                </label>
                <div class="category-options">
                    <div class="radio-card">
                        <input type="radio" id="category-plumbing" name="category" value="plumbing" {{ old('category') == 'plumbing' ? 'checked' : '' }}>
                        <label for="category-plumbing">
                            <i class="fas fa-water"></i>
                            Plumbing
                        </label>
                    </div>
                    <div class="radio-card">
                        <input type="radio" id="category-electrical" name="category" value="electrical" {{ old('category') == 'electrical' ? 'checked' : '' }}>
                        <label for="category-electrical">
                            <i class="fas fa-bolt"></i>
                            Electrical
                        </label>
                    </div>
                    <div class="radio-card">
                        <input type="radio" id="category-hvac" name="category" value="hvac" {{ old('category') == 'hvac' ? 'checked' : '' }}>
                        <label for="category-hvac">
                            <i class="fas fa-wind"></i>
                            HVAC
                        </label>
                    </div>
                    <div class="radio-card">
                        <input type="radio" id="category-appliance" name="category" value="appliance" {{ old('category') == 'appliance' ? 'checked' : '' }}>
                        <label for="category-appliance">
                            <i class="fas fa-blender"></i>
                            Appliance
                        </label>
                    </div>
                    <div class="radio-card">
                        <input type="radio" id="category-structural" name="category" value="structural" {{ old('category') == 'structural' ? 'checked' : '' }}>
                        <label for="category-structural">
                            <i class="fas fa-home"></i>
                            Structural
                        </label>
                    </div>
                    <div class="radio-card">
                        <input type="radio" id="category-cleaning" name="category" value="cleaning" {{ old('category') == 'cleaning' ? 'checked' : '' }}>
                        <label for="category-cleaning">
                            <i class="fas fa-broom"></i>
                            Cleaning
                        </label>
                    </div>
                    <div class="radio-card">
                        <input type="radio" id="category-other" name="category" value="other" {{ old('category') == 'other' ? 'checked' : '' }}>
                        <label for="category-other">
                            <i class="fas fa-tools"></i>
                            Other
                        </label>
                    </div>
                </div>
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Additional Notes -->
            <div class="form-group">
                <label for="tenant_notes">
                    <i class="fas fa-comment"></i> Additional Notes (Optional)
                </label>
                <textarea 
                    id="tenant_notes" 
                    name="tenant_notes" 
                    class="form-control @error('tenant_notes') error @enderror"
                    placeholder="Any additional information that might be helpful..."
                >{{ old('tenant_notes') }}</textarea>
                <small class="form-text">Preferred times for maintenance visit, special instructions, etc.</small>
                @error('tenant_notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
                <a href="{{ route('tenant.maintenance.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection


