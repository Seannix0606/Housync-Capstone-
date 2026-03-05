<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewAnnouncement extends Notification
{
    use Queueable;

    public function __construct(
        protected Announcement $announcement
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $prefix = $this->announcement->type === 'emergency' ? '[EMERGENCY] ' : '';

        return [
            'type' => 'new_announcement',
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'announcement_type' => $this->announcement->type,
            'priority' => $this->announcement->priority,
            'property_name' => $this->announcement->property?->name,
            'message' => "{$prefix}New announcement: \"{$this->announcement->title}\"",
            'url' => $this->getUrl($notifiable),
        ];
    }

    protected function getUrl(object $notifiable): string
    {
        return match ($notifiable->role) {
            'tenant' => route('tenant.announcements.show', $this->announcement->id),
            'staff' => route('staff.announcements.show', $this->announcement->id),
            default => '#',
        };
    }
}
