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

    <!-- Recent Complete Visits -->
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Complete Visits</h5>
            <a href="{{ route('landlord.security.access-logs', ['property_id' => $propertyId]) }}" 
               class="btn btn-sm btn-outline-primary">
                View All
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-visit-pairs">
                    <thead>
                        <tr class="visit-group-header">
                            <th class="text-success">Entry Side</th>
                            <th class="visit-separator-cell"></th>
                            <th class="text-primary">Exit Side</th>
                            <th colspan="4"></th>
                        </tr>
                        <tr>
                            <th>In (Main Entrance)</th>
                            <th class="visit-separator-cell"></th>
                            <th>Out (Main Exit)</th>
                            <th>Duration</th>
                            <th>Card UID</th>
                            <th>Tenant</th>
                            <th>Visit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCompleteVisits as $visit)
                            @php
                                $in = $visit->in;
                                $out = $visit->out;
                                $secs = $in->access_time->diffInSeconds($out->access_time);
                                $mins = (int) floor($secs / 60);
                            @endphp
                            <tr>
                                <td>
                                    <small>
                                        <div>{{ $in->access_time->format('M j, Y') }}</div>
                                        <div class="text-muted">{{ $in->access_time->format('g:i:s A') }}</div>
                                    </small>
                                </td>
                                <td class="visit-separator-cell">
                                    <span class="visit-separator-line"></span>
                                </td>
                                <td>
                                    <small>
                                        <div>{{ $out->access_time->format('M j, Y') }}</div>
                                        <div class="text-muted">{{ $out->access_time->format('g:i:s A') }}</div>
                                    </small>
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
                                    <code class="small">{{ $in->card_uid }}</code>
                                </td>
                                <td>
                                    <small>
                                        @if($in->tenantAssignment)
                                            {{ $in->tenantAssignment->tenant->name }}
                                        @else
                                            Unknown
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-success">In → Out</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">
                                    No complete visits yet.
                                    <br>
                                    <small class="text-muted">A complete visit requires a granted <code>main_entrance</code> tap followed by a granted <code>main_exit</code> tap for the same card UID.</small>
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
