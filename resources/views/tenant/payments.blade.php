@extends('layouts.app')

@section('title', 'Payments')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/billing.css') }}">
    <style>
        .bill-card {
            background: white;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            border-left: 4px solid #e2e8f0;
            transition: all 0.2s;
        }
        .bill-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .bill-card.unpaid { border-left-color: #ef4444; }
        .bill-card.partially_paid { border-left-color: #f59e0b; }
        .bill-card.paid { border-left-color: #10b981; }
        .bill-card.overdue { border-left-color: #dc2626; }
        .bill-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .bill-invoice {
            font-weight: 600;
            color: #1e293b;
        }
        .bill-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .bill-status.paid { background: #d1fae5; color: #065f46; }
        .bill-status.unpaid { background: #fee2e2; color: #991b1b; }
        .bill-status.partially_paid { background: #fef3c7; color: #92400e; }
        .bill-status.overdue { background: #fecaca; color: #7f1d1d; }
        .bill-details {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            font-size: 0.875rem;
            color: #64748b;
        }
        .bill-amount {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }
        .bill-balance {
            font-size: 1rem;
            font-weight: 600;
        }
        .bill-balance.has-balance { color: #ef4444; }
        .bill-balance.paid { color: #10b981; }
    </style>
@endpush

@section('content')
    <div class="billing-content">
        <div class="page-header">
            <div class="page-title-section">
                <h2>My Payments</h2>
                <p>See your rent and utility bills and their status.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="financial-summary">
            <div class="summary-card outstanding">
                <div class="summary-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="summary-info">
                    <h3>Total Outstanding</h3>
                    <span class="summary-value">₱{{ number_format($summary['total_due'] ?? 0, 2) }}</span>
                </div>
            </div>
            <div class="summary-card collected">
                <div class="summary-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="summary-info">
                    <h3>Total Paid</h3>
                    <span class="summary-value">₱{{ number_format($summary['total_paid'] ?? 0, 2) }}</span>
                </div>
            </div>
            <div class="summary-card pending">
                <div class="summary-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="summary-info">
                    <h3>Upcoming Bills</h3>
                    <span class="summary-value">{{ $summary['upcoming_count'] ?? 0 }}</span>
                </div>
            </div>
            <div class="summary-card revenue">
                <div class="summary-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="summary-info">
                    <h3>Overdue Bills</h3>
                    <span class="summary-value">{{ $summary['overdue_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="billing-main">
            <div class="payments-section">
                <div class="section-header">
                    <h3>My Bills</h3>
                </div>
                
                @forelse ($bills as $bill)
                    <div class="bill-card {{ $bill->status }}">
                        <div class="bill-header">
                            <div>
                                <span class="bill-invoice">{{ $bill->invoice_number }}</span>
                                <span class="ms-2 text-muted">•</span>
                                <span class="ms-2 text-muted">{{ ucfirst($bill->type) }}</span>
                            </div>
                            <span class="bill-status {{ $bill->status }}">
                                {{ str_replace('_', ' ', ucfirst($bill->status)) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end flex-wrap gap-2">
                            <div class="bill-details">
                                <div>
                                    <i class="fas fa-building me-1"></i>
                                    @if($bill->unit)
                                        {{ $bill->unit->property->name ?? 'Property' }} - Unit {{ $bill->unit->unit_number }}
                                    @else
                                        —
                                    @endif
                                </div>
                                <div>
                                    <i class="fas fa-calendar me-1"></i>
                                    Due: {{ $bill->due_date ? $bill->due_date->format('M d, Y') : '—' }}
                                </div>
                                @if($bill->billing_period_start && $bill->billing_period_end)
                                <div>
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Period: {{ $bill->billing_period_start->format('M d') }} - {{ $bill->billing_period_end->format('M d, Y') }}
                                </div>
                                @endif
                            </div>
                            <div class="text-end">
                                <div class="bill-amount">₱{{ number_format($bill->amount, 2) }}</div>
                                @if($bill->balance > 0)
                                    <div class="bill-balance has-balance">
                                        Balance: ₱{{ number_format($bill->balance, 2) }}
                                    </div>
                                @else
                                    <div class="bill-balance paid">
                                        <i class="fas fa-check-circle me-1"></i>Fully Paid
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if($bill->description)
                            <div class="mt-2 text-muted" style="font-size: 0.85rem;">
                                <i class="fas fa-info-circle me-1"></i>{{ $bill->description }}
                            </div>
                        @endif
                        @if($bill->payments->count() > 0)
                            <div class="mt-2">
                                <a class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" href="#payments-{{ $bill->id }}">
                                    <i class="fas fa-history me-1"></i>View Payment History ({{ $bill->payments->count() }})
                                </a>
                                <div class="collapse mt-2" id="payments-{{ $bill->id }}">
                                    <div class="card card-body p-2" style="font-size: 0.85rem;">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($bill->payments as $payment)
                                                <tr>
                                                    <td>{{ $payment->paid_at->format('M d, Y') }}</td>
                                                    <td class="text-success">₱{{ number_format($payment->amount, 2) }}</td>
                                                    <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                                                    <td>{{ $payment->reference_number ?? '—' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                        <h5>No Bills Yet</h5>
                        <p class="text-muted">You don't have any bills yet. When your landlord starts billing through the system, they will appear here.</p>
                    </div>
                @endforelse
            </div>

            <aside class="billing-sidebar">
                <div class="quick-actions">
                    <h4>Payment Information</h4>
                    <div class="action-buttons">
                        <p style="font-size: 0.9rem; color: #64748b; margin: 0;">
                            This page displays your bills from your landlord. Please coordinate payment through the agreed channels with your landlord.
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <h4>Payment Methods</h4>
                    <ul class="list-unstyled" style="font-size: 0.875rem; color: #64748b;">
                        <li class="mb-2"><i class="fas fa-money-bill-wave me-2 text-success"></i> Cash</li>
                        <li class="mb-2"><i class="fas fa-university me-2 text-primary"></i> Bank Transfer</li>
                        <li class="mb-2"><i class="fas fa-mobile-alt me-2 text-info"></i> GCash</li>
                    </ul>
                    <p style="font-size: 0.8rem; color: #94a3b8;">
                        Contact your landlord for specific payment instructions and account details.
                    </p>
                </div>
            </aside>
        </div>
    </div>
@endsection
