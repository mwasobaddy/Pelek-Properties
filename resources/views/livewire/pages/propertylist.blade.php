<?php

use App\Models\Property;
use App\Services\PropertySearchService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Livewire\Volt\{state, computed};

state([
    'search' => '',
    'propertyType' => null,
    'priceRange' => null,
    'onlyAvailable' => false
]);

$queryString = [
    'search' => ['except' => ''],
    'propertyType' => ['except' => ''],
    'priceRange' => ['except' => ''],
    'onlyAvailable' => ['except' => false],
];

$resetFilters = function () {
    $this->search = '';
    $this->propertyType = null;
    $this->priceRange = null;
    $this->onlyAvailable = false;
};

$properties = computed(function (PropertySearchService $propertySearchService) {
    return $propertySearchService->search([
        'search' => $this->search,
        'property_type_id' => $this->propertyType,
        'min_price' => $this->priceRange ? explode('-', $this->priceRange)[0] : null,
        'max_price' => $this->priceRange ? explode('-', $this->priceRange)[1] : null,
        'status' => $this->onlyAvailable ? 'available' : null,
    ]);
});

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;
            'properties' => $this->properties(),
        ];
    }
} ?>

<div class="container mx-auto px-4 py-8">
    <!-- Search and Filters -->
    <div class="mb-8 space-y-4">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input 
                    wire:model.live="search" 
                    type="text" 
                    placeholder="Search properties..." 
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                >
            </div>
            <div class="flex gap-4">
                <button 
                    wire:click="resetFilters"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800"
                >
                    Clear Filters
                </button>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-4">
            <label class="flex items-center">
                <input 
                    wire:model.live="onlyAvailable" 
                    type="checkbox" 
                    class="form-checkbox h-5 w-5 text-indigo-600"
                >
                <span class="ml-2">Show only available</span>
            </label>
        </div>
    </div>

    <!-- Properties Grid -->
    <div wire:loading.delay.class="opacity-50">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($properties as $property)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Property Card -->
                    <div class="relative">
                        @if($property->images->isNotEmpty())
                            <img 
                                src="{{ $property->images->first()->url }}" 
                                alt="{{ $property->title }}"
                                class="w-full h-48 object-cover"
                            >
                        @endif
                        <div class="absolute top-2 right-2">
                            <span class="px-2 py-1 text-sm bg-indigo-500 text-white rounded-full">
                                {{ $property->propertyType->name }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2">{{ $property->title }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($property->description, 100) }}</p>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-indigo-600">
                                ${{ number_format($property->rental_price_daily) }} / day
                            </span>
                            <a 
                                href="{{ route('properties.show', $property) }}" 
                                class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition"
                            >
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $properties->links() }}
        </div>
    </div>
</div>
