@extends('layouts.landlord-app')

@section('title', 'Reassign RFID Card')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Reassign RFID Card</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('landlord.security', ['apartment_id' => $card->apartment_id]) }}">Security</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('landlord.security.card-details', $card->id) }}">Card Details</a>
                    </li>
                    <li class="breadcrumb-item active">Reassign Card</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('landlord.security', ['apartment_id' => $card->apartment_id]) }}" 
           class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Security
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="alert-heading">Please fix the following errors:</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <!-- Card Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-id-card"></i> Card Information
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Card UID:</dt>
                        <dd class="col-sm-8">
                            <code>{{ $card->card_uid }}</code>
                        </dd>

                        <dt class="col-sm-4">Card Name:</dt>
                        <dd class="col-sm-8">{{ $card->card_name ?: '-' }}</dd>

                        <dt class="col-sm-4">Apartment:</dt>
                        <dd class="col-sm-8">{{ $card->apartment->name }}</dd>

                        <dt class="col-sm-4">Current Tenant:</dt>
                        <dd class="col-sm-8">
                            @if($card->activeTenantAssignment)
                                <strong>{{ $card->activeTenantAssignment->tenantAssignment->tenant->name }}</strong>
                                <br>
                                <small class="text-muted">Unit: {{ $card->activeTenantAssignment->tenantAssignment->unit->unit_number }}</small>
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $card->status_badge_class }}">
                                {{ ucfirst($card->display_status) }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-check"></i> Reassign to New Tenant
                    </h5>
                </div>
                <div class="card-body">
                    @if($tenantAssignments->count() > 0)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Reassignment Process:</strong> This will deactivate the current tenant's access and assign the card to a new tenant. The card will be automatically activated.
                        </div>

                        <form method="POST" action="{{ route('landlord.security.reassign-card', $card->id) }}">
                            @csrf
                            
                            <!-- Tenant Selection -->
                            <div class="mb-3">
                                <label for="tenant_assignment_id" class="form-label required">Select New Tenant</label>
                                <select class="form-select @error('tenant_assignment_id') is-invalid @enderror" 
                                        id="tenant_assignment_id" 
                                        name="tenant_assignment_id" 
                                        required>
                                    <option value="">Select a tenant</option>
                                    @foreach($tenantAssignments as $assignment)
                                        <option value="{{ $assignment->id }}" 
                                                {{ old('tenant_assignment_id') == $assignment->id ? 'selected' : '' }}>
                                            {{ $assignment->tenant->name }} - Unit {{ $assignment->unit->unit_number }}
                                            ({{ $assignment->lease_start_date->format('M j, Y') }} - {{ $assignment->lease_end_date->format('M j, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    Select the tenant who will receive this RFID card
                                </div>
                                @error('tenant_assignment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Expiry Date -->
                            <div class="mb-3">
                                <label for="expires_at" class="form-label">Card Expiry Date</label>
                                <input type="date" 
                                       class="form-control @error('expires_at') is-invalid @enderror" 
                                       id="expires_at" 
                                       name="expires_at" 
                                       value="{{ old('expires_at') }}"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                <div class="form-text">
                                    Optional: Set when this card should expire. Leave blank for no expiration.
                                </div>
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3"
                                          placeholder="Add any notes about this reassignment...">{{ old('notes') }}</textarea>
                                <div class="form-text">
                                    Optional: Add notes about why this card is being reassigned
                                </div>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-exchange-alt"></i> Reassign Card to New Tenant
                                </button>
                                <a href="{{ route('landlord.security', ['apartment_id' => $card->apartment_id]) }}" 
                                   class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>No Available Tenants</strong>
                            <p class="mb-0">There are no active tenants in <strong>{{ $card->apartment->name }}</strong> available for card assignment. Please ensure you have active tenants before reassigning this card.</p>
                        </div>
                        <a href="{{ route('landlord.security', ['apartment_id' => $card->apartment_id]) }}" 
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Security
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.required::after {
    content: " *";
    color: red;
}
</style>
@endsection

