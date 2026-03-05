<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by_landlord_id',
        'name',
        'phone',
        'address',
        'staff_type',
        'license_number',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function createdByLandlord()
    {
        return $this->belongsTo(User::class, 'created_by_landlord_id');
    }
}


