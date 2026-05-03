@extends('layouts.landlord-app')

@section('title', 'Payments')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/billing.css') }}">
    <style>
        .create-bill-modal .modal-content {
            border-radius: 12px;
            border: none;
        }
        .create-bill-modal .modal-header {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            border-radius: 12px 12px 0 0;
        }
        .create-bill-modal .btn-close {
            filter: brightness(0) invert(1);
        }
        .btn-create-bill {
            background: linear-gradient(135deg, #f97316, #ea580c);
            border: none;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-create-bill:hover {
            background: linear-gradient(135deg, #ea580c, #dc2626);
            color: white;
            transform: translateY(-1px);
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        .status.paid { background: #d1fae5; color: #065f46; }
        .status.unpaid { background: #fee2e2; color: #991b1b; }
        .status.partially_paid { background: #fef3c7; color: #92400e; }
        .status.overdue { background: #fecaca; color: #7f1d1d; }
    </style>
@endpush

@section('content')
    <div class="billing-content">
        <div class="page-header">
            <div class="page-title-section">
                <h2>Payments &amp; Billing</h2>
                <p>Track rent and utility bills across your units.</p>
            </div>
            <div class="page-actions">
                <button type="button" class="btn btn-create-bill" data-bs-toggle="modal" data-bs-target="#createBillModal">
                    <i class="fas fa-plus me-2"></i>Create Bill
                </button>
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
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="financial-summary">
            <div class="summary-card revenue">
                <div class="summary-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="summary-info">
                    <h3>Total Billed</h3>
                    <span class="summary-value">₱{{ number_format($summary['total_amount'] ?? 0, 2) }}</span>
                </div>
            </div>
            <div class="summary-card collected">
                <div class="summary-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="summary-info">
                    <h3>Collected</h3>
                    <span class="summary-value">₱{{ number_format($summary['total_collected'] ?? 0, 2) }}</span>
                </div>
            </div>
            <div class="summary-card outstanding">
                <div class="summary-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="summary-info">
                    <h3>Outstanding</h3>
                    <span class="summary-value">₱{{ number_format($summary['total_outstanding'] ?? 0, 2) }}</span>
                </div>
            </div>
            <div class="summary-card pending">
                <div class="summary-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="summary-info">
                    <h3>Unpaid / Partial Bills</h3>
                    <span class="summary-value">{{ $summary['pending_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-3 d-flex gap-2 flex-wrap">
            <a href="{{ route('landlord.payments') }}" class="btn btn-sm {{ !$status && !$type ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
            <a href="{{ route('landlord.payments', ['status' => 'unpaid']) }}" class="btn btn-sm {{ $status === 'unpaid' ? 'btn-danger' : 'btn-outline-danger' }}">Unpaid</a>
            <a href="{{ route('landlord.payments', ['status' => 'partially_paid']) }}" class="btn btn-sm {{ $status === 'partially_paid' ? 'btn-warning' : 'btn-outline-warning' }}">Partial</a>
            <a href="{{ route('landlord.payments', ['status' => 'paid']) }}" class="btn btn-sm {{ $status === 'paid' ? 'btn-success' : 'btn-outline-success' }}">Paid</a>
            <span class="border-start mx-2"></span>
            <a href="{{ route('landlord.payments', ['type' => 'rent']) }}" class="btn btn-sm {{ $type === 'rent' ? 'btn-info' : 'btn-outline-info' }}">Rent</a>
            <a href="{{ route('landlord.payments', ['type' => 'electricity']) }}" class="btn btn-sm {{ $type === 'electricity' ? 'btn-info' : 'btn-outline-info' }}">Electric</a>
            <a href="{{ route('landlord.payments', ['type' => 'water']) }}" class="btn btn-sm {{ $type === 'water' ? 'btn-info' : 'btn-outline-info' }}">Water</a>
        </div>

        <div class="billing-main">
            <div class="payments-section">
                <div class="section-header">
                    <h3>Recent Bills</h3>
                </div>
                <div class="payments-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Tenant</th>
                                <th>Unit</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Receipt</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($bills as $bill)
                            <tr>
                                <td class="invoice-number">{{ $bill->invoice_number }}</td>
                                <td>{{ optional($bill->tenant)->name ?? '—' }}</td>
                                <td>
                                    @if($bill->unit)
                                        {{ $bill->unit->unit_number }} ({{ $bill->unit->property->name ?? 'Property' }})
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ ucfirst($bill->type) }}</td>
                                <td class="amount">₱{{ number_format($bill->amount, 2) }}</td>
                                <td class="amount">₱{{ number_format($bill->balance, 2) }}</td>
                                <td>
                                    <span class="status {{ $bill->status }}">
                                        {{ str_replace('_', ' ', ucfirst($bill->status)) }}
                                    </span>
                                </td>
                                <td>{{ $bill->due_date ? $bill->due_date->format('M d, Y') : '—' }}</td>
                                <td>
                                    @if(($bill->tenant_proof_count ?? 0) > 0)
                                        <a href="{{ route('landlord.billing.show', $bill->id) }}#payment-history" class="btn btn-sm btn-outline-secondary" title="View tenant payment receipt">
                                            <i class="fas fa-receipt"></i> View
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="action-buttons">
                                    <a href="{{ route('landlord.billing.show', $bill->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($bill->status !== 'paid')
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#recordPaymentModal"
                                                data-bill-id="{{ $bill->id }}"
                                                data-bill-balance="{{ $bill->balance }}"
                                                data-bill-invoice="{{ $bill->invoice_number }}"
                                                title="Record Payment">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <form action="{{ route('landlord.billing.mark-paid', $bill->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark this bill as fully paid?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Mark as Paid">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($bill->amount_paid == 0)
                                        <form action="{{ route('landlord.billing.destroy', $bill->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this bill? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="padding: 1.5rem; vertical-align: middle;">
                                    <x-empty-state-card
                                        class="empty-state-card--wide"
                                        icon="fas fa-file-invoice-dollar"
                                        title="No bills yet"
                                        description="Create a bill to start tracking rent and utilities."
                                    >
                                        <x-slot:action>
                                            <button type="button" class="btn btn-create-bill" data-bs-toggle="modal" data-bs-target="#createBillModal">
                                                <i class="fas fa-plus me-2"></i>Create bill
                                            </button>
                                        </x-slot:action>
                                    </x-empty-state-card>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($bills, 'links'))
                    <div class="mt-3 px-4">
                        {{ $bills->links() }}
                    </div>
                @endif
            </div>

            <aside class="billing-sidebar">
                <div class="quick-actions">
                    <h4>Quick Actions</h4>
                    <div class="action-buttons d-grid gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBillModal">
                            <i class="fas fa-plus me-2"></i>Create New Bill
                        </button>
                    </div>
                </div>
                @auth
                    @php
                        $currentLandlordUser = auth()->user();
                        $instapayCodePublicUrl = $currentLandlordUser->landlord_instapay_quick_response_code_image_public_url;
                    @endphp
                    <div class="mt-4 p-3 border rounded bg-white shadow-sm">
                        <h4 class="h6 mb-2"><i class="fas fa-qrcode me-2 text-primary"></i>InstaPay quick response code</h4>
                        <p class="small text-muted mb-3">Tenants see this on their Payments page when paying online. Same storage as payment proof images (Supabase when configured, otherwise private app storage).</p>
                        @if($instapayCodePublicUrl)
                            <div class="text-center my-3">
                                <img src="{{ $instapayCodePublicUrl }}" alt="Your InstaPay quick response code for tenants" class="img-fluid rounded border" style="max-height: 220px;" loading="lazy">
                            </div>
                            <form action="{{ route('landlord.payments.instapay-code.destroy') }}" method="POST" class="mb-3" onsubmit="return confirm('Remove this image? Tenants will no longer see it.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">Remove image</button>
                            </form>
                        @endif
                        <form action="{{ route('landlord.payments.instapay-code.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label small">{{ $instapayCodePublicUrl ? 'Replace image' : 'Upload image' }} <span class="text-danger">*</span></label>
                            <input type="file" name="instapay_quick_response_code_image" class="form-control form-control-sm" accept="image/jpeg,image/png,image/jpg" required>
                            <div class="form-text small">JPEG or PNG, up to 5 MB.</div>
                            <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">
                                <i class="fas fa-save me-1"></i>{{ $instapayCodePublicUrl ? 'Save new image' : 'Save' }}
                            </button>
                        </form>
                    </div>
                @endauth
                <div class="mt-4">
                    <h4>Bill Types</h4>
                    <ul class="list-unstyled" style="font-size: 0.9rem; color: #64748b;">
                        <li><i class="fas fa-home me-2 text-primary"></i> Rent - Monthly rental payments</li>
                        <li><i class="fas fa-bolt me-2 text-warning"></i> Electricity - Power bills</li>
                        <li><i class="fas fa-tint me-2 text-info"></i> Water - Water utility bills</li>
                        <li><i class="fas fa-ellipsis-h me-2 text-secondary"></i> Other - Misc charges</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>

    <!-- Create Bill Modal -->
    <div class="modal fade create-bill-modal" id="createBillModal" tabindex="-1" aria-labelledby="createBillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createBillModalLabel">
                        <i class="fas fa-file-invoice me-2"></i>Create New Bill
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('landlord.billing.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tenant / Unit <span class="text-danger">*</span></label>
                                <select name="tenant_assignment_id" class="form-select" required>
                                    <option value="">Select tenant...</option>
                                    @foreach($tenantAssignments ?? [] as $assignment)
                                        <option value="{{ $assignment->id }}">
                                            {{ $assignment->tenant->name ?? 'Unknown' }} - 
                                            {{ $assignment->unit->property->name ?? '' }} Unit {{ $assignment->unit->unit_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bill Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="rent">Rent</option>
                                    <option value="electricity">Electricity</option>
                                    <option value="water">Water</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount (₱) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" min="1" step="0.01" required placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" required min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Billing Period Start</label>
                                <input type="date" name="billing_period_start" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Billing Period End</label>
                                <input type="date" name="billing_period_end" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control" placeholder="Optional description...">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes (optional)..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Bill
                        </button>
                    </div>
                </form>
            </div>
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
                <form id="recordPaymentForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <strong>Invoice:</strong> <span id="paymentInvoiceNumber"></span><br>
                            <strong>Outstanding Balance:</strong> ₱<span id="paymentBalance"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Amount (₱) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="paymentAmount" class="form-control" min="1" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="instapay">InstaPay</option>
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

@push('scripts')
<script>
    // Handle Record Payment Modal
    document.getElementById('recordPaymentModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const billId = button.getAttribute('data-bill-id');
        const billBalance = button.getAttribute('data-bill-balance');
        const billInvoice = button.getAttribute('data-bill-invoice');
        
        document.getElementById('recordPaymentForm').action = `/landlord/billing/${billId}/payment`;
        document.getElementById('paymentInvoiceNumber').textContent = billInvoice;
        document.getElementById('paymentBalance').textContent = parseFloat(billBalance).toLocaleString('en-PH', {minimumFractionDigits: 2});
        document.getElementById('paymentAmount').max = billBalance;
        document.getElementById('paymentAmount').value = billBalance;
    });
</script>
@endpush
