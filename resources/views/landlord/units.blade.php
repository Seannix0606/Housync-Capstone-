@extends('layouts.landlord-app')

@section('title', 'My Units')

@push('styles')
<style>
</style>
@endpush

@section('content')
<div class="content-header mb-4">
    <div>
        <h1>My Units</h1>
        <p style="color: #64748b; margin-top: 0.5rem;">Manage all your rental units</p>
    </div>
</div>
@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif
<!-- Stats Cards -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_units'] }}</div>
        <div class="stat-label">Total Units</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['available_units'] }}</div>
        <div class="stat-label">Available Units</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['occupied_units'] }}</div>
        <div class="stat-label">Occupied Units</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">₱{{ number_format($stats['monthly_revenue'], 0) }}</div>
        <div class="stat-label">Monthly Revenue</div>
    </div>
</div>
<!-- Units Section -->
<div class="page-section">
    <div class="section-header d-flex flex-wrap justify-content-between align-items-end mb-3">
        <div>
            <h2 class="section-title">All Units</h2>
            <p class="section-subtitle">View and manage your rental units across all properties</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-end ms-auto" style="min-width:270px;">
            <div class="me-2">
                <label class="me-2" style="font-size: 0.875rem; color: #64748b;">Property:</label>
                <select id="apartmentFilter" style="padding: 0.5rem; min-width:200px; border-radius: 0.375rem; border: 1px solid #e2e8f0;">
                    <option value="">All Properties</option>
                    @foreach($apartments as $apt)
                        <option value="{{ $apt->id }}" {{ (request('apartment') == $apt->id || ($apartmentId ?? null) == $apt->id) ? 'selected' : '' }}>
                            {{ $apt->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sort-dropdown">
                <label class="me-2" style="font-size: 0.875rem; color: #64748b;">Sort by:</label>
                <select id="unitSort" style="padding: 0.5rem; min-width:155px; border-radius: 0.375rem; border: 1px solid #e2e8f0;">
                    <option value="property_unit" {{ request('sort', 'property_unit') == 'property_unit' ? 'selected' : '' }}>Property → Floor → Unit</option>
                    <option value="floor" {{ request('sort') == 'floor' ? 'selected' : '' }}>Floor → Unit Number</option>
                    <option value="property" {{ request('sort') == 'property' ? 'selected' : '' }}>Property Name</option>
                    <option value="unit_number" {{ request('sort') == 'unit_number' ? 'selected' : '' }}>Unit Number Only</option>
                    <option value="status" {{ request('sort') == 'status' ? 'selected' : '' }}>Status (Available First)</option>
                    <option value="rent" {{ request('sort') == 'rent' ? 'selected' : '' }}>Rent (Highest First)</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                </select>
            </div>
            <a href="{{ route('landlord.create-unit') }}" class="btn btn-primary ms-2"><i class="fas fa-plus"></i> Add New Unit</a>
        </div>
    </div>
    @if($units->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 12%;">Unit Number</th>
                        <th style="width: 18%;">Property</th>
                        <th style="width: 12%;">Type</th>
                        <th style="width: 10%;" class="text-center">Beds / Baths</th>
                        <th style="width: 8%;" class="text-center">Floor</th>
                        <th style="width: 10%;" class="text-center">Status</th>
                        <th style="width: 12%;" class="text-end">Rent/Month</th>
                        <th style="width: 8%;" class="text-center">Max Occupants</th>
                        <th style="width: 10%;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($units as $unit)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="unit-number-badge">{{ $unit->unit_number }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-building text-muted me-2"></i>
                                <span class="property-name">{{ $unit->apartment->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="unit-type">{{ str_replace('_', ' ', ucfirst($unit->unit_type ?? 'N/A')) }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center align-items-center gap-3">
                                <span class="bed-bath-info" title="Bedrooms">
                                    <i class="fas fa-bed text-muted me-1"></i>{{ $unit->bedrooms ?? 0 }}
                                </span>
                                <span class="bed-bath-info" title="Bathrooms">
                                    <i class="fas fa-bath text-muted me-1"></i>{{ $unit->bathrooms ?? 1 }}
                                </span>
                            </div>
                        </td>
                        <td class="text-center"><span class="floor-number">{{ $unit->floor_number ?? 'N/A' }}</span></td>
                        <td class="text-center">
                            @php
                                $statusConfig = [
                                    'available' => ['class' => 'badge bg-success', 'text' => 'Available'],
                                    'occupied' => ['class' => 'badge bg-danger', 'text' => 'Occupied'],
                                    'maintenance' => ['class' => 'badge bg-warning', 'text' => 'Maintenance'],
                                ];
                                $config = $statusConfig[$unit->status] ?? ['class' => 'badge bg-secondary', 'text' => ucfirst($unit->status)];
                            @endphp
                            <span class="{{ $config['class'] }}">{{ $config['text'] }}</span>
                        </td>
                        <td class="text-end"><span class="rent-amount">₱{{ number_format($unit->rent_amount ?? 0, 0) }}</span></td>
                        <td class="text-center"><span class="max-occupants">{{ $unit->max_occupants ?? '-' }}</span></td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button onclick="editUnit({{ $unit->id }})" class="btn btn-sm btn-outline-primary" title="Edit Unit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="viewUnitDetails({{ $unit->id }})" class="btn btn-sm btn-outline-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if($units->hasPages())
            <div class="pagination mt-4">
                {{ $units->appends(['sort' => request('sort'), 'apartment' => request('apartment')])->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-door-open"></i></div>
            <h3 class="empty-title">No Units Found</h3>
            <p class="empty-text">
                @if(request()->hasAny(['search', 'status', 'apartment']))
                    No units match your search criteria. Try adjusting your filters.
                @else
                    You haven't added any units yet. Start by adding units to your properties.
                @endif
            </p>
            @if(request()->hasAny(['search', 'status', 'apartment']))
                <a href="{{ route('landlord.units') }}" class="btn btn-primary"><i class="fas fa-refresh"></i> Clear Filters</a>
            @else
                <a href="{{ route('landlord.apartments') }}" class="btn btn-primary"><i class="fas fa-building"></i> Go to Properties</a>
            @endif
        </div>
    @endif
</div>
<!-- Modals and JS remain below as before -->
@endsection

    <script>

        function editUnit(unitId) {
            // Show the edit modal
            const modal = new bootstrap.Modal(document.getElementById('editUnitModal'));
            const modalTitle = document.getElementById('editUnitModalLabel');
            const modalContent = document.getElementById('editUnitContent');
            const saveBtn = document.getElementById('saveUnitBtn');
            const form = document.getElementById('editUnitForm');
            
            // Reset modal content
            modalContent.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading unit details...</p>
                </div>
            `;
            saveBtn.style.display = 'none';
            
            // Show modal
            modal.show();
            
            // Reset gallery files array
            editGalleryFiles = [];
            
            // Fetch unit data
            fetch(`/landlord/units/${unitId}/details`)
                .then(response => response.json())
                .then(data => {
                    modalTitle.textContent = `Edit Unit ${data.unit_number}`;
                    form.action = `/landlord/units/${unitId}`;
                    
                    // Clear any previous previews
                    const editGalleryPreview = document.getElementById('edit_gallery_preview');
                    if (editGalleryPreview) {
                        editGalleryPreview.innerHTML = '';
                        editGalleryPreview.style.display = 'none';
                    }
                    
                    // Generate form content
                    modalContent.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_unit_number" class="form-label">Unit Number *</label>
                                    <input type="text" class="form-control" id="edit_unit_number" name="unit_number" value="${data.unit_number}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_unit_type" class="form-label">Unit Type *</label>
                                    <select class="form-control" id="edit_unit_type" name="unit_type" required>
                                        <option value="studio" ${data.unit_type === 'studio' ? 'selected' : ''}>Studio</option>
                                        <option value="one_bedroom" ${data.unit_type === 'one_bedroom' ? 'selected' : ''}>One Bedroom</option>
                                        <option value="two_bedroom" ${data.unit_type === 'two_bedroom' ? 'selected' : ''}>Two Bedroom</option>
                                        <option value="three_bedroom" ${data.unit_type === 'three_bedroom' ? 'selected' : ''}>Three Bedroom</option>
                                        <option value="penthouse" ${data.unit_type === 'penthouse' ? 'selected' : ''}>Penthouse</option>
                                    </select>
                                    <small class="form-text text-muted">Bedrooms will auto-update based on selection</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_rent_amount" class="form-label">Monthly Rent (₱) *</label>
                                    <input type="number" class="form-control" id="edit_rent_amount" name="rent_amount" value="${data.rent_amount}" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Status *</label>
                                    <select class="form-control" id="edit_status" name="status" required>
                                        <option value="available" ${data.status === 'available' ? 'selected' : ''}>Available</option>
                                        <option value="occupied" ${data.status === 'occupied' ? 'selected' : ''}>Occupied</option>
                                        <option value="maintenance" ${data.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_leasing_type" class="form-label">Leasing Type *</label>
                                    <select class="form-control" id="edit_leasing_type" name="leasing_type" required>
                                        <option value="separate" ${data.leasing_type === 'separate' ? 'selected' : ''}>Separate (Utilities not included)</option>
                                        <option value="inclusive" ${data.leasing_type === 'inclusive' ? 'selected' : ''}>Inclusive (Utilities included)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_bedrooms" class="form-label">Bedrooms *</label>
                                    <input type="number" class="form-control" id="edit_bedrooms" name="bedrooms" value="${data.bedrooms}" min="0" required readonly>
                                    <small class="form-text text-muted">Auto-filled based on unit type</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_bathrooms" class="form-label">Bathrooms *</label>
                                    <input type="number" class="form-control" id="edit_bathrooms" name="bathrooms" value="${data.bathrooms}" min="1" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit_is_furnished" name="is_furnished" value="1" ${data.is_furnished ? 'checked' : ''}>
                                <label class="form-check-label" for="edit_is_furnished">
                                    Furnished Unit
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Amenities</label>
                            <div class="row">
                                ${generateAmenitiesCheckboxes(data.amenities || [])}
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3">${data.description || ''}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="2">${data.notes || ''}</textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="mb-3">
                            <label for="edit_cover_image" class="form-label">Cover Image</label>
                            ${data.cover_image_url ? `
                                <div class="mb-2">
                                    <img src="${data.cover_image_url}" alt="Current Cover" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e2e8f0;">
                                </div>
                            ` : ''}
                            <input type="file" class="form-control" id="edit_cover_image" name="cover_image" accept="image/*" onchange="previewEditCoverImage(this)">
                            <div id="edit_cover_preview" class="mt-2" style="display: none;">
                                <img id="edit_cover_preview_img" src="" alt="Cover Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e2e8f0;">
                            </div>
                            <small class="form-text text-muted">Upload a new cover image (JPEG/PNG, max 5MB)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Gallery Images (up to 12)</label>
                            ${data.gallery_urls && data.gallery_urls.length > 0 ? `
                                <div class="mb-2">
                                    <p class="text-muted small">Current gallery images (${data.gallery_urls.length}):</p>
                                    <div class="d-flex flex-wrap gap-2 mb-2" id="existing_gallery_images">
                                        ${data.gallery_urls.map((url, idx) => `
                                            <div style="position: relative;">
                                                <img src="${url}" alt="Existing Gallery ${idx + 1}" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 2px solid #e2e8f0;">
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : '<p class="text-muted small mb-2">No gallery images yet. Add images below.</p>'}
                            <input type="file" class="form-control" id="edit_gallery_input" name="gallery[]" accept="image/*" multiple style="display: none;" onchange="handleEditGalleryUpload(this, ${data.gallery_urls ? data.gallery_urls.length : 0})">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('edit_gallery_input').click()">
                                <i class="fas fa-plus-circle me-2"></i>Add Images to Gallery
                            </button>
                            <small class="form-text text-muted d-block mt-1">Add more images to showcase the unit (JPEG/PNG, max 5MB each)</small>
                            <div id="edit_gallery_preview" class="mt-3" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem;">
                                <!-- New gallery previews will be added here -->
                            </div>
                        </div>
                    `;
                    
                    // Show save button
                    saveBtn.style.display = 'inline-block';
                    
                    // Add event listener for unit type change to auto-populate bedrooms
                    const editUnitTypeSelect = document.getElementById('edit_unit_type');
                    const editBedroomsInput = document.getElementById('edit_bedrooms');
                    
                    if (editUnitTypeSelect && editBedroomsInput) {
                        editUnitTypeSelect.addEventListener('change', function() {
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
                                    bedroomCount = 3; // Default for penthouse
                                    break;
                                default:
                                    bedroomCount = 0;
                            }
                            
                            editBedroomsInput.value = bedroomCount;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading unit details:', error);
                    modalContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Error loading unit details. Please try again.
                        </div>
                    `;
                });
        }
        
        function generateAmenitiesCheckboxes(amenities) {
            const allAmenities = [
                { value: 'aircon', label: 'Air Conditioning' },
                { value: 'heating', label: 'Heating' },
                { value: 'balcony', label: 'Balcony' },
                { value: 'parking', label: 'Parking' },
                { value: 'gym', label: 'Gym Access' },
                { value: 'pool', label: 'Pool Access' },
                { value: 'wifi', label: 'WiFi' },
                { value: 'laundry', label: 'Laundry' }
            ];
            
            return allAmenities.map(amenity => {
                const checked = amenities.includes(amenity.value) ? 'checked' : '';
                return `
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_amenity_${amenity.value}" name="amenities[]" value="${amenity.value}" ${checked}>
                            <label class="form-check-label" for="edit_amenity_${amenity.value}">
                                ${amenity.label}
                            </label>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Image preview functions for edit form
        function previewEditCoverImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('edit_cover_preview');
                    const previewImg = document.getElementById('edit_cover_preview_img');
                    if (preview && previewImg) {
                        previewImg.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        let editGalleryFiles = [];
        const maxEditGalleryImages = 12;
        
        function handleEditGalleryUpload(input, existingCount = 0) {
            if (input.files && input.files.length > 0) {
                const files = Array.from(input.files);
                const remainingSlots = maxEditGalleryImages - existingCount;
                
                if (files.length > remainingSlots) {
                    alert(`You can only add ${remainingSlots} more image(s). Maximum ${maxEditGalleryImages} images allowed.`);
                    files.splice(remainingSlots);
                }
                
                files.forEach(file => {
                    if (editGalleryFiles.length < remainingSlots) {
                        editGalleryFiles.push(file);
                        addEditGalleryPreview(file, editGalleryFiles.length - 1);
                    }
                });
                
                updateEditGalleryInput();
            }
        }
        
        function addEditGalleryPreview(file, index) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.getElementById('edit_gallery_preview');
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
                         style="width: 100%; height: 100px; object-fit: cover; display: block;">
                    <button type="button" class="btn btn-sm btn-danger" 
                            onclick="removeEditGalleryImage(${index})"
                            style="position: absolute; top: 2px; right: 2px; padding: 0.2rem 0.4rem; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                previewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        }
        
        function removeEditGalleryImage(index) {
            editGalleryFiles.splice(index, 1);
            updateEditGalleryPreview();
            updateEditGalleryInput();
        }
        
        function updateEditGalleryPreview() {
            const previewContainer = document.getElementById('edit_gallery_preview');
            if (!previewContainer) return;
            
            // Remove only new previews (not existing ones)
            const newPreviews = previewContainer.querySelectorAll('.gallery-item[data-index]');
            newPreviews.forEach(preview => preview.remove());
            
            if (editGalleryFiles.length === 0 && previewContainer.children.length === 0) {
                previewContainer.style.display = 'none';
                return;
            }
            
            previewContainer.style.display = 'grid';
            editGalleryFiles.forEach((file, index) => {
                addEditGalleryPreview(file, index);
            });
        }
        
        function updateEditGalleryInput() {
            const input = document.getElementById('edit_gallery_input');
            if (!input) return;
            
            const dataTransfer = new DataTransfer();
            editGalleryFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            input.files = dataTransfer.files;
        }
        
        // Handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            const editForm = document.getElementById('editUnitForm');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const submitBtn = document.getElementById('saveUnitBtn');
                    const originalText = submitBtn.innerHTML;
                    
                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                    
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Close modal
                            bootstrap.Modal.getInstance(document.getElementById('editUnitModal')).hide();
                            
                            // Show success message and reload page
                            alert('Unit updated successfully!');
                            location.reload();
                        } else {
                            // Show error message
                            alert(data.message || 'An error occurred while updating the unit.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the unit.');
                    })
                    .finally(() => {
                        // Reset button state
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
                });
            }
        });

        function viewUnitDetails(unitId) {
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('unitDetailsModal'));
            const modalTitle = document.getElementById('unitDetailsModalLabel');
            const modalContent = document.getElementById('unitDetailsContent');
            const editBtn = document.getElementById('editUnitBtn');
            
            // Reset modal content
            modalContent.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading unit details...</p>
                </div>
            `;
            editBtn.style.display = 'none';
            
            modal.show();
            
            // Fetch unit details
            fetch(`/landlord/units/${unitId}/details`)
                .then(response => response.json())
                .then(data => {
                    modalTitle.textContent = `Unit ${data.unit_number} - Details`;
                    
                    // Create the details HTML
                    modalContent.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Unit Information</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">Unit Number</label>
                                    <p class="mb-1">${data.unit_number}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">Property</label>
                                    <p class="mb-1">${data.apartment_name}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">Unit Type</label>
                                    <p class="mb-1">${data.unit_type ? data.unit_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Not specified'}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">Monthly Rent</label>
                                    <p class="mb-1 text-success fw-bold">₱${Number(data.rent_amount).toLocaleString()}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">Status</label>
                                    <p class="mb-1">
                                        <span class="badge bg-${data.status === 'occupied' ? 'success' : data.status === 'available' ? 'warning' : 'danger'}">
                                            ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Unit Specifications</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-center border rounded p-2 mb-3">
                                            <h4 class="text-primary mb-0">${data.bedrooms || 0}</h4>
                                            <small class="text-muted">Bedrooms</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center border rounded p-2 mb-3">
                                            <h4 class="text-info mb-0">${data.bathrooms || 0}</h4>
                                            <small class="text-muted">Bathrooms</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">Max Occupants</label>
                                    <p class="mb-1">${data.max_occupants || 'Not specified'}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">Floor Number</label>
                                    <p class="mb-1">${data.floor_number || 'Not specified'}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">Furnished</label>
                                    <p class="mb-1">
                                        <span class="badge bg-${data.is_furnished ? 'success' : 'secondary'}">
                                            ${data.is_furnished ? 'Yes' : 'No'}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        ${data.current_tenant ? `
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3">Current Tenant</h6>
                                <div class="alert alert-info">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Name:</strong> ${data.current_tenant.name}<br>
                                            <strong>Email:</strong> ${data.current_tenant.email}<br>
                                            ${data.current_tenant.phone ? `<strong>Phone:</strong> ${data.current_tenant.phone}<br>` : ''}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Lease Start:</strong> ${data.current_tenant.lease_start}<br>
                                            <strong>Lease End:</strong> ${data.current_tenant.lease_end}<br>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <a href="/landlord/tenant-assignments/${data.current_tenant.assignment_id}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View Assignment Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                        
                        ${data.amenities && data.amenities.length > 0 ? `
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3">Amenities</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    ${data.amenities.map(amenity => `<span class="badge bg-soft-primary text-primary"><i class="fas fa-check me-1"></i>${amenity}</span>`).join('')}
                                </div>
                            </div>
                        </div>
                        ` : ''}
                        
                        ${data.description ? `
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3">Description</h6>
                                <p class="text-muted">${data.description}</p>
                            </div>
                        </div>
                        ` : ''}
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3">Quick Actions</h6>
                                <div class="d-flex gap-2 flex-wrap">
                                    ${data.status === 'available' ? `
                                        <a href="/landlord/tenant-assignments?unit_id=${data.id}" class="btn btn-success btn-sm">
                                            <i class="fas fa-user-plus"></i> Assign Tenant
                                        </a>
                                    ` : ''}
                                    <a href="/landlord/tenant-assignments?unit_id=${data.id}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-history"></i> Assignment History
                                    </a>
                                    <a href="/landlord/units/${data.apartment_id}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-building"></i> View Property
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Show edit button and set up click handler
                    editBtn.style.display = 'inline-block';
                    editBtn.onclick = function() {
                        editUnit(data.id);
                    };
                })
                .catch(error => {
                    console.error('Error fetching unit details:', error);
                    modalContent.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-danger">Error Loading Details</h5>
                            <p class="text-muted">Failed to load unit details. Please try again.</p>
                            <button class="btn btn-primary" onclick="viewUnitDetails(${unitId})">Retry</button>
                        </div>
                    `;
                });
        }

        // assignTenant and vacateUnit removed; tenant actions handled in Tenant Assignments tab

        function deleteUnit(unitId, unitNumber) {
            if (confirm(`Are you sure you want to delete Unit ${unitNumber}? This action cannot be undone.\n\nNote: You cannot delete units with active tenant assignments.`)) {
                // Create and submit delete form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/landlord/units/${unitId}`;
                
                // CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                // DELETE method
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

    </script>

    <!-- Unit Details Modal -->
    <div class="modal fade" id="unitDetailsModal" tabindex="-1" aria-labelledby="unitDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unitDetailsModalLabel">Unit Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="unitDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading unit details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="editUnitBtn" style="display: none;">
                        <i class="fas fa-edit"></i> Edit Unit
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Unit Modal -->
    <div class="modal fade" id="editUnitModal" tabindex="-1" aria-labelledby="editUnitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUnitModalLabel">Edit Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUnitForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body" id="editUnitContent">
                        <div class="text-center py-4">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading unit details...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveUnitBtn" style="display: none;">
                            <i class="fas fa-save"></i> Update Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html> 