@extends('layouts.landlord-app')

@section('title', 'My Properties')

@push('styles')
<style>
.properties-list {
    background: white;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
}

.list-header {
    display: flex;
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    font-size: 0.875rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.list-row {
    display: flex;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    align-items: center;
    transition: background 0.15s ease;
}

.list-row:hover {
    background: #f8fafc;
}

.list-row:last-child {
    border-bottom: none;
}

.list-column {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 0 0.5rem;
    font-size: 0.875rem;
}

.text-center {
    justify-content: center;
}

.text-right {
    justify-content: flex-end;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 0.375rem;
    border: 1px solid #e2e8f0;
    background: white;
    color: #64748b;
    transition: all 0.15s ease;
    cursor: pointer;
}

.btn-icon:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #cbd5e1;
}

/* Pagination Styles */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
    flex-wrap: wrap;
}

.pagination nav {
    display: flex;
    gap: 0.25rem;
    align-items: center;
    flex-wrap: wrap;
}

.pagination a,
.pagination span {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    text-decoration: none;
    color: #475569;
    font-size: 0.875rem;
    background: white;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
}

.pagination a:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
}

.pagination .active span {
    background: #f97316;
    border-color: #f97316;
    color: white;
}

/* Fix Tailwind SVG arrow sizing */
.pagination svg {
    width: 1rem !important;
    height: 1rem !important;
    display: inline-block;
}

/* Disabled pagination elements */
.pagination .disabled span,
.pagination [aria-disabled="true"] {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f8fafc;
}

/* Pagination ellipsis */
.pagination .dots {
    padding: 0.5rem;
    color: #94a3b8;
}

/* ===== Dark Mode Overrides ===== */
body.dark-mode .pagination a,
body.dark-mode .pagination span {
    background: #1e293b;
    border-color: #334155;
    color: #94a3b8;
}

body.dark-mode .pagination a:hover {
    background: #334155;
    color: #f1f5f9;
    border-color: #475569;
}

body.dark-mode .pagination .active span {
    background: #f97316;
    border-color: #f97316;
    color: white;
}

body.dark-mode .pagination .disabled span {
    background: #0f172a;
    color: #475569;
}

body.dark-mode .badge[style*="background: #e0e7ff"] {
    background: rgba(99, 102, 241, 0.2) !important;
    color: #a5b4fc !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <div>
            <h1>My Properties</h1>
            <p style="color: #64748b; margin-top: 0.5rem;">Manage your property portfolio</p>
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

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $apartments->count() }}</div>
            <div class="stat-label">Total Properties</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $totalUnits ?? 0 }}</div>
            <div class="stat-label">Total Units</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $occupiedUnits ?? 0 }}</div>
            <div class="stat-label">Occupied Units</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">₱{{ number_format($monthlyRevenue ?? 0, 0) }}</div>
            <div class="stat-label">Monthly Revenue</div>
        </div>
    </div>

    <!-- Properties Section -->
    <div class="page-section">
        <div class="section-header">
            <div>
                <h2 class="section-title">Property Portfolio</h2>
                <p class="section-subtitle">View and manage all your properties</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div class="sort-dropdown">
                    <label style="margin-right: 0.5rem; font-size: 0.875rem; color: #64748b;">Sort by:</label>
                    <select id="propertySort" onchange="window.location.href='?sort=' + this.value" style="padding: 0.5rem; border-radius: 0.375rem; border: 1px solid #e2e8f0;">
                        <option value="name" {{ request('sort', 'name') == 'name' ? 'selected' : '' }}>Property Name (A-Z)</option>
                        <option value="units" {{ request('sort') == 'units' ? 'selected' : '' }}>Total Units (Most)</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                    </select>
                </div>
                <a href="{{ route('landlord.create-apartment') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Property
                </a>
            </div>
        </div>

        @if($apartments->count() > 0)
            <div class="properties-list">
                <div class="list-header">
                    <div class="list-column" style="flex: 2;">Property Name</div>
                    <div class="list-column">Location</div>
                    <div class="list-column text-center">Total Units</div>
                    <div class="list-column text-center">Occupied</div>
                    <div class="list-column text-center">Occupancy</div>
                    <div class="list-column text-right">Revenue/Month</div>
                    <div class="list-column text-center">Actions</div>
                </div>
                
                @foreach($apartments as $apartment)
                    @php
                        $totalUnits = $apartment->units->count();
                        $occupiedUnits = $apartment->units->where('status', 'occupied')->count();
                        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0;
                        $revenue = $apartment->units->where('status', 'occupied')->sum('rent_amount');
                    @endphp
                    <div class="list-row">
                        <div class="list-column" style="flex: 2;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1.125rem;">
                                    {{ strtoupper(substr($apartment->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #1e293b;">{{ $apartment->name }}</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">ID: #{{ $apartment->id }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-column">
                            <i class="fas fa-map-marker-alt" style="color: #94a3b8; margin-right: 0.5rem;"></i>
                            <span title="{{ $apartment->address }}">{{ Str::limit($apartment->address ?? 'No address', 35) }}</span>
                        </div>
                        <div class="list-column text-center">
                            <span class="badge" style="background: #e0e7ff; color: #4338ca; padding: 0.25rem 0.75rem; border-radius: 1rem; font-weight: 600;">
                                {{ $totalUnits }}
                            </span>
                        </div>
                        <div class="list-column text-center">
                            <span style="color: #0f172a; font-weight: 500;">{{ $occupiedUnits }}</span>
                        </div>
                        <div class="list-column text-center">
                            <div class="occupancy-bar" style="width: 100%; max-width: 80px; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; position: relative;">
                                <div style="position: absolute; left: 0; top: 0; height: 100%; background: {{ $occupancyRate >= 80 ? '#22c55e' : ($occupancyRate >= 50 ? '#f59e0b' : '#ef4444') }}; width: {{ $occupancyRate }}%;"></div>
                            </div>
                            <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; display: block;">{{ $occupancyRate }}%</span>
                        </div>
                        <div class="list-column text-right">
                            <span style="color: #0f172a; font-weight: 600;">₱{{ number_format($revenue, 0) }}</span>
                        </div>
                        <div class="list-column text-center">
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <a href="{{ route('landlord.edit-apartment', $apartment->id) }}" class="btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="viewApartmentDetails({{ $apartment->id }})" class="btn-icon" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('landlord.units', $apartment->id) }}" class="btn-icon" title="View Units">
                                    <i class="fas fa-door-open"></i>
                                </a>
                                @if($totalUnits > 0)
                                <button onclick="showForceDeleteModal({{ $apartment->id }}, '{{ addslashes($apartment->name) }}', {{ $totalUnits }})" class="btn-icon" title="Force Delete" style="color: #dc3545; border-color: #dc3545;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </button>
                                @else
                                <button onclick="deleteApartment({{ $apartment->id }}, '{{ addslashes($apartment->name) }}')" class="btn-icon" title="Delete Property" style="color: #dc3545; border-color: #dc3545;">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($apartments->hasPages())
                <div class="pagination" style="margin-top: 1.5rem;">
                    {{ $apartments->appends(['sort' => request('sort')])->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3 class="empty-title">No Properties Yet</h3>
                <p class="empty-text">Start building your property portfolio by adding your first property.</p>
                <a href="{{ route('landlord.create-apartment') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Your First Property
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Apartment Details Modal -->
<div class="modal fade" id="apartmentDetailsModal" tabindex="-1" aria-labelledby="apartmentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="apartmentDetailsModalLabel">Property Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="apartmentDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading property details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editApartmentBtn" style="display: none;">
                    <i class="fas fa-edit"></i> Edit Property
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Force Delete Modal -->
<div id="force-delete-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="force-delete-inner" style="padding: 30px; border-radius: 10px; max-width: 500px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <h3 style="color: #dc3545; margin-bottom: 20px;">
            <i class="fas fa-exclamation-triangle"></i> Force Delete Property
        </h3>
        <div style="margin-bottom: 20px;">
            <p class="force-delete-text" style="margin-bottom: 15px;">
                <strong>Warning:</strong> This will permanently delete the property "<span id="force-delete-property-name"></span>" and all <span id="force-delete-unit-count"></span> unit(s) associated with it.
            </p>
            <p style="color: #dc3545; font-weight: bold; margin-bottom: 15px;">
                This action cannot be undone!
            </p>
            <p class="force-delete-text" style="margin-bottom: 15px;">
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

<!-- Force Delete Form -->
<form id="force-delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="force_delete" value="1">
    <input type="hidden" name="password" id="force-delete-password">
</form>

@endsection

@push('scripts')
<script>
function viewApartmentDetails(apartmentId) {
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('apartmentDetailsModal'));
    const modalTitle = document.getElementById('apartmentDetailsModalLabel');
    const modalContent = document.getElementById('apartmentDetailsContent');
    const editBtn = document.getElementById('editApartmentBtn');
    
    // Reset modal content
    modalContent.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading property details...</p>
        </div>
    `;
    editBtn.style.display = 'none';
    
    modal.show();
    
    // Fetch apartment details
    fetch(`/landlord/apartments/${apartmentId}/details`)
        .then(response => response.json())
        .then(data => {
            modalTitle.textContent = `${data.name} - Details`;
            
            // Calculate additional stats
            const availableUnits = data.available_units || 0;
            const maintenanceUnits = data.maintenance_units || 0;
            const occupancyRate = data.occupancy_rate || 0;
            
            // Create the details HTML
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Property Information</h6>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Property Name</label>
                            <p class="mb-1">${data.name}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Property ID</label>
                            <p class="mb-1">#${data.id}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Total Units</label>
                            <p class="mb-1">${data.total_units}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Monthly Revenue</label>
                            <p class="mb-1 text-success fw-bold">₱${Number(data.total_revenue).toLocaleString()}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Occupancy Statistics</h6>
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h4 class="text-success mb-0">${data.occupied_units}</h4>
                                    <small class="text-muted">Occupied</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h4 class="text-warning mb-0">${availableUnits}</h4>
                                    <small class="text-muted">Available</small>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h4 class="text-danger mb-0">${maintenanceUnits}</h4>
                                    <small class="text-muted">Maintenance</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h4 class="text-primary mb-0">${occupancyRate}%</h4>
                                    <small class="text-muted">Occupancy</small>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Occupancy Rate</label>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar ${occupancyRate >= 80 ? 'bg-success' : occupancyRate >= 50 ? 'bg-warning' : 'bg-danger'}" 
                                     style="width: ${occupancyRate}%">${occupancyRate}%</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">Quick Actions</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="/landlord/apartments/${data.id}/units/create" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Unit
                            </a>
                            <a href="/landlord/units/${data.id}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-door-open"></i> View Units
                            </a>
                            <a href="/landlord/tenant-assignments?apartment_id=${data.id}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-users"></i> View Tenants
                            </a>
                        </div>
                    </div>
                </div>
            `;
            
            // Show edit button and set up click handler
            editBtn.style.display = 'inline-block';
            editBtn.onclick = function() {
                window.location.href = `/landlord/apartments/${data.id}/edit`;
            };
        })
        .catch(error => {
            console.error('Error fetching apartment details:', error);
            modalContent.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-danger">Error Loading Details</h5>
                    <p class="text-muted">Failed to load property details. Please try again.</p>
                    <button class="btn btn-primary" onclick="viewApartmentDetails(${apartmentId})">Retry</button>
                </div>
            `;
        });
}

// Delete Apartment Function (for properties with no units)
function deleteApartment(apartmentId, propertyName) {
    if (confirm(`Are you sure you want to delete the property "${propertyName}"?\n\nThis action cannot be undone.`)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/landlord/apartments/${apartmentId}`;
        
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

// Force Delete Modal Functions
let currentApartmentId = null;

function showForceDeleteModal(apartmentId, propertyName, unitCount) {
    currentApartmentId = apartmentId;
    document.getElementById('force-delete-property-name').textContent = propertyName;
    document.getElementById('force-delete-unit-count').textContent = unitCount;
    document.getElementById('modal-password').value = '';
    document.getElementById('password-error').style.display = 'none';
    document.getElementById('force-delete-modal').style.display = 'flex';
}

function closeForceDeleteModal() {
    document.getElementById('force-delete-modal').style.display = 'none';
    currentApartmentId = null;
}

function confirmForceDelete() {
    const password = document.getElementById('modal-password').value;
    const passwordError = document.getElementById('password-error');
    
    if (!password) {
        passwordError.style.display = 'block';
        return;
    }
    
    passwordError.style.display = 'none';
    
    // Set the form action and password
    const form = document.getElementById('force-delete-form');
    form.action = `/landlord/apartments/${currentApartmentId}`;
    document.getElementById('force-delete-password').value = password;
    
    // Submit the form
    form.submit();
}
</script>
@endpush 