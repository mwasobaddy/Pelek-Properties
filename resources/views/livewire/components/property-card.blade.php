<?php

use function Livewire\Volt\{state, computed};

state([
    'showWhatsApp' => false
]); 

$formattedPrice = computed(function () {
    $price = match($this->property->listing_type) {
        'sale' => $this->property->price,
        'rent' => $this->property->rental_price_monthly,
        'airbnb' => $this->property->airbnb_price_nightly,
    };
    
    return number_format($price, 2);
});

$priceLabel = computed(function () {
    return match($this->property->listing_type) {
        'sale' => 'Price',
        'rent' => 'Monthly Rent',
        'airbnb' => 'Per Night',
    };
});

?>

<div>
    <div class="property-card">
        {{-- Image Section --}}
        <div class="relative overflow-hidden rounded-t-lg aspect-w-16 aspect-h-9">
            <img 
                src="{{ $property->featuredImage?->image_path ?? asset('images/placeholder.jpg') }}"
                alt="{{ $property->title }}"
                class="object-cover w-full h-full transition-transform duration-300 hover:scale-105"
            >
            <div class="absolute top-2 right-2">
                <span @class([
                    'px-2 py-1 text-xs font-semibold rounded-full',
                    'bg-blue-500 text-white dark:bg-blue-600' => $property->listing_type === 'rent',
                    'bg-green-500 text-white dark:bg-green-600' => $property->listing_type === 'sale',
                    'bg-purple-500 text-white dark:bg-purple-600' => $property->listing_type === 'airbnb',
                ])>
                    {{ ucfirst($property->listing_type) }}
                </span>
            </div>
        </div>

        {{-- Content Section --}}
        <div class="p-4">
            <h3 class="property-card-title">{{ $property->title }}</h3>
            
            <div class="mt-2">
                <p class="property-card-location">
                    <x-flux-icon name="map-pin" class="inline-block w-4 h-4 mr-1"/>
                    {{ $property->location }}
                </p>
            </div>

            <div class="grid grid-cols-3 gap-4 mt-4">
                <div class="text-center">
                    <x-flux-icon name="bed" class="inline-block w-4 h-4 pelek-text-secondary"/>
                    <p class="mt-1 text-sm pelek-text-secondary">{{ $property->bedrooms }} Beds</p>
                </div>
                <div class="text-center">
                    <x-flux-icon name="bath" class="inline-block w-4 h-4 pelek-text-secondary"/>
                    <p class="mt-1 text-sm pelek-text-secondary">{{ $property->bathrooms }} Baths</p>
                </div>
                <div class="text-center">
                    <x-flux-icon name="square" class="inline-block w-4 h-4 pelek-text-secondary"/>
                    <p class="mt-1 text-sm pelek-text-secondary">{{ $property->size }}m²</p>
                </div>
            </div>

            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div>
                    <span class="text-sm pelek-text-secondary">{{ $this->priceLabel }}</span>
                    <p class="property-card-price">KES {{ $this->formattedPrice }}</p>
                </div>
                
                <x-flux-button 
                    icon="whatsapp"
                    variant="primary"
                    wire:click="$set('showWhatsApp', true)"
                    class="bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600"
                >
                    Inquire
                </x-flux-button>
            </div>
        </div>

        {{-- WhatsApp Modal --}}
        <x-flux-modal wire:model="showWhatsApp">
            <x-flux-modal.header>
                Contact via WhatsApp
            </x-flux-modal.header>

            <x-flux-modal.body>
                <p class="pelek-text-secondary">
                    You'll be redirected to WhatsApp to inquire about:
                    <span class="font-semibold pelek-text-primary">{{ $property->title }}</span>
                </p>
            </x-flux-modal.body>

            <x-flux-modal.footer>
                <x-flux-button 
                    variant="secondary" 
                    wire:click="$set('showWhatsApp', false)"
                >
                    Cancel
                </x-flux-button>
                
                <x-flux-button 
                    variant="primary"
                    icon="whatsapp"
                    tag="a"
                    href="https://wa.me/{{ $property->whatsapp_number }}"
                    target="_blank"
                    class="bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600"
                >
                    Continue to WhatsApp
                </x-flux-button>
            </x-flux-modal.footer>
        </x-flux-modal>
    </div>
</div>
