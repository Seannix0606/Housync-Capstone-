<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    public Conversation $conversation;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender', 'attachments']);
        $this->conversation = $message->conversation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversation->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender_name,
            'sender_avatar' => $this->message->sender_avatar,
            'content' => $this->message->content,
            'type' => $this->message->type,
            'formatted_time' => $this->message->formatted_time,
            'created_at' => $this->message->created_at->toISOString(),
            'attachments' => $this->message->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_url' => $attachment->file_url,
                    'file_type' => $attachment->file_type,
                    'formatted_size' => $attachment->formatted_size,
                    'is_image' => $attachment->is_image,
                ];
            })->toArray(),
        ];
    }
}



