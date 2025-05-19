<?php
 
use App\Models\Property;
use function Livewire\Volt\{state};
use Illuminate\Support\Facades\Storage;
 
// Component state with computed properties and methods
state([
    // Properties
    'property' => null, // This will receive the Property model
    'debug' => '',
    'currentImageIndex' => 0,
    'showingSlider' => false,
    
    // Computed properties as arrow functions
    'formattedPrice' => fn() => number_format(
        match($this->property->listing_type) {
            'sale' => $this->property->price,
            'rent' => $this->property->rental_price_monthly,
            'airbnb' => $this->property->airbnb_price_nightly,
            'commercial' => $this->property->commercial_price_monthly,
            default => 0,
        }, 2
    ),
    
    'priceLabel' => fn() => match($this->property->listing_type) {
        'sale' => 'Price',
        'rent' => 'Monthly Rent',
        'airbnb' => 'Per Night',
        'commercial' => 'Monthly Rent',
        default => 'Price',
    },

    // Property images handling
    'propertyImages' => fn() => $this->property->listing_type === 'airbnb' 
        ? $this->property->images()->orderBy('display_order')->get()
        : collect([$this->property->featuredImage])->filter(),
    
    'currentImage' => function() {
        $image = $this->property->featuredImage;
        return $image 
            ? Storage::disk('public')->url($image->image_path)
            : asset('images/placeholder.webp');
    },
        
    // Methods directly in state array
    'mount' => function (Property $property) {
        $this->property = $property;
        
        // For Airbnb properties, preload images for the slider
        if ($property->listing_type === 'airbnb') {
            $this->showingSlider = $property->images()->count() > 1;
        }
    },
    
    'nextImage' => function() {
        $totalImages = count($this->propertyImages);
        if ($totalImages <= 1) return;
        
        $this->currentImageIndex = ($this->currentImageIndex + 1) % $totalImages;
    },
    
    'prevImage' => function() {
        $totalImages = count($this->propertyImages);
        if ($totalImages <= 1) return;
        
        $this->currentImageIndex = ($this->currentImageIndex - 1 + $totalImages) % $totalImages;
    }
]);
?>

<div>
    {{-- Property Card - Modern Design 2024+ --}}
    <div class="group relative overflow-hidden rounded-xl bg-white shadow-md transition-all duration-300 hover:shadow-lg dark:bg-gray-800 dark:shadow-gray-700/30">
        {{-- Image Section with Slider for Airbnb Properties --}}
        <div class="relative aspect-w-16 aspect-h-10 overflow-hidden">
            {{-- Main Image --}}
            <img src="{{ $currentImage }}" 
                 alt="{{ $property->title }}" 
                 class="h-full w-full object-cover transition-all duration-500 group-hover:scale-110">
            
            {{-- Slider Controls (only for Airbnb properties with multiple images) --}}
            @if($property->listing_type === 'airbnb' && $showingSlider)
                <div class="absolute inset-0 flex items-center justify-between px-4 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button wire:click="prevImage" class="bg-black/50 rounded-full p-2 text-white hover:bg-black/70 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button wire:click="nextImage" class="bg-black/50 rounded-full p-2 text-white hover:bg-black/70 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
                
                {{-- Image Counter --}}
                <div class="absolute bottom-4 right-4 bg-black/50 rounded-full px-2 py-1 text-xs text-white">
                    {{ $currentImageIndex + 1 }} / {{ count($propertyImages) }}
                </div>
            @endif
            
            {{-- Listing Badge --}}
            <div class="absolute left-4 top-4 z-1">
                <span @class([
                    'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium tracking-wide shadow-sm',
                    'bg-blue-500/90 text-white backdrop-blur-sm' => $property->listing_type === 'rent',
                    'bg-emerald-500/90 text-white backdrop-blur-sm' => $property->listing_type === 'sale',
                    'bg-purple-500/90 text-white backdrop-blur-sm' => $property->listing_type === 'airbnb',
                    'bg-yellow-500/90 text-white backdrop-blur-sm' => $property->listing_type === 'commercial',
                ])>
                    {{ ucfirst($property->listing_type) }}
                </span>
            </div>
            
            {{-- Save/Favorite Button Overlay --}}
            <button class="absolute right-4 top-4 rounded-full bg-white/80 p-2 text-gray-700 opacity-0 transition-opacity duration-300 hover:bg-white dark:bg-gray-800/80 dark:text-gray-200 dark:hover:bg-gray-800 group-hover:opacity-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
        </div>

        {{-- Content Section with Improved Typography and Spacing --}}
        <div class="p-5">
            {{-- Property Title with Improved Typography --}}
            <h3 class="mb-1 truncate text-lg font-medium text-gray-900 dark:text-white">{{ $property->title }}</h3>

            {{-- Location with Icon Alignment --}}
            <div class="mb-4 flex items-center text-sm text-gray-600 dark:text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-4 w-4" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                    <circle cx="12" cy="10" r="3" />
                </svg>
                <span class="truncate">{{ $property->location }}</span>
            </div>

            {{-- Property Features with Modern Icons and Layout --}}
            <div class="grid grid-cols-3 gap-2">
                <div class="flex flex-col items-center rounded-lg bg-gray-50 p-2 text-center dark:bg-gray-700/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 7v11m0-7h18m0 0v7m-5-7v7m-4-7v7m-4-7v7" />
                    </svg>
                    <span class="mt-1 text-xs">{{ $property->bedrooms }} Beds</span>
                </div>
                <div class="flex flex-col items-center rounded-lg bg-gray-50 p-2 text-center dark:bg-gray-700/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 6h11M9 12h11M9 18h11M5 6v.01M5 12v.01M5 18v.01" />
                    </svg>
                    <span class="mt-1 text-xs">{{ $property->bathrooms }} Baths</span>
                </div>
                <div class="flex flex-col items-center rounded-lg bg-gray-50 p-2 text-center dark:bg-gray-700/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <path d="M3 9h18M9 21V9" />
                    </svg>
                    <span class="mt-1 text-xs">{{ number_format($property->size) }} sqft</span>
                </div>
            </div>

            {{-- Pricing Section with CTA --}}
            <div class="mt-6">
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $priceLabel }}</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">${{ $formattedPrice }}</p>
                    </div>
                    <button wire:click="$dispatch('open-modal', { name: 'whatsapp-modal-{{ $property->id }}' })" class="flex items-center rounded-lg bg-green-500 px-3 py-2 text-sm font-medium text-white transition-all duration-300 hover:bg-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Inquire
                    </button>
                </div>
            </div>
        </div>

        {{-- WhatsApp Modal --}}
        <flux:modal name="whatsapp-modal-{{ $property->id }}" dismissible class="max-w-md self-center justify-self-center !p-0">
            <div class="p-1">
                {{-- Modal Header with Property Image --}}
                <div class="mb-4 flex items-center space-x-4">
                    <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg">
                        <img src="{{ $currentImage }}"
                            alt="{{ $property->title }}"
                            class="h-full w-full object-cover">
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $property->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $property->location }}</p>
                    </div>
                </div>

                {{-- Modal Content --}}
                <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                    <p class="text-gray-700 dark:text-gray-300">
                        You'll be redirected to WhatsApp to inquire about this property. Our agent will respond as soon as possible.
                    </p>
                </div>

                {{-- Modal Footer with Actions --}}
                <div class="flex justify-end space-x-3">
                    <button 
                        wire:click="$dispatch('close-modal', { name: 'whatsapp-modal-{{ $property->id }}' })"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                    <a 
                        href="https://wa.me/{{ preg_replace('/[^0-9+]/', '', $property->whatsapp_number) }}?text={{ urlencode("Hi, I'm interested in the property: {$property->title}") }}"
                        target="_blank"
                        class="rounded-lg bg-green-500 px-4 py-2 text-sm font-medium text-white hover:bg-green-600 flex items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.23-8.24 8.23-1.48 0-2.93-.39-4.19-1.15l-.3-.17-3.12.82.83-3.04-.2-.32a8.188 8.188 0 01-1.26-4.38c.01-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.16 0-.43.06-.66.31-.22.25-.87.86-.87 2.07 0 1.22.89 2.39 1 2.56.14.17 1.76 2.67 4.25 3.73.59.27 1.05.42 1.41.53.59.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.16-.48-.27-.25-.14-1.47-.74-1.69-.82-.23-.08-.37-.12-.56.12-.16.25-.64.81-.78.97-.15.17-.29.19-.53.07-.26-.13-1.06-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.12-.24-.01-.39.11-.5.11-.11.27-.29.37-.44.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.11-.56-1.35-.77-1.84-.2-.48-.4-.42-.56-.43-.14 0-.3-.01-.47-.01z" />
                        </svg>
                        Open WhatsApp
                    </a>
                </div>
            </div>
        </flux:modal>
    </div>
</div>
