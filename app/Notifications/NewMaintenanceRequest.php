<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMaintenanceRequest extends Notification
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
        $creatorName = $this->maintenanceRequest->tenant
            ? $this->maintenanceRequest->tenant->name
            : 'Landlord';

        return [
            'type' => 'new_maintenance_request',
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'ticket_number' => $this->maintenanceRequest->ticket_number,
            'title' => $this->maintenanceRequest->title,
            'priority' => $this->maintenanceRequest->priority,
            'category' => $this->maintenanceRequest->category,
            'created_by' => $creatorName,
            'message' => "New {$this->maintenanceRequest->priority} priority maintenance request: \"{$this->maintenanceRequest->title}\" from {$creatorName}.",
            'url' => route('landlord.maintenance.show', $this->maintenanceRequest->id),
        ];
    }
}
