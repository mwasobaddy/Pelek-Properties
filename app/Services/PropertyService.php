<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyService
{
    /**
     * Create a new property listing
     */
    public function create(array $data): Property
    {
        DB::beginTransaction();
        try {
            // Generate slug from title
            $data['slug'] = Str::slug($data['title']);
            
            // Create the property
            $property = Property::create($data);

            // Attach amenities if provided
            if (isset($data['amenities'])) {
                $property->amenities()->attach($data['amenities']);
            }

            DB::commit();
            return $property;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing property
     */
    public function update(Property $property, array $data): Property
    {
        DB::beginTransaction();
        try {
            // Update slug if title changed
            if (isset($data['title']) && $data['title'] !== $property->title) {
                $data['slug'] = Str::slug($data['title']);
            }

            // Update property
            $property->update($data);

            // Sync amenities if provided
            if (isset($data['amenities'])) {
                $property->amenities()->sync($data['amenities']);
            }

            DB::commit();
            return $property;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a property
     */
    public function delete(Property $property): bool
    {
        return $property->delete();
    }

    /**
     * Get featured properties
     */
    public function getFeatured(string $type = null, int $limit = 6): Collection
    {
        $query = Property::featured();

        if ($type) {
            $query->where('listing_type', $type);
        }

        return $query->with(['propertyType', 'featuredImage'])
                    ->latest()
                    ->take($limit)
                    ->get();
    }

    /**
     * Get all featured properties grouped by listing type
     */
    public function getAllFeaturedProperties(int $limit = 4): array
    {
        return [
            'sale' => $this->getFeatured('sale', $limit)->all(),
            'rent' => $this->getFeatured('rent', $limit)->all(),
            'airbnb' => $this->getFeatured('airbnb', $limit)->all(),
        ];
    }

    /**
     * Get properties by type with pagination
     */
    public function getByType(string $type, int $perPage = 12): LengthAwarePaginator
    {
        return Property::where('listing_type', $type)
                      ->with(['propertyType', 'featuredImage', 'amenities'])
                      ->latest()
                      ->paginate($perPage);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Property $property): Property
    {
        $property->update(['is_featured' => !$property->is_featured]);
        return $property;
    }

    /**
     * Update property status
     */
    public function updateStatus(Property $property, string $status): Property
    {
        $property->update(['status' => $status]);
        return $property;
    }

    /**
     * Get property types with their property counts
     */
    public function getPropertyTypesWithCount(): Collection
    {
        return PropertyType::withCount('properties')->get();
    }
}