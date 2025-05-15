<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'property_type_id',
        'title',
        'slug',
        'description',
        'price',
        'location',
        'neighborhood',
        'city',
        'bedrooms',
        'bathrooms',
        'size',
        'listing_type',
        'status',
        'is_featured',
        'additional_features',
        'rental_price_daily',
        'rental_price_monthly',
        'airbnb_price_nightly',
        'airbnb_price_weekly',
        'airbnb_price_monthly',
        'availability_calendar',
        'whatsapp_number',
        'management_fee_percentage',
        'base_management_fee',
        'management_payment_schedule',
        'management_services_included',
    ];

    protected $casts = [
        'additional_features' => 'array',
        'availability_calendar' => 'array',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'rental_price_daily' => 'decimal:2',
        'rental_price_monthly' => 'decimal:2',
        'airbnb_price_nightly' => 'decimal:2',
        'airbnb_price_weekly' => 'decimal:2',
        'airbnb_price_monthly' => 'decimal:2',
        'commercial_amenities' => 'array',
        'zoning_info' => 'array',
        'has_parking' => 'boolean',
        'total_square_feet' => 'decimal:2',
        'price_per_square_foot' => 'decimal:2',
        'year_built' => 'integer',
        'last_renovated' => 'date',
        'management_services_included' => 'array',
        'management_fee_percentage' => 'decimal:2',
        'base_management_fee' => 'decimal:2',
    ];

    /**
     * Get the property type of this property.
     */
    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    /**
     * Get the user who created this property.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all images for this property.
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class);
    }

    /**
     * Get the featured image for this property.
     */
    public function featuredImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_featured', true);
    }
    
    /**
     * Get all Airbnb-specific images for this property.
     */
    public function airbnbImages()
    {
        return $this->hasMany(PropertyImage::class)->whereJsonContains('metadata->type', 'airbnb')->ordered();
    }
    
    /**
     * Get the featured Airbnb image for this property.
     */
    public function featuredAirbnbImage()
    {
        return $this->hasOne(PropertyImage::class)
            ->whereJsonContains('metadata->type', 'airbnb')
            ->where('is_featured', true);
    }

    /**
     * Get all amenities for this property.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'property_amenity')
            ->withPivot('notes')
            ->withTimestamps();
    }

    /**
     * Get all bookings for this property.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(PropertyBooking::class);
    }

    /**
     * Get all active bookings for this property.
     */
    public function activeBookings(): HasMany
    {
        return $this->hasMany(PropertyBooking::class)->where('status', 'confirmed');
    }

    /**
     * Check if the property is available for the given dates.
     */
    public function isAvailable(string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool
    {
        // Check in availability calendar first
        $hasAvailabilityConflict = Availability::areDatesAvailable($this->id, $checkIn, $checkOut);
        
        if (!$hasAvailabilityConflict) {
            return false;
        }

        // Then check in bookings
        return PropertyBooking::areDatesAvailable($this->id, $checkIn, $checkOut, $excludeBookingId);
    }

    /**
     * Scope a query to only include featured properties.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include properties of a specific listing type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('listing_type', $type);
    }

    /**
     * Scope a query to only include available properties.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Get all facilities for this property.
     */
    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'property_facilities')
            ->withPivot(['details', 'last_maintenance', 'next_maintenance', 'maintenance_notes'])
            ->withTimestamps();
    }

    /**
     * Scope a query to only include commercial properties.
     */
    public function scopeCommercial($query)
    {
        return $query->where('listing_type', 'commercial');
    }

    /**
     * Scope a query to only include properties of a specific commercial type.
     */
    public function scopeCommercialType($query, string $type)
    {
        return $query->where('commercial_type', $type);
    }

    /**
     * Get the formatted price per square foot.
     */
    public function getFormattedPricePerSqftAttribute(): string
    {
        return $this->price_per_square_foot
            ? 'KES ' . number_format($this->price_per_square_foot, 2) . '/sq ft'
            : 'Price on request';
    }

    /**
     * Get the commercial summary for this property.
     */
    public function getCommercialSummaryAttribute(): string
    {
        $type = str_replace('_', ' ', ucfirst($this->commercial_type));
        $size = number_format($this->total_square_feet) . ' sq ft';
        return "{$type} Space • {$size}";
    }

    /**
     * Get the availability calendar entries for this property.
     */
    public function availability()
    {
        return $this->hasMany(Availability::class);
    }

    /**
     * Generate or update availability calendar for a date range
     */
    public function generateAvailabilityCalendar(
        string $startDate,
        string $endDate,
        string $defaultStatus = 'available',
        ?float $customPrice = null
    ): void {
        Availability::generateForDateRange(
            $this->id,
            $startDate,
            $endDate,
            $defaultStatus,
            $customPrice ?: $this->airbnb_price_nightly
        );
    }

    /**
     * Block dates in availability calendar for a booking
     */
    public function blockDatesForBooking(PropertyBooking $booking): void
    {
        Availability::blockDatesForBooking(
            $this->id,
            $booking->check_in,
            $booking->check_out,
            [
                'booking_id' => $booking->id,
                'guest_name' => $booking->guest_name,
            ]
        );
    }

    /**
     * Get the tenant information for this property
     */
    public function tenantInfo()
    {
        return $this->hasOne(TenantInfo::class);
    }

    /**
     * Get the maintenance records for this property
     */
    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    /**
     * Check if the property is currently occupied
     */
    public function isOccupied(): bool
    {
        return $this->tenantInfo()
            ->whereDate('lease_start', '<=', now())
            ->whereDate('lease_end', '>=', now())
            ->exists();
    }

    /**
     * Get the management contracts for this property
     */
    public function managementContracts(): HasMany
    {
        return $this->hasMany(ManagementContract::class);
    }

    /**
     * Get the active management contract for this property
     */
    public function activeContract()
    {
        return $this->hasOne(ManagementContract::class)->active();
    }

    /**
     * Get the financial records for this property
     */
    public function financialRecords(): HasMany
    {
        return $this->hasMany(FinancialRecord::class);
    }

    /**
     * Get the financial records summary for the current month
     */
    public function getCurrentMonthFinancials(): array
    {
        $records = $this->financialRecords()
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->get();

        return [
            'income' => $records->where('transaction_type', 'income')->sum('amount'),
            'expenses' => $records->where('transaction_type', 'expense')->sum('amount'),
            'pending' => $records->where('status', 'pending')->count(),
        ];
    }
}
