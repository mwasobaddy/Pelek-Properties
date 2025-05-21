<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PropertyImageService
{
    /**
     * Validate and prepare an uploaded file for storage
     */
    protected function validateAndPrepareImage(UploadedFile $file): bool
    {
        try {
            // Validate mime type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                Log::warning('Invalid image type uploaded', [
                    'mime_type' => $file->getMimeType(),
                    'allowed_types' => $allowedTypes
                ]);
                return false;
            }

            // Validate file size (max 10MB)
            $maxSize = 10 * 1024 * 1024; // 10MB
            if ($file->getSize() > $maxSize) {
                Log::warning('Image too large', [
                    'size' => $file->getSize(),
                    'max_size' => $maxSize
                ]);
                return false;
            }

            // Skip isReadable check for Livewire temporary uploads
            // Livewire's TemporaryUploadedFile may not pass standard isReadable check
            // but is still valid for processing
            if (!$file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile && !$file->isReadable()) {
                Log::warning('Image file is not readable', [
                    'path' => $file->getRealPath()
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error validating image file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Store a new property image
     */
    public function store(Property $property, UploadedFile $file, bool $isFeatured = false): PropertyImage
    {
        try {
            // Validate and prepare the image
            if (!$this->validateAndPrepareImage($file)) {
                throw new \Exception('Invalid image file');
            }

            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            // Get the next display order
            $displayOrder = $property->images()->max('display_order') + 1;

            // Store original image
            $imagePath = $file->storeAs(
                "properties/{$property->id}",
                $filename,
                'public'
            );

            // Create and store thumbnail
            $imageManager = new ImageManager(new Driver());
            $thumbnail = $imageManager->read($file->getRealPath())
                ->resize(300, 200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            
            $thumbnailPath = "properties/{$property->id}/thumbnails/" . $filename;
            Storage::disk('public')->put($thumbnailPath, $thumbnail->toJpeg());

            // If this is set as featured, unset other featured images
            if ($isFeatured) {
                $property->images()->update(['is_featured' => false]);
            }

            // Create image record
            return $property->images()->create([
                'image_path' => $imagePath,
                'thumbnail_path' => $thumbnailPath,
                'is_featured' => $isFeatured,
                'display_order' => $displayOrder,
                'alt_text' => $property->title,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store property image', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Store an Airbnb-specific property image
     * These images can have special handling for Airbnb listings
     */
    public function storeAirbnbImage(Property $property, UploadedFile $file, bool $isFeatured = false): PropertyImage
    {
        try {
            // Validate and prepare the image
            if (!$this->validateAndPrepareImage($file)) {
                throw new \Exception('Invalid image file');
            }

            // Generate unique filename with airbnb prefix
            $filename = 'airbnb-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            // Get the next display order
            $displayOrder = $property->images()->max('display_order') + 1;

            // Store original image in airbnb subfolder
            $imagePath = $file->storeAs(
                "properties/{$property->id}/airbnb",
                $filename,
                'public'
            );

            // Create and store thumbnail with special dimensions optimized for Airbnb
            // Airbnb prefers 1024x683 px images (3:2 ratio)
            $imageManager = new ImageManager(new Driver());
            $thumbnail = $imageManager->read($file->getRealPath())
                ->resize(1024, 683, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            
            $thumbnailPath = "properties/{$property->id}/airbnb/thumbnails/" . $filename;
            Storage::disk('public')->put($thumbnailPath, $thumbnail->toJpeg());

            // If this is set as featured, unset other featured images
            if ($isFeatured) {
                $property->images()->update(['is_featured' => false]);
            }

            // Create image record with airbnb tag
            $image = $property->images()->create([
                'image_path' => $imagePath,
                'thumbnail_path' => $thumbnailPath,
                'is_featured' => $isFeatured,
                'display_order' => $displayOrder,
                'alt_text' => $property->title . ' - Airbnb',
            ]);
            
            // Add metadata for airbnb images
            $image->metadata = [
                'type' => 'airbnb',
                'optimized' => true,
                'uploaded_at' => now()->toDateTimeString()
            ];
            $image->save();
            
            return $image;
        } catch (\Exception $e) {
            Log::error('Failed to store Airbnb property image', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get all images for an Airbnb property
     */
    public function getAirbnbPropertyImages(Property $property)
    {
        if ($property->listing_type !== 'airbnb') {
            // For non-Airbnb properties, just return the featured image
            return collect([$property->featuredImage])->filter();
        }
        
        // For Airbnb properties, get all images ordered by display order
        $images = $property->images()
            ->orderBy('is_featured', 'desc') // Featured images first
            ->orderBy('display_order', 'asc')
            ->get();
            
        // If no images, return empty collection
        if ($images->isEmpty()) {
            return collect();
        }
        
        return $images;
    }

    /**
     * Store multiple property images
     */
    public function storeMany(Property $property, array $files): array
    {
        $images = [];
        foreach ($files as $index => $file) {
            $images[] = $this->store($property, $file, $index === 0 && !$property->images()->exists());
        }
        return $images;
    }

    /**
     * Update image details
     */
    public function update(PropertyImage $image, array $data): PropertyImage
    {
        $image->update($data);

        // If this image is set as featured, unset others
        if ($data['is_featured'] ?? false) {
            $image->property->images()
                ->where('id', '!=', $image->id)
                ->update(['is_featured' => false]);
        }

        return $image;
    }

    /**
     * Delete a property image
     */
    public function delete(PropertyImage $image): bool
    {
        try {
            // Delete files
            Storage::disk('public')->delete($image->image_path);
            Storage::disk('public')->delete($image->thumbnail_path);

            // If this was the featured image, set another as featured
            if ($image->is_featured) {
                $newFeatured = $image->property->images()
                    ->where('id', '!=', $image->id)
                    ->first();
                if ($newFeatured) {
                    $newFeatured->update(['is_featured' => true]);
                }
            }

            return $image->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete property image', [
                'image_id' => $image->id,
                'property_id' => $image->property_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Reorder property images
     */
    public function reorder(Property $property, array $imageIds): void
    {
        foreach ($imageIds as $order => $id) {
            $property->images()
                ->where('id', $id)
                ->update(['display_order' => $order]);
        }
    }

    /**
     * Set a property image as featured
     */
    public function setFeatured(PropertyImage $image): PropertyImage
    {
        try {
            // Unset all other images as featured
            $image->property->images()
                ->where('id', '!=', $image->id)
                ->update(['is_featured' => false]);

            $image->update(['is_featured' => true]);

            return $image;
        } catch (\Exception $e) {
            Log::error('Failed to set featured property image', [
                'image_id' => $image->id,
                'property_id' => $image->property_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}