<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $unit_id
 * @property int $staff_id
 * @property int $landlord_id
 * @property string $staff_type
 * @property \Carbon\Carbon $assigned_at
 * @property \Carbon\Carbon $assignment_start_date
 * @property \Carbon\Carbon|null $assignment_end_date
 * @property float|null $hourly_rate
 * @property string $status
 * @property string|null $notes
 * @property string|null $generated_password
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class StaffAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'staff_id',
        'landlord_id',
        'staff_type',
        'assigned_at',
        'assignment_start_date',
        'assignment_end_date',
        'hourly_rate',
        'status',
        'notes',
        'generated_password',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'assignment_start_date' => 'date',
        'assignment_end_date' => 'date',
        'hourly_rate' => 'decimal:2',
    ];

    // Relationships
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByLandlord($query, $landlordId)
    {
        return $query->where('landlord_id', $landlordId);
    }

    public function scopeByStaffType($query, $staffType)
    {
        return $query->where('staff_type', $staffType);
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    public function isTerminated()
    {
        return $this->status === 'terminated';
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'warning',
            'terminated' => 'danger',
            default => 'secondary'
        };
    }

    public function getStaffTypeDisplayAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->staff_type));
    }

    public function getStaffTypeIconAttribute()
    {
        return match($this->staff_type) {
            'maintenance_worker' => 'mdi-wrench',
            'plumber' => 'mdi-water',
            'electrician' => 'mdi-lightning-bolt',
            'cleaner' => 'mdi-broom',
            'painter' => 'mdi-palette',
            'carpenter' => 'mdi-hammer',
            'security_guard' => 'mdi-shield',
            'gardener' => 'mdi-flower',
            default => 'mdi-account'
        };
    }
} 