<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceComment extends Model
{
    protected $fillable = [
        'maintenance_request_id',
        'user_id',
        'comment',
        'type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeComments($query)
    {
        return $query->where('type', 'comment');
    }

    public function scopeStatusChanges($query)
    {
        return $query->where('type', 'status_change');
    }
}
