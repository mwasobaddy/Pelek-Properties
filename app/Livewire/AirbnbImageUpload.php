<?php

namespace App\Livewire;

use App\Models\Property;
use App\Services\PropertyImageService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class AirbnbImageUpload extends Component
{
    use WithFileUploads;
    
    public $property;
    public $images = [];
    public $uploadedImages = [];
    public $featuredImageId = null;
    
    public function mount(Property $property)
    {
        $this->property = $property;
        // Only allow for Airbnb properties
        if ($this->property->listing_type !== 'airbnb') {
            session()->flash('error', 'This feature is only available for Airbnb properties.');
            return redirect()->route('properties.index');
        }
        
        // Load existing Airbnb images
        $this->loadUploadedImages();
    }
    
    public function loadUploadedImages()
    {
        $this->uploadedImages = $this->property->airbnbImages()->get();
        $featuredImage = $this->property->featuredAirbnbImage;
        $this->featuredImageId = $featuredImage ? $featuredImage->id : null;
    }
    
    public function uploadImages()
    {
        $this->validate([
            'images.*' => 'image|max:10240', // 10MB Max
        ]);
        
        $imageService = new PropertyImageService();
        
        try {
            foreach ($this->images as $image) {
                // Determine if this should be the featured image
                $isFeatured = empty($this->uploadedImages) && !$this->featuredImageId;
                
                // Store the Airbnb-specific image
                $uploadedImage = $imageService->storeAirbnbImage($this->property, $image, $isFeatured);
                
                // If it's the first image and no featured image exists, set it as featured
                if ($isFeatured) {
                    $this->featuredImageId = $uploadedImage->id;
                }
            }
            
            session()->flash('message', count($this->images) . ' Airbnb images uploaded successfully.');
            $this->images = []; // Reset the file input
            $this->loadUploadedImages(); // Refresh the image list
            
        } catch (\Exception $e) {
            Log::error('Error uploading Airbnb images: ' . $e->getMessage());
            session()->flash('error', 'Failed to upload images: ' . $e->getMessage());
        }
    }
    
    public function setAsFeatured($imageId)
    {
        try {
            $imageService = new PropertyImageService();
            $image = $this->property->images()->findOrFail($imageId);
            $imageService->setFeatured($image);
            
            $this->featuredImageId = $imageId;
            session()->flash('message', 'Featured image updated.');
            $this->loadUploadedImages(); // Refresh the image list
            
        } catch (\Exception $e) {
            Log::error('Error setting featured image: ' . $e->getMessage());
            session()->flash('error', 'Failed to set featured image: ' . $e->getMessage());
        }
    }
    
    public function deleteImage($imageId)
    {
        try {
            $imageService = new PropertyImageService();
            $image = $this->property->images()->findOrFail($imageId);
            
            // Check if this is the featured image
            $wasFeatured = $image->is_featured;
            
            // Delete the image
            $imageService->delete($image);
            
            // If we deleted the featured image, we need to set a new one
            if ($wasFeatured && $this->property->images()->count() > 0) {
                $newFeaturedImage = $this->property->images()->first();
                $imageService->setFeatured($newFeaturedImage);
                $this->featuredImageId = $newFeaturedImage->id;
            } elseif ($wasFeatured) {
                $this->featuredImageId = null;
            }
            
            session()->flash('message', 'Image deleted successfully.');
            $this->loadUploadedImages(); // Refresh the image list
            
        } catch (\Exception $e) {
            Log::error('Error deleting image: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete image: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.airbnb-image-upload');
    }
}
