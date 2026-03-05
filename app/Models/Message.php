<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $conversation_id
 * @property int $sender_id
 * @property string $content
 * @property string $type
 * @property bool $is_read
 * @property \Carbon\Carbon|null $read_at
 * @property int|null $reply_to_id
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'type',
        'is_read',
        'read_at',
        'reply_to_id',
        'metadata',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $appends = ['formatted_time', 'sender_name', 'sender_avatar'];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    // Accessors
    public function getFormattedTimeAttribute()
    {
        $now = now();
        $created = $this->created_at;

        if ($created->isToday()) {
            return $created->format('g:i A');
        } elseif ($created->isYesterday()) {
            return 'Yesterday ' . $created->format('g:i A');
        } elseif ($created->isCurrentWeek()) {
            return $created->format('l g:i A');
        } else {
            return $created->format('M j, Y g:i A');
        }
    }

    public function getSenderNameAttribute()
    {
        return $this->sender?->name ?? 'Unknown User';
    }

    public function getSenderAvatarAttribute()
    {
        $name = $this->sender?->name ?? 'U';
        return strtoupper(substr($name, 0, 1));
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function hasAttachments()
    {
        return $this->attachments()->count() > 0;
    }

    public function isFromUser($userId)
    {
        return $this->sender_id === $userId;
    }

    /**
     * Create a system message in a conversation
     */
    public static function createSystemMessage($conversationId, $content, $metadata = [])
    {
        return static::create([
            'conversation_id' => $conversationId,
            'sender_id' => auth()->id() ?? 1, // System user or current user
            'content' => $content,
            'type' => 'system',
            'metadata' => $metadata,
        ]);
    }
}



