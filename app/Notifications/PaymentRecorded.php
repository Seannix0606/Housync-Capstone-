<?php

namespace App\Notifications;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentRecorded extends Notification
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
        return [
            'type' => 'payment_recorded',
            'payment_id' => $this->payment->id,
            'bill_id' => $this->bill->id,
            'invoice_number' => $this->bill->invoice_number,
            'amount' => $this->payment->amount,
            'bill_status' => $this->bill->status,
            'remaining_balance' => $this->bill->balance,
            'message' => "Payment of ₱" . number_format($this->payment->amount, 2) . " recorded for bill {$this->bill->invoice_number}. " .
                ($this->bill->status === 'paid' ? 'Bill is now fully paid.' : "Remaining balance: ₱" . number_format($this->bill->balance, 2) . "."),
            'url' => route('tenant.payments.show', $this->bill->id),
        ];
    }
}
