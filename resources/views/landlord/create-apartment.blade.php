@extends('layouts.landlord-app')

@section('title', 'Add New Property')

@push('styles')
<style>
    /* Progress Indicator */
    .progress-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 3rem;
        background: white;
        padding: 2rem;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        position: relative;
    }

    .progress-step i {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .progress-step.active i {
        background: #f97316;
        color: white;
    }

    .progress-step span {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
    }

    .progress-step.active span {
        color: #f97316;
        font-weight: 600;
    }

    .progress-connector {
        width: 100px;
        height: 2px;
        background: #e2e8f0;
        margin: 0 0.5rem;
    }

    /* Form Styles */
    .form-container {
        max-width: 100%;
    }

    .form-section {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .form-section-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .form-section-title i {
        color: #f97316;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-weight: 500;
        color: #1e293b;
        font-size: 0.875rem;
    }

    .form-label.required::after {
        content: " *";
        color: #ef4444;
    }

    .form-control {
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
    }

    .form-control.error {
        border-color: #ef4444;
    }

    .form-error {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .form-help, .form-text.text-muted {
        color: #64748b;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    /* Custom Checkbox */
    .custom-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .custom-control-input {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .custom-control-label {
        cursor: pointer;
        user-select: none;
    }

    /* Amenities Grid */
    .amenities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .amenity-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }

    .amenity-item:hover {
        border-color: #f97316;
        background: #fff7ed;
    }

    .amenity-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .unit-media-slots {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        margin-top: 1rem;
    }

    .unit-media-card {
        background: white;
        border-radius: 0.5rem;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
    }

    .unit-media-card h4 {
        margin: 0 0 1rem;
        font-size: 1rem;
        font-weight: 600;
        color: #334155;
    }

    .amenity-item input[type="checkbox"]:checked + label {
        color: #f97316;
        font-weight: 600;
    }

    .amenity-item label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.875rem;
        margin: 0;
    }

    .amenity-item i {
        color: #f97316;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 2rem;
        border-top: 2px solid #e2e8f0;
        margin-top: 2rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .progress-connector {
            width: 50px;
        }

        .amenities-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
    <!-- Header -->
    <div class="content-header">
        <div>
            <h1>Add New Property</h1>
            <p style="color: #64748b; margin-top: 0.5rem;">Create a new property in your portfolio</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
            <ul style="margin-left: 1rem; margin-top: 0.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Progress Indicator -->
    <div class="progress-indicator">
        <div class="progress-step active">
            <i class="fas fa-building"></i>
            <span>Property Details</span>
        </div>
        <div class="progress-connector"></div>
        <div class="progress-step">
            <i class="fas fa-images"></i>
            <span>Photos &amp; units</span>
        </div>
        <div class="progress-connector"></div>
        <div class="progress-step">
            <i class="fas fa-check"></i>
            <span>Complete</span>
        </div>
    </div>

    <!-- Form Section -->
    <div class="page-section">
        <div class="section-header">
            <div>
                <h2 class="section-title">Property Information</h2>
                <p class="section-subtitle">Fill in the details for your new property</p>
            </div>
        </div>
        
        <form id="createApartmentForm" method="POST" action="{{ route('landlord.store-apartment') }}" class="form-container" enctype="multipart/form-data">
            @csrf
            
            <!-- Basic Information -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-info-circle"></i>
                    Basic Information
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">Property Name</label>
                        <input type="text" name="name" class="form-control @error('name') error @enderror" 
                               value="{{ old('name') }}" placeholder="e.g., Sunshine Apartments" required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Property Type</label>
                        <select name="property_type" class="form-control @error('property_type') error @enderror" required>
                            <option value="">Select property type</option>
                            <option value="apartment" {{ old('property_type') == 'apartment' ? 'selected' : '' }}>Apartment Building</option>
                            <option value="condominium" {{ old('property_type') == 'condominium' ? 'selected' : '' }}>Condominium</option>
                            <option value="townhouse" {{ old('property_type') == 'townhouse' ? 'selected' : '' }}>Townhouse</option>
                            <option value="house" {{ old('property_type') == 'house' ? 'selected' : '' }}>Single Family House</option>
                            <option value="duplex" {{ old('property_type') == 'duplex' ? 'selected' : '' }}>Duplex</option>
                            <option value="others" {{ old('property_type') == 'others' ? 'selected' : '' }}>Others</option>
                        </select>
                        @error('property_type')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <input type="hidden" name="unit_count" id="duplex_unit_count_input" value="2" disabled>

                    <div class="form-group" id="floors_group">
                        <label class="form-label required" id="floors_label">Number of units</label>
                        <input type="number" name="floors" id="floors" class="form-control @error('floors') error @enderror"
                               value="{{ old('floors', 1) }}" min="1" placeholder="e.g., 5" required>
                        @error('floors')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted" id="floors_hint">How many rentable units does this property have?</small>
                    </div>

                    <div class="form-group" id="building_floors_group" style="display: none;">
                        <label class="form-label required" id="building_floors_label">Building stories (floors)</label>
                        <input type="number" name="building_floors" id="building_floors" class="form-control @error('building_floors') error @enderror"
                               value="{{ old('building_floors') }}" min="1" max="200" placeholder="e.g., 2">
                        @error('building_floors')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted" id="building_floors_hint">Above-grade levels for the whole structure. For a duplex this is optional listing context (for example a stacked building); it does not set each unit’s interior stories.</small>
                    </div>

                    <div class="form-group" id="dwelling_stories_group" style="display: none;">
                        <label class="form-label" for="dwelling_stories">Stories inside this dwelling <span class="text-muted">(optional)</span></label>
                        <input type="number" name="dwelling_stories" id="dwelling_stories" class="form-control @error('dwelling_stories') error @enderror"
                               value="{{ old('dwelling_stories') }}" min="1" max="50" placeholder="e.g., 2">
                        @error('dwelling_stories')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Interior levels of the rented dwelling. Can differ from total building stories when helpful for tenants.</small>
                    </div>

                    <div class="form-group" id="bedrooms_group" style="display: none;">
                        <label class="form-label required">Bedrooms (typical per dwelling)</label>
                        <input type="number" name="bedrooms" id="bedrooms" class="form-control @error('bedrooms') error @enderror"
                               value="{{ old('bedrooms', 1) }}" min="1" placeholder="e.g., 3">
                        @error('bedrooms')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted" id="bedrooms_hint">For a single-family home, total bedrooms. For townhomes, typical per unit if they are similar.</small>
                    </div>

                    <div class="form-grid" id="duplex_unit_bedrooms_group" style="display: none;">
                        <div class="form-group">
                            <label class="form-label required">Unit 1 bedrooms</label>
                            <input type="number" name="unit_bedrooms[0]" id="unit_bedrooms_0" class="form-control @error('unit_bedrooms.0') error @enderror"
                                   value="{{ old('unit_bedrooms.0', 2) }}" min="0" max="50" placeholder="e.g., 2">
                            @error('unit_bedrooms.0')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Unit 2 bedrooms</label>
                            <input type="number" name="unit_bedrooms[1]" id="unit_bedrooms_1" class="form-control @error('unit_bedrooms.1') error @enderror"
                                   value="{{ old('unit_bedrooms.1', 2) }}" min="0" max="50" placeholder="e.g., 2">
                            @error('unit_bedrooms.1')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group full-width">
                            <small class="form-text text-muted">Each side of a duplex can differ (e.g. 2 BR vs 3 BR). Use 0 for a studio-style unit.</small>
                        </div>
                    </div>

                    <div class="form-grid" id="duplex_unit_stories_group" style="display: none;">
                        <div class="form-group">
                            <label class="form-label required">Unit 1 interior stories</label>
                            <input type="number" name="unit_stories[0]" id="unit_stories_0" class="form-control @error('unit_stories.0') error @enderror"
                                   value="{{ old('unit_stories.0', 1) }}" min="1" max="50" placeholder="e.g., 1">
                            @error('unit_stories.0')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Unit 2 interior stories</label>
                            <input type="number" name="unit_stories[1]" id="unit_stories_1" class="form-control @error('unit_stories.1') error @enderror"
                                   value="{{ old('unit_stories.1', 1) }}" min="1" max="50" placeholder="e.g., 2">
                            @error('unit_stories.1')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group full-width">
                            <small class="form-text text-muted">How many levels each rental unit occupies inside its side (for example 1 for a single-level flat, 2 for a two-story unit).</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Structure Information -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-building"></i>
                    Property Structure
                </h3>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Units are created automatically from the type and count above. Add <strong>property</strong> and <strong>per-unit</strong> photos in the sections below—you do not need to visit My Units just to add images.
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-calendar"></i>
                    Building Information
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Year Built</label>
                        <input type="number" name="year_built" class="form-control @error('year_built') error @enderror" 
                               value="{{ old('year_built') }}" min="1900" max="{{ date('Y') }}" placeholder="e.g., 2020">
                        @error('year_built')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Location Information
                </h3>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label required">Street Address</label>
                        <input type="text" name="address" class="form-control @error('address') error @enderror" 
                               value="{{ old('address') }}" placeholder="e.g., 123 Main Street" required>
                        @error('address')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control @error('city') error @enderror" 
                               value="{{ old('city') }}" placeholder="e.g., Manila">
                        @error('city')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">State/Province</label>
                        <input type="text" name="state" class="form-control @error('state') error @enderror" 
                               value="{{ old('state') }}" placeholder="e.g., Metro Manila">
                        @error('state')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" class="form-control @error('postal_code') error @enderror" 
                               value="{{ old('postal_code') }}" placeholder="e.g., 1234">
                        @error('postal_code')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Property Details -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-cogs"></i>
                    Property Details
                </h3>
                <div class="form-grid">

                    <div class="form-group">
                        <label class="form-label">Parking Spaces</label>
                        <input type="number" name="parking_spaces" class="form-control @error('parking_spaces') error @enderror" 
                               value="{{ old('parking_spaces') }}" min="0" placeholder="e.g., 20">
                        @error('parking_spaces')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control @error('contact_person') error @enderror" 
                               value="{{ old('contact_person') }}" placeholder="e.g., John Doe">
                        @error('contact_person')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="tel" name="contact_phone" class="form-control @error('contact_phone') error @enderror" 
                               value="{{ old('contact_phone') }}" placeholder="e.g., +63 912 345 6789">
                        @error('contact_phone')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control @error('contact_email') error @enderror" 
                               value="{{ old('contact_email') }}" placeholder="e.g., contact@example.com">
                        @error('contact_email')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') error @enderror" 
                              placeholder="Describe your property, its features, and what makes it special...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Property Photos -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-image"></i>
                    Photos
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Property Cover Image</label>
                        <input type="file" name="property_cover_image" id="createApartmentCoverImageInput" accept="image/*" class="form-control">
                        <p class="form-help">Shown in property hero and explore property card (JPEG/PNG up to 3MB)</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Property Gallery (up to 12)</label>
                        <input type="file" name="property_gallery[]" id="createApartmentGalleryInput" accept="image/*" multiple class="form-control">
                        <div id="previewContainer" style="display: none; gap: 10px; flex-wrap: wrap; margin-top: 20px;"></div>
                        <script>
                            const propertyGalleryInput = document.querySelector('input[name="property_gallery[]"]');
                            const container = document.getElementById('previewContainer');

                            container.innerHTML = '';

                            propertyGalleryInput.addEventListener('change', function(event) {
                                container.innerHTML = '';
                                container.style.display = 'flex';
                                const files = event.target.files;

                                Array.from(files).forEach(file => {
                                    const reader = new FileReader();

                                    reader.onload = (e) => {
                                    // Create a wrapper for the thumbnail and text
                                    const fileWrapper = document.createElement('div');
                                    fileWrapper.style.textAlign = 'center';
                                    fileWrapper.style.width = '100px';

                                    const img = document.createElement('img');
                                    img.src = e.target.result;
                                    img.style.cssText = 'width:100px;height:100px;object-fit:cover;border-radius:8px;';
                                    const p = document.createElement('p');
                                    p.style.cssText = 'font-size:12px;word-break:break-all;margin-top:5px;';
                                    p.textContent = file.name;

                                    fileWrapper.appendChild(img);
                                    fileWrapper.appendChild(p);

                                    container.appendChild(fileWrapper);
                                    };

                                    // Read the file as a data URL to show the image preview
                                    reader.readAsDataURL(file);
                            })});
                        </script>
                        <p class="form-help">Additional building/common-area photos for property listings (up to 12)</p>
                    </div>
                </div>
            </div>

            <!-- Per-unit photos (optional; matches units created on save) -->
            <div class="form-section" id="unit_media_section">
                <h3 class="form-section-title">
                    <i class="fas fa-door-open"></i>
                    Unit photos (optional)
                </h3>
                <p class="form-help" id="unit_media_intro">
                    Optional cover and gallery for each unit. Unit 1 matches the first auto-created unit, and so on. You can still edit units later from My Units.
                </p>
                <p class="form-help" id="unit_media_placeholder" style="display: none; color: #64748b;"></p>
                <div id="unit_media_slots" class="unit-media-slots" aria-live="polite"></div>
            </div>

            <!-- Amenities -->
            <div class="form-section">
                <h3 class="form-section-title">
                    <i class="fas fa-star"></i>
                    Property Amenities
                </h3>
                <p class="form-help">Select the amenities available in your property</p>
                
                <div class="amenities-grid">
                    <div class="amenity-item">
                        <input type="checkbox" id="pool" name="amenities[]" value="pool" {{ in_array('pool', old('amenities', [])) ? 'checked' : '' }}>
                        <label for="pool">
                            <i class="fas fa-swimming-pool"></i>
                            Swimming Pool
                        </label>
                    </div>
                    <div class="amenity-item">
                        <input type="checkbox" id="gym" name="amenities[]" value="gym" {{ in_array('gym', old('amenities', [])) ? 'checked' : '' }}>
                        <label for="gym">
                            <i class="fas fa-dumbbell"></i>
                            Gym/Fitness Center
                        </label>
                    </div>
                    <div class="amenity-item">
                        <input type="checkbox" id="parking" name="amenities[]" value="parking" {{ in_array('parking', old('amenities', [])) ? 'checked' : '' }}>
                        <label for="parking">
                            <i class="fas fa-parking"></i>
                            Parking
                        </label>
                    </div>
                    <div class="amenity-item">
                        <input type="checkbox" id="security" name="amenities[]" value="security" {{ in_array('security', old('amenities', [])) ? 'checked' : '' }}>
                        <label for="security">
                            <i class="fas fa-shield-alt"></i>
                            24/7 Security
                        </label>
                    </div>
                    <div class="amenity-item">
                        <input type="checkbox" id="elevator" name="amenities[]" value="elevator" {{ in_array('elevator', old('amenities', [])) ? 'checked' : '' }}>
                        <label for="elevator">
                            <i class="fas fa-arrow-up"></i>
                            Elevator
                        </label>
                    </div>
                    <div class="amenity-item">
                        <input type="checkbox" id="laundry" name="amenities[]" value="laundry" {{ in_array('laundry', old('amenities', [])) ? 'checked' : '' }}>
                        <label for="laundry">
                            <i class="fas fa-tshirt"></i>
                            Laundry Room
                        </label>
                    </div>
                    <div class="amenity-item">
                        <input type="checkbox" id="wifi" name="amenities[]" value="wifi" {{ in_array('wifi', old('amenities', [])) ? 'checked' : '' }}>
                        <label for="wifi">
                            <i class="fas fa-wifi"></i>
                            Free WiFi
                        </label>
                    </div>
                    <div class="amenity-item">
                        <input type="checkbox" id="garden" name="amenities[]" value="garden" {{ in_array('garden', old('amenities', [])) ? 'checked' : '' }}>
                        <label for="garden">
                            <i class="fas fa-seedling"></i>
                            Garden/Green Space
                        </label>
                    </div>
                    <div class="amenity-item">
                        <input type="checkbox" id="others" name="amenities[]" value="others" {{ in_array('others', old('amenities', [])) ? 'checked' : '' }}>
                        <label for="others">
                            <i class="fas fa-ellipsis-h"></i>
                            Others
                        </label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('landlord.apartments') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Create Property
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    // Form validation and enhancement
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('createApartmentForm');
        const inputs = form.querySelectorAll('input, select, textarea');
        const coverImageInput = document.getElementById('createApartmentCoverImageInput');
        const galleryInput = document.getElementById('createApartmentGalleryInput');

        if (coverImageInput) {
            coverImageInput.addEventListener('change', function () {
                const selectedFile = this.files && this.files[0];
                if (!selectedFile) {
                    console.warn('[Create Apartment Upload] No cover image selected.');
                    return;
                }

                console.log('[Create Apartment Upload] Cover image selected', {
                    name: selectedFile.name,
                    type: selectedFile.type,
                    size_bytes: selectedFile.size,
                    size_mb: (selectedFile.size / (1024 * 1024)).toFixed(2),
                });
            });
        }

        if (galleryInput) {
            galleryInput.addEventListener('change', function () {
                const files = Array.from(this.files || []);
                console.log('[Create Apartment Upload] Gallery files selected', {
                    count: files.length,
                    files: files.map(function (file) {
                        return {
                            name: file.name,
                            type: file.type,
                            size_bytes: file.size,
                        };
                    }),
                });
            });
        }

        // Real-time validation
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });

        function validateField(field) {
            const value = field.value.trim();
            const isRequired = field.hasAttribute('required');
            
            console.log('Validating field:', field.name, 'value:', value, 'required:', isRequired);
            
            if (isRequired && !value) {
                showError(field, 'This field is required');
                console.log('Field validation failed:', field.name, 'is required but empty');
            } else if (field.type === 'email' && value && !isValidEmail(value)) {
                showError(field, 'Please enter a valid email address');
                console.log('Field validation failed:', field.name, 'invalid email format');
            } else if (field.type === 'tel' && value && !isValidPhone(value)) {
                showError(field, 'Please enter a valid phone number (digits only, 10-20 digits)');
                console.log('Field validation failed:', field.name, 'invalid phone format');
            } else {
                clearError(field);
                console.log('Field validation passed:', field.name);
            }
        }

        function showError(field, message) {
            field.classList.add('error');
            let errorDiv = field.parentNode.querySelector('.form-error');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'form-error';
                field.parentNode.appendChild(errorDiv);
            }
            errorDiv.textContent = message;
        }

        function clearError(field) {
            field.classList.remove('error');
            const errorDiv = field.parentNode.querySelector('.form-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function isValidPhone(phone) {
            // Server expects only digits, so strip formatting and validate
            const digitsOnly = phone.replace(/[\s\-\(\)\+]/g, '');
            return /^[0-9]{10,20}$/.test(digitsOnly);
        }

        // Form submission
        form.addEventListener('submit', function(e) {
            console.log('Form submission started');
            let isValid = true;
            
            // Only validate required fields strictly
            const requiredFields = form.querySelectorAll('input[required], select[required], textarea[required]');
            requiredFields.forEach(field => {
                validateField(field);
                if (field.classList.contains('error') || !field.value.trim()) {
                    isValid = false;
                    console.log('Required field validation failed:', field.name);
                }
            });

            // For optional fields, only validate if they have a value, but don't block submission
            inputs.forEach(input => {
                if (!input.hasAttribute('required') && input.value.trim()) {
                    validateField(input);
                    // Don't block submission for optional field errors, just show the error
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fix the errors in required fields before submitting.');
                console.log('Form submission prevented due to validation errors in required fields');
                return false;
            } else {
                console.log('Form validation passed, submitting...');
                
                // Strip formatting from phone number before submission
                const phoneInput = form.querySelector('input[name="contact_phone"]');
                if (phoneInput && phoneInput.value.trim()) {
                    phoneInput.value = phoneInput.value.replace(/[\s\-\(\)\+]/g, '');
                }
                
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Property...';
                }

                console.log('[Create Apartment Upload] Submitting form', {
                    action: form.action,
                    method: form.method,
                    enctype: form.enctype,
                    cover_selected: Boolean(coverImageInput && coverImageInput.files && coverImageInput.files[0]),
                    gallery_count: galleryInput && galleryInput.files ? galleryInput.files.length : 0,
                });
                // Allow form to submit
                return true;
            }
        });

        // Property type change handler
        const propertyTypeSelect = document.querySelector('select[name="property_type"]');
        const floorsGroup = document.getElementById('floors_group');
        const bedroomsGroup = document.getElementById('bedrooms_group');
        const duplexUnitBedroomsGroup = document.getElementById('duplex_unit_bedrooms_group');
        const duplexUnitStoriesGroup = document.getElementById('duplex_unit_stories_group');
        const buildingFloorsGroup = document.getElementById('building_floors_group');
        const floorsInput = document.getElementById('floors');
        const bedroomsInput = document.getElementById('bedrooms');
        const unitBedrooms0 = document.getElementById('unit_bedrooms_0');
        const unitBedrooms1 = document.getElementById('unit_bedrooms_1');
        const unitStories0 = document.getElementById('unit_stories_0');
        const unitStories1 = document.getElementById('unit_stories_1');
        const buildingFloorsInput = document.getElementById('building_floors');
        const buildingFloorsLabel = document.getElementById('building_floors_label');
        const buildingFloorsHint = document.getElementById('building_floors_hint');
        const dwellingStoriesGroup = document.getElementById('dwelling_stories_group');
        const dwellingStoriesInput = document.getElementById('dwelling_stories');
        const floorsLabel = document.getElementById('floors_label');
        const floorsHint = document.getElementById('floors_hint');
        const duplexUnitCountInput = document.getElementById('duplex_unit_count_input');

        const hintBuildingDuplexOptional = 'Optional: total above-grade stories of the whole building (for example a stacked duplex). This does not replace interior stories per unit below.';
        const hintBuildingDwellingRequired = 'Required: total above-grade stories of this structure (whole building / exterior levels).';

        function togglePropertyFields() {
            const propertyType = propertyTypeSelect.value;

            if (duplexUnitCountInput) {
                duplexUnitCountInput.setAttribute('disabled', 'disabled');
            }
            if (floorsInput) {
                floorsInput.removeAttribute('readonly');
                floorsInput.removeAttribute('data-duplex-locked');
                floorsInput.removeAttribute('disabled');
            }

            if (duplexUnitBedroomsGroup) {
                duplexUnitBedroomsGroup.style.display = 'none';
            }
            if (duplexUnitStoriesGroup) {
                duplexUnitStoriesGroup.style.display = 'none';
            }
            if (unitBedrooms0) {
                unitBedrooms0.setAttribute('disabled', 'disabled');
                unitBedrooms0.removeAttribute('required');
            }
            if (unitBedrooms1) {
                unitBedrooms1.setAttribute('disabled', 'disabled');
                unitBedrooms1.removeAttribute('required');
            }
            if (unitStories0) {
                unitStories0.setAttribute('disabled', 'disabled');
                unitStories0.removeAttribute('required');
            }
            if (unitStories1) {
                unitStories1.setAttribute('disabled', 'disabled');
                unitStories1.removeAttribute('required');
            }
            if (dwellingStoriesGroup) {
                dwellingStoriesGroup.style.display = 'none';
            }
            if (dwellingStoriesInput) {
                dwellingStoriesInput.setAttribute('disabled', 'disabled');
                dwellingStoriesInput.removeAttribute('required');
            }

            if (propertyType === 'house') {
                floorsGroup.style.display = 'none';
                bedroomsGroup.style.display = 'block';
                buildingFloorsGroup.style.display = 'block';
                if (dwellingStoriesGroup) {
                    dwellingStoriesGroup.style.display = 'block';
                }
                if (dwellingStoriesInput) {
                    dwellingStoriesInput.removeAttribute('disabled');
                }
                floorsInput.removeAttribute('required');
                floorsInput.setAttribute('disabled', 'disabled');
                bedroomsInput.removeAttribute('disabled');
                bedroomsInput.setAttribute('required', 'required');
                buildingFloorsInput.setAttribute('required', 'required');
                if (buildingFloorsLabel) {
                    buildingFloorsLabel.classList.add('required');
                    buildingFloorsLabel.textContent = 'Building stories (floors)';
                }
                if (buildingFloorsHint) {
                    buildingFloorsHint.textContent = hintBuildingDwellingRequired;
                }
            } else if (propertyType === 'duplex') {
                floorsGroup.style.display = 'none';
                bedroomsGroup.style.display = 'none';
                if (duplexUnitBedroomsGroup) {
                    duplexUnitBedroomsGroup.style.display = 'grid';
                }
                if (duplexUnitStoriesGroup) {
                    duplexUnitStoriesGroup.style.display = 'grid';
                }
                buildingFloorsGroup.style.display = 'block';
                floorsInput.removeAttribute('required');
                floorsInput.setAttribute('disabled', 'disabled');
                bedroomsInput.removeAttribute('required');
                bedroomsInput.setAttribute('disabled', 'disabled');
                buildingFloorsInput.removeAttribute('required');
                if (buildingFloorsLabel) {
                    buildingFloorsLabel.classList.remove('required');
                    buildingFloorsLabel.innerHTML = 'Building stories (floors) <span class="text-muted font-weight-normal">(optional)</span>';
                }
                if (buildingFloorsHint) {
                    buildingFloorsHint.textContent = hintBuildingDuplexOptional;
                }
                if (unitBedrooms0) {
                    unitBedrooms0.removeAttribute('disabled');
                    unitBedrooms0.setAttribute('required', 'required');
                }
                if (unitBedrooms1) {
                    unitBedrooms1.removeAttribute('disabled');
                    unitBedrooms1.setAttribute('required', 'required');
                }
                if (unitStories0) {
                    unitStories0.removeAttribute('disabled');
                    unitStories0.setAttribute('required', 'required');
                }
                if (unitStories1) {
                    unitStories1.removeAttribute('disabled');
                    unitStories1.setAttribute('required', 'required');
                }
                if (duplexUnitCountInput) {
                    duplexUnitCountInput.removeAttribute('disabled');
                }
            } else if (propertyType === 'townhouse') {
                floorsGroup.style.display = 'none';
                bedroomsGroup.style.display = 'block';
                buildingFloorsGroup.style.display = 'block';
                if (dwellingStoriesGroup) {
                    dwellingStoriesGroup.style.display = 'block';
                }
                if (dwellingStoriesInput) {
                    dwellingStoriesInput.removeAttribute('disabled');
                }
                floorsInput.removeAttribute('required');
                floorsInput.setAttribute('disabled', 'disabled');
                bedroomsInput.removeAttribute('disabled');
                bedroomsInput.setAttribute('required', 'required');
                buildingFloorsInput.setAttribute('required', 'required');
                if (buildingFloorsLabel) {
                    buildingFloorsLabel.classList.add('required');
                    buildingFloorsLabel.textContent = 'Building stories (floors)';
                }
                if (buildingFloorsHint) {
                    buildingFloorsHint.textContent = hintBuildingDwellingRequired;
                }
            } else if (propertyType === 'apartment' || propertyType === 'condominium') {
                floorsGroup.style.display = 'block';
                bedroomsGroup.style.display = 'none';
                buildingFloorsGroup.style.display = 'none';
                floorsInput.setAttribute('required', 'required');
                floorsInput.removeAttribute('disabled');
                bedroomsInput.removeAttribute('required');
                bedroomsInput.removeAttribute('disabled');
                buildingFloorsInput.removeAttribute('required');
                if (buildingFloorsLabel) {
                    buildingFloorsLabel.classList.add('required');
                    buildingFloorsLabel.textContent = 'Building stories (floors)';
                }
                if (floorsLabel) {
                    floorsLabel.textContent = 'Number of units';
                }
                if (floorsHint) {
                    floorsHint.textContent = 'How many rentable units does this building have? (Minimum 2.)';
                }
            } else {
                floorsGroup.style.display = 'block';
                bedroomsGroup.style.display = 'none';
                buildingFloorsGroup.style.display = 'none';
                floorsInput.setAttribute('required', 'required');
                floorsInput.removeAttribute('disabled');
                bedroomsInput.removeAttribute('required');
                bedroomsInput.removeAttribute('disabled');
                buildingFloorsInput.removeAttribute('required');
                if (buildingFloorsLabel) {
                    buildingFloorsLabel.classList.add('required');
                    buildingFloorsLabel.textContent = 'Building stories (floors)';
                }
                if (floorsLabel) {
                    floorsLabel.textContent = 'Number of units';
                }
                if (floorsHint) {
                    floorsHint.textContent = 'How many rentable units does this property have?';
                }
            }
        }

        const unitMediaSlots = document.getElementById('unit_media_slots');
        const unitMediaPlaceholder = document.getElementById('unit_media_placeholder');
        const unitMediaIntro = document.getElementById('unit_media_intro');

        function plannedUnitCountForPhotos() {
            const propertyType = propertyTypeSelect ? propertyTypeSelect.value : '';
            if (propertyType === 'house' || propertyType === 'townhouse') {
                return 1;
            }
            if (propertyType === 'duplex') {
                return 2;
            }
            const floors = parseInt(floorsInput && floorsInput.value ? floorsInput.value : '0', 10);
            if (!floors || floors < 1) {
                return 0;
            }
            if (propertyType === 'apartment' || propertyType === 'condominium') {
                return floors >= 2 ? floors : 0;
            }
            return floors;
        }

        function rebuildUnitMediaSlots() {
            if (!unitMediaSlots) {
                return;
            }
            const n = plannedUnitCountForPhotos();
            unitMediaSlots.innerHTML = '';

            if (unitMediaPlaceholder) {
                unitMediaPlaceholder.style.display = 'none';
                unitMediaPlaceholder.textContent = '';
            }
            if (unitMediaIntro) {
                unitMediaIntro.style.display = n > 0 ? 'block' : 'none';
            }

            if (n === 0) {
                const pt = propertyTypeSelect ? propertyTypeSelect.value : '';
                if (unitMediaPlaceholder && (pt === 'apartment' || pt === 'condominium')) {
                    unitMediaPlaceholder.textContent = 'Enter how many units this building has (at least 2) above to show per-unit photo slots.';
                    unitMediaPlaceholder.style.display = 'block';
                }
                return;
            }

            for (let i = 0; i < n; i++) {
                const card = document.createElement('div');
                card.className = 'unit-media-card';
                const label = i + 1;
                card.innerHTML = `
                    <h4>Unit ${label}</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Unit ${label} cover image</label>
                            <input type="file" name="unit_media[${i}][cover]" accept="image/*" class="form-control">
                            <p class="form-help">JPEG/PNG up to 3MB</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit ${label} gallery (up to 12)</label>
                            <input type="file" name="unit_media[${i}][gallery][]" accept="image/*" multiple class="form-control">
                        </div>
                    </div>
                `;
                unitMediaSlots.appendChild(card);
            }
        }

        if (floorsInput) {
            floorsInput.addEventListener('input', rebuildUnitMediaSlots);
            floorsInput.addEventListener('change', rebuildUnitMediaSlots);
        }

        if (propertyTypeSelect) {
            propertyTypeSelect.addEventListener('change', function () {
                togglePropertyFields();
                rebuildUnitMediaSlots();
            });
            togglePropertyFields();
            rebuildUnitMediaSlots();
        }
    });
</script>
@endpush
