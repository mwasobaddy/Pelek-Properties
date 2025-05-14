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
     * Get all amenities for this property.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class)
            ->withPivot('notes')
            ->withTimestamps();
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
}
