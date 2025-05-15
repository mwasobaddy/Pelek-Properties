<?php

use App\Models\PropertyType;
use App\Services\RentalPropertyService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new class extends Component {
    use WithPagination;

    #[Url]
    public $priceMin = '';

    #[Url]
    public $priceMax = '';

    #[Url]
    public $furnished = false;

    #[Url]
    public $availableFrom = '';

    #[Url]
    public $minLease = '';

    #[Url]
    public $propertyType = '';

    #[Url]
    public $sort = 'latest';

    public function mount(): void
    {
        $this->availableFrom = Carbon::today()->format('Y-m-d');
    }

    #[Computed]
    public function propertyTypes()
    {
        return PropertyType::orderBy('name')->get();
    }

    #[Computed]
    public function rentalProperties()
    {
        return app(RentalPropertyService::class)->getRentalProperties([
            'price_min' => $this->priceMin,
            'price_max' => $this->priceMax,
            'furnished' => $this->furnished,
            'available_from' => $this->availableFrom,
            'min_lease' => $this->minLease,
            'property_type' => $this->propertyType,
        ]);
    }

    public function resetFilters(): void
    {
        $this->reset([
            'priceMin',
            'priceMax',
            'furnished',
            'minLease',
            'propertyType',
        ]);
        $this->availableFrom = Carbon::today()->format('Y-m-d');
    }
} ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Filter Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Price Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Monthly Rent Range
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <input 
                            type="number" 
                            wire:model.live.debounce.500ms="priceMin"
                            placeholder="Min"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                    </div>
                    <div>
                        <input 
                            type="number" 
                            wire:model.live.debounce.500ms="priceMax"
                            placeholder="Max"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        >
                    </div>
                </div>
            </div>

            <!-- Property Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Property Type
                </label>
                <select 
                    wire:model.live="propertyType"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="">All Types</option>
                    @foreach($this->propertyTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Available From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Available From
                </label>
                <input 
                    type="date" 
                    wire:model.live="availableFrom"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
            </div>

            <!-- Furnished Status -->
            <div class="flex items-center">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model.live="furnished"
                        class="rounded border-gray-300 text-[#02c9c2] focus:ring-[#02c9c2] dark:border-gray-700 dark:bg-gray-900"
                    >
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Furnished Only</span>
                </label>
            </div>

            <!-- Minimum Lease Period -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Minimum Lease (Months)
                </label>
                <input 
                    type="number" 
                    wire:model.live="minLease"
                    placeholder="Any"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
            </div>

            <!-- Reset Filters -->
            <div class="flex items-end">
                <flux:button 
                    wire:click="resetFilters"
                    variant="secondary"
                >
                    Reset Filters
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Rental Properties
            </h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $this->rentalProperties->total() }} properties found
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($this->rentalProperties as $property)
                <livewire:components.property.card  
                    :property="$property"
                    :key="'rental-'.$property->id"
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
            {{ $this->rentalProperties->links() }}
        </div>
    </div>
</div>
