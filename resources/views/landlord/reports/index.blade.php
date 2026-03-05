@extends('layouts.landlord-app')
@section('title', 'Reports & Analytics')

@section('content')
<style>
    .report-card{background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:1.5rem}
    .report-card h3{font-size:1.1rem;font-weight:600;color:#1e293b;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem}
    .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem}
    .stat-box{background:#f8fafc;border-radius:10px;padding:1rem;text-align:center;border:1px solid #f1f5f9}
    .stat-box .stat-num{font-size:1.75rem;font-weight:700;color:#1e293b}
    .stat-box .stat-lbl{font-size:.8rem;color:#64748b;margin-top:2px}
    .stat-box.green .stat-num{color:#059669}
    .stat-box.orange .stat-num{color:#f97316}
    .stat-box.red .stat-num{color:#ef4444}
    .stat-box.blue .stat-num{color:#2563eb}
    .stat-box.purple .stat-num{color:#7c3aed}
    .progress-bar-custom{height:8px;border-radius:4px;background:#e5e7eb;overflow:hidden;margin-top:.5rem}
    .progress-bar-fill{height:100%;border-radius:4px;transition:width .4s}
    .table-report{width:100%;border-collapse:separate;border-spacing:0}
    .table-report th{background:#f8fafc;padding:.6rem .8rem;font-size:.78rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;text-align:left;border-bottom:2px solid #e5e7eb}
    .table-report td{padding:.7rem .8rem;font-size:.85rem;color:#334155;border-bottom:1px solid #f1f5f9}
    .table-report tr:hover td{background:#f8fafc}
    .chart-placeholder{background:#f8fafc;border:2px dashed #e5e7eb;border-radius:10px;padding:2rem;text-align:center;color:#94a3b8;min-height:200px;display:flex;flex-direction:column;align-items:center;justify-content:center}
    .filter-bar{display:flex;gap:1rem;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap}
    .filter-bar select{padding:.45rem .8rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.85rem;color:#334155;background:#fff}
    .badge-pill{display:inline-block;padding:2px 8px;border-radius:9999px;font-size:.72rem;font-weight:600}
    .badge-success{background:#d1fae5;color:#059669}
    .badge-warning{background:#fef3c7;color:#d97706}
    .badge-danger{background:#fee2e2;color:#ef4444}
    .badge-info{background:#dbeafe;color:#2563eb}
    .section-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem}
    @media(max-width:900px){.section-grid{grid-template-columns:1fr}}
</style>

<div class="content-header">
    <h1><i class="fas fa-chart-bar" style="color:#f97316"></i> Reports & Analytics</h1>
    <a href="{{ route('landlord.reports.export-financial', ['year' => $year]) }}" class="btn btn-primary">
        <i class="fas fa-download"></i> Export CSV
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="filter-bar">
    <form method="GET" action="{{ route('landlord.reports.index') }}" style="display:flex;gap:.75rem;align-items:center">
        <select name="year" onchange="this.form.submit()">
            @foreach($availableYears as $y)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <select name="month" onchange="this.form.submit()">
            <option value="">All Months</option>
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
            @endfor
        </select>
    </form>
</div>

{{-- FINANCIAL SUMMARY --}}
<div class="report-card">
    <h3><i class="fas fa-peso-sign" style="color:#059669"></i> Financial Summary</h3>
    <div class="stat-grid">
        <div class="stat-box green">
            <div class="stat-num">₱{{ number_format($financial['total_collected'], 2) }}</div>
            <div class="stat-lbl">Total Collected</div>
        </div>
        <div class="stat-box orange">
            <div class="stat-num">₱{{ number_format($financial['total_billed'], 2) }}</div>
            <div class="stat-lbl">Total Billed</div>
        </div>
        <div class="stat-box red">
            <div class="stat-num">₱{{ number_format($financial['total_outstanding'], 2) }}</div>
            <div class="stat-lbl">Outstanding Balance</div>
        </div>
        <div class="stat-box blue">
            <div class="stat-num">{{ $financial['collection_rate'] }}%</div>
            <div class="stat-lbl">Collection Rate</div>
            <div class="progress-bar-custom">
                <div class="progress-bar-fill" style="width:{{ $financial['collection_rate'] }}%;background:{{ $financial['collection_rate'] >= 80 ? '#059669' : ($financial['collection_rate'] >= 50 ? '#f97316' : '#ef4444') }}"></div>
            </div>
        </div>
    </div>
</div>

<div class="section-grid">
    {{-- OCCUPANCY SUMMARY --}}
    <div class="report-card">
        <h3><i class="fas fa-building" style="color:#7c3aed"></i> Occupancy Overview</h3>
        <div class="stat-grid">
            <div class="stat-box purple">
                <div class="stat-num">{{ $occupancy['occupancy_rate'] }}%</div>
                <div class="stat-lbl">Occupancy Rate</div>
                <div class="progress-bar-custom">
                    <div class="progress-bar-fill" style="width:{{ $occupancy['occupancy_rate'] }}%;background:#7c3aed"></div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-num">{{ $occupancy['total_units'] }}</div>
                <div class="stat-lbl">Total Units</div>
            </div>
        </div>
        <div style="margin-top:1rem;display:flex;gap:.75rem;flex-wrap:wrap">
            <span class="badge-pill badge-success"><i class="fas fa-circle" style="font-size:.5rem"></i> {{ $occupancy['occupied'] }} Occupied</span>
            <span class="badge-pill badge-info"><i class="fas fa-circle" style="font-size:.5rem"></i> {{ $occupancy['available'] }} Available</span>
            <span class="badge-pill badge-warning"><i class="fas fa-circle" style="font-size:.5rem"></i> {{ $occupancy['maintenance'] }} Maintenance</span>
        </div>
    </div>

    {{-- MAINTENANCE SUMMARY --}}
    <div class="report-card">
        <h3><i class="fas fa-wrench" style="color:#d97706"></i> Maintenance Summary</h3>
        <div class="stat-grid">
            <div class="stat-box">
                <div class="stat-num">{{ $maintenance['total'] }}</div>
                <div class="stat-lbl">Total Requests</div>
            </div>
            <div class="stat-box green">
                <div class="stat-num">{{ $maintenance['completed'] }}</div>
                <div class="stat-lbl">Completed</div>
            </div>
        </div>
        <div style="margin-top:.75rem;display:flex;gap:.75rem;flex-wrap:wrap">
            <span class="badge-pill badge-warning">{{ $maintenance['pending'] }} Pending</span>
            <span class="badge-pill badge-info">{{ $maintenance['in_progress'] }} In Progress</span>
            @if($maintenance['avg_resolution_days'])
                <span class="badge-pill" style="background:#f1f5f9;color:#334155">Avg Resolution: {{ $maintenance['avg_resolution_days'] }} days</span>
            @endif
            @if($maintenance['avg_rating'])
                <span class="badge-pill" style="background:#fef3c7;color:#92400e"><i class="fas fa-star"></i> {{ $maintenance['avg_rating'] }}/5</span>
            @endif
        </div>
    </div>
</div>

{{-- REVENUE TREND --}}
<div class="report-card">
    <h3><i class="fas fa-chart-line" style="color:#2563eb"></i> Revenue Trend (Last 12 Months)</h3>
    <div style="overflow-x:auto">
        <table class="table-report">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Billed</th>
                    <th>Collected</th>
                    <th>Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($revenueTrend as $entry)
                <tr>
                    <td style="font-weight:500">{{ $entry['month'] }}</td>
                    <td>₱{{ number_format($entry['billed'], 2) }}</td>
                    <td style="color:#059669;font-weight:600">₱{{ number_format($entry['collected'], 2) }}</td>
                    <td>
                        @php $rate = $entry['billed'] > 0 ? round(($entry['collected'] / $entry['billed']) * 100) : 0; @endphp
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div class="progress-bar-custom" style="flex:1;min-width:60px">
                                <div class="progress-bar-fill" style="width:{{ $rate }}%;background:{{ $rate >= 80 ? '#059669' : ($rate >= 50 ? '#f97316' : '#ef4444') }}"></div>
                            </div>
                            <span style="font-size:.8rem;font-weight:500">{{ $rate }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- PROPERTY PERFORMANCE --}}
@if(count($propertyPerformance) > 0)
<div class="report-card">
    <h3><i class="fas fa-building" style="color:#f97316"></i> Property Performance</h3>
    <div style="overflow-x:auto">
        <table class="table-report">
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Units</th>
                    <th>Occupied</th>
                    <th>Occupancy</th>
                    <th>Monthly Revenue</th>
                    <th>Maintenance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($propertyPerformance as $prop)
                <tr>
                    <td style="font-weight:500">{{ $prop['name'] }}</td>
                    <td>{{ $prop['total_units'] }}</td>
                    <td>{{ $prop['occupied_units'] }}</td>
                    <td>
                        <span class="badge-pill {{ $prop['occupancy_rate'] >= 80 ? 'badge-success' : ($prop['occupancy_rate'] >= 50 ? 'badge-warning' : 'badge-danger') }}">
                            {{ $prop['occupancy_rate'] }}%
                        </span>
                    </td>
                    <td style="color:#059669;font-weight:600">₱{{ number_format($prop['monthly_revenue'], 2) }}</td>
                    <td>{{ $prop['maintenance_count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- UPCOMING LEASE EXPIRATIONS --}}
@if($upcomingExpirations->count() > 0)
<div class="report-card">
    <h3><i class="fas fa-calendar-times" style="color:#ef4444"></i> Upcoming Lease Expirations (Next 60 Days)</h3>
    <div style="overflow-x:auto">
        <table class="table-report">
            <thead>
                <tr>
                    <th>Tenant</th>
                    <th>Unit</th>
                    <th>Property</th>
                    <th>Expiry Date</th>
                    <th>Days Left</th>
                </tr>
            </thead>
            <tbody>
                @foreach($upcomingExpirations as $assignment)
                @php $daysLeft = now()->startOfDay()->diffInDays($assignment->lease_end_date); @endphp
                <tr>
                    <td style="font-weight:500">{{ $assignment->tenant?->name ?? 'N/A' }}</td>
                    <td>{{ $assignment->unit?->unit_number ?? 'N/A' }}</td>
                    <td>{{ $assignment->unit?->property?->name ?? 'N/A' }}</td>
                    <td>{{ $assignment->lease_end_date?->format('M d, Y') }}</td>
                    <td>
                        <span class="badge-pill {{ $daysLeft <= 7 ? 'badge-danger' : ($daysLeft <= 14 ? 'badge-warning' : 'badge-info') }}">
                            {{ $daysLeft }} days
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- MAINTENANCE BY CATEGORY --}}
@if(!empty($maintenance['by_category']))
<div class="section-grid">
    <div class="report-card">
        <h3><i class="fas fa-tags" style="color:#d97706"></i> Maintenance by Category</h3>
        @foreach($maintenance['by_category'] as $category => $count)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid #f1f5f9">
            <span style="font-size:.85rem;color:#334155;text-transform:capitalize">{{ $category }}</span>
            <span style="font-size:.85rem;font-weight:600;color:#1e293b">{{ $count }}</span>
        </div>
        @endforeach
    </div>
    <div class="report-card">
        <h3><i class="fas fa-exclamation-triangle" style="color:#ef4444"></i> Maintenance by Priority</h3>
        @foreach($maintenance['by_priority'] as $priority => $count)
        @php
            $pColor = match($priority) {
                'urgent' => '#ef4444',
                'high' => '#f97316',
                'medium' => '#eab308',
                'low' => '#22c55e',
                default => '#64748b',
            };
        @endphp
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid #f1f5f9">
            <span style="font-size:.85rem;color:{{ $pColor }};text-transform:capitalize;font-weight:500">{{ $priority }}</span>
            <span style="font-size:.85rem;font-weight:600;color:#1e293b">{{ $count }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
