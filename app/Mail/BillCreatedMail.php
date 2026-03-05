<?php

namespace App\Mail;

use App\Models\Bill;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BillCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Bill $bill
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Bill #{$this->bill->invoice_number} - HouseSync",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bill-created',
            with: [
                'bill' => $this->bill,
                'tenantName' => $this->bill->tenant?->name ?? 'Tenant',
                'unitNumber' => $this->bill->unit?->unit_number ?? 'N/A',
                'propertyName' => $this->bill->unit?->property?->name ?? 'N/A',
            ],
        );
    }
}
