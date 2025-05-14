<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class PropertyImageService
{
    /**
     * Store a new property image
     */
    public function store(Property $property, UploadedFile $file, bool $isFeatured = false): PropertyImage
    {
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
        $thumbnail = Image::make($file)
            ->fit(300, 200)
            ->encode($file->getClientOriginalExtension(), 90);

        $thumbnailPath = "properties/{$property->id}/thumbnails/" . $filename;
        Storage::disk('public')->put($thumbnailPath, $thumbnail);

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
        $image->property->images()
            ->where('id', '!=', $image->id)
            ->update(['is_featured' => false]);

        $image->update(['is_featured' => true]);

        return $image;
    }
}