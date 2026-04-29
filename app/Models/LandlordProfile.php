<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandlordProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'business_info',
        'company_name',
        'status',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function setPhoneAttribute(mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['phone'] = null;

            return;
        }

        $this->attributes['phone'] = preg_replace('/\D+/', '', (string) $value);
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\SuperAdminProfile::class, 'approved_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
