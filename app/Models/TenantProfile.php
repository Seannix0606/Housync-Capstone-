<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'id_number',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


