<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'category',
    ];

    /**
     * Get all properties that have this amenity.
     */
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class)
            ->withPivot('notes')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include amenities of a specific category.
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
