<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type
 * @property string|null $subject
 * @property int|null $apartment_id
 * @property int|null $unit_id
 * @property int|null $maintenance_request_id
 * @property string $status
 * @property string $priority
 * @property \Carbon\Carbon|null $last_message_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'subject',
        'apartment_id',
        'unit_id',
        'maintenance_request_id',
        'status',
        'priority',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // Relationships
    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
                    ->withPivot(['role', 'last_read_at', 'unread_count', 'is_muted'])
                    ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function apartment()
    {
        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeWithUnread($query, $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('unread_count', '>', 0);
        });
    }

    public function scopeDirect($query)
    {
        return $query->where('type', 'direct');
    }

    public function scopeMaintenanceTickets($query)
    {
        return $query->where('type', 'maintenance_ticket');
    }

    // Helper methods
    public function getUnreadCountFor($userId)
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        return $participant ? $participant->unread_count : 0;
    }

    public function markAsReadFor($userId)
    {
        $this->participants()
             ->where('user_id', $userId)
             ->update([
                 'unread_count' => 0,
                 'last_read_at' => now()
             ]);
        
        // Mark all messages as read
        $this->messages()
             ->where('sender_id', '!=', $userId)
             ->where('is_read', false)
             ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function incrementUnreadFor($excludeUserId = null)
    {
        $query = $this->participants();
        
        if ($excludeUserId) {
            $query->where('user_id', '!=', $excludeUserId);
        }
        
        $query->increment('unread_count');
    }

    public function getOtherParticipant($userId)
    {
        return $this->users()->where('users.id', '!=', $userId)->first();
    }

    public function isParticipant($userId)
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }

    public function addParticipant($userId, $role = 'participant')
    {
        return ConversationParticipant::firstOrCreate([
            'conversation_id' => $this->id,
            'user_id' => $userId,
        ], [
            'role' => $role,
        ]);
    }

    public function removeParticipant($userId)
    {
        return $this->participants()->where('user_id', $userId)->delete();
    }

    /**
     * Get or create a direct conversation between two users
     */
    public static function getOrCreateDirect($userId1, $userId2, $apartmentId = null)
    {
        // Find existing conversation
        $conversation = static::where('type', 'direct')
            ->whereHas('participants', function ($q) use ($userId1) {
                $q->where('user_id', $userId1);
            })
            ->whereHas('participants', function ($q) use ($userId2) {
                $q->where('user_id', $userId2);
            })
            ->first();

        if ($conversation) {
            return $conversation;
        }

        // Create new conversation
        $conversation = static::create([
            'type' => 'direct',
            'apartment_id' => $apartmentId,
            'status' => 'active',
        ]);

        $conversation->addParticipant($userId1, 'owner');
        $conversation->addParticipant($userId2, 'participant');

        return $conversation;
    }

    /**
     * Create a maintenance ticket conversation
     */
    public static function createMaintenanceTicket($subject, $unitId, $tenantId, $landlordId, $priority = 'normal')
    {
        $unit = Unit::find($unitId);
        
        $conversation = static::create([
            'type' => 'maintenance_ticket',
            'subject' => $subject,
            'apartment_id' => $unit?->apartment_id,
            'unit_id' => $unitId,
            'status' => 'active',
            'priority' => $priority,
        ]);

        $conversation->addParticipant($tenantId, 'owner');
        $conversation->addParticipant($landlordId, 'participant');

        return $conversation;
    }
}

