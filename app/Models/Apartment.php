<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string|null $description
 * @property int $landlord_id
 * @property int $total_units
 * @property array|null $amenities
 * @property string|null $contact_person
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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
        'cover_image',
        'gallery',
    ];

    protected $casts = [
        'amenities' => 'array',
        'total_units' => 'integer',
        'gallery' => 'array',
    ];

    protected $appends = ['cover_image_url', 'gallery_urls'];

    // Accessors
    public function getCoverImageUrlAttribute(): ?string
    {
        if (empty($this->cover_image)) {
            return null;
        }

        // If already starts with http/https, return as is
        if (str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }

        // Return the API URL with storage path for Railway
        return url('api/storage/' . $this->cover_image);
    }

    public function getGalleryUrlsAttribute(): array
    {
        if (empty($this->gallery) || !is_array($this->gallery)) {
            return [];
        }

        return array_map(function ($path) {
            // If already starts with http/https, return as is
            if (str_starts_with($path, 'http')) {
                return $path;
            }
            // Return the API URL with storage path for Railway
            return url('api/storage/' . $path);
        }, $this->gallery);
    }

    // Relationships
    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function rfidCards()
    {
        return $this->hasMany(RfidCard::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
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

    public function isUnderMaintenance()
    {
        return $this->status === 'maintenance';
    }

    //
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByLandlord($query, $landlordId)
    {
        return $query->where('landlord_id', $landlordId);
    }

    // Statistics
    public function getOccupiedUnitsCount()
    {
        return $this->units()->where('status', 'occupied')->count();
    }

    public function getAvailableUnitsCount()
    {
        return $this->units()->where('status', 'available')->count();
    }

    public function getMaintenanceUnitsCount()
    {
        return $this->units()->where('status', 'maintenance')->count();
    }

    public function getOccupancyRate()
    {
        $totalUnits = $this->units()->count();
        if ($totalUnits === 0) return 0;
        
        $occupiedUnits = $this->getOccupiedUnitsCount();
        return round(($occupiedUnits / $totalUnits) * 100, 2);
    }

    public function getTotalRevenue()
    {
        return $this->units()->where('status', 'occupied')->sum('rent_amount');
    }
}
