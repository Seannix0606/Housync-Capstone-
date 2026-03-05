<?php

namespace App\Notifications;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentProofSubmitted extends Notification
{
    use Queueable;

    public function __construct(
        protected Payment $payment,
        protected Bill $bill
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $tenantName = $this->payment->tenant?->name ?? 'A tenant';

        return [
            'type' => 'payment_proof_submitted',
            'payment_id' => $this->payment->id,
            'bill_id' => $this->bill->id,
            'invoice_number' => $this->bill->invoice_number,
            'amount' => $this->payment->amount,
            'tenant_name' => $tenantName,
            'message' => "{$tenantName} submitted payment proof of ₱" . number_format($this->payment->amount, 2) . " for bill {$this->bill->invoice_number}. Please verify.",
            'url' => route('landlord.billing.show', $this->bill->id),
        ];
    }
}
