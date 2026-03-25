<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\TenantAssignment;
use App\Models\User;
use App\Notifications\BillCreated;
use App\Notifications\PaymentProofSubmitted;
use App\Notifications\PaymentRecorded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    /**
     * Landlord billing overview (Payments page in landlord portal)
     */
    public function landlordIndex(Request $request)
    {
        $landlordId = Auth::id();

        // Basic filters (status / type) kept simple to avoid errors
        $status = $request->query('status');
        $type = $request->query('type');

        $query = Bill::forLandlord($landlordId)->with(['tenant', 'unit.property']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $bills = $query->orderByDesc('due_date')->orderByDesc('created_at')->paginate(10);

        // Simple summary cards (use null coalescing to avoid errors on empty data)
        $summary = [
            'total_amount' => Bill::forLandlord($landlordId)->sum('amount') ?? 0,
            'total_collected' => Bill::forLandlord($landlordId)->sum('amount_paid') ?? 0,
            'total_outstanding' => Bill::forLandlord($landlordId)->sum('balance') ?? 0,
            'pending_count' => Bill::forLandlord($landlordId)->where('status', 'unpaid')->orWhere('status', 'partially_paid')->count(),
        ];

        // Get active tenant assignments for the "Create Bill" dropdown
        $tenantAssignments = TenantAssignment::where('landlord_id', $landlordId)
            ->where('status', 'active')
            ->with(['tenant', 'unit.property'])
            ->get();

        return view('landlord.billing', compact('bills', 'summary', 'status', 'type', 'tenantAssignments'));
    }

    /**
     * Show form to create a new bill
     */
    public function create()
    {
        $landlordId = Auth::id();

        // Get active tenant assignments
        $tenantAssignments = TenantAssignment::where('landlord_id', $landlordId)
            ->where('status', 'active')
            ->with(['tenant', 'unit.property'])
            ->get();

        if ($tenantAssignments->isEmpty()) {
            return redirect()->route('landlord.payments')
                ->with('error', 'You need active tenant assignments before creating bills.');
        }

        return view('landlord.billing.create', compact('tenantAssignments'));
    }

    /**
     * Store a new bill
     */
    public function store(Request $request)
    {
        $landlordId = Auth::id();

        $request->validate([
            'tenant_assignment_id' => [
                'required',
                Rule::exists('tenant_assignments', 'id')
                    ->where('landlord_id', $landlordId)
                    ->where('status', 'active'),
            ],
            'type' => 'required|in:rent,electricity,water,other',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date|after_or_equal:today',
            'billing_period_start' => 'nullable|date',
            'billing_period_end' => 'nullable|date|after_or_equal:billing_period_start',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $bill = null;
        $assignment = null;

        try {
            DB::transaction(function () use ($request, $landlordId, &$bill, &$assignment) {
                // Re-read with a row lock so no concurrent request can terminate or
                // reassign this row between validation and the insert below.
                $assignment = TenantAssignment::where('id', $request->tenant_assignment_id)
                    ->where('landlord_id', $landlordId)
                    ->where('status', 'active')
                    ->with(['tenant', 'unit'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $invoiceNumber = 'INV-'.strtoupper(Str::random(8));

                while (Bill::where('invoice_number', $invoiceNumber)->exists()) {
                    $invoiceNumber = 'INV-'.strtoupper(Str::random(8));
                }

                $bill = Bill::create([
                    'landlord_id' => $landlordId,
                    'tenant_id' => $assignment->tenant_id,
                    'tenant_assignment_id' => $assignment->id,
                    'unit_id' => $assignment->unit_id,
                    'invoice_number' => $invoiceNumber,
                    'type' => $request->type,
                    'description' => $request->description ?? ucfirst($request->type).' bill for '.$assignment->unit->unit_number,
                    'billing_period_start' => $request->billing_period_start,
                    'billing_period_end' => $request->billing_period_end,
                    'amount' => $request->amount,
                    'amount_paid' => 0,
                    'balance' => $request->amount,
                    'status' => 'unpaid',
                    'due_date' => $request->due_date,
                    'currency' => 'PHP',
                    'notes' => $request->notes,
                ]);
            });

            Log::info('Bill created', [
                'bill_id' => $bill->id,
                'landlord_id' => $landlordId,
                'tenant_id' => $assignment->tenant_id,
                'amount' => $request->amount,
            ]);

            if ($assignment->tenant) {
                $assignment->tenant->notify(new BillCreated($bill));
            }

            ActivityLog::log('bill_created', "Created bill {$bill->invoice_number} for ₱".number_format($request->amount, 2), $bill);

            return redirect()->route('landlord.payments')
                ->with('success', "Bill #{$bill->invoice_number} created successfully for ₱".number_format($request->amount, 2));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return back()->withErrors([
                'tenant_assignment_id' => 'The selected tenant assignment is no longer active.',
            ]);
        } catch (\Exception $exception) {
            Log::error('Failed to create bill', [
                'error' => $exception->getMessage(),
                'landlord_id' => $landlordId,
            ]);

            return back()->with('error', 'Failed to create bill. Please try again.');
        }
    }

    /**
     * Show bill details
     */
    public function show($id)
    {
        $landlordId = Auth::id();

        $bill = Bill::forLandlord($landlordId)
            ->with(['tenant', 'unit.property', 'payments'])
            ->findOrFail($id);

        return view('landlord.billing.show', compact('bill'));
    }

    /**
     * Record a payment for a bill
     */
    public function recordPayment(Request $request, $billId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,bank_transfer,gcash,other',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'paid_at' => 'nullable|date|before_or_equal:today',
        ]);

        $landlordId = Auth::id();

        $bill = Bill::forLandlord($landlordId)->findOrFail($billId);

        // Validate payment amount
        if ($request->amount > $bill->balance) {
            return back()->with('error', 'Payment amount cannot exceed the outstanding balance of ₱'.number_format($bill->balance, 2));
        }

        try {
            DB::transaction(function () use ($request, $bill) {
                // Create payment record
                $payment = Payment::create([
                    'bill_id' => $bill->id,
                    'tenant_id' => $bill->tenant_id,
                    'amount' => $request->amount,
                    'method' => $request->method,
                    'reference_number' => $request->reference_number,
                    'status' => 'verified', // Auto-verify landlord-recorded payments
                    'notes' => $request->notes,
                    'paid_at' => $request->paid_at ?? now(),
                ]);

                // Update bill
                $newAmountPaid = $bill->amount_paid + $request->amount;
                $newBalance = $bill->amount - $newAmountPaid;

                $newStatus = 'unpaid';
                if ($newBalance <= 0) {
                    $newStatus = 'paid';
                    $newBalance = 0;
                } elseif ($newAmountPaid > 0) {
                    $newStatus = 'partially_paid';
                }

                $bill->update([
                    'amount_paid' => $newAmountPaid,
                    'balance' => $newBalance,
                    'status' => $newStatus,
                    'paid_at' => $newStatus === 'paid' ? now() : null,
                ]);
            });

            // Notify tenant about the payment
            $bill->refresh();
            if ($bill->tenant_id) {
                $tenant = User::find($bill->tenant_id);
                $latestPayment = $bill->payments()->latest()->first();
                if ($tenant && $latestPayment) {
                    $tenant->notify(new PaymentRecorded($latestPayment, $bill));
                }
            }

            ActivityLog::log('payment_recorded', 'Recorded payment of ₱'.number_format($request->amount, 2)." for bill {$bill->invoice_number}", $bill);

            return back()->with('success', 'Payment of ₱'.number_format($request->amount, 2).' recorded successfully.');

        } catch (\Exception $exception) {
            Log::error('Failed to record payment', [
                'error' => $exception->getMessage(),
                'bill_id' => $billId,
            ]);

            return back()->with('error', 'Failed to record payment. Please try again.');
        }
    }

    /**
     * Mark bill as paid (quick action)
     */
    public function markAsPaid($billId)
    {
        $landlordId = Auth::id();
        $bill = Bill::forLandlord($landlordId)->findOrFail($billId);

        if ($bill->status === 'paid') {
            return back()->with('info', 'This bill is already marked as paid.');
        }

        try {
            DB::transaction(function () use ($bill) {
                // Create a payment record for the remaining balance
                if ($bill->balance > 0) {
                    Payment::create([
                        'bill_id' => $bill->id,
                        'tenant_id' => $bill->tenant_id,
                        'amount' => $bill->balance,
                        'method' => 'cash',
                        'status' => 'verified',
                        'notes' => 'Marked as paid by landlord',
                        'paid_at' => now(),
                    ]);
                }

                $bill->update([
                    'amount_paid' => $bill->amount,
                    'balance' => 0,
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            });

            return back()->with('success', 'Bill marked as paid successfully.');

        } catch (\Exception $exception) {
            Log::error('Failed to mark bill as paid', ['error' => $exception->getMessage(), 'bill_id' => $billId]);

            return back()->with('error', 'Failed to update bill. Please try again.');
        }
    }

    /**
     * Delete a bill (only if unpaid)
     */
    public function destroy($billId)
    {
        $landlordId = Auth::id();
        $bill = Bill::forLandlord($landlordId)->findOrFail($billId);

        if ($bill->status === 'paid' || $bill->amount_paid > 0) {
            return back()->with('error', 'Cannot delete a bill that has payments recorded.');
        }

        try {
            $invoiceNumber = $bill->invoice_number;
            $bill->delete();

            return redirect()->route('landlord.payments')
                ->with('success', "Bill #{$invoiceNumber} deleted successfully.");

        } catch (\Exception $exception) {
            Log::error('Failed to delete bill', ['error' => $exception->getMessage(), 'bill_id' => $billId]);

            return back()->with('error', 'Failed to delete bill. Please try again.');
        }
    }

    /**
     * Tenant payments view (Payments page in tenant portal)
     */
    public function tenantIndex()
    {
        $tenantId = Auth::id();

        $bills = Bill::forTenant($tenantId)
            ->with(['unit.property', 'landlord', 'payments'])
            ->orderByDesc('due_date')
            ->orderByDesc('created_at')
            ->get();

        $summary = [
            'total_due' => $bills->sum('balance'),
            'total_paid' => $bills->sum('amount_paid'),
            'upcoming_count' => $bills->where('status', 'unpaid')->count(),
            'overdue_count' => $bills->where('status', 'overdue')->count(),
        ];

        return view('tenant.payments', compact('bills', 'summary'));
    }

    /**
     * Tenant view bill details
     */
    public function tenantShowBill($id)
    {
        $tenantId = Auth::id();

        $bill = Bill::forTenant($tenantId)
            ->with(['unit.property', 'landlord', 'payments'])
            ->findOrFail($id);

        return view('tenant.billing.show', compact('bill'));
    }

    /**
     * Tenant submits payment proof for a bill.
     */
    public function submitPaymentProof(Request $request, $billId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,bank_transfer,gcash,other',
            'reference_number' => 'nullable|string|max:100',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $tenantId = Auth::id();
        $bill = Bill::forTenant($tenantId)->findOrFail($billId);

        if ($bill->status === 'paid') {
            return back()->with('error', 'This bill is already fully paid.');
        }

        if ($request->amount > $bill->balance) {
            return back()->with('error', 'Payment amount cannot exceed the outstanding balance.');
        }

        try {
            $proofPath = null;
            if ($request->hasFile('proof_image')) {
                $proofPath = $request->file('proof_image')->store('payment-proofs', 'public');
            }

            $payment = Payment::create([
                'bill_id' => $bill->id,
                'tenant_id' => $tenantId,
                'amount' => $request->amount,
                'method' => $request->method,
                'reference_number' => $request->reference_number,
                'proof_image' => $proofPath,
                'status' => 'pending',
                'notes' => $request->notes,
                'paid_at' => now(),
            ]);

            // Notify landlord
            $landlord = User::find($bill->landlord_id);
            if ($landlord) {
                $landlord->notify(new PaymentProofSubmitted($payment, $bill));
            }

            ActivityLog::log('payment_proof_submitted', 'Submitted payment proof of ₱'.number_format($request->amount, 2), $bill);

            return back()->with('success', 'Payment proof submitted! Your landlord will verify it shortly.');

        } catch (\Exception $exception) {
            Log::error('Failed to submit payment proof', ['error' => $exception->getMessage()]);

            return back()->with('error', 'Failed to submit payment proof. Please try again.');
        }
    }

    /**
     * Landlord verifies a tenant-submitted payment.
     */
    public function verifyPayment(Request $request, $paymentId)
    {
        $request->validate([
            'action' => 'required|in:verify,reject',
        ]);

        $landlordId = Auth::id();

        $payment = Payment::whereHas('bill', function ($query) use ($landlordId) {
            $query->where('landlord_id', $landlordId);
        })->findOrFail($paymentId);

        $bill = $payment->bill;

        if ($request->action === 'verify') {
            DB::transaction(function () use ($payment, $bill, $landlordId) {
                $payment->update([
                    'status' => 'verified',
                    'verified_by' => $landlordId,
                    'verified_at' => now(),
                ]);

                $newAmountPaid = $bill->amount_paid + $payment->amount;
                $newBalance = $bill->amount - $newAmountPaid;

                $newStatus = 'unpaid';
                if ($newBalance <= 0) {
                    $newStatus = 'paid';
                    $newBalance = 0;
                } elseif ($newAmountPaid > 0) {
                    $newStatus = 'partially_paid';
                }

                $bill->update([
                    'amount_paid' => $newAmountPaid,
                    'balance' => $newBalance,
                    'status' => $newStatus,
                    'paid_at' => $newStatus === 'paid' ? now() : null,
                ]);
            });

            $bill->refresh();
            if ($bill->tenant_id) {
                $tenant = User::find($bill->tenant_id);
                if ($tenant) {
                    $tenant->notify(new PaymentRecorded($payment, $bill));
                }
            }

            ActivityLog::log('payment_verified', 'Verified payment of ₱'.number_format($payment->amount, 2), $bill);

            return back()->with('success', 'Payment verified successfully.');
        }

        // Reject
        $payment->update(['status' => 'rejected']);
        ActivityLog::log('payment_rejected', "Rejected payment proof for bill {$bill->invoice_number}", $bill);

        return back()->with('success', 'Payment proof rejected.');
    }
}
