<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $conversation_id
 * @property int $user_id
 * @property string $role
 * @property \Carbon\Carbon|null $last_read_at
 * @property int $unread_count
 * @property bool $is_muted
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ConversationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'role',
        'last_read_at',
        'unread_count',
        'is_muted',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'is_muted' => 'boolean',
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    public function scopeNotMuted($query)
    {
        return $query->where('is_muted', false);
    }
}



