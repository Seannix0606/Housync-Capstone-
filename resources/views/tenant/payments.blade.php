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

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
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
                                                <tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach($bill->payments as $payment)
                                                <tr>
                                                    <td>{{ $payment->paid_at->format('M d, Y') }}</td>
                                                    <td class="text-success">₱{{ number_format($payment->amount, 2) }}</td>
                                                    <td>{{ $payment->payment_method_label }}</td>
                                                    <td>{{ $payment->reference_number ?? '—' }}</td>
                                                    <td>
                                                        @if($payment->status === 'verified')
                                                            <span class="badge bg-success">Verified</span>
                                                        @elseif($payment->status === 'rejected')
                                                            <span class="badge bg-danger">Rejected</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark">Pending review</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($bill->balance > 0)
                            <div class="mt-3 pt-3 border-top">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#proofModal-{{ $bill->id }}">
                                    <i class="fas fa-upload me-1"></i> Upload proof of payment
                                </button>
                                <span class="text-muted ms-2" style="font-size: 0.8rem;">Submit a screenshot or photo after paying so your landlord can verify.</span>
                            </div>

                            <div class="modal fade" id="proofModal-{{ $bill->id }}" tabindex="-1" aria-labelledby="proofModalLabel-{{ $bill->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="proofModalLabel-{{ $bill->id }}">
                                                <i class="fas fa-receipt me-2"></i>Proof of payment — {{ $bill->invoice_number }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('tenant.payments.submit-proof', $bill->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="proof_bill_id" value="{{ $bill->id }}">
                                            <div class="modal-body">
                                                <p class="text-muted small mb-3">Outstanding balance for this bill: <strong>₱{{ number_format($bill->balance, 2) }}</strong></p>
                                                @if($bill->landlord && $bill->landlord->landlord_instapay_quick_response_code_image_public_url)
                                                    <div class="alert alert-light border text-center mb-3">
                                                        <p class="small fw-semibold mb-2">Pay with InstaPay using your landlord&rsquo;s quick response code</p>
                                                        <img src="{{ $bill->landlord->landlord_instapay_quick_response_code_image_public_url }}" alt="Landlord InstaPay quick response code" class="img-fluid rounded border" style="max-height: 240px;" loading="lazy">
                                                    </div>
                                                @else
                                                    <div class="alert alert-secondary small mb-3 mb-0">
                                                        Your landlord has not uploaded an InstaPay quick response code yet. You can still pay in cash and upload proof below, or contact your landlord.
                                                    </div>
                                                @endif
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Amount paid (₱) <span class="text-danger">*</span></label>
                                                        <input type="number" name="amount" class="form-control" min="1" step="0.01" max="{{ $bill->balance }}" required
                                                            value="{{ old('proof_bill_id') == $bill->id ? old('amount') : number_format($bill->balance, 2, '.', '') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Payment method <span class="text-danger">*</span></label>
                                                        <select name="method" class="form-select" required>
                                                            @php
                                                                $selectedPaymentMethod = old('proof_bill_id') == $bill->id ? old('method') : 'instapay';
                                                            @endphp
                                                            <option value="cash" {{ $selectedPaymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                                                            <option value="instapay" {{ $selectedPaymentMethod === 'instapay' ? 'selected' : '' }}>InstaPay</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Reference / transaction number</label>
                                                        <input type="text" name="reference_number" class="form-control" maxlength="100" placeholder="Optional"
                                                            value="{{ old('proof_bill_id') == $bill->id ? old('reference_number') : '' }}">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Proof image <span class="text-danger">*</span></label>
                                                        <input type="file" name="proof_image" class="form-control" accept="image/jpeg,image/png,image/jpg" required>
                                                        <div class="form-text">JPEG or PNG, up to 5 MB.</div>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Notes</label>
                                                        <textarea name="notes" class="form-control" rows="2" maxlength="500" placeholder="Optional">{{ old('proof_bill_id') == $bill->id ? old('notes') : '' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane me-1"></i>Submit proof
                                                </button>
                                            </div>
                                        </form>
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
                            Pay through your agreed channel, then use <strong>Upload proof of payment</strong> on any bill with a balance so your landlord can verify and record it.
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <h4>Payment methods</h4>
                    <ul class="list-unstyled" style="font-size: 0.875rem; color: #64748b;">
                        <li class="mb-2"><i class="fas fa-money-bill-wave me-2 text-success"></i> Cash</li>
                        <li class="mb-2"><i class="fas fa-mobile-alt me-2 text-info"></i> InstaPay (scan your landlord&rsquo;s code when you open <strong>Upload proof of payment</strong>)</li>
                    </ul>
                    <p style="font-size: 0.8rem; color: #94a3b8;">
                        Your landlord may upload an InstaPay quick response image on their Payments page so you can scan it when paying.
                    </p>
                </div>
            </aside>
        </div>
    </div>

    @if(old('proof_bill_id'))
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var id = {{ (int) old('proof_bill_id') }};
                    var el = document.getElementById('proofModal-' + id);
                    if (el && typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(el).show();
                    }
                });
            </script>
        @endpush
    @endif
@endsection
