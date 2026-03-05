<?php

namespace App\Mail;

use App\Models\TenantAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaseExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TenantAssignment $assignment,
        public int $daysRemaining
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Lease Expiry Reminder - {$this->daysRemaining} days remaining - HouseSync",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lease-expiry-reminder',
            with: [
                'assignment' => $this->assignment,
                'daysRemaining' => $this->daysRemaining,
                'tenantName' => $this->assignment->tenant?->name ?? 'Tenant',
                'unitNumber' => $this->assignment->unit?->unit_number ?? 'N/A',
                'expiryDate' => $this->assignment->lease_end_date?->format('F d, Y'),
            ],
        );
    }
}
