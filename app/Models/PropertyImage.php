<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'image_path',
        'thumbnail_path',
        'is_featured',
        'display_order',
        'alt_text',
        'metadata',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'display_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the property that owns this image.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Scope a query to only include featured images.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include airbnb images.
     */
    public function scopeAirbnb($query)
    {
        return $query->whereJsonContains('metadata->type', 'airbnb');
    }

    /**
     * Order images by their display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
