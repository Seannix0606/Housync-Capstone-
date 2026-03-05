<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaintenanceStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        protected MaintenanceRequest $maintenanceRequest,
        protected string $oldStatus,
        protected string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'maintenance_status_updated',
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'ticket_number' => $this->maintenanceRequest->ticket_number,
            'title' => $this->maintenanceRequest->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "Maintenance request \"{$this->maintenanceRequest->title}\" status changed from {$this->oldStatus} to {$this->newStatus}.",
            'url' => $this->getUrl($notifiable),
        ];
    }

    protected function getUrl(object $notifiable): string
    {
        return match ($notifiable->role) {
            'landlord' => route('landlord.maintenance.show', $this->maintenanceRequest->id),
            'tenant' => route('tenant.maintenance.show', $this->maintenanceRequest->id),
            'staff' => route('staff.maintenance.show', $this->maintenanceRequest->id),
            default => '#',
        };
    }
}
