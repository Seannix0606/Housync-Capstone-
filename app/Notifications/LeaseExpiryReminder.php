<?php

namespace App\Notifications;

use App\Models\TenantAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaseExpiryReminder extends Notification
{
    use Queueable;

    public function __construct(
        protected TenantAssignment $assignment,
        protected int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $unitNumber = $this->assignment->unit?->unit_number ?? 'N/A';
        $expiryDate = $this->assignment->lease_end_date?->format('M d, Y') ?? 'N/A';
        $tenantName = $this->assignment->tenant?->name ?? 'Tenant';

        $message = $notifiable->isLandlord()
            ? "Lease for {$tenantName} (Unit {$unitNumber}) expires in {$this->daysRemaining} days ({$expiryDate})."
            : "Your lease for Unit {$unitNumber} expires in {$this->daysRemaining} days ({$expiryDate}).";

        return [
            'type' => 'lease_expiry_reminder',
            'assignment_id' => $this->assignment->id,
            'unit_number' => $unitNumber,
            'tenant_name' => $tenantName,
            'days_remaining' => $this->daysRemaining,
            'expiry_date' => $expiryDate,
            'message' => $message,
            'url' => $notifiable->isLandlord()
                ? route('landlord.assignment-details', $this->assignment->id)
                : route('tenant.dashboard'),
        ];
    }
}
