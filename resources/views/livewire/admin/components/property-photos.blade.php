<?php

use App\Models\Property;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public Property $property;
    public $photos = [];

    public function mount(Property $property)
    {
        $this->property = $property;
    }

    public function save()
    {
        $this->validate([
            'photos.*' => 'image|max:10240', // 10MB Max
        ]);

        try {
            foreach ($this->photos as $photo) {
                $this->property->images()->create([
                    'image_path' => $photo->store('property-images', 'public'),
                    'thumbnail_path' => $photo->store('property-images/thumbnails', 'public'),
                    'alt_text' => $this->property->title,
                ]);
            }

            $this->photos = [];
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Photos uploaded successfully'
            ]);
        } catch (\Exception $e) {
            logger()->error('Error uploading photos', [
                'property_id' => $this->property->id,
                'error' => $e->getMessage()
            ]);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error uploading photos'
            ]);
        }
    }

    public function deleteImage($imageId)
    {
        $image = $this->property->images()->findOrFail($imageId);
        $image->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Photo deleted successfully'
        ]);
    }
} ?>

<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold">Manage Photos - {{ $property->title }}</h2>
                        <a 
                            href="{{ route('admin.properties.index') }}" 
                            class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
                        >
                            ← Back to Properties
                        </a>
                    </div>

                    <!-- Upload Form -->
                    <form wire:submit="save" class="mb-8">
                        <div class="space-y-4">
                            <div class="flex items-center justify-center w-full">
                                <label for="file-upload" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <flux:icon name="upload" class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" />
                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="font-semibold">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            PNG, JPG or WebP (MAX. 10MB)
                                        </p>
                                    </div>
                                    <input 
                                        wire:model="photos" 
                                        id="file-upload" 
                                        type="file" 
                                        multiple
                                        class="hidden" 
                                        accept="image/*"
                                    />
                                </label>
                            </div>

                            <div class="flex justify-end">
                                <flux:button type="submit">
                                    Upload Photos
                                </flux:button>
                            </div>
                        </div>
                    </form>

                    <!-- Photo Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($property->images as $image)
                            <div class="relative group">
                                <x-responsive-image 
                                    :image="$image" 
                                    class="w-full h-48 object-cover rounded-lg bg-gray-100 dark:bg-gray-700"
                                    sizes="(min-width: 1280px) 25vw, (min-width: 768px) 33vw, 50vw"
                                />
                                <button
                                    wire:click="deleteImage({{ $image->id }})"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <flux:icon name="trash" class="w-4 h-4" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
