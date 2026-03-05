<?php

namespace App\Notifications;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BillCreated extends Notification
{
    use Queueable;

    public function __construct(
        protected Bill $bill
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bill_created',
            'bill_id' => $this->bill->id,
            'invoice_number' => $this->bill->invoice_number,
            'amount' => $this->bill->amount,
            'type_label' => ucfirst($this->bill->type),
            'due_date' => $this->bill->due_date?->format('M d, Y'),
            'message' => "New {$this->bill->type} bill ({$this->bill->invoice_number}) for ₱" . number_format($this->bill->amount, 2) . " due on " . ($this->bill->due_date?->format('M d, Y') ?? 'N/A') . ".",
            'url' => route('tenant.payments.show', $this->bill->id),
        ];
    }
}
