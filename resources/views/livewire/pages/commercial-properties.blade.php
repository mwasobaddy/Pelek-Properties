<?php

use App\Models\Facility;
use App\Services\CommercialPropertyService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new class extends Component {
    use WithPagination;

    #[Url]
    public $commercialType = '';

    #[Url]
    public $minSize = '';

    #[Url]
    public $maxSize = '';

    #[Url]
    public $minPrice = '';

    #[Url]
    public $maxPrice = '';

    #[Url]
    public $hasParking = false;

    #[Url]
    public array $selectedFacilities = [];

    public array $commercialTypes = [
        'office' => 'Office Space',
        'retail' => 'Retail Space',
        'industrial' => 'Industrial Space',
        'warehouse' => 'Warehouse',
        'mixed_use' => 'Mixed Use',
    ];

    #[Computed]
    public function facilities()
    {
        return app(CommercialPropertyService::class)->getFacilitiesGroupedByType();
    }

    #[Computed]
    public function commercialProperties()
    {
        return app(CommercialPropertyService::class)->getCommercialProperties([
            'commercial_type' => $this->commercialType,
            'min_size' => $this->minSize,
            'max_size' => $this->maxSize,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'has_parking' => $this->hasParking,
            'facilities' => $this->selectedFacilities,
        ]);
    }

    public function resetFilters(): void
    {
        $this->reset([
            'commercialType',
            'minSize',
            'maxSize',
            'minPrice',
            'maxPrice',
            'hasParking',
            'selectedFacilities'
        ]);
    }

    public function toggleFacility($id): void
    {
        if (in_array($id, $this->selectedFacilities)) {
            $this->selectedFacilities = array_diff($this->selectedFacilities, [$id]);
        } else {
            $this->selectedFacilities[] = $id;
        }
    }
} ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Filter Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Property Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Property Type
                </label>
                <select 
                    wire:model.live="commercialType"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="">All Types</option>
                    @foreach($commercialTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Size Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Size Range (sq ft)
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <input 
                            type="number" 
                            wire:model.live.debounce.500ms="minSize"
                            placeholder="Min"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                    </div>
                    <div>
                        <input 
                            type="number" 
                            wire:model.live.debounce.500ms="maxSize"
                            placeholder="Max"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                    </div>
                </div>
            </div>

            <!-- Price Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Price Range
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <input 
                            type="number" 
                            wire:model.live.debounce.500ms="minPrice"
                            placeholder="Min"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                    </div>
                    <div>
                        <input 
                            type="number" 
                            wire:model.live.debounce.500ms="maxPrice"
                            placeholder="Max"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                    </div>
                </div>
            </div>

            <!-- Parking -->
            <div class="flex items-center">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model.live="hasParking"
                        class="rounded border-gray-300 text-[#02c9c2] focus:ring-[#02c9c2] dark:border-gray-700 dark:bg-gray-900"
                    >
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Has Parking</span>
                </label>
            </div>
        </div>

        <!-- Facilities -->
        <div class="mt-6">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Facilities</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($this->facilities as $type => $facilitiesList)
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ $type }}</h4>
                        <div class="space-y-2">
                            @foreach($facilitiesList as $facility)
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:click="toggleFacility({{ $facility->id }})"
                                        @checked(in_array($facility->id, $selectedFacilities))
                                        class="rounded border-gray-300 text-[#02c9c2] focus:ring-[#02c9c2] dark:border-gray-700 dark:bg-gray-900"
                                    >
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $facility->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Reset Filters -->
        <div class="mt-6">
            <flux:button 
                wire:click="resetFilters"
                variant="secondary"
            >
                Reset Filters
            </flux:button>
        </div>
    </div>

    <!-- Results Section -->
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Commercial Properties
            </h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $this->commercialProperties->total() }} properties found
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($this->commercialProperties as $property)
                <livewire:components.property-card 
                    :property="$property"
                    :key="'commercial-'.$property->id"
                />
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">
                        No properties found matching your criteria.
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $this->commercialProperties->links() }}
        </div>
    </div>
</div>
