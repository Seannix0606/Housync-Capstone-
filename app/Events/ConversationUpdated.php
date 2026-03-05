<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Conversation $conversation;
    public int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(Conversation $conversation, int $userId)
    {
        $this->conversation = $conversation->load('latestMessage');
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId . '.conversations'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'conversation.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $otherParticipant = $this->conversation->getOtherParticipant($this->userId);
        
        return [
            'id' => $this->conversation->id,
            'type' => $this->conversation->type,
            'subject' => $this->conversation->subject,
            'status' => $this->conversation->status,
            'priority' => $this->conversation->priority,
            'unread_count' => $this->conversation->getUnreadCountFor($this->userId),
            'last_message' => $this->conversation->latestMessage ? [
                'content' => $this->conversation->latestMessage->content,
                'sender_name' => $this->conversation->latestMessage->sender_name,
                'formatted_time' => $this->conversation->latestMessage->formatted_time,
            ] : null,
            'other_participant' => $otherParticipant ? [
                'id' => $otherParticipant->id,
                'name' => $otherParticipant->name,
            ] : null,
        ];
    }
}



