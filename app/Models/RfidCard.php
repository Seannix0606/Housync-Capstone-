<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $card_uid
 * @property int $landlord_id
 * @property int $apartment_id
 * @property string|null $card_name
 * @property string $status
 * @property \Carbon\Carbon $issued_at
 * @property \Carbon\Carbon|null $expires_at
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class RfidCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_uid',
        'landlord_id',
        'apartment_id',
        'card_name',
        'status',
        'issued_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function tenantRfidAssignments()
    {
        return $this->hasMany(TenantRfidAssignment::class);
    }

    public function activeTenantAssignment()
    {
        return $this->hasOne(TenantRfidAssignment::class)
                    ->where('status', 'active')
                    ->with('tenantAssignment');
    }

    // Direct relationship to TenantAssignment through TenantRfidAssignment
    public function tenantAssignment()
    {
        return $this->hasOneThrough(
            TenantAssignment::class,
            TenantRfidAssignment::class,
            'rfid_card_id',        // Foreign key on TenantRfidAssignment table
            'id',                  // Foreign key on TenantAssignment table
            'id',                  // Local key on RfidCard table
            'tenant_assignment_id' // Local key on TenantRfidAssignment table
        )->where('tenant_rfid_assignments.status', 'active');
    }

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function apartment()
    {
        return $this->belongsTo(Property::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

    //--
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeCompromised($query)
    {
        return $query->whereIn('status', ['lost', 'stolen']);
    }

    public function scopeForApartment($query, $apartmentId)
    {
        return $query->where('apartment_id', $apartmentId);
    }

    public function scopeForLandlord($query, $landlordId)
    {
        return $query->where('landlord_id', $landlordId);
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
//--
    public function isCompromised()
    {
        return in_array($this->status, ['lost', 'stolen']);
    }
//--
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'active' => $this->isExpired() ? 'warning' : 'success',
            'inactive' => 'secondary',
            'lost' => 'warning',
            'stolen' => 'danger',
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

    // Check if card can grant access
    public function canGrantAccess()
    {
        if (!$this->isActive()) {
            return false;
        }

        $activeAssignment = $this->activeTenantAssignment;
        if (!$activeAssignment) {
            return false;
        }

        return $activeAssignment->canGrantAccess();
    }

    // Get access denial reason
    public function getAccessDenialReason()
    {
        if ($this->isCompromised()) {
            return 'card_' . $this->status;
        }

        if ($this->status !== 'active') {
            return 'card_inactive';
        }

        if ($this->isExpired()) {
            return 'card_expired';
        }

        $activeAssignment = $this->activeTenantAssignment;
        if (!$activeAssignment) {
            return 'card_not_assigned';
        }

        return $activeAssignment->getAccessDenialReason();
    }
}