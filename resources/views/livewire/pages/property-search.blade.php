<?php

use Livewire\WithPagination;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed};

new #[Layout('components.layouts.guest')] class extends Component {
use App\Models\Property;
use App\Models\PropertyType;
use App\Services\PropertySearchService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed};

// new #[Layout('components.layouts.guest')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?string $type = null;

    #[Url]
    public ?string $priceRange = null;

    #[Url]
    public string $sortBy = 'newest';

    #[Url]
    public array $amenities = [];

    public bool $showFilters = false;

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function setType($type)
    {
        $this->type = $type === $this->type ? null : $type;
    }

    public function setPriceRange($range)
    {
        $this->priceRange = $range === $this->priceRange ? null : $range;
    }

    public function toggleAmenity($amenityId)
    {
        if (in_array($amenityId, $this->amenities)) {
            $this->amenities = array_diff($this->amenities, [$amenityId]);
        } else {
            $this->amenities[] = $amenityId;
        }
    }

    public function resetFilters()
    {
        $this->reset('search', 'type', 'priceRange', 'sortBy', 'amenities');
    }

    public function with(): array
    {
        return [
            'properties' => computed(function () {
                return app(PropertySearchService::class)->search(
                    search: $this->search,
                    type: $this->type,
                    priceRange: $this->priceRange,
                    amenities: $this->amenities,
                    sortBy: $this->sortBy
                )->paginate(12);
            }),
            'propertyTypes' => computed(function () {
                return PropertyType::all();
            }),
            'priceRanges' => computed(function () {
                return [
                    '0-100' => 'Under $100',
                    '100-200' => '$100 - $200',
                    '200-300' => '$200 - $300',
                    '300-500' => '$300 - $500',
                    '500-plus' => '$500+'
                ];
            }),
            'sortOptions' => computed(function () {
                return [
                    'newest' => 'Newest First',
                    'price_low' => 'Price: Low to High',
                    'price_high' => 'Price: High to Low'
                ];
            })
        ];
    }
} ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Search Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex-1">
            <div class="relative">
                <x-flux-input 
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search properties by location, title or description..."
                    class="w-full pr-10"
                />
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <x-flux-icon 
                        name="magnifying-glass"
                        class="h-5 w-5 text-gray-400 dark:text-gray-600"
                    />
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <x-flux-dropdown>
                <x-slot name="trigger">
                    <x-flux-button>
                        {{ $sortOptions[$sortBy] }}
                        <x-flux-icon name="chevron-down" class="ml-2 h-4 w-4"/>
                    </x-flux-button>
                </x-slot>
                
                @foreach($sortOptions as $value => $label)
                    <x-flux-dropdown-item 
                        wire:click="$set('sortBy', '{{ $value }}')"
                        :active="$sortBy === $value"
                    >
                        {{ $label }}
                    </x-flux-dropdown-item>
                @endforeach
            </x-flux-dropdown>

            <x-flux-button
                wire:click="toggleFilters"
                variant="secondary"
            >
                <x-flux-icon name="adjustments-horizontal" class="h-5 w-5 mr-2"/>
                Filters
            </x-flux-button>
        </div>
    </div>

    <!-- Active Filters -->
    @if($type || $priceRange || !empty($amenities))
        <div class="flex flex-wrap items-center gap-2 mb-6">
            @if($type)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                    {{ $propertyTypes->firstWhere('id', $type)->name }}
                    <button wire:click="setType('{{ $type }}')" class="ml-2 text-indigo-600 hover:text-indigo-500">
                        <x-flux-icon name="x-mark" class="h-4 w-4"/>
                    </button>
                </span>
            @endif

            @if($priceRange)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                    {{ $priceRanges[$priceRange] }}
                    <button wire:click="setPriceRange('{{ $priceRange }}')" class="ml-2 text-indigo-600 hover:text-indigo-500">
                        <x-flux-icon name="x-mark" class="h-4 w-4"/>
                    </button>
                </span>
            @endif

            @if(!empty($amenities))
                <button
                    wire:click="resetFilters"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                >
                    Clear all filters
                </button>
            @endif
        </div>
    @endif

    <!-- Filters Panel -->
    <div x-show="$wire.showFilters" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8"
    >
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Property Types -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Property Type</h3>
                <div class="space-y-3">
                    @foreach($propertyTypes as $propertyType)
                        <label class="flex items-center">
                            <input
                                type="radio"
                                name="type"
                                wire:click="setType('{{ $propertyType->id }}')"
                                :checked="$wire.type === '{{ $propertyType->id }}'"
                                class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                            >
                            <span class="ml-3 text-gray-700 dark:text-gray-300">
                                {{ $propertyType->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Price Ranges -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Price Range</h3>
                <div class="space-y-3">
                    @foreach($priceRanges as $value => $label)
                        <label class="flex items-center">
                            <input
                                type="radio"
                                name="priceRange"
                                wire:click="setPriceRange('{{ $value }}')"
                                :checked="$wire.priceRange === '{{ $value }}'"
                                class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                            >
                            <span class="ml-3 text-gray-700 dark:text-gray-300">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div wire:loading.delay.class="opacity-50">
        @if($properties->isEmpty())
            <div class="text-center py-12">
                <x-flux-icon name="magnifying-glass" class="mx-auto h-12 w-12 text-gray-400"/>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No properties found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Try adjusting your search or filter criteria
                </p>
                <div class="mt-6">
                    <button
                        wire:click="resetFilters"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                        Reset all filters
                    </button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($properties as $property)
                    <div wire:key="{{ $property->id }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
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
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                {{ $property->title }}
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                                {{ Str::limit($property->description, 100) }}
                            </p>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
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
        @endif
    </div>
</div>
