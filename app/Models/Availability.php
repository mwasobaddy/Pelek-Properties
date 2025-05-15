<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
