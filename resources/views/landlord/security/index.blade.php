@extends('layouts.landlord-app')

@section('title', 'Security Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Security Management</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('landlord.security.create-card', ['property_id' => $propertyId]) }}" 
               class="btn btn-primary">
                <i class="fas fa-plus"></i> Assign New Card
            </a>
            <a href="{{ route('landlord.security.access-logs', ['property_id' => $propertyId]) }}" 
               class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> View All Logs
            </a>
        </div>
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

    @if(isset($readerLocationsSummary) && $readerLocationsSummary->isNotEmpty())
        <div class="alert alert-light border mb-4 d-flex flex-wrap align-items-center gap-2">
            <span class="fw-semibold text-secondary"><i class="fas fa-map-marker-alt me-1"></i> Reader locations (last 30 days)</span>
            @foreach($readerLocationsSummary as $loc)
                <span class="badge rounded-pill bg-light text-primary border">
                    {{ ucfirst(str_replace('_', ' ', $loc->reader_location)) }}
                    <span class="fw-normal opacity-75 ms-1">· {{ \Carbon\Carbon::parse($loc->last_seen_at)->diffForHumans() }}</span>
                </span>
            @endforeach
        </div>
    @endif

    <!-- Apartment Filter -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Filter by Apartment</h6>
                    <form method="GET" action="{{ route('landlord.security.index') }}">
                        <div class="input-group">
                            <select name="property_id" class="form-select" onchange="this.form.submit()">
                                <option value="">All Apartments</option>
                                @foreach($apartments as $apartment)
                                    <option value="{{ $apartment->id }}" 
                                            {{ $propertyId == $apartment->id ? 'selected' : '' }}>
                                        {{ $apartment->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <!-- Access Statistics -->
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Access Statistics (Last 30 Days)</h6>
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="text-primary">
                                <strong>{{ $stats['total_attempts'] }}</strong>
                                <br><small>Total</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-success">
                                <strong>{{ $stats['granted'] }}</strong>
                                <br><small>Granted</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-danger">
                                <strong>{{ $stats['denied'] }}</strong>
                                <br><small>Denied</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-info">
                                <strong>{{ $stats['unique_cards'] }}</strong>
                                <br><small>Cards</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RFID Cards List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">RFID Cards</h5>
        </div>
        <div class="card-body">
            @if($cards->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Card UID</th>
                                <th>Card Name</th>
                                <th>Tenant</th>
                                <th>Apartment</th>
                                <th>Status</th>
                                <th>Issued Date</th>
                                <th>Expires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cards as $card)
                                <tr>
                                    <td>
                                        <code>{{ $card->card_uid }}</code>
                                    </td>
                                    <td>{{ $card->card_name ?: '-' }}</td>
                                    <td>
                                        @if($card->tenantAssignment)
                                            <strong>{{ $card->tenantAssignment->tenant->name }}</strong>
                                            <br>
                                            <small class="text-muted">Unit: {{ $card->tenantAssignment->unit->unit_number }}</small>
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>{{ $card->apartment->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $card->status_badge_class }}">
                                            {{ $card->display_status }}
                                        </span>
                                        @if($card->isExpired())
                                            <br><small class="text-warning">Expired</small>
                                        @endif
                                    </td>
                                    <td>{{ $card->issued_at->format('M j, Y') }}</td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('landlord.security.card-details', $card->id) }}" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <a href="{{ route('landlord.security.reassign-card-form', $card->id) }}" 
                                               class="btn btn-outline-info" title="Reassign to Another Tenant">
                                                <i class="fas fa-exchange-alt"></i>
                                            </a>
                                            
                                            <form method="POST" 
                                                  action="{{ route('landlord.security.toggle-card-status', $card->id) }}" 
                                                  style="display: inline;">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" 
                                                        class="btn btn-outline-{{ $card->status === 'active' ? 'warning' : 'success' }}"
                                                        title="{{ $card->status === 'active' ? 'Deactivate' : 'Activate' }}"
                                                        onclick="return confirm('Are you sure you want to {{ $card->status === 'active' ? 'deactivate' : 'activate' }} this card?')">
                                                    <i class="fas fa-{{ $card->status === 'active' ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $cards->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-id-card-alt fa-3x text-muted mb-3"></i>
                    <h5>No RFID Cards Found</h5>
                    <p class="text-muted">Get started by assigning RFID cards to your tenants.</p>
                    <a href="{{ route('landlord.security.create-card', ['property_id' => $propertyId]) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> Assign First Card
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Access Logs -->
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Access Attempts</h5>
            <a href="{{ route('landlord.security.access-logs', ['property_id' => $propertyId]) }}" 
               class="btn btn-sm btn-outline-primary">
                View All
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive" id="recent-logs-container">
                <table class="table table-sm" id="recent-logs-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Card UID</th>
                            <th>Tenant</th>
                            <th>Reader</th>
                            <th>Result</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody id="recent-logs-body">
                        @forelse($recentLogs as $log)
                            <tr>
                                <td><small>{{ $log->access_time->format('M j, g:i A') }}</small></td>
                                <td><code class="small">{{ $log->card_uid }}</code></td>
                                <td><small>{{ $log->tenant_name }}</small></td>
                                <td><small>{{ $log->reader_location_display }}</small></td>
                                <td><span class="badge bg-{{ $log->display_badge_class }}">{{ $log->display_result }}</span></td>
                                <td>
                                    @if($log->denial_reason)
                                        <small class="text-muted">{{ $log->denial_reason_display }}</small>
                                    @else
                                        <small class="text-success">Access granted</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="recent-logs-empty-row">
                                <td colspan="6" class="text-center text-muted py-3">
                                    No recent access attempts yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bodyEl = document.getElementById('recent-logs-body');
    if (!bodyEl) return;
    const propertyId = @json($propertyId);

    function renderRows(logs) {
        if (!Array.isArray(logs) || logs.length === 0) {
            bodyEl.innerHTML = `
                <tr id="recent-logs-empty-row">
                    <td colspan="6" class="text-center text-muted py-3">
                        No recent access attempts yet.
                    </td>
                </tr>`;
            return;
        }

        const rows = logs.map(l => `
            <tr>
                <td><small>${l.access_time_human || ''}</small></td>
                <td><code class="small">${l.card_uid || ''}</code></td>
                <td><small>${l.tenant_name || ''}</small></td>
                <td><small>${l.reader_location_display || '—'}</small></td>
                <td><span class="badge bg-${l.result_badge_class || 'secondary'}">${l.result_text || ''}</span></td>
                <td>${l.denial_reason ? `<small class="text-muted">${l.denial_reason}</small>` : '<small class="text-success">Access granted</small>'}</td>
            </tr>`).join('');
        bodyEl.innerHTML = rows;
    }

    async function refreshLogs() {
        try {
            const params = new URLSearchParams();
            if (propertyId) params.set('property_id', propertyId);
            params.set('limit', 10);
            const res = await fetch(`/api/rfid/recent-logs?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data && data.success && Array.isArray(data.logs)) {
                renderRows(data.logs);
            }
        } catch (e) { /* silent */ }
    }

    // Initial and interval refresh (every 2s)
    refreshLogs();
    setInterval(refreshLogs, 2000);
});
</script>
@endpush

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
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    code {
        color: #e83e8c;
        font-size: 0.875em;
    }
    
    .badge {
        font-size: 0.75em;
    }
</style>
@endsection
