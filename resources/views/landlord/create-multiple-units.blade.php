@extends('layouts.landlord-app')

@section('title', 'Create Multiple Units')

@push('styles')
<style>
    .bulk-creation-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .property-info {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .property-info h4 {
        color: #1e293b;
        margin-bottom: 0.5rem;
        font-size: 1.25rem;
    }

    .property-info p {
        color: #64748b;
        margin: 0.25rem 0;
    }

    .form-container {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: all 0.2s;
        background-color: white;
    }

    .form-control:focus {
        outline: none;
        border-color: #ea580c;
        box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.1);
    }

    .form-control.error {
        border-color: #ef4444;
    }

    .error-message {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .checkbox-group input[type="checkbox"] {
        width: 1.25rem;
        height: 1.25rem;
        accent-color: #ea580c;
    }

    .checkbox-group label {
        margin: 0;
        font-weight: 500;
        cursor: pointer;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }

    .btn-primary {
        background-color: #ea580c;
        color: white;
    }

    .btn-primary:hover {
        background-color: #dc2626;
        text-decoration: none;
        color: white;
    }

    .btn-outline {
        background-color: transparent;
        color: #ea580c;
        border: 1px solid #ea580c;
    }

    .btn-outline:hover {
        background-color: #ea580c;
        color: white;
        text-decoration: none;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }

    .alert {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .alert-info {
        background-color: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="bulk-creation-container">
    <!-- Header -->
    <div class="content-header">
        <div>
            <h1>Create Multiple Units</h1>
            <p style="color: #64748b; margin-top: 0.5rem;">Set up basic parameters for bulk unit creation</p>
        </div>
    </div>

    @if(isset($apartment))
    <!-- Property Info -->
    <div class="property-info">
        <h4>{{ $apartment->name }}</h4>
        <p><strong>Type:</strong> {{ ucfirst($apartment->property_type) }}</p>
        <p><strong>Address:</strong> {{ $apartment->address }}</p>
        @if($apartment->property_type !== 'house')
            <p><strong>Floors:</strong> {{ $apartment->floors ?? 'Not specified' }}</p>
        @else
            <p><strong>Bedrooms:</strong> {{ $apartment->bedrooms ?? 'Not specified' }}</p>
        @endif
    </div>
    @endif

    <!-- Info Alert -->
    <div class="alert alert-info">
        <strong>How it works:</strong> Set your basic parameters here, then customize each unit individually in the bulk editor.
    </div>

    <form method="POST" action="{{ route('landlord.store-bulk-units', $apartment->id) }}" enctype="multipart/form-data">
        @csrf
        
        <div class="form-container">
            <!-- Creation Type -->
            <div class="form-section">
                <h3 class="section-title">Creation Type</h3>
                
                @if($apartment->property_type === 'house')
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="create_all_bedrooms" name="create_all_bedrooms" value="1" checked>
                        <label for="create_all_bedrooms">Create all {{ $apartment->bedrooms ?? 1 }} bedrooms as separate units</label>
                    </div>
                    <small style="color: #64748b;">Each bedroom will become an individual rental unit</small>
                </div>
                @else
                <div class="form-group">
                    <label for="units_per_floor" class="form-label">Units Per Floor *</label>
                    <input type="number" id="units_per_floor" name="units_per_floor" class="form-control @error('units_per_floor') error @enderror" 
                           value="{{ old('units_per_floor', 4) }}" min="1" required>
                    @error('units_per_floor')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    <small style="color: #64748b;">How many units per floor? (Total: {{ $apartment->floors ?? 1 }} floors)</small>
                </div>
                @endif
            </div>

            <!-- Default Settings -->
            <div class="form-section">
                <h3 class="section-title">Default Unit Settings</h3>
                <p style="color: #64748b; margin-bottom: 1.5rem;">These will be applied to all units. You can customize each unit individually in the next step.</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="default_unit_type" class="form-label">Default Unit Type *</label>
                        <select id="default_unit_type" name="default_unit_type" class="form-control @error('default_unit_type') error @enderror" required>
                            <option value="">Select Unit Type</option>
                            <option value="studio" {{ old('default_unit_type') == 'studio' ? 'selected' : '' }}>Studio</option>
                            <option value="one_bedroom" {{ old('default_unit_type') == 'one_bedroom' ? 'selected' : '' }}>One Bedroom</option>
                            <option value="two_bedroom" {{ old('default_unit_type') == 'two_bedroom' ? 'selected' : '' }}>Two Bedroom</option>
                            <option value="three_bedroom" {{ old('default_unit_type') == 'three_bedroom' ? 'selected' : '' }}>Three Bedroom</option>
                            <option value="penthouse" {{ old('default_unit_type') == 'penthouse' ? 'selected' : '' }}>Penthouse</option>
                        </select>
                        @error('default_unit_type')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="default_rent" class="form-label">Default Rent (₱) *</label>
                        <input type="number" id="default_rent" name="default_rent" class="form-control @error('default_rent') error @enderror" 
                               value="{{ old('default_rent', 15000) }}" min="0" step="100" required>
                        @error('default_rent')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="default_bedrooms" class="form-label">Default Bedrooms *</label>
                        <input type="number" id="default_bedrooms" name="default_bedrooms" class="form-control @error('default_bedrooms') error @enderror" 
                               value="{{ old('default_bedrooms', 2) }}" min="0" max="10" required>
                        @error('default_bedrooms')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="default_bathrooms" class="form-label">Default Bathrooms *</label>
                        <input type="number" id="default_bathrooms" name="default_bathrooms" class="form-control @error('default_bathrooms') error @enderror" 
                               value="{{ old('default_bathrooms', 1) }}" min="1" max="10" required>
                        @error('default_bathrooms')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('landlord.units', $apartment->id) }}" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Continue to Bulk Editor
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-select unit type based on bedrooms for houses
    const createAllBedrooms = document.getElementById('create_all_bedrooms');
    const defaultUnitType = document.getElementById('default_unit_type');
    const defaultBedrooms = document.getElementById('default_bedrooms');
    
    if (createAllBedrooms && defaultUnitType && defaultBedrooms) {
        createAllBedrooms.addEventListener('change', function() {
            if (this.checked) {
                defaultUnitType.value = 'one_bedroom';
                defaultBedrooms.value = 1;
            }
        });
    }
    
    // Format rent amount
    const rentInput = document.getElementById('default_rent');
    if (rentInput) {
        rentInput.addEventListener('input', function() {
            let value = this.value.replace(/[^\d.]/g, '');
            if (value) {
                value = parseFloat(value).toFixed(2);
                this.value = value;
            }
        });
    }
    
    // Debug: Add form submission listener
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Check if all required fields are filled
            const requiredFields = form.querySelectorAll('[required]');
            let allFilled = true;
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    allFilled = false;
                }
            });
            
            if (!allFilled) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            // Soft safety check for very large bulk generations
            const unitsPerFloorInput = document.getElementById('units_per_floor');
            if (unitsPerFloorInput) {
                const unitsPerFloor = parseInt(unitsPerFloorInput.value, 10) || 0;
                const totalFloors = {{ (int) ($apartment->floors ?? 1) }};
                const estimatedUnits = unitsPerFloor * totalFloors;

                // Warn if overall unit count is very high (can slow down the bulk editor)
                if (estimatedUnits > 300) {
                    const message =
                        'You are about to generate approximately ' + estimatedUnits.toLocaleString() +
                        ' units (' + unitsPerFloor + ' per floor × ' + totalFloors + ' floors).\n\n' +
                        'This may be slow or hard to manage in one go.\n\n' +
                        'Do you still want to continue?';
                    const confirmed = confirm(message);
                    if (!confirmed) {
                        e.preventDefault();
                        return false;
                    }
                }
            }
        });
    }
});
</script>
@endsection
