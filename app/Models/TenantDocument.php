<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $document_type
 * @property string $file_name
 * @property string $file_path
 * @property int $file_size
 * @property string $mime_type
 * @property string $verification_status
 * @property int|null $verified_by
 * @property \Carbon\Carbon|null $verified_at
 * @property string|null $verification_notes
 * @property \Carbon\Carbon|null $expiry_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TenantDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_at',
        'verified_at',
        'verified_by',
        'verification_status',
        'verification_notes',
        'expiry_date',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'file_size' => 'integer',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    // Note: tenant_assignment_id column was removed from the database
    // Documents are now associated directly with tenants, not assignments

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'rejected');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    // Helper methods
    public function isVerified()
    {
        return $this->verification_status === 'verified';
    }

    public function isPending()
    {
        return $this->verification_status === 'pending';
    }

    public function isRejected()
    {
        return $this->verification_status === 'rejected';
    }

    public function getVerificationStatusBadgeClassAttribute()
    {
        return match($this->verification_status) {
            'verified' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getDocumentTypeLabelAttribute()
    {
        return match($this->document_type) {
            'government_id' => 'Government ID',
            'proof_of_income' => 'Proof of Income',
            'employment_contract' => 'Employment Contract',
            'bank_statement' => 'Bank Statement',
            'character_reference' => 'Character Reference',
            'rental_history' => 'Rental History',
            'other' => 'Other Document',
            default => ucfirst(str_replace('_', ' ', $this->document_type))
        };
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the full URL for the document file
     * Handles both Supabase URLs and local storage paths
     */
    public function getFileUrlAttribute()
    {
        // If it's already a full URL (Supabase), return as-is
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }
        
        // For local storage, generate the proper URL
        return asset('storage/' . $this->file_path);
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= $days;
    }
} 