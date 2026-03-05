<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'user_id',
        'property_id',
        'title',
        'content',
        'type',
        'priority',
        'audience',
        'is_pinned',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeActive($query)
    {
        return $query->published()
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeForProperty($query, $propertyId)
    {
        return $query->where(function ($q) use ($propertyId) {
            $q->where('property_id', $propertyId)
              ->orWhereNull('property_id');
        });
    }

    public function getTypeBadgeClassAttribute()
    {
        return match ($this->type) {
            'emergency' => 'danger',
            'maintenance' => 'warning',
            'event' => 'info',
            default => 'primary',
        };
    }

    public function getPriorityBadgeClassAttribute()
    {
        return match ($this->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'low' => 'secondary',
            default => 'info',
        };
    }
}
