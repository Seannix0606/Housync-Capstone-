@extends('layouts.staff-app')

@section('title', 'Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
                <h4 class="page-title">Profile Information</h4>
            </div>
        </div>
    </div>

    <!-- Profile Information Cards -->
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="mdi mdi-account me-1"></i>
                        Personal Information
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Full Name:</label>
                                <p class="mb-1">{{ $staff->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address:</label>
                                <p class="mb-1">{{ $staff->email }}</p>
                            </div>
                            @if($staff->phone)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phone Number:</label>
                                <p class="mb-1">{{ $staff->phone }}</p>
                            </div>
                            @endif
                            @if($staff->address)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Address:</label>
                                <p class="mb-1">{{ $staff->address }}</p>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Account Type:</label>
                                <p class="mb-1">
                                    <span class="badge bg-info">
                                        <i class="mdi mdi-account-hard-hat me-1"></i>
                                        {{ ucfirst($staff->role) }}
                                    </span>
                                </p>
                            </div>
                            @if($staff->staff_type)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Staff Type:</label>
                                <p class="mb-1">
                                    <span class="badge bg-primary">
                                        {{ ucfirst(str_replace('_', ' ', $staff->staff_type)) }}
                                    </span>
                                </p>
                            </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label fw-bold">Account Status:</label>
                                <p class="mb-1">
                                    <span class="badge bg-success">
                                        <i class="mdi mdi-check-circle me-1"></i>
                                        Active
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Member Since:</label>
                                <p class="mb-1">{{ $staff->created_at->format('F Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($assignment)
            <!-- Current Assignment Information -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="mdi mdi-briefcase me-1"></i>
                        Current Assignment
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Unit Number:</label>
                                <p class="mb-1">{{ $assignment->unit->unit_number }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Apartment:</label>
                                <p class="mb-1">{{ $assignment->unit->apartment->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Address:</label>
                                <p class="mb-1">{{ $assignment->unit->apartment->address }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Assignment Type:</label>
                                <p class="mb-1">
                                    <span class="badge bg-info">
                                        {{ ucfirst(str_replace('_', ' ', $assignment->staff_type)) }}
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Start Date:</label>
                                <p class="mb-1">{{ $assignment->assignment_start_date->format('M d, Y') }}</p>
                            </div>
                            @if($assignment->hourly_rate)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Hourly Rate:</label>
                                <p class="mb-1">₱{{ number_format($assignment->hourly_rate, 2) }}/hr</p>
                            </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p class="mb-1">
                                    @if($assignment->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($assignment->status === 'completed')
                                        <span class="badge bg-primary">Completed</span>
                                    @else
                                        <span class="badge bg-warning">{{ ucfirst($assignment->status) }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    @if($assignment->notes)
                    <div class="mt-3">
                        <label class="form-label fw-bold">Assignment Notes:</label>
                        <div class="p-3 bg-light rounded">
                            {{ $assignment->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Password Change Section -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="mdi mdi-lock me-1"></i>
                        Change Password
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information me-1"></i>
                        <strong>Staff members can change their password anytime.</strong>
                        <br><small>No document verification required.</small>
                    </div>
                    
                    <form id="passwordForm">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('current_password')">
                                    <i class="mdi mdi-eye" id="current_password_icon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('new_password')">
                                    <i class="mdi mdi-eye" id="new_password_icon"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimum 8 characters required</small>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required minlength="8">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('new_password_confirmation')">
                                    <i class="mdi mdi-eye" id="new_password_confirmation_icon"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-key me-1"></i>
                            Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Landlord Information -->
            @if($assignment && $assignment->landlord)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="mdi mdi-account-tie me-1"></i>
                        Landlord Contact
                    </h4>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title bg-soft-primary rounded-circle">
                                {{ substr($assignment->landlord->name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $assignment->landlord->name }}</h6>
                            <small class="text-muted">{{ $assignment->landlord->email }}</small>
                        </div>
                    </div>
                    @if($assignment->landlord->phone)
                    <div class="mb-2">
                        <label class="form-label fw-bold">Phone:</label>
                        <p class="mb-1">{{ $assignment->landlord->phone }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('mdi-eye');
        icon.classList.add('mdi-eye-off');
    } else {
        field.type = 'password';
        icon.classList.remove('mdi-eye-off');
        icon.classList.add('mdi-eye');
    }
}

document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Disable button and show loading
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i>Updating...';
    
    fetch('{{ route("staff.update-password") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="mdi mdi-check-circle me-1"></i>
                ${data.success}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.getElementById('passwordForm'));
            
            // Reset form
            this.reset();
            
            // Auto-dismiss alert after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        } else if (data.error) {
            // Show error message
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                <i class="mdi mdi-alert-circle me-1"></i>
                ${data.error}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.getElementById('passwordForm'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            <i class="mdi mdi-alert-circle me-1"></i>
            Failed to update password. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.card-body').insertBefore(alert, document.getElementById('passwordForm'));
    })
    .finally(() => {
        // Re-enable button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});
</script>
@endpush
