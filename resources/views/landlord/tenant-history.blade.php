@extends('layouts.landlord-app')

@section('title', 'Tenant History')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('landlord.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tenant History</li>
                    </ol>
                </div>
                <h4 class="page-title">Tenant History</h4>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0" title="Total Assignments">Total Assignments</h5>
                            <h3 class="mt-3 mb-3">{{ $stats['total_assignments'] }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-primary rounded">
                                <i class="mdi mdi-account-multiple font-20 text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0" title="Active Tenants">Active Tenants</h5>
                            <h3 class="mt-3 mb-3">{{ $stats['active_assignments'] }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-success rounded">
                                <i class="mdi mdi-check-circle font-20 text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0" title="Terminated">Terminated</h5>
                            <h3 class="mt-3 mb-3">{{ $stats['terminated_assignments'] }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-danger rounded">
                                <i class="mdi mdi-close-circle font-20 text-danger"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0" title="Total Revenue">Total Revenue</h5>
                            <h3 class="mt-3 mb-3">₱{{ number_format($stats['total_revenue'], 2) }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-info rounded">
                                <i class="mdi mdi-currency-php font-20 text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
                        <h4 class="header-title mb-0">
                            <i class="mdi mdi-filter-variant me-1"></i> Filter History
                        </h4>
                        <div class="btn-group d-flex d-md-inline-flex">
                            <button type="button" class="btn btn-sm btn-success" onclick="exportCSV()">
                                <i class="mdi mdi-file-excel me-1"></i> <span class="d-none d-sm-inline">Export</span> CSV
                            </button>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('landlord.tenant-history') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label">Property</label>
                                <select name="property_id" class="form-select" id="propertyFilter">
                                    <option value="">All Properties</option>
                                    @foreach($apartments as $apartment)
                                        <option value="{{ $apartment->id }}" 
                                            {{ request('property_id') == $apartment->id ? 'selected' : '' }}>
                                            {{ $apartment->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label">Unit</label>
                                <select name="unit_id" class="form-select" id="unitFilter">
                                    <option value="">All Units</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" 
                                            data-apartment="{{ $unit->apartment_id }}"
                                            {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->apartment->name }} - Unit {{ $unit->unit_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label">Tenant Name</label>
                                <input type="text" name="tenant_name" class="form-control" 
                                    placeholder="Search by name or email" 
                                    value="{{ request('tenant_name') }}">
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                </select>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label">Move-in From</label>
                                <input type="date" name="date_from" class="form-control" 
                                    value="{{ request('date_from') }}">
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <label class="form-label">Move-out To</label>
                                <input type="date" name="date_to" class="form-control" 
                                    value="{{ request('date_to') }}">
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label d-none d-lg-block">&nbsp;</label>
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1 flex-sm-grow-0">
                                        <i class="mdi mdi-magnify me-1"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('landlord.tenant-history') }}" class="btn btn-secondary flex-grow-1 flex-sm-grow-0">
                                        <i class="mdi mdi-refresh me-1"></i> Clear Filters
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenant History Timeline/Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">
                        <i class="mdi mdi-timeline me-1"></i> Rental Timeline
                    </h4>

                    @if($assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tenant</th>
                                        <th>Property & Unit</th>
                                        <th>Bedroom Details</th>
                                        <th>Move-in Date</th>
                                        <th>Move-out Date</th>
                                        <th>Duration</th>
                                        <th>Rent Amount</th>
                                        <th>Status</th>
                                        <th>Payment Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assignment)
                                        @php
                                            $leaseStartDate = \Carbon\Carbon::parse($assignment->lease_start_date);
                                            $leaseEndDate = \Carbon\Carbon::parse($assignment->lease_end_date);
                                            $leaseDuration = $leaseStartDate->diffInMonths($leaseEndDate);
                                            
                                            // Determine if lease is active or expired
                                            $now = \Carbon\Carbon::now();
                                            $isExpired = $leaseEndDate->isPast();
                                            $isActive = $assignment->status == 'active' && !$isExpired;
                                            
                                            // Payment status
                                            $paymentStatus = $assignment->documents_verified ? 'verified' : ($assignment->documents_uploaded ? 'pending' : 'not_uploaded');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-2">
                                                        {{ strtoupper(substr($assignment->tenant->name ?? 'T', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-0 font-14">{{ $assignment->tenant->name ?? 'N/A' }}</h5>
                                                        <small class="text-muted">{{ $assignment->tenant->email ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $assignment->unit->apartment->name ?? 'N/A' }}</strong><br>
                                                    <small class="text-muted">Unit {{ $assignment->unit->unit_number ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-info text-info">
                                                    <i class="mdi mdi-bed me-1"></i>{{ $assignment->unit->bedrooms ?? 0 }} Bedrooms
                                                </span>
                                            </td>
                                            <td>
                                                <i class="mdi mdi-calendar-import text-success me-1"></i>
                                                {{ $assignment->lease_start_date ? $assignment->lease_start_date->format('M d, Y') : 'N/A' }}
                                            </td>
                                            <td>
                                                <i class="mdi mdi-calendar-export text-danger me-1"></i>
                                                {{ $assignment->lease_end_date ? $assignment->lease_end_date->format('M d, Y') : 'N/A' }}
                                                @if($isExpired)
                                                    <br><small class="text-danger">(Expired)</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $leaseDuration }}</strong> months
                                            </td>
                                            <td>
                                                <strong class="text-primary">₱{{ number_format($assignment->rent_amount, 2) }}</strong><br>
                                                @if($assignment->security_deposit > 0)
                                                    <small class="text-muted">Deposit: ₱{{ number_format($assignment->security_deposit, 2) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($assignment->status == 'active')
                                                    <span class="badge bg-success">
                                                        <i class="mdi mdi-check-circle me-1"></i>Active
                                                    </span>
                                                @elseif($assignment->status == 'pending')
                                                    <span class="badge bg-warning">
                                                        <i class="mdi mdi-clock-outline me-1"></i>Pending
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="mdi mdi-close-circle me-1"></i>Terminated
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($paymentStatus == 'verified')
                                                    <span class="badge bg-soft-success text-success">
                                                        <i class="mdi mdi-check-circle me-1"></i>Verified
                                                    </span>
                                                @elseif($paymentStatus == 'pending')
                                                    <span class="badge bg-soft-warning text-warning">
                                                        <i class="mdi mdi-clock-outline me-1"></i>Pending
                                                    </span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger">
                                                        <i class="mdi mdi-alert-circle me-1"></i>No Documents
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('landlord.assignment-details', $assignment->id) }}" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="View Details">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @if($assignment->notes)
                                            <tr>
                                                <td colspan="10" class="bg-light">
                                                    <small class="text-muted">
                                                        <i class="mdi mdi-note-text me-1"></i><strong>Notes:</strong> {{ $assignment->notes }}
                                                    </small>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $assignments->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-database-search" style="font-size: 4rem; color: #ddd;"></i>
                            <h4 class="mt-3">No tenant history found</h4>
                            <p class="text-muted">
                                @if(request()->hasAny(['property_id', 'unit_id', 'tenant_name', 'status', 'date_from', 'date_to']))
                                    Try adjusting your filters to see more results.
                                @else
                                    You haven't assigned any tenants yet.
                                @endif
                            </p>
                            @if(request()->hasAny(['property_id', 'unit_id', 'tenant_name', 'status', 'date_from', 'date_to']))
                                <a href="{{ route('landlord.tenant-history') }}" class="btn btn-primary">
                                    <i class="mdi mdi-refresh me-1"></i> Clear Filters
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-sm {
        width: 48px;
        height: 48px;
    }
    
    .avatar-title {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
    
    .bg-soft-primary {
        background-color: rgba(114, 124, 245, 0.18);
    }
    
    .bg-soft-success {
        background-color: rgba(10, 207, 151, 0.18);
    }
    
    .bg-soft-danger {
        background-color: rgba(250, 92, 124, 0.18);
    }
    
    .bg-soft-info {
        background-color: rgba(57, 175, 209, 0.18);
    }
    
    .bg-soft-warning {
        background-color: rgba(255, 193, 7, 0.18);
    }
    
    .table > :not(caption) > * > * {
        padding: 0.75rem 0.75rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(114, 124, 245, 0.05);
    }
    
    .card {
        border: none;
        box-shadow: 0 0 35px 0 rgba(154, 161, 171, 0.15);
        margin-bottom: 24px;
    }
    
    .header-title {
        font-size: 1rem;
        font-weight: 600;
        color: #6c757d;
    }
    
    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #343a40;
    }
    
    .badge {
        padding: 0.35rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Responsive Enhancements */
    @media (max-width: 991px) {
        .page-title {
            font-size: 1.25rem;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .col-xl-3 {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 767px) {
        .table-responsive {
            border: 0;
        }
        
        .table > :not(caption) > * > * {
            padding: 0.5rem 0.5rem;
        }
        
        .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .page-title-box {
            padding: 1rem;
        }
        
        .header-title {
            font-size: 0.875rem;
        }

        /* Stack stat cards vertically */
        .row > div[class*="col-"] {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 576px) {
        .table thead {
            display: none;
        }
        
        .table,
        .table tbody,
        .table tr,
        .table td {
            display: block;
            width: 100%;
        }
        
        .table tr {
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.75rem;
        }
        
        .table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border: none;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        
        .table td::before {
            content: attr(data-label);
            position: absolute;
            left: 0.75rem;
            width: 45%;
            text-align: left;
            font-weight: 600;
            color: #6c757d;
        }
        
        .badge {
            font-size: 0.625rem;
            padding: 0.25rem 0.5rem;
        }
    }
</style>

<script>
    // Filter units based on selected property
    document.getElementById('propertyFilter').addEventListener('change', function() {
        const selectedProperty = this.value;
        const unitFilter = document.getElementById('unitFilter');
        const unitOptions = unitFilter.querySelectorAll('option[data-apartment]');
        
        unitOptions.forEach(option => {
            if (!selectedProperty || option.dataset.apartment === selectedProperty) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset unit selection if current selection is hidden
        if (unitFilter.value && unitFilter.querySelector(`option[value="${unitFilter.value}"]`).style.display === 'none') {
            unitFilter.value = '';
        }
    });
    
    // Trigger on page load if property is already selected
    window.addEventListener('DOMContentLoaded', function() {
        document.getElementById('propertyFilter').dispatchEvent(new Event('change'));
    });
    
    // Export CSV function
    function exportCSV() {
        const form = document.getElementById('filterForm');
        const url = new URL('{{ route("landlord.tenant-history.export-csv") }}', window.location.origin);
        
        // Add all current filter values to the export URL
        const formData = new FormData(form);
        formData.forEach((value, key) => {
            if (value) {
                url.searchParams.append(key, value);
            }
        });
        
        // Redirect to download the CSV
        window.location.href = url.toString();
    }
</script>
@endsection

