@extends('layouts.landlord-app')

@section('title', 'Staff Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('landlord.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Staff Management</li>
                    </ol>
                </div>
                <h4 class="page-title">Staff Management</h4>
            </div>
        </div>
    </div>

    <!-- Credentials Alert -->
    @if(session('credentials') || session('staff_credentials'))
        <script>
            // Auto-show credentials modal when page loads
            document.addEventListener('DOMContentLoaded', function() {
                const credentialsModal = new bootstrap.Modal(document.getElementById('staffCredentialsModal'));
                credentialsModal.show();
            });
        </script>
    @endif

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="text-muted fw-normal mt-0" title="Total Staff">Total Staff</h5>
                            <h3 class="mt-3 mb-3">{{ $stats['total'] }}</h3>
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
                            <h5 class="text-muted fw-normal mt-0" title="Active Staff">Active Staff</h5>
                            <h3 class="mt-3 mb-3">{{ $stats['active'] }}</h3>
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
                            <h5 class="text-muted fw-normal mt-0" title="Inactive Staff">Inactive Staff</h5>
                            <h3 class="mt-3 mb-3">{{ $stats['inactive'] }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-warning rounded">
                                <i class="mdi mdi-pause-circle font-20 text-warning"></i>
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
                            <h5 class="text-muted fw-normal mt-0" title="Staff Types">Staff Types</h5>
                            <h3 class="mt-3 mb-3">{{ $stats['staff_types'] }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-info rounded">
                                <i class="mdi mdi-tag-multiple font-20 text-info"></i>
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
                    <form method="GET" action="{{ route('landlord.staff') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="staff_type" class="form-label">Staff Type</label>
                            <select name="staff_type" id="staff_type" class="form-select">
                                <option value="">All Types</option>
                                <option value="maintenance_worker" {{ request('staff_type') == 'maintenance_worker' ? 'selected' : '' }}>Maintenance Worker</option>
                                <option value="plumber" {{ request('staff_type') == 'plumber' ? 'selected' : '' }}>Plumber</option>
                                <option value="electrician" {{ request('staff_type') == 'electrician' ? 'selected' : '' }}>Electrician</option>
                                <option value="cleaner" {{ request('staff_type') == 'cleaner' ? 'selected' : '' }}>Cleaner</option>
                                <option value="painter" {{ request('staff_type') == 'painter' ? 'selected' : '' }}>Painter</option>
                                <option value="carpenter" {{ request('staff_type') == 'carpenter' ? 'selected' : '' }}>Carpenter</option>
                                <option value="security_guard" {{ request('staff_type') == 'security_guard' ? 'selected' : '' }}>Security Guard</option>
                                <option value="gardener" {{ request('staff_type') == 'gardener' ? 'selected' : '' }}>Gardener</option>
                                <option value="others" {{ request('staff_type') == 'others' ? 'selected' : '' }}>Others</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('landlord.staff') }}" class="btn btn-secondary">Clear</a>
                            <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                                <i class="mdi mdi-account-plus me-1"></i> Add Staff
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStaffModalLabel">Add New Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('landlord.add-staff') }}" id="addStaffForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_staff_name" class="form-label">Staff Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_staff_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="add_staff_phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="add_staff_phone" name="phone">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_staff_type" class="form-label">Staff Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="add_staff_type" name="staff_type" required>
                                    <option value="">-- Select Staff Type --</option>
                                    <option value="maintenance_worker">Maintenance Worker</option>
                                    <option value="plumber">Plumber</option>
                                    <option value="electrician">Electrician</option>
                                    <option value="cleaner">Cleaner</option>
                                    <option value="painter">Painter</option>
                                    <option value="carpenter">Carpenter</option>
                                    <option value="security_guard">Security Guard</option>
                                    <option value="gardener">Gardener</option>
                                    <option value="others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="add_staff_address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="add_staff_address" name="address">
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <h6 class="alert-heading">What happens next?</h6>
                            <ul class="mb-0">
                                <li>A new staff account will be created with auto-generated login credentials</li>
                                <li>The staff member can be assigned to units later</li>
                                <li>Login credentials will be displayed after creation</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="mdi mdi-account-plus me-1"></i> Add Staff Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Staff Assignments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Staff</th>
                                    <th>Type</th>
                                    <th>Active Tasks</th>
                                    <th>Current Task</th>
                                    <th>Expected Completion</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staff as $staffMember)
                                @php
                                    $activeTasks = $staffMember->assignedMaintenanceRequests;
                                    $currentTask = $activeTasks->first();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <span class="avatar-title bg-soft-primary rounded-circle">
                                                    {{ substr($staffMember->staffProfile->name ?? 'N', 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h5 class="font-14 mb-0">{{ $staffMember->staffProfile->name ?? 'N/A' }}</h5>
                                                <small class="text-muted">{{ $staffMember->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <i class="mdi mdi-tools me-1"></i>
                                            {{ ucwords(str_replace('_', ' ', $staffMember->staffProfile->staff_type ?? 'N/A')) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($activeTasks->count() > 0)
                                            <span class="badge bg-primary" style="font-size: 0.9rem;">
                                                {{ $activeTasks->count() }} {{ $activeTasks->count() == 1 ? 'Task' : 'Tasks' }}
                                            </span>
                                        @else
                                            <span class="text-muted">No active tasks</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($currentTask)
                                            <div>
                                                <strong style="font-size: 0.9rem;">{{ Str::limit($currentTask->title, 30) }}</strong><br>
                                                <small class="text-muted">
                                                    <i class="mdi mdi-home me-1"></i>{{ $currentTask->unit->apartment->name }} - Unit {{ $currentTask->unit->unit_number }}
                                                </small><br>
                                                <span class="badge bg-{{ $currentTask->status_badge_class }}" style="font-size: 0.75rem;">
                                                    {{ ucwords(str_replace('_', ' ', $currentTask->status)) }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($currentTask && $currentTask->expected_completion_date)
                                            <div>
                                                <small class="text-muted">{{ $currentTask->expected_completion_date->format('M d, Y') }}</small>
                                                @if($currentTask->expected_completion_date->isPast() && $currentTask->status != 'completed')
                                                    <br><span class="badge bg-danger" style="font-size: 0.7rem;">Overdue</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $staffMember->staffProfile->status == 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst($staffMember->staffProfile->status ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Actions">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if($activeTasks->count() > 0)
                                                <li><a class="dropdown-item" href="{{ route('landlord.maintenance.index') }}?staff={{ $staffMember->id }}" title="View All Tasks">
                                                    <i class="mdi mdi-format-list-bulleted me-1"></i> View All Tasks ({{ $activeTasks->count() }})
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                @endif
                                                <li><a class="dropdown-item" href="#" onclick="alert('Email: {{ $staffMember->email }}\nPassword: Use password reset if needed')" title="View Login Credentials">
                                                    <i class="mdi mdi-key me-1"></i> View Credentials
                                                </a></li>
                                                @if($staffMember->staffProfile->status === 'active')
                                                <li><a class="dropdown-item" href="#" onclick="alert('Deactivate feature coming soon')" title="Deactivate Staff">
                                                    <i class="mdi mdi-pause me-1"></i> Deactivate
                                                </a></li>
                                                @else
                                                <li><a class="dropdown-item" href="#" onclick="alert('Activate feature coming soon')" title="Activate Staff">
                                                    <i class="mdi mdi-play me-1"></i> Activate
                                                </a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No staff members found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $staff->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Form -->
<form id="staffStatusForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="status" id="staffStatusInput">
</form>

<!-- Delete Staff Form -->
<form id="deleteStaffForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Credentials Modal -->
<div class="modal fade" id="staffCredentialsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="mdi mdi-check-circle text-success me-2"></i>
                    Staff Assigned Successfully!
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if(session('credentials') || session('staff_credentials'))
                    @if(session('credentials'))
                        <div class="alert alert-success mb-4">
                            <h6 class="alert-heading mb-2">
                                <i class="mdi mdi-account-plus me-1"></i>
                                Staff Assigned Successfully!
                            </h6>
                            <p class="mb-0">A new staff account has been created for <strong>{{ session('credentials')['staff_name'] }}</strong> and assigned to a unit. Please share these credentials with the staff member:</p>
                        </div>
                    @else
                        <div class="alert alert-success mb-4">
                            <h6 class="alert-heading mb-2">
                                <i class="mdi mdi-account-plus me-1"></i>
                                New Staff Member Added!
                            </h6>
                            <p class="mb-0">A new staff account has been created for <strong>{{ session('staff_credentials')['staff_name'] }}</strong> ({{ ucwords(str_replace('_', ' ', session('staff_credentials')['staff_type'])) }}). Please share these credentials with the staff member:</p>
                        </div>
                    @endif
                    
                    <!-- Credentials Display -->
                    <div class="credentials-box p-4 mb-4" style="background: #f8f9fa; border: 2px solid #28a745; border-radius: 8px;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-primary">Email Address:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="assignedStaffEmail" value="{{ session('credentials')['email'] ?? session('staff_credentials')['email'] }}" readonly style="background: white; font-weight: bold; font-size: 1.1rem;">
                                    <button class="btn btn-outline-primary" type="button" onclick="copyText('assignedStaffEmail')" title="Copy email">
                                        <i class="mdi mdi-content-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-primary">Password:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="assignedStaffPassword" value="{{ session('credentials')['password'] ?? session('staff_credentials')['password'] }}" readonly style="background: white; font-weight: bold; font-size: 1.1rem; color: #dc3545;">
                                    <button class="btn btn-outline-primary" type="button" onclick="copyText('assignedStaffPassword')" title="Copy password">
                                        <i class="mdi mdi-content-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mb-4">
                        <button type="button" class="btn btn-primary" onclick="copyAllStaffCredentials()" title="Copy both email and password">
                            <i class="mdi mdi-content-copy me-1"></i> Copy All Credentials
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="printStaffCredentials()" title="Print credentials">
                            <i class="mdi mdi-printer me-1"></i> Print
                        </button>
                    </div>
                    
                    <!-- Important Notice -->
                    <div class="alert alert-warning mb-0" style="border-left: 4px solid #ffc107;">
                        <h6 class="alert-heading"><i class="mdi mdi-alert-circle me-1"></i> Important:</h6>
                        <ul class="mb-0">
                            <li><strong>Save these credentials securely</strong> - They won't be shown again</li>
                            <li>The staff member must use these credentials to log in to their dashboard</li>
                            <li>Staff can access their assigned unit information and work orders</li>
                        </ul>
                    </div>
                @else
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Login Information</h6>
                        <p class="mb-2">Share these credentials with the staff member:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Email:</strong><br>
                                <code id="staffEmail"></code>
                            </div>
                            <div class="col-md-6">
                                <strong>Password:</strong><br>
                                <code id="staffPassword"></code>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Important Notes:</h6>
                        <ul class="mb-0">
                            <li>The staff member should change their password after first login</li>
                            <li>These credentials are for initial access only</li>
                            <li>Keep these credentials secure and private</li>
                        </ul>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                @if(session('credentials') || session('staff_credentials'))
                    <button type="button" class="btn btn-primary" onclick="copyAllStaffCredentials()">
                        <i class="mdi mdi-content-copy me-1"></i> Copy Credentials
                    </button>
                @else
                    <button type="button" class="btn btn-primary" onclick="copyStaffCredentials()">
                        <i class="mdi mdi-content-copy me-1"></i> Copy Credentials
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function updateStaffStatus(assignmentId, status) {
    if (confirm('Are you sure you want to update this staff assignment status?')) {
        const form = document.getElementById('staffStatusForm');
        const statusInput = document.getElementById('staffStatusInput');
        
        form.action = `/landlord/staff/${assignmentId}/status`;
        statusInput.value = status;
        form.submit();
    }
}

function viewStaffCredentials(assignmentId, email) {
    // Fetch credentials from the server
    fetch(`/landlord/staff/${assignmentId}/credentials`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('staffEmail').textContent = data.email;
            document.getElementById('staffPassword').textContent = data.password;
            
            const modal = new bootstrap.Modal(document.getElementById('staffCredentialsModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error fetching credentials:', error);
            alert('Error fetching credentials. Please try again.');
        });
}

function copyStaffCredentials() {
    const email = document.getElementById('staffEmail').textContent;
    const password = document.getElementById('staffPassword').textContent;
    const credentials = `Email: ${email}\nPassword: ${password}`;
    
    navigator.clipboard.writeText(credentials).then(function() {
        alert('Credentials copied to clipboard!');
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Could not copy to clipboard. Please copy manually.');
    });
}

function deleteStaffAssignment(assignmentId, staffName) {
    if (confirm(`Are you sure you want to delete the assignment for ${staffName}? This action cannot be undone.`)) {
        const deleteForm = document.getElementById('deleteStaffForm');
        deleteForm.action = `/landlord/staff/${assignmentId}`;
        deleteForm.submit();
    }
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

function copyAllStaffCredentials() {
    const email = document.getElementById('assignedStaffEmail').value;
    const password = document.getElementById('assignedStaffPassword').value;
    const credentials = `Staff Login Credentials:
Email: ${email}
Password: ${password}

Please use these credentials to log in to your staff dashboard.`;
    
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

function printStaffCredentials() {
    const email = document.getElementById('assignedStaffEmail').value;
    const password = document.getElementById('assignedStaffPassword').value;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Staff Login Credentials</title>
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
                <h2>HouseSync - Staff Login Credentials</h2>
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
                    <li>Use these credentials to log in to your staff dashboard</li>
                    <li>You can view your assigned units and work orders</li>
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

// Set minimum date for assignment start date to today
const today = new Date().toISOString().split('T')[0];
document.getElementById('assignment_start_date').min = today;
document.getElementById('assignment_start_date').addEventListener('change', function() {
    const startDate = this.value;
    const endDateInput = document.getElementById('assignment_end_date');
    endDateInput.min = startDate;
    if (endDateInput.value && endDateInput.value < startDate) {
        endDateInput.value = '';
    }
});

// Function to filter staff by type
function filterStaffByType() {
    const staffTypeSelect = document.getElementById('staff_type_filter');
    const staffSelect = document.getElementById('staff_id');
    const selectedType = staffTypeSelect.value;
    
    // Clear and disable staff select
    staffSelect.innerHTML = '<option value="">Loading...</option>';
    staffSelect.disabled = true;
    
    if (!selectedType) {
        staffSelect.innerHTML = '<option value="">-- Select Staff Type First --</option>';
        return;
    }
    
    // Fetch staff members by type
    fetch(`/landlord/staff/by-type/${selectedType}`)
        .then(response => response.json())
        .then(data => {
            staffSelect.innerHTML = '<option value="">-- Select Staff Member --</option>';
            
            if (data.staff && data.staff.length > 0) {
                data.staff.forEach(staff => {
                    const option = document.createElement('option');
                    option.value = staff.id;
                    option.textContent = `${staff.name} (${staff.email})`;
                    staffSelect.appendChild(option);
                });
                staffSelect.disabled = false;
            } else {
                staffSelect.innerHTML = '<option value="">No staff members available for this type</option>';
            }
        })
        .catch(error => {
            console.error('Error fetching staff:', error);
            staffSelect.innerHTML = '<option value="">Error loading staff members</option>';
        });
}
</script>
@endpush 