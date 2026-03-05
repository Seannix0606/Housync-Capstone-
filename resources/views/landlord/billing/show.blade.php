@extends('layouts.landlord-app')

@section('title', 'Bill Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/billing.css') }}">
    <style>
        .bill-details-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .bill-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .bill-invoice {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }
        .bill-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .bill-status.paid { background: #d1fae5; color: #065f46; }
        .bill-status.unpaid { background: #fee2e2; color: #991b1b; }
        .bill-status.partially_paid { background: #fef3c7; color: #92400e; }
        .bill-status.overdue { background: #fecaca; color: #7f1d1d; }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .info-item label {
            color: #64748b;
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 600;
            display: block;
            margin-bottom: 0.25rem;
        }
        .info-item span {
            font-size: 1rem;
            color: #1e293b;
            font-weight: 500;
        }
        .amount-summary {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }
        .amount-row.total {
            border-top: 2px solid #e2e8f0;
            font-weight: 700;
            font-size: 1.25rem;
            padding-top: 1rem;
            margin-top: 0.5rem;
        }
        .payments-table {
            width: 100%;
            border-collapse: collapse;
        }
        .payments-table th, .payments-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .payments-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #64748b;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .empty-payments {
            text-align: center;
            padding: 2rem;
            color: #94a3b8;
        }

        /* ===== Dark Mode ===== */
        body.dark-mode .bill-details-card {
            background: #1e293b !important;
            color: #e2e8f0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        body.dark-mode .bill-header {
            border-color: #334155 !important;
        }

        body.dark-mode .bill-invoice {
            color: #f1f5f9 !important;
        }

        body.dark-mode .bill-status.paid { background: #064e3b; color: #6ee7b7; }
        body.dark-mode .bill-status.unpaid { background: #7f1d1d; color: #fca5a5; }
        body.dark-mode .bill-status.partially_paid { background: #78350f; color: #fbbf24; }
        body.dark-mode .bill-status.overdue { background: #450a0a; color: #fca5a5; }

        body.dark-mode .info-item label {
            color: #94a3b8 !important;
        }

        body.dark-mode .info-item span {
            color: #f1f5f9 !important;
        }

        body.dark-mode .amount-summary {
            background: #0f172a !important;
        }

        body.dark-mode .amount-row {
            color: #e2e8f0;
        }

        body.dark-mode .payments-table th {
            background: #0f172a !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        body.dark-mode .payments-table td {
            color: #e2e8f0;
            border-color: #334155 !important;
        }

        body.dark-mode .bill-details-card h4 {
            color: #f1f5f9 !important;
        }
    </style>
@endpush

@section('content')
<div class="billing-content">
    <div class="page-header">
        <div class="page-title-section">
            <a href="{{ route('landlord.payments') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                <i class="fas fa-arrow-left me-2"></i>Back to Payments
            </a>
            <h2>Bill Details</h2>
        </div>
        <div class="page-actions">
            @if($bill->status !== 'paid')
                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                    <i class="fas fa-plus me-2"></i>Record Payment
                </button>
                <form action="{{ route('landlord.billing.mark-paid', $bill->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark this bill as fully paid?');">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check me-2"></i>Mark as Paid
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="bill-details-card">
        <div class="bill-header">
            <div>
                <span class="bill-invoice">{{ $bill->invoice_number }}</span>
                <p class="text-muted mb-0">Created {{ $bill->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <span class="bill-status {{ $bill->status }}">
                {{ str_replace('_', ' ', ucfirst($bill->status)) }}
            </span>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Tenant</label>
                <span>{{ $bill->tenant->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <label>Unit</label>
                <span>{{ $bill->unit->property->name ?? '' }} - {{ $bill->unit->unit_number ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <label>Bill Type</label>
                <span>{{ ucfirst($bill->type) }}</span>
            </div>
            <div class="info-item">
                <label>Due Date</label>
                <span>{{ $bill->due_date ? $bill->due_date->format('M d, Y') : 'N/A' }}</span>
            </div>
            @if($bill->billing_period_start && $bill->billing_period_end)
            <div class="info-item">
                <label>Billing Period</label>
                <span>{{ $bill->billing_period_start->format('M d, Y') }} - {{ $bill->billing_period_end->format('M d, Y') }}</span>
            </div>
            @endif
            @if($bill->description)
            <div class="info-item">
                <label>Description</label>
                <span>{{ $bill->description }}</span>
            </div>
            @endif
        </div>

        <div class="amount-summary">
            <div class="amount-row">
                <span>Total Amount</span>
                <span>₱{{ number_format($bill->amount, 2) }}</span>
            </div>
            <div class="amount-row text-success">
                <span>Amount Paid</span>
                <span>- ₱{{ number_format($bill->amount_paid, 2) }}</span>
            </div>
            <div class="amount-row total {{ $bill->balance > 0 ? 'text-danger' : 'text-success' }}">
                <span>Outstanding Balance</span>
                <span>₱{{ number_format($bill->balance, 2) }}</span>
            </div>
        </div>

        @if($bill->notes)
        <div class="mt-3 p-3 bg-light rounded">
            <strong class="text-muted">Notes:</strong>
            <p class="mb-0">{{ $bill->notes }}</p>
        </div>
        @endif
    </div>

    <!-- Payment History -->
    <div class="bill-details-card">
        <h4 class="mb-3"><i class="fas fa-history me-2"></i>Payment History</h4>
        
        @if($bill->payments->count() > 0)
            <table class="payments-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bill->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at->format('M d, Y h:i A') }}</td>
                        <td class="fw-bold text-success">₱{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                        <td>{{ $payment->reference_number ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $payment->status === 'verified' ? 'success' : ($payment->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst(str_replace('_', ' ', $payment->status)) }}
                            </span>
                        </td>
                        <td>{{ $payment->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-payments">
                <i class="fas fa-receipt fa-3x mb-3"></i>
                <p>No payments recorded yet.</p>
            </div>
        @endif
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="recordPaymentModalLabel">
                    <i class="fas fa-money-bill-wave me-2"></i>Record Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('landlord.billing.record-payment', $bill->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>Invoice:</strong> {{ $bill->invoice_number }}<br>
                        <strong>Outstanding Balance:</strong> ₱{{ number_format($bill->balance, 2) }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" min="1" max="{{ $bill->balance }}" step="0.01" value="{{ $bill->balance }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="gcash">GCash</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Optional...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="paid_at" class="form-control" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

