<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandlordDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'landlord_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_at',
        'verification_status',
        'verified_at',
        'verified_by',
        'verification_notes',
        'expiry_date',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'file_size' => 'integer',
        'expiry_date' => 'date',
    ];

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}


