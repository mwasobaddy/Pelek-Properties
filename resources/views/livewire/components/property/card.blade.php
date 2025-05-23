<?php

use App\Models\Property;
use function Livewire\Volt\{state, mount};
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;

new class extends Component {
    #[State]
    public $showModal = false;

    #[State]
    public $modalName = '';

    #[State]
    public $property = null;

    #[State]
    public $currentImageIndex = 0;

    #[State]
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
        $phoneNumber = preg_replace('/[^0-9+]/', '', $this->property->whatsapp_number);
        $message = urlencode("Hi, I'm interested in the property: {$this->property->title}");
        return "https://wa.me/{$phoneNumber}?text={$message}";
    }

        public function openInquiryModal(): void 
    {
        $this->showModal = true;
        $this->modalName = "whatsapp-modal-{$this->property->id}";
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->modalName = '';
    }
}; ?>

<div>
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
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $this->getPriceLabel() }}</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">KSH {{ $this->getFormattedPrice() }}
                        </p>
                    </div>
                    <button
                        wire:click="openInquiryModal"
                        class="flex items-center rounded-lg bg-green-500 px-3 py-2 text-sm font-medium text-white transition-all duration-300 hover:bg-green-600">
                        <flux:icon name="chat-bubble-left-ellipsis" class="mr-1.5 h-5 w-5" />
                        Inquire
                    </button>
                </div>
            </div>
        </div>

        {{-- WhatsApp Modal --}}
        <flux:modal 
            wire:model="showModal"
            name="whatsapp-modal-{{ $this->property->id }}" 
            dismissible
            class="max-w-md self-center justify-self-center !p-0"
        >
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

                <div class="flex justify-end space-x-3">
                    <flux:button 
                        wire:click="closeModal" 
                        variant="ghost"
                    >
                        Cancel
                    </flux:button>
                    <flux:button 
                        href="{{ $this->getWhatsAppUrl() }}"
                        target="_blank" 
                        icon="chat-bubble-left-ellipsis"
                        class="bg-green-500 hover:bg-green-600"
                    >
                        Open WhatsApp
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</div>
