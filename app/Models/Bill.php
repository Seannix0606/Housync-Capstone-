<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $landlord_id
 * @property int|null $tenant_id
 * @property int|null $tenant_assignment_id
 * @property int|null $unit_id
 * @property string $invoice_number
 * @property string $type
 * @property string|null $description
 * @property \Carbon\Carbon|null $billing_period_start
 * @property \Carbon\Carbon|null $billing_period_end
 * @property float $amount
 * @property float $amount_paid
 * @property float $balance
 * @property string $status
 * @property \Carbon\Carbon|null $due_date
 * @property \Carbon\Carbon|null $paid_at
 * @property string $currency
 * @property string|null $notes
 */
class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'landlord_id',
        'tenant_id',
        'tenant_assignment_id',
        'unit_id',
        'invoice_number',
        'type',
        'description',
        'billing_period_start',
        'billing_period_end',
        'amount',
        'amount_paid',
        'balance',
        'status',
        'due_date',
        'paid_at',
        'currency',
        'notes',
    ];

    protected $casts = [
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function tenantAssignment()
    {
        return $this->belongsTo(TenantAssignment::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeForLandlord($query, int $landlordId)
    {
        return $query->where('landlord_id', $landlordId);
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // Helpers
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'overdue';
    }

    public function getFormattedAmountAttribute(): string
    {
        return '₱' . number_format($this->amount, 2);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return '₱' . number_format($this->balance, 2);
    }
}



