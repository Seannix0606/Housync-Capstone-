@extends('layouts.landlord-app')

@section('title', 'RFID Card Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">RFID Card Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('landlord.security', ['apartment_id' => $card->apartment_id]) }}">Security</a>
                    </li>
                    <li class="breadcrumb-item active">Card {{ $card->card_uid }}</li>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <!-- Card Information -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-id-card"></i> Card Information
                    </h5>
                    <span class="badge bg-{{ $card->status_badge_class }} fs-6">
                        {{ $card->display_status }}
                    </span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Card UID:</dt>
                        <dd class="col-sm-8">
                            <code class="fs-6">{{ $card->card_uid }}</code>
                        </dd>

                        <dt class="col-sm-4">Card Name:</dt>
                        <dd class="col-sm-8">{{ $card->card_name ?: 'Not set' }}</dd>

                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $card->status_badge_class }}">
                                {{ $card->display_status }}
                            </span>
                            @if($card->isExpired())
                                <br><small class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Expired
                                </small>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Issued:</dt>
                        <dd class="col-sm-8">{{ $card->issued_at->format('M j, Y g:i A') }}</dd>

                        <dt class="col-sm-4">Expires:</dt>
                        <dd class="col-sm-8">
                            @if($card->expires_at)
                                {{ $card->expires_at->format('M j, Y') }}
                                @if($card->expires_at->isPast())
                                    <span class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </span>
                                @endif
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </dd>

                        @if($card->notes)
                            <dt class="col-sm-4">Notes:</dt>
                            <dd class="col-sm-8">{{ $card->notes }}</dd>
                        @endif
                    </dl>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('landlord.security.reassign-card-form', $card->id) }}" 
                           class="btn btn-info">
                            <i class="fas fa-exchange-alt"></i> Reassign to Another Tenant
                        </a>
                        
                        <form method="POST" 
                              action="{{ route('landlord.security.toggle-card-status', $card->id) }}" 
                              style="display: inline;">
                            @csrf
                            @method('PUT')
                            <button type="submit" 
                                    class="btn btn-{{ $card->status === 'active' ? 'warning' : 'success' }} w-100"
                                    onclick="return confirm('Are you sure you want to {{ $card->status === 'active' ? 'deactivate' : 'activate' }} this card?')">
                                <i class="fas fa-{{ $card->status === 'active' ? 'pause' : 'play' }}"></i>
                                {{ $card->status === 'active' ? 'Deactivate' : 'Activate' }} Card
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tenant Information -->
            @if($card->tenantAssignment)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-user"></i> Assigned Tenant
                        </h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Name:</dt>
                            <dd class="col-sm-8">
                                <strong>{{ $card->tenantAssignment->tenant->name }}</strong>
                            </dd>

                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">{{ $card->tenantAssignment->tenant->email }}</dd>

                            <dt class="col-sm-4">Unit:</dt>
                            <dd class="col-sm-8">{{ $card->tenantAssignment->unit->unit_number }}</dd>

                            <dt class="col-sm-4">Apartment:</dt>
                            <dd class="col-sm-8">{{ $card->apartment->name }}</dd>

                            <dt class="col-sm-4">Lease Status:</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-{{ $card->tenantAssignment->status_badge_class }}">
                                    {{ ucfirst($card->tenantAssignment->status) }}
                                </span>
                            </dd>

                            <dt class="col-sm-4">Lease Period:</dt>
                            <dd class="col-sm-8">
                                {{ $card->tenantAssignment->lease_start_date->format('M j, Y') }}
                                <br>
                                <small class="text-muted">to</small>
                                <br>
                                {{ $card->tenantAssignment->lease_end_date->format('M j, Y') }}
                            </dd>
                        </dl>
                    </div>
                </div>
            @else
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                        <h6>No Tenant Assigned</h6>
                        <p class="text-muted mb-0">This card is not currently assigned to any tenant.</p>
                    </div>
                </div>
            @endif

            <!-- Access Status -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-shield-alt"></i> Access Status
                    </h6>
                </div>
                <div class="card-body text-center">
                    @if($card->canGrantAccess())
                        <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                        <h6 class="text-success">Access Granted</h6>
                        <p class="text-muted mb-0">This card can currently access the facility.</p>
                    @else
                        <i class="fas fa-times-circle fa-3x text-danger mb-2"></i>
                        <h6 class="text-danger">Access Denied</h6>
                        <p class="text-muted mb-2">
                            Reason: <strong>{{ $card->getAccessDenialReason() }}</strong>
                        </p>
                        <p class="text-muted mb-0 small">
                            This card will be denied access if scanned.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Access Logs -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history"></i> Access History
                    </h5>
                    <span class="badge bg-secondary">{{ $accessLogs->total() }} total attempts</span>
                </div>
                <div class="card-body">
                    @if($accessLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Result</th>
                                        <th>Reason/Notes</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($accessLogs as $log)
                                        <tr>
                                            <td>
                                                <div>{{ $log->access_time->format('M j, Y') }}</div>
                                                <small class="text-muted">{{ $log->access_time->format('g:i:s A') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $log->display_badge_class }}">
                                                    {{ $log->display_result }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($log->denial_reason)
                                                    <small class="text-muted">{{ $log->denial_reason_display }}</small>
                                                @else
                                                    <small class="text-success">Access granted successfully</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $log->reader_location)) }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $accessLogs->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5>No Access History</h5>
                            <p class="text-muted">This card has not been used to access the facility yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        border-bottom: 2px solid #dee2e6;
    }
    
    code {
        color: #e83e8c;
        font-size: 0.875em;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    dt {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.875rem;
    }
    
    dd {
        margin-bottom: 0.5rem;
    }
</style>
@endsection
