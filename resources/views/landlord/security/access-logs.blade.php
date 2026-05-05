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
                        <a href="{{ route('landlord.security.index', ['property_id' => $propertyId]) }}">Security</a>
                    </li>
                    <li class="breadcrumb-item active">Access Logs</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('landlord.security.index', ['property_id' => $propertyId]) }}" 
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
                        <label for="property_id" class="form-label">Apartment</label>
                        <select name="property_id" id="property_id" class="form-select">
                            <option value="">All Apartments</option>
                            @foreach($apartments as $apartment)
                                <option value="{{ $apartment->id }}" 
                                        {{ $propertyId == $apartment->id ? 'selected' : '' }}>
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

    @if($pairingTruncated ?? false)
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle"></i>
            Showing pairing for the oldest <strong>{{ $maxForPairing ?? 5000 }}</strong> matching events (by time).
            Narrow your date range or filters to include newer records in visit pairing.
        </div>
    @endif

    <!-- Summary Statistics -->
    @if($allLogsAsc->count() > 0)
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row text-center g-2">
                            <div class="col-6 col-md">
                                <div class="text-primary">
                                    <strong>{{ $allLogsAsc->count() }}</strong>
                                    <br><small>Total Attempts</small>
                                </div>
                            </div>
                            <div class="col-6 col-md">
                                <div class="text-dark">
                                    <strong>{{ $visitLogs->total() }}</strong>
                                    <br><small>Complete Visits</small>
                                </div>
                            </div>
                            <div class="col-6 col-md">
                                <div class="text-success">
                                    <strong>{{ $allLogsAsc->where('access_result', 'granted')->count() }}</strong>
                                    <br><small>Granted</small>
                                </div>
                            </div>
                            <div class="col-6 col-md">
                                <div class="text-danger">
                                    <strong>{{ $allLogsAsc->where('access_result', 'denied')->count() }}</strong>
                                    <br><small>Denied</small>
                                </div>
                            </div>
                            <div class="col-12 col-md">
                                <div class="text-info">
                                    <strong>{{ $allLogsAsc->unique('card_uid')->count() }}</strong>
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

    <!-- Complete visits: main_entrance (in) then main_exit (out), same card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-0">
                    <i class="fas fa-door-open"></i> Complete visits
                </h5>
                <small class="text-muted">Granted tap at main entrance followed by granted tap at main exit (same card UID).</small>
            </div>
            @if($visitLogs->total() > 0)
                <span class="badge bg-secondary">
                    {{ $visitLogs->firstItem() }}–{{ $visitLogs->lastItem() }} of {{ $visitLogs->total() }}
                </span>
            @endif
        </div>
        <div class="card-body">
            @if($visitLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-visit-pairs">
                        <thead>
                            <tr class="visit-group-header">
                                <th class="text-success">Entry Side</th>
                                <th class="visit-separator-cell"></th>
                                <th class="text-primary">Exit Side</th>
                                <th colspan="5"></th>
                            </tr>
                            <tr>
                                <th>In (main entrance)</th>
                                <th class="visit-separator-cell"></th>
                                <th>Out (main exit)</th>
                                <th>Duration</th>
                                <th>Card UID</th>
                                <th>Tenant</th>
                                <th>Apartment</th>
                                <th>Visit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($visitLogs as $visit)
                                @php
                                    $in = $visit->in;
                                    $out = $visit->out;
                                    $secs = $in->access_time->diffInSeconds($out->access_time);
                                    $mins = (int) floor($secs / 60);
                                @endphp
                                <tr>
                                    <td>
                                        <div>{{ $in->access_time->format('M j, Y') }}</div>
                                        <small class="text-muted">{{ $in->access_time->format('g:i:s A') }}</small>
                                    </td>
                                    <td class="visit-separator-cell">
                                        <span class="visit-separator-line"></span>
                                    </td>
                                    <td>
                                        <div>{{ $out->access_time->format('M j, Y') }}</div>
                                        <small class="text-muted">{{ $out->access_time->format('g:i:s A') }}</small>
                                    </td>
                                    <td>
                                        <small>
                                            @if($secs < 60)
                                                {{ $secs }}s
                                            @elseif($mins < 60)
                                                {{ $mins }} min
                                            @else
                                                {{ intdiv($mins, 60) }} h {{ $mins % 60 }} min
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <code>{{ $in->card_uid }}</code>
                                        @if($in->rfidCard)
                                            <br>
                                            <small class="text-muted">
                                                <a href="{{ route('landlord.security.card-details', $in->rfidCard->id) }}" class="text-decoration-none">View Card</a>
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($in->tenantAssignment)
                                            <strong>{{ $in->tenantAssignment->tenant->name }}</strong>
                                            <br>
                                            <small class="text-muted">Unit: {{ $in->tenantAssignment->unit->unit_number ?? '—' }}</small>
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($in->apartment)
                                            {{ $in->apartment->name }}
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success">In → Out</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $visitLogs->links() }}
                </div>
            @else
                <p class="text-muted mb-0">
                    @if($allLogsAsc->count() > 0)
                        No complete entrance-to-exit pairs in this range. Pairs require a <strong>granted</strong> tap at <code>main_entrance</code> and a later <strong>granted</strong> tap at <code>main_exit</code> for the same card.
                    @else
                        No data for complete visits.
                    @endif
                </p>
            @endif
        </div>
    </div>

    <!-- Access events: denied, orphan exit/in, other locations, incomplete entrance -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="card-title mb-0">
                    <i class="fas fa-list"></i> Access events
                </h5>
                <small class="text-muted">Denied attempts, taps at other readers, orphan exit without matching entrance, or entrance without a matching exit yet.</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($otherLogsPaginator->total() > 0)
                    <span class="badge bg-secondary">
                        {{ $otherLogsPaginator->firstItem() }}–{{ $otherLogsPaginator->lastItem() }} of {{ $otherLogsPaginator->total() }}
                    </span>
                @endif
                <button class="btn btn-sm btn-outline-secondary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#access-events-collapse"
                        aria-expanded="false"
                        aria-controls="access-events-collapse">
                    Toggle
                </button>
            </div>
        </div>
        <div id="access-events-collapse" class="collapse">
        <div class="card-body">
            @if($otherLogsPaginator->count() > 0)
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
                            @foreach($otherLogsPaginator as $log)
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
                                            <small class="text-muted">Unit: {{ $log->tenantAssignment->unit->unit_number ?? '—' }}</small>
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

                <div class="d-flex justify-content-center mt-4">
                    {{ $otherLogsPaginator->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    @if($allLogsAsc->count() > 0)
                        <p class="text-muted mb-0">No other events — all matching logs were paired as complete visits.</p>
                    @elseif(request()->hasAny(['property_id', 'card_uid', 'result', 'date_from', 'date_to']))
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

    .table-visit-pairs .visit-separator-cell {
        width: 18px;
        min-width: 18px;
        padding-left: 0 !important;
        padding-right: 0 !important;
        text-align: center;
    }

    .table-visit-pairs .visit-separator-line {
        display: inline-block;
        width: 2px;
        min-height: 34px;
        background: #ced4da;
        border-radius: 99px;
    }

    .table-visit-pairs .visit-group-header th {
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border-bottom: 0;
        padding-bottom: 0.35rem;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when quick filters change
    const quickFilters = ['property_id', 'result'];
    
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
