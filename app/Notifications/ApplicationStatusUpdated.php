<?php

namespace App\Notifications;

use App\Models\TenantAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        protected TenantAssignment $assignment,
        protected string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $unitNumber = $this->assignment->unit?->unit_number ?? 'N/A';
        $statusLabel = $this->status === 'active' ? 'approved' : $this->status;

        return [
            'type' => 'application_status_updated',
            'assignment_id' => $this->assignment->id,
            'unit_number' => $unitNumber,
            'status' => $statusLabel,
            'message' => "Your application for Unit {$unitNumber} has been {$statusLabel}.",
            'url' => route('tenant.dashboard'),
        ];
    }
}
