<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StaffAssignedToMaintenance extends Notification
{
    use Queueable;

    public function __construct(
        protected MaintenanceRequest $maintenanceRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'staff_assigned_to_maintenance',
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'ticket_number' => $this->maintenanceRequest->ticket_number,
            'title' => $this->maintenanceRequest->title,
            'priority' => $this->maintenanceRequest->priority,
            'category' => $this->maintenanceRequest->category,
            'unit' => $this->maintenanceRequest->unit?->unit_number,
            'message' => "You have been assigned to maintenance request: \"{$this->maintenanceRequest->title}\" ({$this->maintenanceRequest->priority} priority).",
            'url' => route('staff.maintenance.show', $this->maintenanceRequest->id),
        ];
    }
}
