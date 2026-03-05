@extends('layouts.landlord-app')

@section('title', 'Access Logs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Access Logs</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('landlord.security', ['apartment_id' => $apartmentId]) }}">Security</a>
                    </li>
                    <li class="breadcrumb-item active">Access Logs</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('landlord.security', ['apartment_id' => $apartmentId]) }}" 
           class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Security
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="fas fa-filter"></i> Filter Access Logs
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('landlord.security.access-logs') }}">
                <div class="row g-3">
                    <!-- Apartment Filter -->
                    <div class="col-md-3">
                        <label for="apartment_id" class="form-label">Apartment</label>
                        <select name="apartment_id" id="apartment_id" class="form-select">
                            <option value="">All Apartments</option>
                            @foreach($apartments as $apartment)
                                <option value="{{ $apartment->id }}" 
                                        {{ $apartmentId == $apartment->id ? 'selected' : '' }}>
                                    {{ $apartment->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Card UID Filter -->
                    <div class="col-md-3">
                        <label for="card_uid" class="form-label">Card UID</label>
                        <input type="text" 
                               class="form-control" 
                               id="card_uid" 
                               name="card_uid" 
                               value="{{ $cardUid }}"
                               placeholder="Search by Card UID"
                               style="font-family: monospace;">
                    </div>

                    <!-- Result Filter -->
                    <div class="col-md-2">
                        <label for="result" class="form-label">Result</label>
                        <select name="result" id="result" class="form-select">
                            <option value="">All Results</option>
                            <option value="granted" {{ $result === 'granted' ? 'selected' : '' }}>Granted</option>
                            <option value="denied" {{ $result === 'denied' ? 'selected' : '' }}>Denied</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_from" 
                               name="date_from" 
                               value="{{ $dateFrom }}">
                    </div>

                    <!-- Date To -->
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="date_to" 
                               name="date_to" 
                               value="{{ $dateTo }}">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="{{ route('landlord.security.access-logs') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    @if($logs->total() > 0)
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="text-primary">
                                    <strong>{{ $logs->total() }}</strong>
                                    <br><small>Total Attempts</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-success">
                                    <strong>{{ $logs->where('access_result', 'granted')->count() }}</strong>
                                    <br><small>Granted</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-danger">
                                    <strong>{{ $logs->where('access_result', 'denied')->count() }}</strong>
                                    <br><small>Denied</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-info">
                                    <strong>{{ $logs->unique('card_uid')->count() }}</strong>
                                    <br><small>Unique Cards</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                @if($deniedReasons->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Top Denial Reasons</h6>
                        </div>
                        <div class="card-body">
                            @foreach($deniedReasons->take(3) as $reason)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small>{{ $reason->denial_reason_display ?? $reason->denial_reason }}</small>
                                    <span class="badge bg-danger">{{ $reason->count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Access Logs Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> Access Attempts
            </h5>
            @if($logs->total() > 0)
                <span class="badge bg-secondary">
                    Showing {{ $logs->firstItem() }}-{{ $logs->lastItem() }} of {{ $logs->total() }}
                </span>
            @endif
        </div>
        <div class="card-body">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Card UID</th>
                                <th>Tenant</th>
                                <th>Apartment</th>
                                <th>Result</th>
                                <th>Reason</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>
                                        <div>{{ $log->access_time->format('M j, Y') }}</div>
                                        <small class="text-muted">{{ $log->access_time->format('g:i:s A') }}</small>
                                    </td>
                                    <td>
                                        <code>{{ $log->card_uid }}</code>
                                        @if($log->rfidCard)
                                            <br>
                                            <small class="text-muted">
                                                <a href="{{ route('landlord.security.card-details', $log->rfidCard->id) }}" 
                                                   class="text-decoration-none">
                                                    View Card
                                                </a>
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->tenantAssignment)
                                            <strong>{{ $log->tenantAssignment->tenant->name }}</strong>
                                            <br>
                                            <small class="text-muted">Unit: {{ $log->tenantAssignment->unit->unit_number }}</small>
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->apartment)
                                            {{ $log->apartment->name }}
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
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
                                            <small class="text-success">Access granted</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ ucfirst(str_replace('_', ' ', $log->reader_location)) }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    @if(request()->hasAny(['apartment_id', 'card_uid', 'result', 'date_from', 'date_to']))
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5>No Access Logs Found</h5>
                        <p class="text-muted">No access attempts match your current filters.</p>
                        <a href="{{ route('landlord.security.access-logs') }}" class="btn btn-outline-primary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    @else
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h5>No Access Logs Yet</h5>
                        <p class="text-muted">No access attempts have been recorded for your properties.</p>
                        <p class="text-muted small">
                            Access logs will appear here when tenants use their RFID cards to access the facility.
                        </p>
                    @endif
                </div>
            @endif
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
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when quick filters change
    const quickFilters = ['apartment_id', 'result'];
    
    quickFilters.forEach(filterId => {
        const filterElement = document.getElementById(filterId);
        if (filterElement) {
            filterElement.addEventListener('change', function() {
                // Only auto-submit if it's a dropdown change, not manual input
                if (this.tagName === 'SELECT') {
                    this.form.submit();
                }
            });
        }
    });
});
</script>
@endsection
