<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $card_uid
 * @property int|null $rfid_card_id
 * @property int|null $tenant_assignment_id
 * @property int|null $apartment_id
 * @property string $access_result
 * @property string|null $denial_reason
 * @property \Carbon\Carbon $access_time
 * @property string $reader_location
 * @property array|null $raw_data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_uid',
        'rfid_card_id',
        'tenant_assignment_id',
        'apartment_id',
        'access_result',
        'denial_reason',
        'access_time',
        'reader_location',
        'raw_data',
    ];

    protected $casts = [
        'access_time' => 'datetime',
        'raw_data' => 'array',
    ];

    // Relationships
    public function rfidCard()
    {
        return $this->belongsTo(RfidCard::class);
    }

    public function tenantAssignment()
    {
        return $this->belongsTo(TenantAssignment::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Property::class);
    }

    // Scopes
    public function scopeGranted($query)
    {
        return $query->where('access_result', 'granted');
    }

    public function scopeDenied($query)
    {
        return $query->where('access_result', 'denied');
    }

    public function scopeForApartment($query, $apartmentId)
    {
        return $query->where('apartment_id', $apartmentId);
    }

    public function scopeForCard($query, $cardUid)
    {
        return $query->where('card_uid', $cardUid);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('access_time', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('access_time', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('access_time', now()->month)
                    ->whereYear('access_time', now()->year);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('access_time', [$startDate, $endDate]);
    }

    public function scopeRecentActivity($query, $hours = 24)
    {
        return $query->where('access_time', '>=', now()->subHours($hours));
    }

    // Helper methods
    public function isGranted()
    {
        return $this->access_result === 'granted';
    }

    public function isDenied()
    {
        return $this->access_result === 'denied';
    }

    public function getResultBadgeClassAttribute()
    {
        return match($this->access_result) {
            'granted' => 'success',
            'denied' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Entry state derived from raw_data['entry_state'] if available ("in"|"out").
     */
    public function getEntryStateAttribute(): ?string
    {
        $raw = $this->raw_data;
        if (is_array($raw) && isset($raw['entry_state'])) {
            $state = strtolower((string) $raw['entry_state']);
            return in_array($state, ['in', 'out'], true) ? $state : null;
        }
        return null;
    }

    /**
     * Display result: for granted entries show IN/OUT when available; otherwise fallback to result.
     */
    public function getDisplayResultAttribute(): string
    {
        if ($this->access_result === 'granted' && $this->entry_state) {
            return strtoupper($this->entry_state);
        }
        return ucfirst($this->access_result);
    }

    /**
     * Badge class matching display result (IN/OUT) when available.
     */
    public function getDisplayBadgeClassAttribute(): string
    {
        if ($this->access_result === 'granted' && $this->entry_state) {
            return $this->entry_state === 'in' ? 'success' : 'primary';
        }
        return $this->result_badge_class;
    }

    public function getDenialReasonDisplayAttribute()
    {
        return match($this->denial_reason) {
            'card_not_found' => 'Card not registered',
            'card_inactive' => 'Card deactivated',
            'card_expired' => 'Card expired',
            'tenant_inactive' => 'Tenant access revoked',
            'outside_access_hours' => 'Outside allowed hours',
            'card_stolen' => 'Card reported stolen',
            'card_lost' => 'Card reported lost',
            default => $this->denial_reason
        };
    }

    public function getTenantNameAttribute()
    {
        return $this->tenantAssignment?->tenant?->name ?? 'Unknown';
    }

    public function getApartmentNameAttribute()
    {
        return $this->apartment?->name ?? 'Unknown';
    }

    // Static methods for statistics
    public static function getAccessStats($apartmentId = null, $days = 30)
    {
        $baseQuery = static::query();
        
        if ($apartmentId) {
            $baseQuery->where('apartment_id', $apartmentId);
        }
        
        $baseQuery->where('access_time', '>=', now()->subDays($days));
        
        return [
            'total_attempts' => (clone $baseQuery)->count(),
            'granted' => (clone $baseQuery)->where('access_result', 'granted')->count(),
            'denied' => (clone $baseQuery)->where('access_result', 'denied')->count(),
            'unique_cards' => (clone $baseQuery)->distinct()->count('card_uid'),
        ];
    }

    public static function getRecentActivity($apartmentId = null, $limit = 10)
    {
        $query = static::with(['rfidCard', 'tenantAssignment.tenant', 'apartment'])
                      ->orderBy('access_time', 'desc');
        
        if ($apartmentId) {
            $query->where('apartment_id', $apartmentId);
        }
        
        return $query->limit($limit)->get();
    }

    public static function getDeniedAccessReasons($apartmentId = null, $days = 30)
    {
        $query = static::where('access_result', 'denied')
                      ->where('access_time', '>=', now()->subDays($days));
        
        if ($apartmentId) {
            $query->where('apartment_id', $apartmentId);
        }
        
        return $query->groupBy('denial_reason')
                    ->selectRaw('denial_reason, count(*) as count')
                    ->orderBy('count', 'desc')
                    ->get();
    }
}