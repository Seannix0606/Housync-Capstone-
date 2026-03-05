<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $rfid_card_id
 * @property int $tenant_assignment_id
 * @property \Carbon\Carbon $assigned_at
 * @property \Carbon\Carbon|null $expires_at
 * @property string $status
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TenantRfidAssignment extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        // Ensure only one active assignment per card
        static::creating(function ($assignment) {
            if ($assignment->status === 'active') {
                // Deactivate any existing active assignments for this card
                static::where('rfid_card_id', $assignment->rfid_card_id)
                      ->where('status', 'active')
                      ->update(['status' => 'inactive']);
            }
        });

        static::updating(function ($assignment) {
            if ($assignment->status === 'active' && $assignment->getOriginal('status') !== 'active') {
                // Deactivate any existing active assignments for this card
                static::where('rfid_card_id', $assignment->rfid_card_id)
                      ->where('id', '!=', $assignment->id)
                      ->where('status', 'active')
                      ->update(['status' => 'inactive']);
            }
        });
    }

    protected $fillable = [
        'rfid_card_id',
        'tenant_assignment_id',
        'assigned_at',
        'expires_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function rfidCard()
    {
        return $this->belongsTo(RfidCard::class);
    }

    public function tenantAssignment()
    {
        return $this->belongsTo(TenantAssignment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeRevoked($query)
    {
        return $query->where('status', 'revoked');
    }

    public function scopeForCard($query, $cardId)
    {
        return $query->where('rfid_card_id', $cardId);
    }

    public function scopeForTenant($query, $tenantAssignmentId)
    {
        return $query->where('tenant_assignment_id', $tenantAssignmentId);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active' && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isRevoked()
    {
        return $this->status === 'revoked';
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'active' => $this->isExpired() ? 'warning' : 'success',
            'inactive' => 'secondary',
            'revoked' => 'danger',
            default => 'secondary'
        };
    }

    public function getDisplayStatusAttribute()
    {
        if ($this->status === 'active' && $this->isExpired()) {
            return 'expired';
        }
        return $this->status;
    }

    // Check if this assignment can grant access
    public function canGrantAccess()
    {
        if (!$this->isActive()) {
            return false;
        }

        // Check if the tenant assignment is still active
        $tenantAssignment = $this->tenantAssignment;
        if (!$tenantAssignment || !$tenantAssignment->isActive()) {
            return false;
        }

        // Check if the RFID card is still active
        $rfidCard = $this->rfidCard;
        if (!$rfidCard || !$rfidCard->isActive()) {
            return false;
        }

        return true;
    }

    // Get access denial reason
    public function getAccessDenialReason()
    {
        if ($this->status === 'revoked') {
            return 'card_assignment_revoked';
        }

        if ($this->status !== 'active') {
            return 'card_assignment_inactive';
        }

        if ($this->isExpired()) {
            return 'card_assignment_expired';
        }

        $tenantAssignment = $this->tenantAssignment;
        if (!$tenantAssignment || !$tenantAssignment->isActive()) {
            return 'tenant_inactive';
        }

        $rfidCard = $this->rfidCard;
        if (!$rfidCard) {
            return 'card_not_found';
        }

        if (!$rfidCard->isActive()) {
            return $rfidCard->getAccessDenialReason();
        }

        return null;
    }
}
