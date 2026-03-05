@extends('layouts.landlord-app')

@section('title', 'Tenant Assignments')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('landlord.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tenant Assignments</li>
                    </ol>
                </div>
                <h4 class="page-title">Tenant Assignments</h4>
            </div>
        </div>
    </div>

    <!-- Success Alert (without credentials) -->
    @if(session('credentials'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-check-circle me-2" style="font-size: 1.5rem; color: #28a745;"></i>
                <h5 class="alert-heading mb-0">✅ Tenant Assigned Successfully!</h5>
            </div>
            <p class="mt-2 mb-0">New tenant credentials have been generated and are ready for sharing.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                            <h5 class="text-muted fw-normal mt-0" title="Active Assignments">Active Assignments</h5>
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

    <!-- Filters -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('landlord.tenant-assignments') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <label class="form-label d-none d-lg-block">&nbsp;</label>
                            <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch">
                                <button type="submit" class="btn btn-primary flex-grow-1 flex-sm-grow-0">Filter</button>
                                <a href="{{ route('landlord.tenant-assignments') }}" class="btn btn-secondary flex-grow-1 flex-sm-grow-0">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Unit</th>
                                    <th>Apartment</th>
                                    <th>Lease Period</th>
                                    <th>Rent</th>
                                    <th>Status</th>
                                    <th>Documents</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignments as $assignment)
                                <tr class="{{ $assignment->status === 'pending_approval' ? 'table-warning' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <span class="avatar-title bg-soft-primary rounded-circle">
                                                    {{ substr($assignment->tenant->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h5 class="font-14 mb-0">{{ $assignment->tenant->name }}</h5>
                                                <small class="text-muted">{{ $assignment->tenant->email }}</small>
                                                @if($assignment->status === 'pending_approval')
                                                    <br><small class="text-info"><i class="mdi mdi-briefcase"></i> {{ $assignment->occupation }}</small>
                                                    <br><small class="text-success"><i class="mdi mdi-currency-php"></i> {{ number_format($assignment->monthly_income, 2) }}/mo</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $assignment->unit->unit_number }}</span>
                                    </td>
                                    <td>{{ $assignment->unit->apartment->name }}</td>
                                    <td>
                                        @if($assignment->status === 'pending_approval')
                                            <span class="badge bg-warning">Awaiting Approval</span>
                                        @elseif($assignment->lease_start_date && $assignment->lease_end_date)
                                            <div>
                                                <small class="text-muted">Start: {{ $assignment->lease_start_date->format('M d, Y') }}</small><br>
                                                <small class="text-muted">End: {{ $assignment->lease_end_date->format('M d, Y') }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>₱{{ number_format($assignment->rent_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $assignment->status === 'pending_approval' ? 'warning' : $assignment->status_badge_class }}">
                                            {{ $assignment->status === 'pending_approval' ? 'Pending Approval' : ($assignment->status === 'terminated' ? 'Terminated' : ucfirst($assignment->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($assignment->tenant && $assignment->tenant->documents && $assignment->tenant->documents->count() > 0)
                                            <span class="badge bg-info">
                                                <i class="mdi mdi-file-document"></i> {{ $assignment->tenant->documents->count() }} Doc{{ $assignment->tenant->documents->count() > 1 ? 's' : '' }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                No Documents
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="View Details">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if($assignment->status === 'pending_approval')
                                                    <li><a class="dropdown-item" href="#" onclick="viewApplicationDetails({{ $assignment->id }})" title="View Application">
                                                        <i class="mdi mdi-eye me-1"></i> View Application
                                                    </a></li>
                                                    <li><a class="dropdown-item text-success" href="#" onclick="approveApplication({{ $assignment->id }})" title="Approve Application">
                                                        <i class="mdi mdi-check-circle me-1"></i> Approve Application
                                                    </a></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="rejectApplication({{ $assignment->id }})" title="Reject Application">
                                                        <i class="mdi mdi-close-circle me-1"></i> Reject Application
                                                    </a></li>
                                                @else
                                                    <li><a class="dropdown-item" href="{{ route('landlord.assignment-details', $assignment->id) }}">
                                                        <i class="mdi mdi-eye me-1"></i> View Details
                                                    </a></li>
                                                    @if($assignment->status === 'pending')
                                                    <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $assignment->id }}, 'active')" title="Activate Assignment">
                                                        <i class="mdi mdi-check me-1"></i> Activate
                                                    </a></li>
                                                    @endif
                                                    @if($assignment->status === 'active')
                                                    <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $assignment->id }}, 'terminated')" title="Terminate Assignment">
                                                        <i class="mdi mdi-close me-1"></i> Terminate
                                                    </a></li>
                                                    @endif
                                                    @if($assignment->status === 'terminated')
                                                    {{-- Reassignment removed - tenants can only apply through the application system --}}
                                                    @endif
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteTenantAssignment({{ $assignment->id }}, '{{ $assignment->tenant->name }}')" title="Delete Assignment">
                                                    <i class="mdi mdi-delete me-1"></i> Delete Assignment
                                                </a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No tenant inquiries found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $assignments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Form -->
<form id="statusForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="status" id="statusInput">
</form>

<!-- Delete Assignment Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Credentials Modal -->
<div class="modal fade" id="credentialsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tenant Login Credentials</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Login Information</h6>
                    <p class="mb-2">Share these credentials with the tenant:</p>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Email:</strong><br>
                            <code id="tenantEmail"></code>
                        </div>
                        <div class="col-md-6">
                            <strong>Password:</strong><br>
                            <code id="tenantPassword"></code>
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Important Notes:</h6>
                    <ul class="mb-0">
                        <li>The tenant should change their password after first login</li>
                        <li>These credentials are for initial access only</li>
                        <li>Keep these credentials secure and private</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="copyCredentials()">
                    <i class="mdi mdi-content-copy me-1"></i> Copy Credentials
                </button>
            </div>
        </div>
    </div>
</div>

<!-- New Tenant Assignment Credentials Modal -->
<div class="modal fade" id="newCredentialsModal" tabindex="-1" aria-labelledby="newCredentialsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="newCredentialsModalLabel">
                    <i class="mdi mdi-check-circle me-2"></i>Tenant Assigned Successfully!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="alert alert-success mb-3">
                        <i class="mdi mdi-account-plus" style="font-size: 3rem; color: #28a745;"></i>
                        <h4 class="mt-2 mb-0">New Tenant Account Created!</h4>
                        <p class="mb-0">Please share these login credentials with the tenant.</p>
                    </div>
                </div>
                
                <!-- Credentials Display -->
                <div class="credentials-box p-4 mb-4" style="background: #f8f9fa; border: 2px solid #28a745; border-radius: 10px;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-primary">
                                <i class="mdi mdi-email me-1"></i>Email Address:
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="newTenantEmail" readonly 
                                       style="background: white; font-weight: bold; font-size: 1.1rem; color: #0d6efd;">
                                <button class="btn btn-outline-primary" type="button" onclick="copyText('newTenantEmail')" title="Copy email">
                                    <i class="mdi mdi-content-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-primary">
                                <i class="mdi mdi-key me-1"></i>Password:
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="newTenantPassword" readonly 
                                       style="background: white; font-weight: bold; font-size: 1.1rem; color: #dc3545;">
                                <button class="btn btn-outline-primary" type="button" onclick="copyText('newTenantPassword')" title="Copy password">
                                    <i class="mdi mdi-content-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex gap-2 justify-content-center mb-4">
                    <button type="button" class="btn btn-primary px-4" onclick="copyAllNewCredentials()" title="Copy both email and password">
                        <i class="mdi mdi-content-copy me-1"></i> Copy All Credentials
                    </button>
                    <button type="button" class="btn btn-outline-success px-4" onclick="printNewCredentials()" title="Print credentials">
                        <i class="mdi mdi-printer me-1"></i> Print Credentials
                    </button>

                </div>
                
                <!-- Important Notice -->
                <div class="alert alert-warning border-0" style="background: linear-gradient(135deg, #fff3cd 0%, #fdf7e3 100%); border-left: 4px solid #ffc107 !important;">
                    <h6 class="alert-heading fw-bold">
                        <i class="mdi mdi-alert-circle me-2"></i>Important Information:
                    </h6>
                    <ul class="mb-0">
                        <li><strong>Save these credentials securely</strong> - They won't be shown again after closing this window</li>
                        <li><strong>Share with tenant immediately</strong> - They need these to access their dashboard</li>
                        <li><strong>Document upload required</strong> - Assignment status will remain "Active" but tenant must upload documents</li>
                        <li><strong>First-time login</strong> - Tenant can change password after logging in</li>
                    </ul>
                </div>
                
                <!-- Next Steps -->
                <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #d1ecf1 0%, #e3f2fd 100%); border-left: 4px solid #17a2b8 !important;">
                    <h6 class="alert-heading fw-bold">
                        <i class="mdi mdi-list-status me-2"></i>Next Steps:
                    </h6>
                    <ol class="mb-0">
                        <li>Share these credentials with the tenant</li>
                        <li>Tenant logs in and uploads required documents</li>
                        <li>Review and verify documents when submitted</li>
                        <li>Assignment becomes fully active once documents are verified</li>
                    </ol>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="mdi mdi-close me-1"></i>Close
                </button>
                <button type="button" class="btn btn-success" onclick="copyAllNewCredentials(); alert('Credentials copied! You can now close this window.'); document.querySelector('#newCredentialsModal .btn-close').click();">
                    <i class="mdi mdi-check-all me-1"></i>Copy & Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
window.isReassigning = false;

// Simple test function to verify JavaScript is working  
window.testAssignButton = function(assignmentId) {
    alert('Assign button test clicked for assignment ' + assignmentId);
    console.log('Test function called for assignment:', assignmentId);
    return false; // Prevent any default action
};

// Simple function to open assign modal for existing tenant reassignment
function updateStatus(assignmentId, status) {
    if (confirm('Are you sure you want to update this assignment status?')) {
        const form = document.getElementById('statusForm');
        const statusInput = document.getElementById('statusInput');
        
        form.action = `/landlord/tenant-assignments/${assignmentId}/status`;
        statusInput.value = status;
        form.submit();
    }
}

function viewCredentials(assignmentId, email) {
    // Fetch credentials from the server
    fetch(`/landlord/tenant-assignments/${assignmentId}/credentials`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('tenantEmail').textContent = data.email;
            document.getElementById('tenantPassword').textContent = data.password;
            
            const modal = new bootstrap.Modal(document.getElementById('credentialsModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error fetching credentials:', error);
            alert('Error fetching credentials. Please try again.');
        });
}

function copyCredentials() {
    const email = document.getElementById('tenantEmail').textContent;
    const password = document.getElementById('tenantPassword').textContent;
    const credentials = `Email: ${email}\nPassword: ${password}`;
    
    navigator.clipboard.writeText(credentials).then(function() {
        alert('Credentials copied to clipboard!');
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Could not copy to clipboard. Please copy manually.');
    });
}

function copyAssignedCredentials() {
    const email = document.getElementById('assignedTenantEmail').value;
    const password = document.getElementById('assignedTenantPassword').value;
    const credentials = `Email: ${email}\nPassword: ${password}`;
    navigator.clipboard.writeText(credentials).then(function() {
        alert('Credentials copied to clipboard!');
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Could not copy to clipboard. Please copy manually.');
    });
}

function copyText(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices
    
    navigator.clipboard.writeText(element.value).then(function() {
        // Show success feedback
        const btn = element.nextElementSibling;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="mdi mdi-check"></i>';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        // Fallback for older browsers
        document.execCommand('copy');
        alert('Copied to clipboard!');
    });
}

function copyAllCredentials() {
    const email = document.getElementById('assignedTenantEmail').value;
    const password = document.getElementById('assignedTenantPassword').value;
    const credentials = `Tenant Login Credentials:
Email: ${email}
Password: ${password}

Please use these credentials to log in and upload your required documents.`;
    
    navigator.clipboard.writeText(credentials).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="mdi mdi-check me-1"></i> Copied!';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
        }, 3000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Could not copy to clipboard. Please copy manually.');
    });
}

function printCredentials() {
    const email = document.getElementById('assignedTenantEmail').value;
    const password = document.getElementById('assignedTenantPassword').value;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Tenant Login Credentials</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .header { text-align: center; color: #28a745; margin-bottom: 30px; }
                .credentials { background: #f8f9fa; padding: 20px; border: 2px solid #28a745; border-radius: 8px; margin: 20px 0; }
                .credential-item { margin: 15px 0; }
                .label { font-weight: bold; color: #333; }
                .value { font-size: 1.2rem; color: #dc3545; font-weight: bold; margin-left: 10px; }
                .footer { margin-top: 30px; color: #666; font-size: 0.9rem; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>HouseSync - Tenant Login Credentials</h2>
                <p>Generated on: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
            </div>
            
            <div class="credentials">
                <div class="credential-item">
                    <span class="label">Email Address:</span>
                    <span class="value">${email}</span>
                </div>
                <div class="credential-item">
                    <span class="label">Password:</span>
                    <span class="value">${password}</span>
                </div>
            </div>
            
            <div class="footer">
                <h4>Important Instructions:</h4>
                <ul>
                    <li>Use these credentials to log in to your tenant dashboard</li>
                    <li>Upload all required documents after logging in</li>
                    <li>Your assignment will be activated once documents are verified</li>
                    <li>Keep these credentials secure and private</li>
                </ul>
                <p><strong>Login URL:</strong> ${window.location.origin}/login</p>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function deleteTenantAssignment(assignmentId, tenantName) {
    if (confirm(`Are you sure you want to delete the assignment for ${tenantName}? This action cannot be undone.`)) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/landlord/tenant-assignments/${assignmentId}`;
        deleteForm.submit();
    }
}

// New functions for the new credentials modal
function copyAllNewCredentials() {
    const email = document.getElementById('newTenantEmail').value;
    const password = document.getElementById('newTenantPassword').value;
    const credentials = `HouseSync - New Tenant Account Created

Email: ${email}
Password: ${password}

Instructions:
1. Use these credentials to log in to your tenant dashboard
2. Upload required documents after logging in
3. Change your password after first login for security

Login URL: ${window.location.origin}/login

Keep these credentials secure and private.`;
    
    navigator.clipboard.writeText(credentials).then(function() {
        // Show success toast
        showSuccessToast('All credentials copied to clipboard!');
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Could not copy to clipboard. Please copy manually.');
    });
}

function printNewCredentials() {
    const email = document.getElementById('newTenantEmail').value;
    const password = document.getElementById('newTenantPassword').value;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Tenant Login Credentials</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .header { text-align: center; color: #28a745; margin-bottom: 30px; }
                .credentials { background: #f8f9fa; padding: 20px; border: 2px solid #28a745; border-radius: 8px; margin: 20px 0; }
                .field { margin: 10px 0; }
                .label { font-weight: bold; color: #333; }
                .value { font-family: monospace; font-size: 1.1em; color: #0d6efd; }
                .instructions { background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0; }
                .warning { background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>HouseSync</h1>
                <h2>New Tenant Account Credentials</h2>
                <p>Generated on ${new Date().toLocaleString()}</p>
            </div>
            
            <div class="credentials">
                <div class="field">
                    <div class="label">Email Address:</div>
                    <div class="value">${email}</div>
                </div>
                <div class="field">
                    <div class="label">Password:</div>
                    <div class="value">${password}</div>
                </div>
            </div>
            
            <div class="instructions">
                <h3>📋 Instructions for Tenant:</h3>
                <ol>
                    <li>Use these credentials to log in to your tenant dashboard</li>
                    <li>Upload all required documents after logging in</li>
                    <li>Change your password after first login for security</li>
                    <li>Contact your landlord if you have any issues</li>
                </ol>
                <p><strong>Login URL:</strong> ${window.location.origin}/login</p>
            </div>
            
            <div class="warning">
                <h3>⚠️ Important Security Notes:</h3>
                <ul>
                    <li>Keep these credentials secure and private</li>
                    <li>Do not share with unauthorized persons</li>
                    <li>Change password after first login</li>
                    <li>Report any suspicious activity immediately</li>
                </ul>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function emailCredentials() {
    const email = document.getElementById('newTenantEmail').value;
    const password = document.getElementById('newTenantPassword').value;
    
    const subject = 'HouseSync - Your New Tenant Account Credentials';
    const body = `Dear Tenant,

Welcome to HouseSync! Your new tenant account has been created.

Your login credentials:
Email: ${email}
Password: ${password}

Please follow these steps:
1. Log in to your tenant dashboard at: ${window.location.origin}/login
2. Upload all required documents
3. Change your password after first login

If you have any questions, please contact your landlord.

Best regards,
Your Landlord`;

    const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.open(mailtoLink);
}

function showSuccessToast(message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'alert alert-success position-fixed';
    toast.style.cssText = `
        top: 20px; 
        right: 20px; 
        z-index: 9999; 
        min-width: 300px;
        animation: slideInRight 0.3s ease-out;
    `;
    toast.innerHTML = `
        <i class="mdi mdi-check-circle me-2"></i>${message}
        <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 3000);
}

// Auto-show credentials modal when page loads with credentials
document.addEventListener('DOMContentLoaded', function() {
    @if(session('credentials'))
        const credentialsData = @json(session('credentials'));
        showNewCredentialsModal(credentialsData.email, credentialsData.password);
    @endif
});

function showNewCredentialsModal(email, password) {
    // Populate the modal with credentials
    document.getElementById('newTenantEmail').value = email;
    document.getElementById('newTenantPassword').value = password;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('newCredentialsModal'), {
        backdrop: 'static',
        keyboard: false
    });
    modal.show();
}

// View application documents
function viewApplicationDocuments(assignmentId) {
    window.location.href = `/landlord/tenant-assignments/${assignmentId}`;
}

// View application details in modal
function viewApplicationDetails(assignmentId) {
    // This would be better to fetch via AJAX and show in a modal
    // For now, redirect to assignment details page
    window.location.href = `/landlord/tenant-assignments/${assignmentId}`;
}

// Approve application
function approveApplication(assignmentId) {
    if (!confirm('Are you sure you want to approve this tenant application? This will allow the tenant to access their unit.')) {
        return;
    }
    
    fetch(`/landlord/tenant-assignments/${assignmentId}/approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessToast('Application approved successfully!');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            alert(data.message || 'Failed to approve application');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while approving the application');
    });
}

// Reject application
function rejectApplication(assignmentId) {
    const reason = prompt('Please provide a reason for rejection (optional):');
    if (reason === null) return; // User cancelled
    
    if (!confirm('Are you sure you want to reject this tenant application?')) {
        return;
    }
    
    fetch(`/landlord/tenant-assignments/${assignmentId}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessToast('Application rejected successfully');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            alert(data.message || 'Failed to reject application');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while rejecting the application');
    });
}
</script>
@endpush 