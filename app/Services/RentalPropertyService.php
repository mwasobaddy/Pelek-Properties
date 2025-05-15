<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RentalPropertyService
{
    public function __construct(
        private readonly PropertyService $propertyService
    ) {}

    /**
     * Get all rental properties with optional filtering
     */
    public function getRentalProperties(array $filters = []): LengthAwarePaginator
    {
        $query = Property::query()
            ->where('listing_type', 'rent')
            ->with(['propertyType', 'featuredImage', 'images', 'amenities']);

        // Apply filters
        if (!empty($filters['price_min'])) {
            $query->where('rental_price_monthly', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('rental_price_monthly', '<=', $filters['price_max']);
        }

        if (!empty($filters['furnished'])) {
            $query->where('is_furnished', true);
        }

        if (!empty($filters['available_from'])) {
            $query->where('available_from', '<=', $filters['available_from']);
        }

        if (!empty($filters['min_lease'])) {
            $query->where('minimum_lease_period', '>=', $filters['min_lease']);
        }

        if (!empty($filters['property_type'])) {
            $query->where('property_type_id', $filters['property_type']);
        }

        return $query->latest()->paginate(12);
    }

    /**
     * Get featured rental properties
     */
    public function getFeaturedRentals(int $limit = 4): Collection
    {
        return Property::where('listing_type', 'rent')
            ->where('is_featured', true)
            ->with(['propertyType', 'featuredImage'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Check rental property availability for a given date
     */
    public function isAvailableForDate(Property $property, string $date): bool
    {
        if (!$property->available_from) {
            return false;
        }

        return $property->available_from <= $date;
    }

    /**
     * Get similar rental properties
     */
    public function getSimilarProperties(Property $property, int $limit = 3): Collection
    {
        return Property::where('listing_type', 'rent')
            ->where('id', '!=', $property->id)
            ->where('property_type_id', $property->property_type_id)
            ->whereBetween('rental_price_monthly', [
                $property->rental_price_monthly * 0.8,
                $property->rental_price_monthly * 1.2
            ])
            ->with(['propertyType', 'featuredImage'])
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }
}
