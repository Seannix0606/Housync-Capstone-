<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Property Model - Represents a building/complex owned by a landlord
 * 
 * This replaces the old "Apartment" model after schema refactor.
 * Properties contain multiple Units which are the actual rentable spaces.
 * 
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $property_type
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $description
 * @property int $landlord_id
 * @property int $total_units
 * @property int|null $floors
 * @property int|null $bedrooms
 * @property int|null $year_built
 * @property int|null $parking_spaces
 * @property array|null $amenities
 * @property string|null $contact_person
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property string $status
 * @property bool $is_active
 * @property string|null $cover_image
 * @property array|null $gallery
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'property_type',
        'address',
        'city',
        'state',
        'postal_code',
        'description',
        'landlord_id',
        'total_units',
        'floors',
        'bedrooms',
        'year_built',
        'parking_spaces',
        'amenities',
        'contact_person',
        'contact_phone',
        'contact_email',
        'status',
        'is_active',
        'cover_image',
        'gallery',
    ];

    protected $casts = [
        'amenities' => 'array',
        'gallery' => 'array',
        'total_units' => 'integer',
        'floors' => 'integer',
        'bedrooms' => 'integer',
        'year_built' => 'integer',
        'parking_spaces' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = ['cover_image_url', 'gallery_urls'];

    /**
     * Boot method - auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->name);
            }
        });

        static::updating(function ($property) {
            if ($property->isDirty('name') && empty($property->slug)) {
                $property->slug = Str::slug($property->name);
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the landlord who owns this property
     */
    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    /**
     * Get all units in this property
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get RFID cards for this property
     */
    public function rfidCards()
    {
        return $this->hasMany(RfidCard::class);
    }

    /**
     * Get access logs for this property
     */
    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

    /**
     * Get staff assignments for this property
     */
    public function staffAssignments()
    {
        return $this->hasMany(StaffAssignment::class);
    }

    /**
     * Get maintenance requests for this property
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Get announcements for this property
     */
    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the cover image URL
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if (empty($this->cover_image)) {
            return null;
        }

        // If already a full URL, return as-is
        if (str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }

        // Return the API URL with storage path
        return url('api/storage/' . $this->cover_image);
    }

    /**
     * Get gallery image URLs
     */
    public function getGalleryUrlsAttribute(): array
    {
        if (empty($this->gallery) || !is_array($this->gallery)) {
            return [];
        }

        return array_map(function ($path) {
            if (str_starts_with($path, 'http')) {
                return $path;
            }
            return url('api/storage/' . $path);
        }, $this->gallery);
    }

    // ==================== SCOPES ====================

    /**
     * Scope to get active properties
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    /**
     * Scope to get properties by landlord
     */
    public function scopeByLandlord($query, $landlordId)
    {
        return $query->where('landlord_id', $landlordId);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if property is active
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->is_active;
    }

    /**
     * Check if property is inactive
     */
    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    /**
     * Check if property is under maintenance
     */
    public function isUnderMaintenance()
    {
        return $this->status === 'maintenance';
    }

    // ==================== STATISTICS ====================

    /**
     * Get count of occupied units
     */
    public function getOccupiedUnitsCount()
    {
        return $this->units()->where('status', 'occupied')->count();
    }

    /**
     * Get count of available units
     */
    public function getAvailableUnitsCount()
    {
        return $this->units()->where('status', 'available')->count();
    }

    /**
     * Get count of units under maintenance
     */
    public function getMaintenanceUnitsCount()
    {
        return $this->units()->where('status', 'maintenance')->count();
    }

    /**
     * Get occupancy rate percentage
     */
    public function getOccupancyRate()
    {
        $totalUnits = $this->units()->count();
        if ($totalUnits === 0) return 0;
        
        $occupiedUnits = $this->getOccupiedUnitsCount();
        return round(($occupiedUnits / $totalUnits) * 100, 2);
    }

    /**
     * Get total revenue from occupied units
     */
    public function getTotalRevenue()
    {
        return $this->units()->where('status', 'occupied')->sum('rent_amount');
    }

    /**
     * Get minimum rent from available units
     */
    public function getMinRent()
    {
        return $this->units()->where('status', 'available')->min('rent_amount');
    }

    /**
     * Get maximum rent from available units
     */
    public function getMaxRent()
    {
        return $this->units()->where('status', 'available')->max('rent_amount');
    }

    /**
     * Get the first available unit for this property
     * Used for tenant applications when property is selected directly
     */
    public function getUnit()
    {
        return $this->units()->where('status', 'available')->first();
    }
}
