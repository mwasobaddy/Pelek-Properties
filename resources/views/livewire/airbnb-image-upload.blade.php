@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
    <h3 class="text-xl font-semibold text-purple-600 mb-4 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        Airbnb Photos Manager
    </h3>
    
    <div class="mb-6">
        <p class="text-gray-600 dark:text-gray-300 mb-2">
            Upload high-quality images optimized for Airbnb listings. For best results:
        </p>
        <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 ml-2">
            <li>Use high-resolution images (min 1024x683 pixels)</li>
            <li>Maintain a 3:2 aspect ratio when possible</li>
            <li>Keep file sizes under 10MB per image</li>
            <li>Upload bright, well-lit photos that showcase the property</li>
        </ul>
    </div>
    
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
            {{ session('error') }}
        </div>
    @endif
    
    <!-- Upload Form -->
    <form wire:submit.prevent="uploadImages" class="mb-8">
        <div class="mb-4">
            <label for="images" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Upload Airbnb Photos
            </label>
            <input 
                type="file" 
                wire:model="images" 
                multiple
                accept="image/*"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 dark:file:bg-purple-900 dark:file:text-purple-200 dark:text-gray-400"
            />
            @error('images.*') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>
        
        <div wire:loading wire:target="images" class="mb-4">
            <div class="animate-pulse flex items-center space-x-2">
                <div class="rounded-full bg-purple-400 h-3 w-3"></div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Processing images...</p>
            </div>
        </div>
        
        <button 
            type="submit"
            class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-800 focus:outline-none focus:border-purple-800 focus:ring ring-purple-300 disabled:opacity-25 transition"
            wire:loading.attr="disabled"
            wire:target="uploadImages"
        >
            <svg wire:loading wire:target="uploadImages" xmlns="http://www.w3.org/2000/svg" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Upload Photos
        </button>
    </form>
    
    <!-- Image Gallery -->
    @if ($uploadedImages->count() > 0)
        <h4 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-3">Airbnb Photos ({{ $uploadedImages->count() }})</h4>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
            @foreach($uploadedImages as $image)
                <div class="relative group bg-white rounded-lg overflow-hidden shadow-sm dark:bg-gray-700">
                    <img 
                        src="{{ Storage::url($image->image_path) }}"
                        alt="{{ $image->alt_text }}" 
                        class="w-full h-48 object-cover"
                    />
                    
                    <!-- Overlay with actions -->
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-3">
                        <!-- Featured badge -->
                        @if($image->id == $featuredImageId)
                            <span class="absolute top-2 right-2 inline-flex items-center bg-amber-500 text-white text-xs px-2 py-1 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                Featured
                            </span>
                        @endif
                        
                        <!-- Actions -->
                        <div class="flex justify-between mt-auto">
                            <!-- Set as featured -->
                            @if($image->id != $featuredImageId)
                                <button 
                                    wire:click="setAsFeatured({{ $image->id }})"
                                    class="text-xs bg-amber-500 text-white px-2 py-1 rounded hover:bg-amber-600 transition"
                                >
                                    Set as Featured
                                </button>
                            @endif
                            
                            <!-- Delete -->
                            <button 
                                wire:click="deleteImage({{ $image->id }})"
                                class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg dark:border-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No Airbnb photos uploaded yet</p>
        </div>
    @endif
</div>
