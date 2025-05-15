<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Amenity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class PropertySearchService
{
    /**
     * Search properties with various filters
     */
    public function search(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $cacheKey = $this->generateCacheKey($filters, $perPage);

        return Cache::remember($cacheKey, 3600, function () use ($filters, $perPage) {
            $query = Property::query()
                ->with(['propertyType', 'featuredImage', 'amenities']);

            $this->applyFilters($query, $filters);

            // Apply custom sorting if provided
            if (!empty($filters['sort'])) {
                switch ($filters['sort']) {
                    case 'name_asc':
                        $query->orderBy('title', 'asc');
                        break;
                    case 'name_desc':
                        $query->orderBy('title', 'desc');
                        break;
                    case 'newest':
                        $query->latest();
                        break;
                    case 'oldest':
                        $query->oldest();
                        break;
                    default:
                        $query->latest();
                }
            } else {
                $query->latest();
            }

            return $query->paginate($perPage);
        });
    }

    /**
     * Apply search filters to the query
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        // Listing type filter
        if (!empty($filters['listing_type'])) {
            $query->where('listing_type', $filters['listing_type']);
        }

        // Price range filter
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Location filters
        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }
        if (!empty($filters['neighborhood'])) {
            $query->where('neighborhood', $filters['neighborhood']);
        }

        // Property features filters
        if (!empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }
        if (!empty($filters['bathrooms'])) {
            $query->where('bathrooms', '>=', $filters['bathrooms']);
        }

        // Property type filter
        if (!empty($filters['property_type_id'])) {
            $query->where('property_type_id', $filters['property_type_id']);
        }

        // Property base type filter
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Amenities filter
        if (!empty($filters['amenities'])) {
            $query->whereHas('amenities', function ($q) use ($filters) {
                $q->whereIn('amenities.id', (array) $filters['amenities']);
            }, '=', count((array) $filters['amenities']));
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Size range filter
        if (!empty($filters['min_size'])) {
            $query->where('size', '>=', $filters['min_size']);
        }
        if (!empty($filters['max_size'])) {
            $query->where('size', '<=', $filters['max_size']);
        }

        // Square range filter (predefined ranges)
        if (!empty($filters['square_range'])) {
            $query->where('square_range', $filters['square_range']);
        }

        // Floors filter
        if (!empty($filters['floors'])) {
            $query->where('floors', $filters['floors']);
        }

        // Text search
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('location', 'LIKE', "%{$searchTerm}%");
            });
        }
    }

    /**
     * Generate a cache key based on the search filters
     */
    private function generateCacheKey(array $filters, int $perPage): string
    {
        ksort($filters); // Sort by key for consistent cache keys
        return 'property_search_' . md5(json_encode($filters) . $perPage);
    }

    /**
     * Get unique cities from properties
     */
    public function getAvailableCities(): array
    {
        return Cache::remember('available_cities', 3600, function () {
            return Property::distinct()
                ->pluck('city')
                ->sort()
                ->values()
                ->toArray();
        });
    }

    /**
     * Get neighborhoods for a specific city
     */
    public function getNeighborhoodsByCity(string $city): array
    {
        return Cache::remember("neighborhoods_{$city}", 3600, function () use ($city) {
            return Property::where('city', $city)
                ->distinct()
                ->pluck('neighborhood')
                ->filter()
                ->sort()
                ->values()
                ->toArray();
        });
    }

    /**
     * Get price ranges for properties
     */
    public function getPriceRanges(string $listingType = null): array
    {
        $query = Property::query();
        
        if ($listingType) {
            $query->where('listing_type', $listingType);
        }

        $stats = $query->selectRaw('
            MIN(price) as min_price,
            MAX(price) as max_price,
            AVG(price) as avg_price
        ')->first();

        return [
            'min' => (int) $stats->min_price,
            'max' => (int) $stats->max_price,
            'avg' => (int) $stats->avg_price,
        ];
    }

    /**
     * Get count of properties by listing type
     */
    public function getPropertyCountsByType(): array
    {
        return Cache::remember('property_counts_by_type', 3600, function () {
            return Property::query()
                ->selectRaw('listing_type, count(*) as count')
                ->groupBy('listing_type')
                ->pluck('count', 'listing_type')
                ->toArray();
        });
    }

    /**
     * Get all available amenities
     */
    public function getAvailableAmenities(): array
    {
        return Cache::remember('available_amenities', 3600, function () {
            return Amenity::query()
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
        });
    }
}