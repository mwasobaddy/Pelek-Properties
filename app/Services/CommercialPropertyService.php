<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CommercialPropertyService
{
    public function __construct(
        private readonly PropertyService $propertyService
    ) {}

    /**
     * Get all commercial properties with optional filtering
     */
    public function getCommercialProperties(array $filters = []): LengthAwarePaginator
    {
        $query = Property::query()
            ->where('listing_type', 'commercial')
            ->with(['propertyType', 'featuredImage', 'images', 'facilities']);

        // Apply filters
        if (!empty($filters['commercial_type'])) {
            $query->where('commercial_type', $filters['commercial_type']);
        }

        if (!empty($filters['min_size'])) {
            $query->where('total_square_feet', '>=', $filters['min_size']);
        }

        if (!empty($filters['max_size'])) {
            $query->where('total_square_feet', '<=', $filters['max_size']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['has_parking'])) {
            $query->where('has_parking', true);
        }

        if (!empty($filters['facilities'])) {
            $query->whereHas('facilities', function ($q) use ($filters) {
                $q->whereIn('facilities.id', $filters['facilities']);
            });
        }

        return $query->latest()->paginate(12);
    }

    /**
     * Get featured commercial properties
     */
    public function getFeaturedCommercial(int $limit = 4): Collection
    {
        return Property::where('listing_type', 'commercial')
            ->where('is_featured', true)
            ->with(['propertyType', 'featuredImage', 'facilities'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get similar commercial properties
     */
    public function getSimilarProperties(Property $property, int $limit = 3): Collection
    {
        return Property::where('listing_type', 'commercial')
            ->where('id', '!=', $property->id)
            ->where('commercial_type', $property->commercial_type)
            ->whereBetween('total_square_feet', [
                $property->total_square_feet * 0.7,
                $property->total_square_feet * 1.3
            ])
            ->with(['propertyType', 'featuredImage', 'facilities'])
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    /**
     * Get all active facilities grouped by type
     */
    public function getFacilitiesGroupedByType(): Collection
    {
        return Facility::active()
            ->get()
            ->groupBy('type');
    }
}
