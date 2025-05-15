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
                            class="text-primary-600 hover:text-primary-700"
                        >
                            ← Back to Properties
                        </a>
                    </div>

                    <!-- Upload Form -->
                    <form wire:submit="save" class="mb-8">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Upload Photos
                                </label>
                                <input 
                                    type="file" 
                                    wire:model="photos" 
                                    multiple 
                                    class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-300
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-primary-600 file:text-white
                                        hover:file:bg-primary-700"
                                >
                            </div>
                            <div>
                                <button 
                                    type="submit"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors"
                                >
                                    Upload Photos
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Existing Photos -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($property->images as $image)
                            <div class="relative group">
                                <img 
                                    src="{{ asset('storage/' . $image->image_path) }}" 
                                    alt="{{ $image->alt_text }}"
                                    class="w-full h-48 object-cover rounded-lg"
                                >
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
