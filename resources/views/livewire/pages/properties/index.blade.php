<?php

use Livewire\WithPagination;
use Livewire\Volt\Component;
use function Livewire\Volt\{state};
use App\Models\Property;
use App\Models\PropertyType;
use App\Services\PropertySearchService;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.guest')] class extends Component {
    use WithPagination;

    protected $queryString = [
        'search' => ['except' => ''],
        'propertyType' => ['except' => ''],
        'priceRange' => ['except' => ''],
        'onlyAvailable' => ['except' => false],
        'listingType' => ['except' => ''],
    ];

    public $priceRange;
    public $search = '';
    public $propertyType = null;
    public $onlyAvailable = false;
    public $listingType = null;
    public $pageTitle = 'All Properties';
    public $pageDescription = 'Browse our collection of properties';

    public function mount($type = null)
    {
        if ($type) {
            $this->listingType = $type;
            
            switch ($type) {
                case 'sale':
                    $this->pageTitle = 'Properties for Sale';
                    $this->pageDescription = "Discover your dream property in Nairobi's most desirable locations";
                    break;
                case 'rent':
                    $this->pageTitle = 'Properties for Rent';
                    $this->pageDescription = 'Find your perfect rental property in Nairobi';
                    break;
                case 'airbnb':
                    $this->pageTitle = 'Airbnb Properties';
                    $this->pageDescription = 'Find the perfect holiday home or short-term rental';
                    break;
            }
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->propertyType = null;
        $this->priceRange = null;
        $this->onlyAvailable = false;
    }

    public function with(): array
    {
        $min_price = null;
        $max_price = null;
        $priceRange = $this->priceRange;

        if ($priceRange !== null && $priceRange !== '') {
            $range = explode('-', (string) $priceRange);
            if (!empty($range[0])) {
                $min_price = (int) $range[0];
            }
            if (!empty($range[1])) {
                $max_price = (int) $range[1];
            }
        }

        $searchParams = [
            'search' => $this->search,
            'property_type_id' => $this->propertyType,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'status' => $this->onlyAvailable ? 'available' : null,
        ];

        if ($this->listingType) {
            $searchParams['listing_type'] = $this->listingType;
        }

        return [
            'properties' => app(PropertySearchService::class)->search($searchParams),
            'propertyTypes' => PropertyType::all()
        ];
    }
}
?>

<div class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 min-h-screen">
    <!-- Enhanced Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-zinc-900 to-[#012e2b] dark:from-zinc-950 dark:to-[#012e2b]">
        <!-- Background elements with parallax effect -->
        <div class="absolute inset-0" x-data="{}" x-on:scroll.window="$el.style.transform = `translateY(${window.scrollY * 0.1}px)`">
            <div class="absolute inset-0 bg-gradient-to-br from-zinc-900/85 via-[#012e2b]/75 to-[#02c9c2]/30 backdrop-blur-sm"></div>
            <!-- Replace with a relevant property image -->
            <img src="{{ asset('images/placeholder.webp') }}" alt="Properties Background" class="h-full w-full object-cover opacity-40">
        </div>
        
        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-[#02c9c2]/20 blur-3xl"></div>
            <div class="absolute right-0 bottom-0 h-80 w-80 rounded-full bg-[#02c9c2]/15 blur-3xl"></div>
        </div>
        
        <!-- Header Content -->
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:py-24">
            <div class="max-w-3xl animate-fade-in">
                <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">
                    {{ $listingType ? ucfirst($listingType) : 'All Properties' }}
                </span>
                <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white">
                    {{ $pageTitle }}
                </h1>
                <p class="mt-4 text-lg leading-relaxed text-zinc-300 sm:text-xl max-w-xl">
                    {{ $pageDescription }}
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="container mx-auto px-4 py-12 max-w-7xl">
        <!-- Modern Search and Filters Panel -->
        <div class="mb-10 rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-xl p-6 shadow-xl ring-1 ring-black/5 dark:ring-white/10 transition-all duration-300">
            <div class="space-y-6">
                <!-- Search Input with Floating Label -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="col-span-1 md:col-span-2 group relative">
                        <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                            <flux:icon name="magnifying-glass" class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                        </div>
                        <input 
                            wire:model.live="search" 
                            type="text" 
                            placeholder="Search by location, title or features..."
                            class="block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                        >
                    </div>
                    
                    <!-- Property Type Select -->
                    <div class="relative group">
                        <select 
                            wire:model.live="propertyType"
                            class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-4 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                        >
                            <option value="">All Property Types</option>
                            @foreach($propertyTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                            <flux:icon name="chevron-down" class="h-5 w-5" />
                        </div>
                    </div>
                </div>
                
                <!-- Additional Filters -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <!-- Availability Filter -->
                    <label class="flex items-center space-x-2 cursor-pointer group">
                        <div class="relative">
                            <input 
                                wire:model.live="onlyAvailable" 
                                type="checkbox" 
                                class="peer sr-only"
                            >
                            <div class="h-5 w-5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 peer-checked:bg-[#02c9c2] peer-checked:border-[#02c9c2] transition-colors duration-200"></div>
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100 text-white transition-opacity duration-200">
                                <flux:icon name="check" class="h-3 w-3" />
                            </div>
                        </div>
                        <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors duration-200">Show only available properties</span>
                    </label>
                    
                    <!-- Reset Filters -->
                    <button 
                        wire:click="resetFilters"
                        class="inline-flex items-center text-sm text-gray-500 hover:text-[#02c9c2] transition-colors duration-200"
                    >
                        <flux:icon name="arrow-path" class="h-4 w-4 mr-1.5" />
                        Reset all filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div wire:loading.delay class="flex justify-center my-12">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#02c9c2]"></div>
        </div>

        <!-- Properties Grid with Enhanced Animation -->
        <div 
            wire:loading.delay.remove
            x-data="{ show: false }"
            x-init="setTimeout(() => show = true, 100)"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            class="space-y-8"
        >
            <!-- Results Summary -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    @if($properties->total() > 0)
                        Showing <span class="font-medium">{{ $properties->firstItem() }}-{{ $properties->lastItem() }}</span> of <span class="font-medium">{{ $properties->total() }}</span> properties
                    @else
                        No properties found
                    @endif
                </p>
                
                <!-- Could add sorting options here in the future -->
            </div>

            <!-- Properties Grid with Modern Layout -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8">
                @forelse($properties as $property)
                    <div 
                        x-data="{ show: false }"
                        x-init="setTimeout(() => show = true, 50 * {{ $loop->index }})"
                        x-show="show"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform translate-y-4"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                    >
                        <livewire:components.property.card :property="$property" wire:key="property-{{ $property->id }}" />
                    </div>
                @empty
                    <!-- Enhanced Empty State -->
                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-center bg-white/50 dark:bg-gray-800/50 rounded-2xl backdrop-blur-sm">
                        <div class="mb-4 rounded-full bg-[#02c9c2]/10 p-4">
                            <flux:icon name="magnifying-glass" class="h-8 w-8 text-[#02c9c2]" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No properties found</h3>
                        <p class="text-gray-600 dark:text-gray-400 max-w-md mb-6">
                            We couldn't find any properties matching your search criteria. Try adjusting your filters or search term.
                        </p>
                        <button 
                            wire:click="resetFilters"
                            class="inline-flex items-center px-4 py-2 rounded-lg bg-[#02c9c2] text-white hover:bg-[#02c9c2]/90 transition-colors duration-200"
                        >
                            <flux:icon name="arrow-path" class="h-4 w-4 mr-2" />
                            Reset Filters
                        </button>
                    </div>
                @endforelse
            </div>

            <!-- Modern Pagination -->
            @if($properties->hasPages())
                <div class="mt-12 flex justify-center">
                    <div class="rounded-lg bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
                        {{ $properties->links() }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Contact Call to Action -->
        <div class="mt-20 px-6 py-10 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 shadow-xl relative overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-[#02c9c2]/10 rounded-full blur-2xl"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-[#02c9c2]/5 rounded-full blur-xl"></div>
            
            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-6 md:mb-0 md:mr-8">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Can't find what you're looking for?
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Contact our property specialists for personalized assistance
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('contact') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-[#02c9c2] to-[#012e2b] hover:from-[#012e2b] hover:to-[#02c9c2] transition-all duration-300 shadow-md hover:shadow-lg">
                        Contact Us
                        <flux:icon name="arrow-right" class="ml-2 -mr-1 h-5 w-5" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
