<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentProofSubmitted;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    /**
     * Tenant payments view (Payments page in tenant portal)
     */
    public function index()
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
    public function show($id)
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
    public function submitProof(Request $request, $billId)
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
            $proofStored = null;
            if ($request->hasFile('proof_image')) {
                $file = $request->file('proof_image');
                $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->guessExtension()));
                $extension = preg_replace('/[^a-z0-9]/', '', $extension);
                if ($extension === '') {
                    $extension = 'jpg';
                }
                $safeName = sprintf(
                    'bill-%d-tenant-%d-%s.%s',
                    $bill->id,
                    $tenantId,
                    Str::uuid()->toString(),
                    $extension
                );
                $objectPath = 'payment-proofs/'.$safeName;

                /** @var SupabaseService $supabase */
                $supabase = app(SupabaseService::class);
                $uploadResult = $supabase->uploadFile(
                    config('services.supabase.bucket'),
                    $objectPath,
                    $file->getRealPath(),
                );

                if (! empty($uploadResult['success'])) {
                    $proofStored = $uploadResult['url'] ?? null;
                }

                if (! $proofStored) {
                    Log::warning('Payment proof Supabase upload unavailable or failed; using local storage', [
                        'bill_id' => $bill->id,
                        'tenant_id' => $tenantId,
                        'message' => $uploadResult['message'] ?? null,
                    ]);
                    $proofStored = $file->storeAs('payment-proofs', $safeName, 'private');
                }
            }

            $payment = Payment::create([
                'bill_id' => $bill->id,
                'tenant_id' => $tenantId,
                'amount' => $request->amount,
                'method' => $request->method,
                'reference_number' => $request->reference_number,
                'proof_image' => $proofStored,
                'status' => 'pending_verification',
                'notes' => $request->notes,
                'paid_at' => now(),
            ]);

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
}
