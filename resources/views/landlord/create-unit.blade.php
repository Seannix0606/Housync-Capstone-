@extends('layouts.landlord-app')

@section('title', 'Add New Unit')

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

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 1rem;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .breadcrumb a {
            color: #ea580c;
            text-decoration: none;
        }

        .breadcrumb i {
            margin: 0 0.5rem;
        }

        /* Form Styles */
        .form-container {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-width: 800px;
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

        .form-group.full-width {
            grid-column: 1 / -1;
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
        }

        .checkbox-group input[type="checkbox"] {
            width: 1.25rem;
            height: 1.25rem;
            accent-color: #ea580c;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: 500;
        }

        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .amenity-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .amenity-item input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            accent-color: #ea580c;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
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

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
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

        /* Property Info */
        .property-info {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .property-info h4 {
            color: #1e293b;
            margin-bottom: 0.5rem;
            font-size: 1.125rem;
        }

        .property-info p {
            color: #64748b;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

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

        /* Success/Error Messages */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

    </style>
@endpush

@section('content')
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="{{ route('landlord.dashboard') }}">Dashboard</a>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('landlord.units') }}">Units</a>
                <i class="fas fa-chevron-right"></i>
                <span>Add New Unit</span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Add New Unit</h1>
                <p class="page-subtitle">Create a new rental unit for your property</p>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                @if(isset($apartment))
                <!-- Property Info -->
                <div class="property-info">
                    <h4>Property Information</h4>
                    <p><strong>Property:</strong> {{ $apartment->name }}</p>
                    <p><strong>Type:</strong> {{ ucfirst($apartment->property_type) }}</p>
                    <p><strong>Address:</strong> {{ $apartment->address }}</p>
                    @if($apartment->property_type === 'house')
                        <p><strong>Bedrooms:</strong> {{ $apartment->bedrooms ?? 'Not specified' }}</p>
                    @else
                        <p><strong>Floors:</strong> {{ $apartment->floors ?? 'Not specified' }}</p>
                    @endif
                </div>
                @endif

                <form method="POST" action="{{ isset($apartment) ? route('landlord.store-unit', $apartment->id) : route('landlord.create-unit') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Creation Options -->
                    <div class="form-section">
                        <h3 class="section-title">Creation Options</h3>
                        
                        <!-- Simple link to multiple units page -->
                        <div class="form-group">
                            <div style="text-align: center; padding: 2rem; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 0.5rem;">
                                <h4 style="color: #1e293b; margin-bottom: 1rem;">Need to create multiple units?</h4>
                                <p style="color: #64748b; margin-bottom: 1.5rem;">Use our dedicated bulk creation tool for creating multiple units at once.</p>
                                <a href="{{ route('landlord.create-multiple-units', $apartment->id) }}" class="btn btn-outline">
                                    <i class="fas fa-plus"></i> Create Multiple Units
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="form-section" id="single_unit_form">
                        <h3 class="section-title">Basic Information</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="unit_number" class="form-label">Unit Number *</label>
                                <input type="text" id="unit_number" name="unit_number" class="form-control @error('unit_number') error @enderror" 
                                       value="{{ old('unit_number') }}" placeholder="e.g., A101, 1A, etc." required>
                                @error('unit_number')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="unit_type" class="form-label">Unit Type *</label>
                                <select id="unit_type" name="unit_type" class="form-control @error('unit_type') error @enderror" required>
                                    <option value="">Select Unit Type</option>
                                    <option value="studio" {{ old('unit_type') == 'studio' ? 'selected' : '' }}>Studio</option>
                                    <option value="one_bedroom" {{ old('unit_type') == 'one_bedroom' ? 'selected' : '' }}>One Bedroom</option>
                                    <option value="two_bedroom" {{ old('unit_type') == 'two_bedroom' ? 'selected' : '' }}>Two Bedroom</option>
                                    <option value="three_bedroom" {{ old('unit_type') == 'three_bedroom' ? 'selected' : '' }}>Three Bedroom</option>
                                    <option value="penthouse" {{ old('unit_type') == 'penthouse' ? 'selected' : '' }}>Penthouse</option>
                                </select>
                                @error('unit_type')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Number of bedrooms will be set based on your selection</small>
                            </div>
                            
                            @if(isset($apartment) && $apartment->property_type !== 'house')
                            <div class="form-group">
                                <label for="floor_number" class="form-label">Floor Number *</label>
                                <select id="floor_number" name="floor_number" class="form-control @error('floor_number') error @enderror" required>
                                    <option value="">Select Floor</option>
                                    @for($i = 1; $i <= ($apartment->floors ?? 1); $i++)
                                        <option value="{{ $i }}" {{ old('floor_number') == $i ? 'selected' : '' }}>
                                            Floor {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                @error('floor_number')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="rent_amount" class="form-label">Monthly Rent (₱) *</label>
                                <input type="number" id="rent_amount" name="rent_amount" class="form-control @error('rent_amount') error @enderror" 
                                       value="{{ old('rent_amount') }}" placeholder="0.00" min="0" step="0.01" required>
                                @error('rent_amount')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="status" class="form-label">Status *</label>
                                <select id="status" name="status" class="form-control @error('status') error @enderror" required>
                                    <option value="">Select Status</option>
                                    <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('status')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="leasing_type" class="form-label">Leasing Type *</label>
                                <select id="leasing_type" name="leasing_type" class="form-control @error('leasing_type') error @enderror" required>
                                    <option value="">Select Leasing Type</option>
                                    <option value="separate" {{ old('leasing_type') == 'separate' ? 'selected' : '' }}>Separate (Utilities not included)</option>
                                    <option value="inclusive" {{ old('leasing_type') == 'inclusive' ? 'selected' : '' }}>Inclusive (Utilities included)</option>
                                </select>
                                @error('leasing_type')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Room Configuration -->
                    <div class="form-section">
                        <h3 class="section-title">Room Configuration</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="bedrooms" class="form-label">Number of Bedrooms *</label>
                                <input type="number" id="bedrooms" name="bedrooms" class="form-control @error('bedrooms') error @enderror" 
                                       value="{{ old('bedrooms', 0) }}" placeholder="0" min="0" required readonly>
                                @error('bedrooms')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Auto-filled based on unit type</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="bathrooms" class="form-label">Number of Bathrooms *</label>
                                <input type="number" id="bathrooms" name="bathrooms" class="form-control @error('bathrooms') error @enderror" 
                                       value="{{ old('bathrooms', 1) }}" placeholder="1" min="1" required>
                                @error('bathrooms')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_furnished" name="is_furnished" value="1" {{ old('is_furnished') ? 'checked' : '' }}>
                                <label for="is_furnished">Furnished Unit</label>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="form-section">
                        <h3 class="section-title">Amenities</h3>
                        <p style="color: #64748b; margin-bottom: 1rem;">Select the amenities available in this unit:</p>
                        
                        <div class="amenities-grid">
                            <div class="amenity-item">
                                <input type="checkbox" id="amenity_aircon" name="amenities[]" value="aircon" {{ in_array('aircon', old('amenities', [])) ? 'checked' : '' }}>
                                <label for="amenity_aircon">Air Conditioning</label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="amenity_heating" name="amenities[]" value="heating" {{ in_array('heating', old('amenities', [])) ? 'checked' : '' }}>
                                <label for="amenity_heating">Heating</label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="amenity_balcony" name="amenities[]" value="balcony" {{ in_array('balcony', old('amenities', [])) ? 'checked' : '' }}>
                                <label for="amenity_balcony">Balcony</label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="amenity_parking" name="amenities[]" value="parking" {{ in_array('parking', old('amenities', [])) ? 'checked' : '' }}>
                                <label for="amenity_parking">Parking</label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="amenity_gym" name="amenities[]" value="gym" {{ in_array('gym', old('amenities', [])) ? 'checked' : '' }}>
                                <label for="amenity_gym">Gym Access</label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="amenity_pool" name="amenities[]" value="pool" {{ in_array('pool', old('amenities', [])) ? 'checked' : '' }}>
                                <label for="amenity_pool">Pool Access</label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="amenity_wifi" name="amenities[]" value="wifi" {{ in_array('wifi', old('amenities', [])) ? 'checked' : '' }}>
                                <label for="amenity_wifi">WiFi</label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="amenity_laundry" name="amenities[]" value="laundry" {{ in_array('laundry', old('amenities', [])) ? 'checked' : '' }}>
                                <label for="amenity_laundry">Laundry</label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="amenity_others" name="amenities[]" value="others" {{ in_array('others', old('amenities', [])) ? 'checked' : '' }}>
                                <label for="amenity_others">Others</label>
                            </div>
                        </div>
                    </div>

                    <!-- Photos -->
                    <div class="form-section">
                        <h3 class="section-title">Photos</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Cover Image <span class="text-danger">*</span></label>
                                <input type="file" name="cover_image" id="cover_image" accept="image/*" class="form-control" onchange="previewCoverImage(this)">
                                <div id="cover_image_preview" class="mt-2" style="display: none;">
                                    <img id="cover_preview_img" src="" alt="Cover Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e2e8f0;">
                                </div>
                                <p class="form-help text-muted mt-1">Main image displayed for this unit (JPEG/PNG, max 5MB)</p>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label class="form-label">Gallery Images (up to 12)</label>
                            <div class="gallery-upload-container">
                                <input type="file" name="gallery[]" id="gallery_input" accept="image/*" multiple class="form-control" onchange="handleGalleryUpload(this)" style="display: none;">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('gallery_input').click()">
                                    <i class="fas fa-plus-circle me-2"></i>Add Images to Gallery
                                </button>
                                <p class="form-help text-muted mt-2">Add multiple images to showcase the unit (JPEG/PNG, max 5MB each)</p>
                            </div>
                            
                            <div id="gallery_preview" class="gallery-preview mt-3" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem;">
                                <!-- Gallery previews will be added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="form-section">
                        <h3 class="section-title">Additional Information</h3>
                        
                        <div class="form-group full-width">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control @error('description') error @enderror" 
                                      rows="4" placeholder="Describe the unit, its features, and any special characteristics...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea id="notes" name="notes" class="form-control @error('notes') error @enderror" 
                                      rows="3" placeholder="Any additional notes or special instructions...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="{{ route('landlord.units') }}" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i>
                            <span id="submitText">Create Unit</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        console.log('Script starting...');
        
        // Cover image preview
        function previewCoverImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('cover_preview_img').src = e.target.result;
                    document.getElementById('cover_image_preview').style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Gallery images handling
        let galleryFiles = [];
        const maxGalleryImages = 12;
        
        function handleGalleryUpload(input) {
            if (input.files && input.files.length > 0) {
                const files = Array.from(input.files);
                const remainingSlots = maxGalleryImages - galleryFiles.length;
                
                if (files.length > remainingSlots) {
                    alert(`You can only add ${remainingSlots} more image(s). Maximum ${maxGalleryImages} images allowed.`);
                    files.splice(remainingSlots);
                }
                
                files.forEach(file => {
                    if (galleryFiles.length < maxGalleryImages) {
                        galleryFiles.push(file);
                        addGalleryPreview(file, galleryFiles.length - 1);
                    }
                });
                
                // Update the file input
                updateGalleryInput();
            }
        }
        
        function addGalleryPreview(file, index) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.getElementById('gallery_preview');
                previewContainer.style.display = 'grid';
                
                const previewDiv = document.createElement('div');
                previewDiv.className = 'gallery-item';
                previewDiv.style.position = 'relative';
                previewDiv.style.border = '2px solid #e2e8f0';
                previewDiv.style.borderRadius = '8px';
                previewDiv.style.overflow = 'hidden';
                previewDiv.dataset.index = index;
                
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" alt="Gallery Preview ${index + 1}" 
                         style="width: 100%; height: 150px; object-fit: cover; display: block;">
                    <button type="button" class="btn btn-sm btn-danger" 
                            onclick="removeGalleryImage(${index})"
                            style="position: absolute; top: 5px; right: 5px; padding: 0.25rem 0.5rem; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-times" style="font-size: 0.75rem;"></i>
                    </button>
                `;
                
                previewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        }
        
        function removeGalleryImage(index) {
            galleryFiles.splice(index, 1);
            updateGalleryPreview();
            updateGalleryInput();
        }
        
        function updateGalleryPreview() {
            const previewContainer = document.getElementById('gallery_preview');
            previewContainer.innerHTML = '';
            
            if (galleryFiles.length === 0) {
                previewContainer.style.display = 'none';
                return;
            }
            
            previewContainer.style.display = 'grid';
            galleryFiles.forEach((file, index) => {
                addGalleryPreview(file, index);
            });
        }
        
        function updateGalleryInput() {
            const input = document.getElementById('gallery_input');
            const dataTransfer = new DataTransfer();
            
            galleryFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            
            input.files = dataTransfer.files;
        }
        
        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing...');
            
            const form = document.querySelector('form');
            const rentInput = document.getElementById('rent_amount');
            const unitNumberInput = document.getElementById('unit_number');
            
            

            // Format rent amount
            rentInput.addEventListener('input', function() {
                let value = this.value.replace(/[^\d.]/g, '');
                if (value) {
                    value = parseFloat(value).toFixed(2);
                    this.value = value;
                }
            });

            // Auto-generate unit number if empty
            unitNumberInput.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    const unitType = document.getElementById('unit_type').value;
                    const floorNumber = document.getElementById('floor_number')?.value;
                    
                    if (unitType) {
                        if (floorNumber) {
                            // For buildings, suggest floor-based numbering
                            this.value = floorNumber + '01';
                        } else {
                            // For houses, use type-based numbering
                            const timestamp = Date.now().toString().slice(-4);
                            this.value = `${unitType.charAt(0).toUpperCase()}${timestamp}`;
                        }
                    }
                }
            });
            
            // Update unit number suggestion when floor changes
            const floorNumberSelect = document.getElementById('floor_number');
            if (floorNumberSelect) {
                floorNumberSelect.addEventListener('change', function() {
                    if (!unitNumberInput.value.trim()) {
                        const unitType = document.getElementById('unit_type').value;
                        if (unitType && this.value) {
                            unitNumberInput.value = this.value + '01';
                        }
                    }
                });
            }

            // Auto-populate bedrooms based on unit type
            const unitTypeSelect = document.getElementById('unit_type');
            const bedroomsInput = document.getElementById('bedrooms');

            unitTypeSelect.addEventListener('change', function() {
                const unitType = this.value;
                let bedroomCount = 0;

                switch(unitType) {
                    case 'studio':
                        bedroomCount = 0;
                        break;
                    case 'one_bedroom':
                        bedroomCount = 1;
                        break;
                    case 'two_bedroom':
                        bedroomCount = 2;
                        break;
                    case 'three_bedroom':
                        bedroomCount = 3;
                        break;
                    case 'penthouse':
                        bedroomCount = 3; // Default for penthouse, can be adjusted
                        break;
                    default:
                        bedroomCount = 0;
                }

                bedroomsInput.value = bedroomCount;
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('error');
                        isValid = false;
                    } else {
                        field.classList.remove('error');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
        });
    });
    
    </script>
@endsection