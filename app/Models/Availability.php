<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Availability extends Model
{
    use HasFactory;

    protected $table = 'availability_calendar';

    protected $fillable = [
        'property_id',
        'date',
        'status',
        'custom_price',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'notes' => 'json',
        'custom_price' => 'decimal:2'
    ];

    /**
     * Get the property that owns this availability record.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Scope to get available dates
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to get dates within a range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Check if given dates are available for booking
     */
    public static function areDatesAvailable(int $propertyId, string $checkIn, string $checkOut): bool
    {
        $bookedDates = self::where('property_id', $propertyId)
            ->whereIn('status', ['booked', 'blocked', 'maintenance'])
            ->whereBetween('date', [$checkIn, $checkOut])
            ->count();

        return $bookedDates === 0;
    }

    /**
     * Generate availability records for a date range
     */
    public static function generateForDateRange(
        int $propertyId,
        string $startDate,
        string $endDate,
        string $status = 'available',
        ?float $customPrice = null
    ): void {
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            self::updateOrCreate(
                [
                    'property_id' => $propertyId,
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'status' => $status,
                    'custom_price' => $customPrice,
                ]
            );
        }
    }

    /**
     * Block dates for a booking
     */
    public static function blockDatesForBooking(
        int $propertyId,
        string $checkIn,
        string $checkOut,
        array $notes = null
    ): void {
        $period = CarbonPeriod::create($checkIn, $checkOut);

        foreach ($period as $date) {
            self::updateOrCreate(
                [
                    'property_id' => $propertyId,
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'status' => 'booked',
                    'notes' => $notes,
                ]
            );
        }
    }

    /**
     * Get availability calendar for a date range
     */
    public static function getCalendar(
        int $propertyId,
        string $startDate = null,
        string $endDate = null,
        bool $includeNotes = false
    ): Collection {
        $query = self::where('property_id', $propertyId);

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        if (!$includeNotes) {
            $query->select(['date', 'status', 'custom_price']);
        }

        return $query->orderBy('date')->get();
    }
}
