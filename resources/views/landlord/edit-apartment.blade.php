@extends('layouts.landlord-app')

@section('title', 'Edit Property')

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

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .content-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .user-profile {
            display: flex;
            align-items: center;
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f97316, #ea580c);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 0.75rem;
        }

        .user-info h3 {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
        }

        .user-info p {
            font-size: 0.75rem;
            color: #64748b;
        }

        /* Page Content */
        .page-section {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .section-subtitle {
            color: #64748b;
            font-size: 1rem;
            margin-top: 0.25rem;
        }

        /* Form Styles */
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 0.75rem;
            border-left: 4px solid #f97316;
        }

        .form-section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section-title i {
            color: #f97316;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .form-label.required::after {
            content: ' *';
            color: #ef4444;
        }

        .form-control {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }

        .form-control.error {
            border-color: #ef4444;
        }

        .form-control.error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        select.form-control {
            cursor: pointer;
        }

        .form-help {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .form-error {
            font-size: 0.75rem;
            color: #ef4444;
            margin-top: 0.25rem;
        }

        /* Amenities Grid */
        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .amenity-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .amenity-item:hover {
            border-color: #f97316;
            background: #fef7ed;
        }

        .amenity-item input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            accent-color: #f97316;
        }

        .amenity-item label {
            font-size: 0.875rem;
            color: #1e293b;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .amenity-item i {
            color: #f97316;
            width: 16px;
        }

        /* Action Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #f97316;
            color: white;
        }

        .btn-primary:hover {
            background: #ea580c;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-outline-primary {
            background: transparent;
            border: 2px solid #f97316;
            color: #f97316;
        }

        .btn-outline-primary:hover {
            background: #f97316;
            color: white;
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1rem;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #f1f5f9;
        }

        .form-actions-left {
            display: flex;
            gap: 1rem;
        }

        .form-actions-right {
            display: flex;
            gap: 1rem;
        }

        /* Alert Styles */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #047857;
        }

        .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        /* Property Preview */
        .property-preview {
            background: #f8fafc;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid #10b981;
        }

        .preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .preview-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
        }

        .preview-icon {
            color: #10b981;
        }

        .preview-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .preview-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .preview-item i {
            color: #f97316;
            width: 16px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .amenities-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }

            .form-actions-left,
            .form-actions-right {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
            <!-- Header -->
            <div class="content-header">
                <div>
                    <h1>Edit Property</h1>
                    <p style="color: #64748b; margin-top: 0.5rem;">Update property information</p>
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

            <!-- Property Preview -->
            <div class="property-preview">
                <div class="preview-header">
                    <i class="fas fa-eye preview-icon"></i>
                    <h3 class="preview-title">Editing: {{ $apartment->name }}</h3>
                    <div style="display: flex; gap: 0.75rem; margin-left: auto;">
                        <a href="{{ route('landlord.units', $apartment->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-door-open"></i> View Units
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="fas fa-trash"></i> Delete Property
                        </button>
                        @if($apartment->units()->count() > 0)
                        <button type="button" class="btn btn-danger" onclick="showForceDeleteModal()" style="background-color: #dc3545; border-color: #dc3545;">
                            <i class="fas fa-exclamation-triangle"></i> Force Delete
                        </button>
                        @endif
                    </div>
                </div>
                <div class="preview-info">
                    <div class="preview-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ $apartment->address }}</span>
                    </div>
                    <div class="preview-item">
                        <i class="fas fa-door-open"></i>
                        <span>{{ $apartment->units->count() }} Units</span>
                    </div>
                    <div class="preview-item">
                        <i class="fas fa-calendar"></i>
                        <span>Created {{ $apartment->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="preview-item">
                        <i class="fas fa-users"></i>
                        <span>{{ $apartment->units->where('status', 'occupied')->count() }} Occupied</span>
                    </div>
                </div>
            </div>

            <!-- Form Section -->
            <div class="page-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Property Information</h2>
                        <p class="section-subtitle">Update the details for your property</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('landlord.update-apartment', $apartment->id) }}" class="form-container" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
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
                                       value="{{ old('name', $apartment->name) }}" placeholder="e.g., Sunshine Apartments" required>
                                @error('name')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Property Type</label>
                                <select name="property_type" class="form-control @error('property_type') error @enderror" required>
                                    <option value="">Select property type</option>
                                    <option value="apartment" {{ old('property_type', $apartment->property_type) == 'apartment' ? 'selected' : '' }}>Apartment Building</option>
                                    <option value="condominium" {{ old('property_type', $apartment->property_type) == 'condominium' ? 'selected' : '' }}>Condominium</option>
                                    <option value="townhouse" {{ old('property_type', $apartment->property_type) == 'townhouse' ? 'selected' : '' }}>Townhouse</option>
                                    <option value="house" {{ old('property_type', $apartment->property_type) == 'house' ? 'selected' : '' }}>Single Family House</option>
                                    <option value="duplex" {{ old('property_type', $apartment->property_type) == 'duplex' ? 'selected' : '' }}>Duplex</option>
                                    <option value="others" {{ old('property_type', $apartment->property_type) == 'others' ? 'selected' : '' }}>Others</option>
                                </select>
                                @error('property_type')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Total Units</label>
                                <input type="number" name="total_units" id="total_units" class="form-control @error('total_units') error @enderror" 
                                       value="{{ old('total_units', $apartment->total_units) }}" min="{{ $apartment->units()->count() }}" placeholder="e.g., 24" required>
                                @error('total_units')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Current actual units: {{ $apartment->units()->count() }}
                                    @if($apartment->units()->count() < $apartment->total_units)
                                        ({{ $apartment->total_units - $apartment->units()->count() }} units need to be created)
                                    @endif
                                </small>
                            </div>

                            <input type="hidden" id="current_unit_count" value="{{ $apartment->units()->count() }}">

                            @if($apartment->units()->count() < $apartment->total_units)
                            <div class="form-group full-width">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Discrepancy Detected:</strong> This property has {{ $apartment->total_units }} units listed but only {{ $apartment->units()->count() }} units actually created.
                                    <br>Would you like to auto-generate the missing {{ $apartment->total_units - $apartment->units()->count() }} units?
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="auto_create_missing" name="auto_create_missing" value="1">
                                        <label class="custom-control-label" for="auto_create_missing">
                                            Yes, auto-create missing units
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="form-group full-width" id="increase_units_notice" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    You're increasing the total units. Would you like to auto-generate the additional units?
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="auto_generate_additional" name="auto_generate_additional" value="1" checked>
                                        <label class="custom-control-label" for="auto_generate_additional">
                                            Yes, auto-generate <span id="additional_count">0</span> additional units
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Year Built</label>
                                <input type="number" name="year_built" class="form-control @error('year_built') error @enderror" 
                                       value="{{ old('year_built', $apartment->year_built) }}" min="1900" max="{{ date('Y') }}" placeholder="e.g., 2020">
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
                                       value="{{ old('address', $apartment->address) }}" placeholder="e.g., 123 Main Street" required>
                                @error('address')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control @error('city') error @enderror" 
                                       value="{{ old('city', $apartment->city) }}" placeholder="e.g., Manila">
                                @error('city')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">State/Province</label>
                                <input type="text" name="state" class="form-control @error('state') error @enderror" 
                                       value="{{ old('state', $apartment->state) }}" placeholder="e.g., Metro Manila">
                                @error('state')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control @error('postal_code') error @enderror" 
                                       value="{{ old('postal_code', $apartment->postal_code) }}" placeholder="e.g., 1234">
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
                                <label class="form-label">Number of Floors</label>
                                <input type="number" name="floors" class="form-control @error('floors') error @enderror" 
                                       value="{{ old('floors', $apartment->floors) }}" min="1" placeholder="e.g., 5">
                                @error('floors')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Parking Spaces</label>
                                <input type="number" name="parking_spaces" class="form-control @error('parking_spaces') error @enderror" 
                                       value="{{ old('parking_spaces', $apartment->parking_spaces) }}" min="0" placeholder="e.g., 20">
                                @error('parking_spaces')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control @error('contact_person') error @enderror" 
                                       value="{{ old('contact_person', $apartment->contact_person) }}" placeholder="e.g., John Doe">
                                @error('contact_person')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Contact Phone</label>
                                <input type="tel" name="contact_phone" class="form-control @error('contact_phone') error @enderror" 
                                       value="{{ old('contact_phone', $apartment->contact_phone) }}" placeholder="e.g., +63 912 345 6789">
                                @error('contact_phone')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Contact Email</label>
                                <input type="email" name="contact_email" class="form-control @error('contact_email') error @enderror" 
                                       value="{{ old('contact_email', $apartment->contact_email) }}" placeholder="e.g., contact@example.com">
                                @error('contact_email')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') error @enderror" 
                                      placeholder="Describe your property, its features, and what makes it special...">{{ old('description', $apartment->description) }}</textarea>
                            @error('description')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-star"></i>
                            Property Amenities
                        </h3>
                        <p class="form-help">Select the amenities available in your property</p>
                        
                        @php
                            $currentAmenities = is_array($apartment->amenities) ? $apartment->amenities : [];
                            $oldAmenities = old('amenities', []);
                            $selectedAmenities = !empty($oldAmenities) ? $oldAmenities : $currentAmenities;
                        @endphp
                        
                        <div class="amenities-grid">
                            <div class="amenity-item">
                                <input type="checkbox" id="pool" name="amenities[]" value="pool" {{ in_array('pool', $selectedAmenities) ? 'checked' : '' }}>
                                <label for="pool">
                                    <i class="fas fa-swimming-pool"></i>
                                    Swimming Pool
                                </label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="gym" name="amenities[]" value="gym" {{ in_array('gym', $selectedAmenities) ? 'checked' : '' }}>
                                <label for="gym">
                                    <i class="fas fa-dumbbell"></i>
                                    Gym/Fitness Center
                                </label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="parking" name="amenities[]" value="parking" {{ in_array('parking', $selectedAmenities) ? 'checked' : '' }}>
                                <label for="parking">
                                    <i class="fas fa-parking"></i>
                                    Parking
                                </label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="security" name="amenities[]" value="security" {{ in_array('security', $selectedAmenities) ? 'checked' : '' }}>
                                <label for="security">
                                    <i class="fas fa-shield-alt"></i>
                                    24/7 Security
                                </label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="elevator" name="amenities[]" value="elevator" {{ in_array('elevator', $selectedAmenities) ? 'checked' : '' }}>
                                <label for="elevator">
                                    <i class="fas fa-arrow-up"></i>
                                    Elevator
                                </label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="laundry" name="amenities[]" value="laundry" {{ in_array('laundry', $selectedAmenities) ? 'checked' : '' }}>
                                <label for="laundry">
                                    <i class="fas fa-tshirt"></i>
                                    Laundry Room
                                </label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="wifi" name="amenities[]" value="wifi" {{ in_array('wifi', $selectedAmenities) ? 'checked' : '' }}>
                                <label for="wifi">
                                    <i class="fas fa-wifi"></i>
                                    Free WiFi
                                </label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="garden" name="amenities[]" value="garden" {{ in_array('garden', $selectedAmenities) ? 'checked' : '' }}>
                                <label for="garden">
                                    <i class="fas fa-seedling"></i>
                                    Garden/Green Space
                                </label>
                            </div>
                            <div class="amenity-item">
                                <input type="checkbox" id="others" name="amenities[]" value="others" {{ in_array('others', $selectedAmenities) ? 'checked' : '' }}>
                                <label for="others">
                                    <i class="fas fa-ellipsis-h"></i>
                                    Others
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Property Photos -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-image"></i>
                            Property Photos
                        </h3>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label class="form-label">Cover Image</label>
                                @if($apartment->cover_image_url)
                                    <div class="mb-2">
                                        <p class="text-muted small mb-2">Current cover image:</p>
                                        <img src="{{ $apartment->cover_image_url }}" alt="Current Cover" style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 2px solid #e2e8f0;">
                                    </div>
                                @else
                                    <p class="text-muted small mb-2">No cover image yet. Upload one below.</p>
                                @endif
                                <input type="file" name="cover_image" id="cover_image" accept="image/*" class="form-control" onchange="previewCoverImage(this)">
                                <div id="cover_image_preview" class="mt-2" style="display: none;">
                                    <img id="cover_preview_img" src="" alt="Cover Preview" style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 2px solid #e2e8f0;">
                                </div>
                                <p class="form-help text-muted mt-1">Main image displayed for this property (JPEG/PNG, max 5MB)</p>
                            </div>
                        </div>
                        
                        <div class="form-group full-width mt-3">
                            <label class="form-label">Gallery Images (up to 12)</label>
                            @if($apartment->gallery_urls && count($apartment->gallery_urls) > 0)
                                <div class="mb-2">
                                    <p class="text-muted small">Current gallery images ({{ count($apartment->gallery_urls) }}):</p>
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        @foreach($apartment->gallery_urls as $url)
                                            <div style="position: relative;">
                                                <img src="{{ $url }}" alt="Gallery Image" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; border: 2px solid #e2e8f0;">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-muted small mb-2">No gallery images yet. Add images below.</p>
                            @endif
                            <div class="gallery-upload-container">
                                <input type="file" name="gallery[]" id="gallery_input" accept="image/*" multiple class="form-control" onchange="handleGalleryUpload(this)" style="display: none;">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('gallery_input').click()">
                                    <i class="fas fa-plus-circle me-2"></i>Add Images to Gallery
                                </button>
                                <p class="form-help text-muted mt-2">Add multiple images to showcase the property (JPEG/PNG, max 5MB each)</p>
                            </div>
                            
                            <div id="gallery_preview" class="gallery-preview mt-3" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem;">
                                <!-- Gallery previews will be added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <div class="form-actions-left">
                            <a href="{{ route('landlord.apartments') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                        <div class="form-actions-right">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Update Property
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Delete Form -->
                <form id="delete-form" method="POST" action="{{ route('landlord.delete-apartment', $apartment->id) }}" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>

                <!-- Force Delete Form -->
                <form id="force-delete-form" method="POST" action="{{ route('landlord.delete-apartment', $apartment->id) }}" style="display: none;">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="force_delete" value="1">
                    <input type="hidden" name="password" id="force-delete-password">
                </form>
            </div>
        </div>
    </div>

    <!-- Force Delete Modal -->
    <div id="force-delete-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
            <h3 style="color: #dc3545; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> Force Delete Property
            </h3>
            <div style="margin-bottom: 20px;">
                <p style="color: #666; margin-bottom: 15px;">
                    <strong>Warning:</strong> This will permanently delete the property "{{ $apartment->name }}" and all {{ $apartment->units()->count() }} unit(s) associated with it.
                </p>
                <p style="color: #dc3545; font-weight: bold; margin-bottom: 15px;">
                    This action cannot be undone!
                </p>
                <p style="color: #666; margin-bottom: 15px;">
                    Please enter your password to confirm:
                </p>
                <input type="password" id="modal-password" class="form-control" placeholder="Enter your password" style="margin-bottom: 10px;">
                <div id="password-error" style="color: #dc3545; font-size: 14px; display: none; margin-top: 5px;">
                    Please enter your password
                </div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeForceDeleteModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmForceDelete()">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Force Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        // Force Delete Modal Functions
        function showForceDeleteModal() {
            const modal = document.getElementById('force-delete-modal');
            modal.style.display = 'flex';
            document.getElementById('modal-password').value = '';
            document.getElementById('password-error').style.display = 'none';
        }

        function closeForceDeleteModal() {
            const modal = document.getElementById('force-delete-modal');
            modal.style.display = 'none';
            document.getElementById('modal-password').value = '';
            document.getElementById('password-error').style.display = 'none';
        }

        function confirmForceDelete() {
            const password = document.getElementById('modal-password').value;
            const errorDiv = document.getElementById('password-error');
            
            if (!password) {
                errorDiv.style.display = 'block';
                return;
            }
            
            // Set password in hidden form field
            document.getElementById('force-delete-password').value = password;
            
            // Submit the force delete form
            document.getElementById('force-delete-form').submit();
        }

        // Close modal when clicking outside
        document.getElementById('force-delete-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeForceDeleteModal();
            }
        });

        // Allow Enter key to submit
        document.getElementById('modal-password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                confirmForceDelete();
            }
        });
    </script>

    <script>
        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input, select, textarea');

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
                
                if (isRequired && !value) {
                    showError(field, 'This field is required');
                } else if (field.type === 'email' && value && !isValidEmail(value)) {
                    showError(field, 'Please enter a valid email address');
                } else if (field.type === 'tel' && value && !isValidPhone(value)) {
                    showError(field, 'Please enter a valid phone number');
                } else {
                    clearError(field);
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
                const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
                return phoneRegex.test(phone);
            }

            // Form submission
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                inputs.forEach(input => {
                    validateField(input);
                    if (input.classList.contains('error')) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fix the errors before submitting.');
                }
            });

            // Detect when total units is increased
            const totalUnitsInput = document.getElementById('total_units');
            const currentUnitCount = parseInt(document.getElementById('current_unit_count').value);
            const increaseNotice = document.getElementById('increase_units_notice');
            const additionalCountSpan = document.getElementById('additional_count');

            if (totalUnitsInput && increaseNotice) {
                totalUnitsInput.addEventListener('input', function() {
                    const newTotal = parseInt(this.value) || 0;
                    
                    if (newTotal > currentUnitCount) {
                        const additional = newTotal - currentUnitCount;
                        additionalCountSpan.textContent = additional;
                        increaseNotice.style.display = 'block';
                    } else {
                        increaseNotice.style.display = 'none';
                    }
                });
            }
        });

        function confirmDelete() {
            const unitCount = {{ $apartment->units()->count() }};
            let message = 'Are you sure you want to delete this property?\n\nThis action cannot be undone.';
            
            if (unitCount > 0) {
                message += '\n\nNote: You must first delete all units in this property before you can delete the property itself.';
            }
            
            if (confirm(message)) {
                document.getElementById('delete-form').submit();
            }
        }

        // Cover image preview
        function previewCoverImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('cover_image_preview');
                    const previewImg = document.getElementById('cover_preview_img');
                    if (preview && previewImg) {
                        previewImg.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Gallery images handling
        let galleryFiles = [];
        const maxGalleryImages = 12;
        const existingGalleryCount = {{ $apartment->gallery_urls ? count($apartment->gallery_urls) : 0 }};
        
        function handleGalleryUpload(input) {
            if (input.files && input.files.length > 0) {
                const files = Array.from(input.files);
                const remainingSlots = maxGalleryImages - existingGalleryCount;
                
                if (files.length > remainingSlots) {
                    alert(`You can only add ${remainingSlots} more image(s). Maximum ${maxGalleryImages} images allowed.`);
                    files.splice(remainingSlots);
                }
                
                files.forEach(file => {
                    if (galleryFiles.length < remainingSlots) {
                        galleryFiles.push(file);
                        addGalleryPreview(file, galleryFiles.length - 1);
                    }
                });
                
                updateGalleryInput();
            }
        }
        
        function addGalleryPreview(file, index) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.getElementById('gallery_preview');
                if (!previewContainer) return;
                
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
            if (!previewContainer) return;
            
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
            if (!input) return;
            
            const dataTransfer = new DataTransfer();
            galleryFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            input.files = dataTransfer.files;
        }
    </script>
@endsection