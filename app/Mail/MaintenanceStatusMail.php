<?php

namespace App\Mail;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MaintenanceStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MaintenanceRequest $maintenanceRequest,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Maintenance Update: {$this->maintenanceRequest->title} - HouseSync",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.maintenance-status',
            with: [
                'request' => $this->maintenanceRequest,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'ticketNumber' => $this->maintenanceRequest->ticket_number,
            ],
        );
    }
}
