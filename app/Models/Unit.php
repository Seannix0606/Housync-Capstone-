<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Unit Model - Represents a rentable space within a Property
 * 
 * @property int $id
 * @property string $unit_number
 * @property int $property_id
 * @property string $unit_type
 * @property float $rent_amount
 * @property string $status
 * @property string $leasing_type
 * @property int $tenant_count
 * @property int|null $max_occupants
 * @property int|null $floor_number
 * @property string|null $description
 * @property float|null $floor_area
 * @property int $bedrooms
 * @property int $bathrooms
 * @property bool $is_furnished
 * @property array|null $amenities
 * @property string|null $notes
 * @property string|null $cover_image
 * @property array|null $gallery
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_number',
        'property_id',
        'unit_type',
        'rent_amount',
        'status',
        'leasing_type',
        'tenant_count',
        'max_occupants',
        'floor_number',
        'description',
        'floor_area',
        'bedrooms',
        'bathrooms',
        'is_furnished',
        'amenities',
        'notes',
        'cover_image',
        'gallery',
    ];

    protected $casts = [
        'rent_amount' => 'decimal:2',
        'floor_area' => 'decimal:2',
        'is_furnished' => 'boolean',
        'amenities' => 'array',
        'gallery' => 'array',
        'tenant_count' => 'integer',
        'max_occupants' => 'integer',
        'floor_number' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
    ];

    protected $appends = ['cover_image_url', 'gallery_urls'];

    // ==================== ACCESSORS ====================

    /**
     * Get the cover image URL
     */
    public function getCoverImageUrlAttribute()
    {
        if (empty($this->cover_image)) {
            return null;
        }

        if (str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }

        return url('api/storage/' . $this->cover_image);
    }

    /**
     * Get gallery image URLs
     */
    public function getGalleryUrlsAttribute()
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

    /**
     * Get formatted rent amount
     */
    public function getFormattedRentAttribute()
    {
        return '₱' . number_format($this->rent_amount, 2);
    }

    /**
     * Check if unit is available
     */
    public function getIsAvailableAttribute()
    {
        return $this->status === 'available';
    }

    /**
     * Get status badge CSS class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'occupied' => 'occupied',
            'available' => 'available',
            'maintenance' => 'maintenance',
            default => 'available'
        };
    }

    /**
     * Get leasing type label
     */
    public function getLeasingTypeLabelAttribute()
    {
        return match($this->leasing_type) {
            'separate' => 'Separate Bills',
            'inclusive' => 'All Inclusive',
            default => 'Separate Bills'
        };
    }

    /**
     * Get leasing type description
     */
    public function getLeasingTypeDescriptionAttribute()
    {
        return match($this->leasing_type) {
            'separate' => 'Tenant pays rent + utilities separately',
            'inclusive' => 'Rent includes all utilities and bills',
            default => 'Tenant pays rent + utilities separately'
        };
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the property this unit belongs to
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Alias for backward compatibility - maps to property()
     * @deprecated Use property() instead
     */
    public function apartment()
    {
        return $this->property();
    }

    /**
     * Get the current tenant assignment
     */
    public function tenantAssignment()
    {
        return $this->hasOne(TenantAssignment::class);
    }

    /**
     * Get all tenant assignments (history)
     */
    public function tenantAssignments()
    {
        return $this->hasMany(TenantAssignment::class);
    }

    /**
     * Get current tenant through assignment
     */
    public function currentTenant()
    {
        return $this->hasOneThrough(
            User::class, 
            TenantAssignment::class, 
            'unit_id', 
            'id', 
            'id', 
            'tenant_id'
        )->where('tenant_assignments.status', 'active');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get the landlord through property
     */
    public function getLandlord()
    {
        return $this->property ? $this->property->landlord : null;
    }

    /**
     * Check if leasing is inclusive
     */
    public function isInclusiveLeasing()
    {
        return $this->leasing_type === 'inclusive';
    }

    /**
     * Check if leasing is separate
     */
    public function isSeparateLeasing()
    {
        return $this->leasing_type === 'separate';
    }

    // ==================== SCOPES ====================

    /**
     * Scope to get available units
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to get occupied units
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope to get units under maintenance
     */
    public function scopeUnderMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('unit_type', $type);
    }

    /**
     * Scope to filter by rent range
     */
    public function scopeRentRange($query, $min, $max)
    {
        return $query->whereBetween('rent_amount', [$min, $max]);
    }

    /**
     * Scope to get units by property
     */
    public function scopeByProperty($query, $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }
}
