<?php

use App\Models\Property;
use function Livewire\Volt\{state, mount, computed};
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;

new class extends Component {
    public $showModal = false;
    public $property = null;
    public $currentImageIndex = 0;
    public $showingSlider = false;

    

    public function with(): array
    {
        return [
            'formattedPrice' => fn() => number_format(
                match ($this->property?->listing_type) {
                    'sale' => $this->property->price,
                    'rent' => $this->property->rental_price_monthly,
                    'airbnb' => $this->property->airbnb_price_nightly,
                    'commercial' => $this->property->commercial_price_monthly,
                    default => 0,
                },
                2,
            ),
            'priceLabel' => fn() => match ($this->property?->listing_type) {
                'sale' => 'Price',
                'rent' => 'Monthly Rent',
                'airbnb' => 'Per Night',
                'commercial' => 'Monthly Rent',
                default => 'Price',
            },
        ];
    }

    public function mount(Property $property)
    {
        $this->property = $property;

        // For Airbnb properties, preload images for the slider
        if ($property->listing_type === 'airbnb') {
            $this->showingSlider = $property->images()->count() > 1;
        }
    }

    public function getFormattedPrice()
    {
        return number_format(
            match ($this->property->listing_type) {
                'sale' => $this->property->price,
                'rent' => $this->property->rental_price_monthly,
                'airbnb' => $this->property->airbnb_price_nightly,
                'commercial' => $this->property->commercial_price_monthly,
                default => 0,
            },
            2,
        );
    }

    public function getPriceLabel()
    {
        return match ($this->property->listing_type) {
            'sale' => 'Price',
            'rent' => 'Monthly Rent',
            'airbnb' => 'Per Night',
            'commercial' => 'Monthly Rent',
            default => 'Price',
        };
    }

    public function getPropertyImages()
    {
        return $this->property->listing_type === 'airbnb' ? $this->property->images()->orderBy('display_order')->get() : collect([$this->property->featuredImage])->filter();
    }

    public function getCurrentImage()
    {
        if ($this->property->listing_type === 'airbnb' && $this->showingSlider) {
            $images = $this->getPropertyImages();
            if ($images->count() > 0) {
                $image = $images->get($this->currentImageIndex);
                return $image ? Storage::disk('public')->url($image->image_path) : asset('images/placeholder.webp');
            }
        }

        $image = $this->property->featuredImage;
        return $image ? Storage::disk('public')->url($image->image_path) : asset('images/placeholder.webp');
    }

    public function nextImage()
    {
        $totalImages = $this->getPropertyImages()->count();
        if ($totalImages <= 1) {
            return;
        }

        $this->currentImageIndex = ($this->currentImageIndex + 1) % $totalImages;
    }

    public function prevImage()
    {
        $totalImages = $this->getPropertyImages()->count();
        if ($totalImages <= 1) {
            return;
        }

        $this->currentImageIndex = ($this->currentImageIndex - 1 + $totalImages) % $totalImages;
    }

    public function getListingBadgeClasses()
    {
        return match ($this->property->listing_type) {
            'rent' => 'bg-blue-500/90 text-white backdrop-blur-sm',
            'sale' => 'bg-emerald-500/90 text-white backdrop-blur-sm',
            'airbnb' => 'bg-purple-500/90 text-white backdrop-blur-sm',
            'commercial' => 'bg-yellow-500/90 text-white backdrop-blur-sm',
            default => 'bg-gray-500/90 text-white backdrop-blur-sm',
        };
    }

    public function getWhatsAppUrl()
    {
        $phoneNumber = '254711614099'; // Added country code for Kenya (254)
        $message = urlencode("Hi, I'm interested in the property: {$this->property->title} at {$this->property->location}. I would like to know more about it.");
        return "https://wa.me/{$phoneNumber}?text={$message}";
    }

    public function openInquiryModal(): void 
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }
}; ?>

<div x-data="{ showModal: @entangle('showModal') }">
    {{-- Property Card - Modern Design 2024+ --}}
    <div
        class="group relative overflow-hidden rounded-xl bg-white shadow-md transition-all duration-300 hover:shadow-lg dark:bg-gray-800 dark:shadow-gray-700/30">
        {{-- Image Section with Slider for Airbnb Properties --}}
        <div class="relative aspect-w-16 aspect-h-10 overflow-hidden">
            {{-- Main Image --}}
            <img src="{{ $this->getCurrentImage() }}" alt="{{ $this->property->title }}"
                class="h-full w-full object-cover transition-all duration-500 group-hover:scale-110">

            {{-- Slider Controls (only for Airbnb properties with multiple images) --}}
            @if ($this->property->listing_type === 'airbnb' && $showingSlider)
                <div
                    class="absolute inset-0 flex items-center justify-between px-4 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button wire:click="prevImage"
                        class="bg-black/50 rounded-full p-2 text-white hover:bg-black/70 transition">
                        <flux:icon name="chevron-left" class="h-6 w-6" />
                    </button>
                    <button wire:click="nextImage"
                        class="bg-black/50 rounded-full p-2 text-white hover:bg-black/70 transition">
                        <flux:icon name="chevron-right" class="h-6 w-6" />
                    </button>
                </div>

                {{-- Image Counter --}}
                <div class="absolute bottom-4 right-4 bg-black/50 rounded-full px-2 py-1 text-xs text-white">
                    {{ $currentImageIndex + 1 }} / {{ $this->getPropertyImages()->count() }}
                </div>
            @endif

            {{-- Listing Badge --}}
            <div class="absolute left-4 top-4 z-1">
                <span
                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium tracking-wide shadow-sm {{ $this->getListingBadgeClasses() }}">
                    {{ ucfirst($this->property->listing_type) }}
                </span>
            </div>

            {{-- Save/Favorite Button Overlay --}}
            <button
                class="absolute right-4 top-4 rounded-full bg-white/80 p-2 text-gray-700 opacity-0 transition-opacity duration-300 hover:bg-white dark:bg-gray-800/80 dark:text-gray-200 dark:hover:bg-gray-800 group-hover:opacity-100">
                <flux:icon name="heart" class="h-5 w-5" />
            </button>
        </div>

        {{-- Content Section with Improved Typography and Spacing --}}
        <div class="p-5">
            {{-- Property Title with Improved Typography --}}
            <h3 class="mb-1 truncate text-lg font-medium text-gray-900 dark:text-white">{{ $this->property->title }}</h3>

            {{-- Location with Icon Alignment --}}
            <div class="mb-4 flex items-center text-sm text-gray-600 dark:text-gray-300">
                <flux:icon name="map-pin" class="mr-1.5 h-4 w-4" />
                <span class="truncate">{{ $this->property->location }}</span>
            </div>

            {{-- Property Features with Modern Icons and Layout --}}
            <div class="grid grid-cols-3 gap-2">
                <div class="flex flex-col items-center rounded-lg bg-gray-50 p-2 text-center dark:bg-gray-700/50">
                    <flux:icon name="home" class="h-5 w-5 text-gray-500 dark:text-gray-300" />
                    <span class="mt-1 text-xs">{{ $this->property->bedrooms }} Beds</span>
                </div>
                <div class="flex flex-col items-center rounded-lg bg-gray-50 p-2 text-center dark:bg-gray-700/50">
                    <flux:icon name="square-3-stack-3d" class="h-5 w-5 text-gray-500 dark:text-gray-300" />
                    <span class="mt-1 text-xs">{{ $this->property->bathrooms }} Baths</span>
                </div>
                <div class="flex flex-col items-center rounded-lg bg-gray-50 p-2 text-center dark:bg-gray-700/50">
                    <flux:icon name="squares-2x2" class="h-5 w-5 text-gray-500 dark:text-gray-300" />
                    <span class="mt-1 text-xs">{{ number_format($this->property->size) }} sqft</span>
                </div>
            </div>

            {{-- Pricing Section with CTA --}}
            <div class="mt-6">
                <div class="flex items-start justify-between flex-col gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $this->getPriceLabel() }}</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">KSH {{ $this->getFormattedPrice() }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a 
                            href="{{ route('properties.show', $property) }}" 
                            wire:navigate
                            class="flex items-center rounded-lg bg-indigo-500 px-3 py-2 text-sm font-medium text-white transition-all duration-300 hover:bg-indigo-600"
                        >
                            <flux:icon name="eye" class="mr-1.5 h-5 w-5" />
                            View
                        </a>
                        <button
                            wire:click="openInquiryModal"
                            class="flex items-center rounded-lg bg-green-500 px-3 py-2 text-sm font-medium text-white transition-all duration-300 hover:bg-green-600"
                        >
                            <flux:icon name="chat-bubble-left-ellipsis" class="mr-1.5 h-5 w-5" />
                            Inquire
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- WhatsApp Modal --}}
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-90"
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-800/50 dark:bg-gray-500/50 transition-opacity" 
                     x-show="showModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="showModal = false"></div>

                <!-- Modal panel -->
                <div class="relative inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle dark:bg-gray-800">
                <div class="bg-gradient-to-r from-green-500/20 to-green-600/20 dark:from-green-900/30 dark:to-green-700/30 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <flux:icon name="chat-bubble-left-ellipsis" class="w-6 h-6 text-green-600" />
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Contact via WhatsApp
                            </h3>
                        </div>
                        <button @click="showModal = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors">
                            <flux:icon name="x-mark" class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Modal Content -->
                    <div class="mb-4 flex items-center space-x-4">
                        <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg">
                            <img src="{{ $this->getCurrentImage() }}" 
                                alt="{{ $this->property->title }}"
                                class="h-full w-full object-cover">
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $this->property->title }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $this->property->location }}</p>
                        </div>
                    </div>

                    <div class="mb-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                        <p class="text-gray-700 dark:text-gray-300">
                            You'll be redirected to WhatsApp to inquire about this property. Our agent will respond as soon as possible.
                        </p>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <flux:button @click="showModal = false" variant="primary">
                            Cancel
                        </flux:button>
                        <flux:button 
                            href="{{ $this->getWhatsAppUrl() }}"
                            target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-medium hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm"
                        >
                            <flux:icon name="chat-bubble-left-ellipsis" class="w-4 h-4 mr-2" />
                            Open WhatsApp
                        </flux:button>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
