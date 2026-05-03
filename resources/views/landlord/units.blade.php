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
<div class="stats-grid mb-4" id="landlordUnitsStats">
    <div class="stat-card">
        <div class="stat-value" id="statTotalUnits">{{ $stats['total_units'] }}</div>
        <div class="stat-label">Total Units</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="statAvailableUnits">{{ $stats['available_units'] }}</div>
        <div class="stat-label">Available Units</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="statOccupiedUnits">{{ $stats['occupied_units'] }}</div>
        <div class="stat-label">Occupied Units</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="statMonthlyRevenue">₱{{ number_format($stats['monthly_revenue'], 0) }}</div>
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
            <button type="button" class="btn btn-primary ms-2" id="openAddUnitModal" data-bs-toggle="modal" data-bs-target="#addUnitModal"
                @if(!empty($addUnitBlockedMessage)) disabled title="{{ $addUnitBlockedMessage }}" @endif
            ><i class="fas fa-plus"></i> Add New Unit</button>
        </div>
        @if(!empty($addUnitBlockedMessage))
            <p class="small text-muted mt-2 mb-0 w-100 text-end">{{ $addUnitBlockedMessage }}</p>
        @endif
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
                <tbody id="unitsTableBody">
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

@include('landlord.partials.add-unit.modal')

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

@endsection

@push('scripts')
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_unit_stories" class="form-label">Interior stories <span class="text-muted small">(optional)</span></label>
                                    <input type="number" class="form-control" id="edit_unit_stories" name="unit_stories" value="${data.unit_stories !== null && data.unit_stories !== undefined ? data.unit_stories : ''}" min="1" max="50" placeholder="Levels inside this unit">
                                    <small class="form-text text-muted">Per-unit levels (for duplex sides, townhomes, etc.). Leave blank if unknown.</small>
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
                            <label for="edit_cover_image" class="form-label">Unit Cover Image</label>
                            ${data.cover_image_url ? `
                                <div class="mb-2">
                                    <img src="${data.cover_image_url}" alt="Current Cover" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e2e8f0;">
                                </div>
                            ` : ''}
                            <input type="file" class="form-control" id="edit_cover_image" name="unit_cover_image" accept="image/*" onchange="previewEditCoverImage(this)">
                            <div id="edit_cover_preview" class="mt-2" style="display: none;">
                                <img id="edit_cover_preview_img" src="" alt="Cover Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e2e8f0;">
                            </div>
                            <small class="form-text text-muted">Shown in unit card and unit details (JPEG/PNG, max 5MB)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Unit Gallery (up to 12)</label>
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
                            <input type="file" class="form-control" id="edit_gallery_input" name="unit_gallery[]" accept="image/*" multiple style="display: none;" onchange="handleEditGalleryUpload(this, ${data.gallery_urls ? data.gallery_urls.length : 0})">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('edit_gallery_input').click()">
                                <i class="fas fa-plus-circle me-2"></i>Add Images to Gallery
                            </button>
                            <small class="form-text text-muted d-block mt-1">Additional room/unit photos for unit listing presentation</small>
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
                                    <p class="mb-1">${data.property_name}</p>
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
                                    <a href="/landlord/units/${data.property_id}" class="btn btn-outline-secondary btn-sm">
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

(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const modalEl = document.getElementById('addUnitModal');
    if (!modalEl) return;

    const urls = {
        single: @json(route('landlord.units.modal.single')),
        bulkSessionPost: @json(route('landlord.store-bulk-units', ['apartmentId' => 888888888])),
        bulkSessionPostPlaceholder: '888888888',
    };

    let step = 1;
    let mode = null;

    const panels = () => modalEl.querySelectorAll('[data-add-unit-panel]');
    const stepEls = () => modalEl.querySelectorAll('[data-add-unit-step]');
    const btnBack = document.getElementById('addUnitBtnBack');
    const btnNext = document.getElementById('addUnitBtnNext');
    const btnSubmit = document.getElementById('addUnitBtnSubmit');
    const errBox = document.getElementById('addUnitModalError');

    function fixedDwellingPropertyTypes() {
        return ['house', 'townhouse', 'duplex'];
    }

    function propertySupportsFlooredBulk(propertyType) {
        const t = String(propertyType || '').toLowerCase();

        return !fixedDwellingPropertyTypes().includes(t);
    }

    function selectedPropertyOption() {
        const sel = document.getElementById('addUnitPropertyId');
        if (!sel || !sel.value) {
            return null;
        }

        return sel.options[sel.selectedIndex];
    }

    function selectedPropertyMayAddUnits() {
        const opt = selectedPropertyOption();
        if (!opt || !opt.value) {
            return false;
        }
        const maxRaw = opt.getAttribute('data-max-units');
        if (maxRaw === null || maxRaw === '') {
            return true;
        }
        const max = parseInt(maxRaw, 10);
        if (Number.isNaN(max)) {
            return true;
        }
        const cur = parseInt(opt.getAttribute('data-units-count') || '0', 10) || 0;

        return cur < max;
    }

    function updateFlooredTotalPreview() {
        const wrapFloored = document.getElementById('addUnitBulkFlooredWrap');
        if (!wrapFloored || wrapFloored.classList.contains('d-none')) {
            return;
        }
        const opt = selectedPropertyOption();
        const stories = opt ? parseInt(opt.getAttribute('data-building-stories') || '1', 10) : 1;
        const upf = parseInt(document.getElementById('addUnitUnitsPerFloor').value, 10) || 0;
        const total = Math.max(0, stories * upf);
        const el = document.getElementById('addUnitFlooredTotalPreview');
        if (el) {
            el.textContent = String(total);
        }
    }

    function updateBulkFloorUi() {
        const floored = document.getElementById('addUnitBulkFlooredWrap');
        const flat = document.getElementById('addUnitBulkFlatWrap');
        const houseWrap = document.getElementById('addUnitBulkHouseWrap');
        const flatLabel = document.getElementById('addUnitBulkFlatLabel');
        const storiesDisp = document.getElementById('addUnitBuildingStoriesDisplay');
        if (!floored || !flat) {
            return;
        }
        if (mode !== 'bulk') {
            return;
        }
        const opt = selectedPropertyOption();
        const ptypeRaw = opt ? (opt.getAttribute('data-property-type') || '') : '';
        const ptype = String(ptypeRaw).toLowerCase();
        const stories = opt ? parseInt(opt.getAttribute('data-building-stories') || '1', 10) : 1;
        const cab = document.getElementById('addUnitBulkCreateAllBedrooms');

        if (houseWrap) {
            houseWrap.classList.toggle('d-none', ptype !== 'house');
        }

        if (ptype === 'house') {
            floored.classList.add('d-none');
            const bedroomMode = cab && cab.checked;
            flat.classList.toggle('d-none', bedroomMode);
            if (flatLabel) {
                flatLabel.textContent = 'Units per floor (layout)';
            }
            return;
        }

        if (flatLabel) {
            flatLabel.textContent = 'Number of units';
        }

        if (opt && propertySupportsFlooredBulk(ptypeRaw)) {
            floored.classList.remove('d-none');
            flat.classList.add('d-none');
            if (storiesDisp) {
                storiesDisp.textContent = String(stories);
            }
            updateFlooredTotalPreview();
        } else {
            floored.classList.add('d-none');
            flat.classList.remove('d-none');
        }
    }

    document.getElementById('addUnitPropertyId')?.addEventListener('change', updateBulkFloorUi);
    document.getElementById('addUnitUnitsPerFloor')?.addEventListener('input', updateFlooredTotalPreview);
    document.getElementById('addUnitBulkCreateAllBedrooms')?.addEventListener('change', updateBulkFloorUi);
    document.getElementById('addUnitBulkDefaultUnitType')?.addEventListener('change', function () {
        const map = { studio: 0, one_bedroom: 1, two_bedroom: 2, three_bedroom: 3, penthouse: 3 };
        const b = map[this.value];
        const el = document.getElementById('addUnitBulkDefaultBedrooms');
        if (el !== null && b !== undefined) {
            el.value = String(b);
        }
    });

    function showError(msg) {
        if (!errBox) return;
        errBox.textContent = msg || '';
        errBox.classList.toggle('d-none', !msg);
    }

    function hideAddUnitPropertyDropdown() {
        const panel = document.getElementById('addUnitPropertyDropdown');
        const inp = document.getElementById('addUnitPropertySearch');
        if (panel) {
            panel.classList.add('d-none');
            panel.innerHTML = '';
        }
        if (inp) {
            inp.setAttribute('aria-expanded', 'false');
        }
    }

    function syncAddUnitPropertySearchFromSelect() {
        const sel = document.getElementById('addUnitPropertyId');
        const inp = document.getElementById('addUnitPropertySearch');
        if (!sel || !inp) {
            return;
        }
        const opt = sel.selectedOptions[0];
        if (sel.value && opt && opt.value) {
            inp.value = opt.textContent.trim();
        }
    }

    function renderAddUnitPropertyDropdown(showPanel) {
        const inp = document.getElementById('addUnitPropertySearch');
        const panel = document.getElementById('addUnitPropertyDropdown');
        const sel = document.getElementById('addUnitPropertyId');
        if (!inp || !panel || !sel || step !== 2) {
            return;
        }

        const q = inp.value.trim().toLowerCase();
        const opts = Array.from(sel.querySelectorAll('option')).filter((o) => o.value);

        panel.innerHTML = '';
        let matches = 0;

        opts.forEach((opt) => {
            const blob = (opt.getAttribute('data-search-label') || '').toLowerCase();
            if (q !== '' && !blob.includes(q)) {
                return;
            }
            matches++;

            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'dropdown-item w-100 text-start py-2 px-3 border-0 bg-transparent';
            row.setAttribute('role', 'option');
            row.textContent = opt.textContent.trim();
            row.disabled = opt.disabled;

            if (!opt.disabled) {
                row.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                });
                row.addEventListener('click', function () {
                    sel.value = opt.value;
                    inp.value = opt.textContent.trim();
                    hideAddUnitPropertyDropdown();
                    if (mode === 'bulk') {
                        updateBulkFloorUi();
                        updateFlooredTotalPreview();
                    }
                });
            } else {
                row.classList.add('text-muted');
                row.title = 'At unit limit for this property type.';
                row.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                });
            }
            panel.appendChild(row);
        });

        if (matches === 0) {
            const empty = document.createElement('div');
            empty.className = 'text-muted small px-3 py-2';
            empty.textContent = q === '' ? 'No properties available.' : 'No properties match your search.';
            empty.setAttribute('role', 'status');
            panel.appendChild(empty);
        }

        const open = showPanel !== false;
        if (open) {
            panel.classList.remove('d-none');
            inp.setAttribute('aria-expanded', 'true');
        } else {
            panel.classList.add('d-none');
            inp.setAttribute('aria-expanded', 'false');
        }
    }

    function clearAddUnitPropertySearchFilter() {
        const inp = document.getElementById('addUnitPropertySearch');
        if (inp) {
            inp.value = '';
        }
        const sel = document.getElementById('addUnitPropertyId');
        if (sel) {
            sel.value = '';
        }
        hideAddUnitPropertyDropdown();
        document.querySelectorAll('#addUnitPropertyId option').forEach((opt) => {
            opt.hidden = false;
        });
    }

    function syncStepUi() {
        stepEls().forEach((el) => {
            const n = parseInt(el.getAttribute('data-add-unit-step'), 10);
            el.classList.toggle('is-active', n === step);
            el.classList.toggle('is-done', n < step);
        });
        panels().forEach((el) => {
            const n = parseInt(el.getAttribute('data-add-unit-panel'), 10);
            el.classList.toggle('d-none', n !== step);
        });
        btnBack.disabled = step === 1;
        btnNext.classList.toggle('d-none', step === 3);
        btnSubmit.classList.toggle('d-none', step !== 3);
        if (step === 1) {
            btnNext.disabled = !mode;
        } else {
            btnNext.disabled = false;
        }
        if (step === 2 && mode === 'bulk') {
            updateBulkFloorUi();
        }

        const step3Ind = document.getElementById('addUnitStep3IndicatorLabel');
        const step3Title = document.getElementById('addUnitStep3Title');
        const step3Foot = document.getElementById('addUnitStep3Footnote');
        const submitLbl = document.getElementById('addUnitBtnSubmitLabel');
        const submitSpin = document.getElementById('addUnitBtnSubmitSpinner');
        if (step === 3 && mode === 'bulk') {
            if (step3Ind) {
                step3Ind.textContent = 'Bulk editor';
            }
            if (step3Title) {
                step3Title.textContent = 'Open full-page bulk editor';
            }
            if (step3Foot) {
                step3Foot.textContent = 'You will leave this modal and go to Edit Bulk Units to customize each unit and floor, then finalize.';
            }
            if (submitLbl) {
                submitLbl.innerHTML = '<i class="fas fa-arrow-right me-1"></i> Open bulk editor';
            }
            if (submitSpin) {
                submitSpin.classList.add('d-none');
            }
        } else if (step === 3) {
            if (step3Ind) {
                step3Ind.textContent = 'Review & Submit';
            }
            if (step3Title) {
                step3Title.textContent = 'Review your entries';
            }
            if (step3Foot) {
                step3Foot.textContent = 'Submitting will create the unit on the selected property.';
            }
            if (submitLbl) {
                submitLbl.innerHTML = '<i class="fas fa-check me-1"></i> Confirm & create';
            }
            if (submitSpin) {
                submitSpin.classList.add('d-none');
            }
        }
    }

    function resetWizard() {
        step = 1;
        mode = null;
        showError('');
        modalEl.querySelectorAll('.add-unit-mode-card').forEach((c) => c.classList.remove('is-selected'));
        document.getElementById('addUnitPropertySearch').value = '';
        document.getElementById('addUnitPropertyId').value = '';
        hideAddUnitPropertyDropdown();
        document.querySelectorAll('#addUnitPropertyId option').forEach((opt) => { opt.hidden = false; });
        document.getElementById('addUnitNameSingle').value = '';
        document.getElementById('addUnitPriceSingle').value = '';
        document.getElementById('addUnitStatusSingle').value = 'available';
        document.getElementById('addUnitCountBulk').value = '2';
        const upf = document.getElementById('addUnitUnitsPerFloor');
        if (upf) {
            upf.value = '4';
        }
        document.getElementById('addUnitPriceBulk').value = '';
        const ut = document.getElementById('addUnitBulkDefaultUnitType');
        if (ut) {
            ut.value = 'studio';
        }
        const db = document.getElementById('addUnitBulkDefaultBedrooms');
        if (db) {
            db.value = '0';
        }
        const dba = document.getElementById('addUnitBulkDefaultBathrooms');
        if (dba) {
            dba.value = '1';
        }
        const cab = document.getElementById('addUnitBulkCreateAllBedrooms');
        if (cab) {
            cab.checked = false;
        }
        document.getElementById('addUnitFieldsSingle').classList.remove('d-none');
        document.getElementById('addUnitFieldsBulk').classList.add('d-none');
        document.getElementById('addUnitReviewBody').innerHTML = '';
        const submitLblEl = document.getElementById('addUnitBtnSubmitLabel');
        if (submitLblEl) {
            submitLblEl.classList.remove('d-none');
        }
        document.getElementById('addUnitBtnSubmitSpinner')?.classList.add('d-none');
        btnSubmit.disabled = false;
        syncStepUi();
    }

    modalEl.addEventListener('hidden.bs.modal', resetWizard);

    modalEl.querySelectorAll('[data-add-unit-mode]').forEach((card) => {
        card.addEventListener('click', () => {
            mode = card.getAttribute('data-add-unit-mode');
            modalEl.querySelectorAll('.add-unit-mode-card').forEach((c) => c.classList.remove('is-selected'));
            card.classList.add('is-selected');
            const single = document.getElementById('addUnitFieldsSingle');
            const bulk = document.getElementById('addUnitFieldsBulk');
            if (mode === 'single') {
                single.classList.remove('d-none');
                bulk.classList.add('d-none');
            } else {
                single.classList.add('d-none');
                bulk.classList.remove('d-none');
                updateBulkFloorUi();
            }
            syncStepUi();
        });
    });

    const addUnitPropertySearchEl = document.getElementById('addUnitPropertySearch');
    if (addUnitPropertySearchEl) {
        addUnitPropertySearchEl.addEventListener('focus', function () {
            renderAddUnitPropertyDropdown(true);
        });
        addUnitPropertySearchEl.addEventListener('input', function () {
            const sel = document.getElementById('addUnitPropertyId');
            if (sel) {
                if (this.value.trim() === '') {
                    sel.value = '';
                } else if (sel.value) {
                    const chosen = sel.options[sel.selectedIndex];
                    const chosenLabel = chosen && chosen.value ? chosen.textContent.trim() : '';
                    if (chosenLabel !== this.value.trim()) {
                        sel.value = '';
                    }
                }
            }
            renderAddUnitPropertyDropdown(true);
        });
        addUnitPropertySearchEl.addEventListener('blur', function () {
            window.setTimeout(function () {
                hideAddUnitPropertyDropdown();
                syncAddUnitPropertySearchFromSelect();
            }, 200);
        });
        addUnitPropertySearchEl.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideAddUnitPropertyDropdown();
            }
        });
    }

    btnNext.addEventListener('click', () => {
        showError('');
        const prevStep = step;
        if (step === 1 && !mode) {
            showError('Please choose single or bulk creation.');
            return;
        }
        if (step === 2) {
            const pid = document.getElementById('addUnitPropertyId').value;
            if (!pid) {
                showError('Please select a property.');
                return;
            }
            if (!selectedPropertyMayAddUnits()) {
                showError('This property already has the maximum number of units for its type (single-family house and townhouse: one unit; duplex: two). Edit those units or add another property if you need more rentals.');
                return;
            }
            if (mode === 'single') {
                const name = document.getElementById('addUnitNameSingle').value.trim();
                const price = document.getElementById('addUnitPriceSingle').value;
                if (!name) {
                    showError('Please enter a unit name.');
                    return;
                }
                if (price === '' || Number(price) < 0) {
                    showError('Please enter a valid price.');
                    return;
                }
            } else {
                const price = document.getElementById('addUnitPriceBulk').value;
                const opt = selectedPropertyOption();
                const ptype = opt ? String(opt.getAttribute('data-property-type') || '').toLowerCase() : '';
                const useFloors = opt && propertySupportsFlooredBulk(opt.getAttribute('data-property-type') || '');
                const cab = document.getElementById('addUnitBulkCreateAllBedrooms');

                if (ptype === 'house') {
                    if (!cab || !cab.checked) {
                        const upf = parseInt(document.getElementById('addUnitCountBulk').value, 10);
                        if (!upf || upf < 1) {
                            showError('Enter units per floor, or turn on “each bedroom as its own unit”.');
                            return;
                        }
                    }
                } else if (useFloors) {
                    const upf = parseInt(document.getElementById('addUnitUnitsPerFloor').value, 10);
                    const stories = parseInt(opt.getAttribute('data-building-stories') || '1', 10);
                    if (!upf || upf < 1) {
                        showError('Please enter units per floor.');
                        return;
                    }
                    if (stories * upf > 200) {
                        showError('Total units (units per floor × building stories) cannot exceed 200.');
                        return;
                    }
                } else {
                    const cnt = parseInt(document.getElementById('addUnitCountBulk').value, 10);
                    if (!cnt || cnt < 1) {
                        showError('Please enter how many units to create.');
                        return;
                    }
                }

                const ut = document.getElementById('addUnitBulkDefaultUnitType');
                if (!ut || !ut.value) {
                    showError('Please select a default unit type.');
                    return;
                }
                const beds = parseInt(document.getElementById('addUnitBulkDefaultBedrooms').value, 10);
                const baths = parseInt(document.getElementById('addUnitBulkDefaultBathrooms').value, 10);
                if (Number.isNaN(beds) || beds < 0) {
                    showError('Please enter default bedrooms.');
                    return;
                }
                if (Number.isNaN(baths) || baths < 1) {
                    showError('Please enter default bathrooms (at least 1).');
                    return;
                }
                if (price === '' || Number(price) < 0) {
                    showError('Please enter a valid default rent.');
                    return;
                }
            }
            renderReview();
        }
        step = Math.min(3, step + 1);
        if (step === 2 && prevStep === 1) {
            clearAddUnitPropertySearchFilter();
        }
        syncStepUi();
    });

    btnBack.addEventListener('click', () => {
        showError('');
        const prevStep = step;
        step = Math.max(1, step - 1);
        if ((prevStep === 2 && step === 1) || (prevStep === 3 && step === 2)) {
            clearAddUnitPropertySearchFilter();
        }
        syncStepUi();
    });

    function propertyLabel(id) {
        const opt = document.querySelector('#addUnitPropertyId option[value="' + id + '"]');
        return opt ? opt.textContent.trim() : '';
    }

    function renderReview() {
        const pid = document.getElementById('addUnitPropertyId').value;
        const pName = propertyLabel(pid);
        let html = '<dl class="row mb-0">';
        html += '<dt class="col-sm-4">Property</dt><dd class="col-sm-8">' + escapeHtml(pName) + '</dd>';
        if (mode === 'single') {
            html += '<dt class="col-sm-4">Mode</dt><dd class="col-sm-8">Single unit</dd>';
            html += '<dt class="col-sm-4">Unit name</dt><dd class="col-sm-8">' + escapeHtml(document.getElementById('addUnitNameSingle').value.trim()) + '</dd>';
            html += '<dt class="col-sm-4">Price</dt><dd class="col-sm-8">₱' + Number(document.getElementById('addUnitPriceSingle').value).toLocaleString() + '</dd>';
            html += '<dt class="col-sm-4">Status</dt><dd class="col-sm-8">' + escapeHtml(document.getElementById('addUnitStatusSingle').value) + '</dd>';
        } else {
            const opt = selectedPropertyOption();
            const ptypeRaw = opt ? (opt.getAttribute('data-property-type') || '') : '';
            const ptype = String(ptypeRaw).toLowerCase();
            const useFloors = opt && propertySupportsFlooredBulk(ptypeRaw);
            const cab = document.getElementById('addUnitBulkCreateAllBedrooms');
            html += '<dt class="col-sm-4">Mode</dt><dd class="col-sm-8">Bulk → full-page editor</dd>';
            if (ptype === 'house' && cab && cab.checked) {
                html += '<dt class="col-sm-4">House layout</dt><dd class="col-sm-8">One unit per bedroom</dd>';
            } else if (useFloors) {
                const stories = parseInt(opt.getAttribute('data-building-stories') || '1', 10);
                const upf = parseInt(document.getElementById('addUnitUnitsPerFloor').value, 10);
                html += '<dt class="col-sm-4">Building stories</dt><dd class="col-sm-8">' + escapeHtml(String(stories)) + ' <span class="text-muted">(from property)</span></dd>';
                html += '<dt class="col-sm-4">Units per floor</dt><dd class="col-sm-8">' + escapeHtml(String(upf)) + '</dd>';
                html += '<dt class="col-sm-4">Total placeholders</dt><dd class="col-sm-8">' + escapeHtml(String(stories * upf)) + '</dd>';
            } else {
                html += '<dt class="col-sm-4">Units per floor / count</dt><dd class="col-sm-8">' + escapeHtml(document.getElementById('addUnitCountBulk').value) + '</dd>';
            }
            html += '<dt class="col-sm-4">Default unit type</dt><dd class="col-sm-8">' + escapeHtml(document.getElementById('addUnitBulkDefaultUnitType').value) + '</dd>';
            html += '<dt class="col-sm-4">Default rent</dt><dd class="col-sm-8">₱' + Number(document.getElementById('addUnitPriceBulk').value).toLocaleString() + '</dd>';
            html += '<dt class="col-sm-4">Default beds / baths</dt><dd class="col-sm-8">' + escapeHtml(document.getElementById('addUnitBulkDefaultBedrooms').value) + ' / ' + escapeHtml(document.getElementById('addUnitBulkDefaultBathrooms').value) + '</dd>';
        }
        html += '</dl>';
        document.getElementById('addUnitReviewBody').innerHTML = html;
    }

    function escapeHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function statsScopePropertyId() {
        const sel = document.getElementById('apartmentFilter');
        const v = sel ? sel.value : '';
        return v ? parseInt(v, 10) : null;
    }

    function formatUnitType(t) {
        if (!t) return 'N/A';
        return String(t).replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
    }

    function statusBadge(status) {
        const map = {
            available: ['bg-success', 'Available'],
            occupied: ['bg-danger', 'Occupied'],
            maintenance: ['bg-warning', 'Maintenance'],
        };
        const x = map[status] || ['bg-secondary', status];
        return '<span class="badge ' + x[0] + '">' + x[1] + '</span>';
    }

    function rowHtml(u) {
        return '<tr>'
            + '<td><div class="d-flex align-items-center"><div class="unit-number-badge">' + escapeHtml(u.unit_number) + '</div></div></td>'
            + '<td><div class="d-flex align-items-center"><i class="fas fa-building text-muted me-2"></i><span class="property-name">' + escapeHtml(u.property_name) + '</span></div></td>'
            + '<td><span class="unit-type">' + escapeHtml(formatUnitType(u.unit_type)) + '</span></td>'
            + '<td class="text-center"><div class="d-flex justify-content-center align-items-center gap-3">'
            + '<span class="bed-bath-info" title="Bedrooms"><i class="fas fa-bed text-muted me-1"></i>' + (u.bedrooms ?? 0) + '</span>'
            + '<span class="bed-bath-info" title="Bathrooms"><i class="fas fa-bath text-muted me-1"></i>' + (u.bathrooms ?? 1) + '</span>'
            + '</div></td>'
            + '<td class="text-center"><span class="floor-number">' + (u.floor_number ?? 'N/A') + '</span></td>'
            + '<td class="text-center">' + statusBadge(u.status) + '</td>'
            + '<td class="text-end"><span class="rent-amount">₱' + Number(u.rent_amount ?? 0).toLocaleString(undefined, { maximumFractionDigits: 0 }) + '</span></td>'
            + '<td class="text-center"><span class="max-occupants">' + (u.max_occupants ?? '-') + '</span></td>'
            + '<td class="text-center"><div class="btn-group" role="group">'
            + '<button onclick="editUnit(' + u.id + ')" class="btn btn-sm btn-outline-primary" title="Edit Unit"><i class="fas fa-edit"></i></button>'
            + '<button onclick="viewUnitDetails(' + u.id + ')" class="btn btn-sm btn-outline-info" title="View Details"><i class="fas fa-eye"></i></button>'
            + '</div></td>'
            + '</tr>';
    }

    function applyStats(stats) {
        if (!stats) return;
        const tu = document.getElementById('statTotalUnits');
        const au = document.getElementById('statAvailableUnits');
        const ou = document.getElementById('statOccupiedUnits');
        const mr = document.getElementById('statMonthlyRevenue');
        if (tu) tu.textContent = stats.total_units;
        if (au) au.textContent = stats.available_units;
        if (ou) ou.textContent = stats.occupied_units;
        if (mr) mr.textContent = '₱' + Number(stats.monthly_revenue ?? 0).toLocaleString(undefined, { maximumFractionDigits: 0 });
    }

    function appendBulkSessionField(form, name, value) {
        const i = document.createElement('input');
        i.type = 'hidden';
        i.name = name;
        i.value = String(value);
        form.appendChild(i);
    }

    function submitBulkSessionForm() {
        const pid = document.getElementById('addUnitPropertyId').value;
        const action = urls.bulkSessionPost.split(urls.bulkSessionPostPlaceholder).join(pid);
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;

        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = csrfToken;
        form.appendChild(token);

        const opt = selectedPropertyOption();
        const ptype = opt ? String(opt.getAttribute('data-property-type') || '').toLowerCase() : '';

        appendBulkSessionField(form, 'default_unit_type', document.getElementById('addUnitBulkDefaultUnitType').value);
        appendBulkSessionField(form, 'default_rent', document.getElementById('addUnitPriceBulk').value);
        appendBulkSessionField(form, 'default_bedrooms', document.getElementById('addUnitBulkDefaultBedrooms').value);
        appendBulkSessionField(form, 'default_bathrooms', document.getElementById('addUnitBulkDefaultBathrooms').value);

        const cab = document.getElementById('addUnitBulkCreateAllBedrooms');

        if (ptype === 'house' && cab && cab.checked) {
            appendBulkSessionField(form, 'create_all_bedrooms', '1');
        } else if (opt && propertySupportsFlooredBulk(opt.getAttribute('data-property-type') || '')) {
            appendBulkSessionField(form, 'units_per_floor', document.getElementById('addUnitUnitsPerFloor').value);
        } else {
            appendBulkSessionField(form, 'units_per_floor', document.getElementById('addUnitCountBulk').value);
        }

        document.body.appendChild(form);
        form.submit();
    }

    btnSubmit.addEventListener('click', async () => {
        showError('');
        if (mode === 'bulk') {
            submitBulkSessionForm();

            return;
        }

        const pid = parseInt(document.getElementById('addUnitPropertyId').value, 10);
        const scope = statsScopePropertyId();
        const body = {
            stats_scope_property_id: scope || null,
            property_id: pid,
            unit_number: document.getElementById('addUnitNameSingle').value.trim(),
            rent_amount: document.getElementById('addUnitPriceSingle').value,
            status: document.getElementById('addUnitStatusSingle').value,
        };

        btnSubmit.disabled = true;
        document.getElementById('addUnitBtnSubmitLabel')?.classList.add('d-none');
        document.getElementById('addUnitBtnSubmitSpinner')?.classList.remove('d-none');

        try {
            const res = await fetch(urls.single, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(body),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.success) {
                let msg = data.message || 'Could not create unit.';
                if (data.errors) {
                    const first = Object.values(data.errors)[0];
                    if (Array.isArray(first) && first[0]) msg = first[0];
                }
                showError(msg);
                return;
            }

            const tbody = document.getElementById('unitsTableBody');
            if (!tbody) {
                window.location.reload();
                return;
            }
            const rows = (data.units || []).map(rowHtml).join('');
            tbody.insertAdjacentHTML('afterbegin', rows);
            applyStats(data.stats);

            const modal = bootstrap.Modal.getInstance(modalEl);
            modal?.hide();
        } catch (e) {
            console.error(e);
            showError('Something went wrong. Please try again.');
        } finally {
            btnSubmit.disabled = false;
            document.getElementById('addUnitBtnSubmitLabel')?.classList.remove('d-none');
            document.getElementById('addUnitBtnSubmitSpinner')?.classList.add('d-none');
        }
    });

    syncStepUi();
})();
</script>
@endpush
