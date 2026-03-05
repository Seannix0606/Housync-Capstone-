<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $name
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * Delegated to Profile:
 * @property string $status (via profile)
 * @property string|null $phone (via profile)
 * @property string|null $address (via profile)
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * Note: name is stored in profiles only, not in users table
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'role',
    ];

    /**
     * Eager load profile relationship based on role
     */
    protected $with = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved_at' => 'datetime',
        ];
    }

    // Relationships
    /**
     * Get properties owned by this landlord
     */
    public function properties()
    {
        return $this->hasMany(Property::class, 'landlord_id');
    }

    /**
     * Alias for backward compatibility
     * @deprecated Use properties() instead
     */
    public function apartments()
    {
        return $this->properties();
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedUsers()
    {
        return $this->hasMany(User::class, 'approved_by');
    }

    public function rfidCards()
    {
        return $this->hasMany(RfidCard::class, 'landlord_id');
    }

    public function landlordDocuments()
    {
        return $this->hasMany(LandlordDocument::class, 'landlord_id');
    }

    // Profiles
    public function landlordProfile()
    {
        return $this->hasOne(LandlordProfile::class);
    }

    public function tenantProfile()
    {
        return $this->hasOne(TenantProfile::class);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function superAdminProfile()
    {
        return $this->hasOne(SuperAdminProfile::class);
    }

    /**
     * Get the profile relation name based on role
     */
    public function getProfileRelation()
    {
        return match($this->role) {
            'super_admin' => 'superAdminProfile',
            'landlord' => 'landlordProfile',
            'tenant' => 'tenantProfile',
            'staff' => 'staffProfile',
            default => null,
        };
    }

    /**
     * Get the profile instance based on role
     */
    public function profile(): ?Model
    {
        return match($this->role) {
            'super_admin' => $this->superAdminProfile,
            'landlord' => $this->landlordProfile,
            'tenant' => $this->tenantProfile,
            'staff' => $this->staffProfile,
            default => null,
        };
    }

    // Accessors - Delegate to Profile
    public function getNameAttribute($value): string
    {
        // Ensure profile is loaded
        $relationName = $this->getProfileRelation();
        if ($relationName && !$this->relationLoaded($relationName)) {
            $this->load($relationName);
        }
        
        // Try to get name from the profile relationship
        $profile = $this->profile();
        if ($profile && isset($profile->name) && $profile->name !== 'New User') {
            return $profile->name;
        }
        
        // Fallback to the value from users table if it exists and is not "New User"
        return ($value && $value !== 'New User') ? $value : 'User';
    }

    public function getPhoneAttribute($value): ?string
    {
        return $this->profile()?->phone ?? $value;
    }

    public function getAddressAttribute($value): ?string
    {
        return $this->profile()?->address ?? $value;
    }

    public function getStatusAttribute($value): string
    {
        return $this->profile()?->status ?? $value ?? 'active';
    }

    // Landlord-specific accessors
    public function getBusinessInfoAttribute($value): ?string
    {
        if ($this->isLandlord()) {
            return $this->landlordProfile?->business_info ?? $value;
        }
        return $value;
    }

    public function getApprovedAtAttribute($value): ?\Carbon\Carbon
    {
        if ($this->isLandlord()) {
            return $this->landlordProfile?->approved_at ?? $value;
        }
        return $value;
    }

    public function getApprovedByAttribute($value): ?int
    {
        if ($this->isLandlord()) {
            return $this->landlordProfile?->approved_by ?? $value;
        }
        return $value;
    }

    public function getRejectionReasonAttribute($value): ?string
    {
        if ($this->isLandlord()) {
            return $this->landlordProfile?->rejection_reason ?? $value;
        }
        return $value;
    }

    // Staff-specific accessor
    public function getStaffTypeAttribute($value): ?string
    {
        if ($this->isStaff()) {
            return $this->staffProfile?->staff_type ?? $value;
        }
        return $value;
    }

    // Tenant assignments
    public function tenantAssignments()
    {
        return $this->hasMany(TenantAssignment::class, 'tenant_id');
    }

    public function documents()
    {
        return $this->hasMany(TenantDocument::class, 'tenant_id');
    }

    public function landlordAssignments()
    {
        return $this->hasMany(TenantAssignment::class, 'landlord_id');
    }

    // Staff assignments
    public function staffAssignments()
    {
        return $this->hasMany(StaffAssignment::class, 'staff_id');
    }

    public function landlordStaffAssignments()
    {
        return $this->hasMany(StaffAssignment::class, 'landlord_id');
    }
    
    // Maintenance requests assigned to staff
    public function assignedMaintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_staff_id');
    }

    public function verifiedDocuments()
    {
        return $this->hasMany(TenantDocument::class, 'verified_by');
    }

    // Chat relationships
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
                    ->withPivot(['role', 'last_read_at', 'unread_count', 'is_muted'])
                    ->withTimestamps();
    }

    public function conversationParticipants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function getTotalUnreadMessagesAttribute(): int
    {
        return $this->conversationParticipants()->sum('unread_count');
    }

    // Role helper methods
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isLandlord()
    {
        return $this->role === 'landlord';
    }

    public function isTenant()
    {
        return $this->role === 'tenant';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    // Status helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    // Scopes
    public function scopePendingLandlords($query)
    {
        return $query->where('role', 'landlord')
            ->whereHas('landlordProfile', function($q) {
                $q->where('status', 'pending');
            });
    }

    public function scopeApprovedLandlords($query)
    {
        return $query->where('role', 'landlord')
            ->whereHas('landlordProfile', function($q) {
                $q->where('status', 'approved');
            });
    }

    public function scopeRejectedLandlords($query)
    {
        return $query->where('role', 'landlord')
            ->whereHas('landlordProfile', function($q) {
                $q->where('status', 'rejected');
            });
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Methods - Now update profiles instead of users table
    public function approve($adminId)
    {
        if ($this->isLandlord() && $this->landlordProfile) {
            $this->landlordProfile->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $adminId,
                'rejection_reason' => null,
            ]);
        }
    }

    public function reject($adminId, $reason = null)
    {
        if ($this->isLandlord() && $this->landlordProfile) {
            $this->landlordProfile->update([
                'status' => 'rejected',
                'approved_at' => null,
                'approved_by' => $adminId,
                'rejection_reason' => $reason,
            ]);
        }
    }
    
    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Auto-load appropriate profile when user is retrieved
        static::retrieved(function ($user) {
            $relationName = $user->getProfileRelation();
            if ($relationName && !$user->relationLoaded($relationName)) {
                $user->load($relationName);
            }
        });
        
        // Create profile when user is created
        static::created(function ($user) {
            $user->createProfileIfNeeded();
        });
    }
    
    /**
     * Create profile if it doesn't exist
     * Note: This should only be called when a name is available
     */
    public function createProfileIfNeeded()
    {
        $profileClass = match($this->role) {
            'super_admin' => SuperAdminProfile::class,
            'landlord' => LandlordProfile::class,
            'tenant' => TenantProfile::class,
            'staff' => StaffProfile::class,
            default => null,
        };
        
        if ($profileClass && !$this->profile()) {
            // Only create profile if we have a proper name
            if ($this->name && $this->name !== 'New User') {
                $profileData = [
                    'user_id' => $this->id,
                    'name' => $this->name,
                ];
                
                // Add status based on role
                if ($this->role === 'landlord') {
                    $profileData['status'] = 'pending';
                } elseif ($this->role === 'tenant') {
                    $profileData['status'] = 'active';
                } elseif ($this->role === 'staff') {
                    $profileData['status'] = 'active';
                } elseif ($this->role === 'super_admin') {
                    $profileData['status'] = 'active';
                }
                
                $profileClass::create($profileData);
            }
        }
    }
}

