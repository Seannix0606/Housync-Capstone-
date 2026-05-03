<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property int $property_id
 * @property int $unit_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'unit_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (Booking $booking) {
            if ($booking->unit_id === null) {
                throw ValidationException::withMessages([
                    'unit_id' => ['A booking must reference a unit.'],
                ]);
            }

            $unit = Unit::query()->find($booking->unit_id);
            if ($unit === null) {
                throw ValidationException::withMessages([
                    'unit_id' => ['The selected unit does not exist.'],
                ]);
            }

            if ($booking->property_id !== null && (int) $booking->property_id !== (int) $unit->property_id) {
                throw ValidationException::withMessages([
                    'unit_id' => ['The unit does not belong to the given property.'],
                    'property_id' => ['The unit does not belong to the given property.'],
                ]);
            }

            $booking->property_id = $unit->property_id;
        });
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
