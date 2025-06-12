<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state};
use App\Models\Property;
use App\Models\PropertyType;
use App\Services\PropertySearchService;
use App\Services\SEOService;
use Livewire\Attributes\Layout;
use \Livewire\WithPagination;

new #[Layout('components.layouts.guest')] class extends Component {
    use WithPagination;

    // Use our custom pagination view
    protected $paginationTheme = 'tailwind';
    
    // Keep scroll position when paginating
    protected $preserveScroll = true;

    // Define state variables
    public $search = '';
    public $propertyType = null;
    public $priceRange = '';
    public $onlyAvailable = false;
    public $listingType = null;
    public $location = '';
    public $neighborhood = '';
    public $bedrooms = '';
    public $bathrooms = '';
    public $floors = '';
    public $squareRange = '';
    public $propertyListingType = '';
    public $propertyBaseType = '';
    public $amenities = [];
    public $sort = '';

    // Page title and description
    public $pageTitle = 'Explore Our Properties';
    public $pageDescription = 'Find your perfect property from our extensive collection';
    
    // Cities and amenities lists
    public $cities = [];
    public $allAmenities = [];
    public $neighborhoods = [];
    
    public function mount(SEOService $seoService, $type = null)
    {
        // Get the property type from the URL query parameter
        $propertyListingType = request()->query('propertyListingType', 'all');
        
        // Set SEO meta tags based on property type
        if ($propertyListingType !== 'all') {
            $seoService->setPropertyTypeMeta($propertyListingType);
        } else {
            // Set default property listing SEO
            SEOMeta::setTitle('Properties for Sale, Rent & Airbnb in Kenya | Pelek Properties');
            SEOMeta::setDescription('Browse our curated selection of properties in Kenya. Find luxury homes, apartments, commercial spaces, and Airbnb rentals. Trusted by thousands for quality real estate.');
            SEOMeta::addKeyword([
                'property kenya',
                'real estate kenya', 
                'houses for sale kenya',
                'apartments for rent kenya',
                'commercial property kenya',
                'airbnb kenya'
            ]);
            
            JsonLd::setType('RealEstateAgent');
            JsonLd::addValue('@context', 'https://schema.org');
            JsonLd::setTitle('Pelek Properties - All Properties');
            JsonLd::setDescription('Browse our complete collection of premium properties across Kenya.');
            JsonLd::addValue('areaServed', [
                '@type' => 'Country',
                'name' => 'Kenya'
            ]);
        }
        
        // Initialize the PropertySearchService
        $propertySearchService = app(PropertySearchService::class);

        // Fetch cities and amenities
        $this->cities = $propertySearchService->getAvailableCities();
        $this->allAmenities = $propertySearchService->getAvailableAmenities();

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
                case 'commercial':
                    $this->pageTitle = 'Commercial Properties';
                    $this->pageDescription = 'Explore our range of commercial properties for your business needs';
                    break;
            }
        }
    }
    
    protected $queryString = [
        'search' => ['except' => ''],
        'propertyType' => ['except' => ''],
        'priceRange' => ['except' => ''],
        'onlyAvailable' => ['except' => false],
        'listingType' => ['except' => ''],
        'location' => ['except' => ''],
        'neighborhood' => ['except' => ''],
        'bedrooms' => ['except' => ''],
        'bathrooms' => ['except' => ''],
        'floors' => ['except' => ''],
        'squareRange' => ['except' => ''],
        'propertyListingType' => ['except' => ''],
        'propertyBaseType' => ['except' => ''],
        'amenities' => ['except' => []],
        'sort' => ['except' => '']
    ];

    public function boot(PropertySearchService $propertySearchService)
    {
        // Fetch cities and amenities
        $this->cities = $propertySearchService->getAvailableCities();
        $this->allAmenities = $propertySearchService->getAvailableAmenities();
    }

    public function updating($name, $value)
    {
        if ($name === 'listingType') {
            switch ($value) {
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
                case 'commercial':
                    $this->pageTitle = 'Commercial Properties';
                    $this->pageDescription = 'Explore our range of commercial properties for your business needs';
                    break;
            }
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->propertyType = null;
        $this->priceRange = '';
        $this->onlyAvailable = false;
        $this->listingType = null;
        $this->location = '';
        $this->neighborhood = '';
        $this->bedrooms = '';
        $this->bathrooms = '';
        $this->squareRange = '';
        $this->floors = '';
        $this->amenities = [];

        // Reset pagination to first page
        $this->resetPage();
    }

    public function updatedLocation()
    {
        $this->resetPage();
    }

    public function updatedFloors()
    {
        $this->resetPage();
    }

    public function updatedSquareRange()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPropertyType()
    {
        $this->resetPage();
    }

    public function updatedPriceRange()
    {
        $this->resetPage();
    }

    public function updatedSort()
    {
        $this->resetPage();
    }

    public function updatedCity()
    {
        $this->resetPage();
        // Reset neighborhood when city changes
        $this->neighborhood = '';

        // Update neighborhoods based on selected city using the service
        if ($this->city) {
            $this->neighborhoods = app(PropertySearchService::class)->getNeighborhoodsByCity($this->city);
        } else {
            $this->neighborhoods = [];
        }
    }

    public function updatedNeighborhood()
    {
        $this->resetPage();
    }

    public function updatedBedrooms()
    {
        $this->resetPage();
    }

    public function updatedBathrooms()
    {
        $this->resetPage();
    }

    public function updatedAmenities()
    {
        $this->resetPage();
    }

    public function updatedPropertyListingType()
    {
        $this->resetPage();
    }

    public function updatedPropertyBaseType()
    {
        $this->resetPage();
    }

    #[Computed]
    public function with(): array
    {
        $min_price = null;
        $max_price = null;

        if ($this->priceRange !== null && $this->priceRange !== '') {
            $range = explode('-', (string) $this->priceRange);
            if (!empty($range[0])) {
                $min_price = (int) $range[0];
            }
            if (!empty($range[1])) {
                $max_price = (int) $range[1];
            }
        }

        // Handle square range
        $min_area = null;
        $max_area = null;
        if ($this->squareRange !== null && $this->squareRange !== '') {
            $range = explode('-', str_replace('+', '', $this->squareRange));
            if (!empty($range[0])) {
                $min_area = (int) $range[0];
            }
            if (!empty($range[1])) {
                $max_area = (int) $range[1];
            }
        }

        // Update the searchParams with new filters
        $searchParams = array_filter(
            [
                'search' => $this->search,
                'property_type_id' => $this->propertyType,
                'min_price' => $min_price,
                'max_price' => $max_price,
                'status' => $this->onlyAvailable ? 'available' : null,
                'listing_type' => $this->propertyListingType ?: $this->listingType,
                'type' => $this->propertyBaseType,
                'location' => $this->location,
                'city' => $this->city,
                'neighborhood' => $this->neighborhood,
                'bedrooms' => $this->bedrooms,
                'bathrooms' => $this->bathrooms,
                'floors' => $this->floors,
                'amenities' => $this->amenities,
                'square_range' => $this->squareRange,
                'min_area' => $min_area,
                'max_area' => $max_area,
                'sort' => $this->sort,
            ],
            function ($value) {
                return $value !== null && $value !== '';
            },
        );

        $properties = app(PropertySearchService::class)->search($searchParams);

        return [
            'properties' => $properties,
            'propertyTypes' => PropertyType::orderBy('name')->get(),
        ];
    }
};
?>

<div class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 min-h-screen">
    <!-- Enhanced Hero Section -->
    <div
        class="relative overflow-hidden bg-gradient-to-br from-zinc-900 to-[#012e2b] dark:from-zinc-950 dark:to-[#012e2b]">
        <!-- Background elements with parallax effect -->
        <div class="absolute inset-0" x-data="{}"
            x-on:scroll.window="$el.style.transform = `translateY(${window.scrollY * 0.1}px)`">
            <div
                class="absolute inset-0 bg-gradient-to-br from-zinc-900/85 via-[#012e2b]/75 to-[#02c9c2]/30 backdrop-blur-sm">
            </div>
            <!-- Replace with a relevant property image -->
            <img src="{{ asset('images/placeholder.webp') }}" alt="Properties Background"
                class="h-full w-full object-cover opacity-40">
        </div>

        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-[#02c9c2]/20 blur-3xl"></div>
            <div class="absolute right-0 bottom-0 h-80 w-80 rounded-full bg-[#02c9c2]/15 blur-3xl"></div>
        </div>

        <!-- Header Content -->
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:py-24">
            <div class="flex flex-col items-center justify-center text-center">
                <div class="max-w-3xl animate-fade-in flex flex-col items-start">
                    <span
                        class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">
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
    </div>

    <!-- Main Content Area -->
    <div class="container mx-auto px-4 py-12 max-w-7xl">
        <!-- Modern Search and Filters Panel -->
        <div
            class="relative z-2 mb-10 rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-xl p-6 shadow-xl ring-1 ring-black/5 dark:ring-white/10 transition-all duration-300">
            <!-- Mobile Filter Toggle Button -->
            <div class="md:hidden flex justify-between items-center mb-6">
                <button x-data="" x-on:click="$dispatch('toggle-filters')"
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-[#02c9c2]/10 text-[#02c9c2] hover:bg-[#02c9c2]/20 transition-colors duration-200">
                    <flux:icon name="adjustments-horizontal" class="h-5 w-5 mr-2" />
                    Filters
                </button>

                <!-- Sort Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-[#02c9c2]/10 text-[#02c9c2] hover:bg-[#02c9c2]/20 transition-colors duration-200">
                        <flux:icon name="arrows-up-down" class="h-5 w-5 mr-2" />
                        Sort
                    </button>
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-48 rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 dark:ring-white/10 z-50">
                        <div class="py-1">
                            <button wire:click="$set('sort', 'name_asc')"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                A to Z
                            </button>
                            <button wire:click="$set('sort', 'name_desc')"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Z to A
                            </button>
                            <button wire:click="$set('sort', 'newest')"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Newest First
                            </button>
                            <button wire:click="$set('sort', 'oldest')"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                Oldest First
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6" x-data="{ showFilters: window.innerWidth >= 768 }" x-on:toggle-filters.window="showFilters = !showFilters"
                x-on:resize.window="showFilters = window.innerWidth >= 768">
                <!-- Search input - Always visible -->
                <div class="relative group mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search
                        Properties</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                            <flux:icon name="magnifying-glass"
                                class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                            placeholder="Search by name, location, or type..."
                            class="block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                    </div>
                </div>

                <!-- Filter section - Toggleable on mobile -->
                <div x-show="showFilters" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform -translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform -translate-y-4" class="space-y-6">
                    <!-- Filter Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <!-- City -->

                        <div class="relative group">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <flux:icon name="building-office"
                                        class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                                </div>
                                <select wire:model.live="city"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">Any City</option>
                                    @foreach ($cities as $cityOption)
                                        <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                                    @endforeach
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>

                        <!-- Neighborhood (dependent on city) -->
                        <div class="relative group">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Neighborhood</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <flux:icon name="map-pin"
                                        class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                                </div>
                                <select wire:model.live="neighborhood"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                    @disabled(!$city)>
                                    <option value="">Any Neighborhood</option>
                                    @foreach ($neighborhoods ?? [] as $neighborhoodOption)
                                        <option value="{{ $neighborhoodOption }}">{{ $neighborhoodOption }}</option>
                                    @endforeach
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>

                        <!-- Bedrooms -->
                        <div class="relative group">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bedrooms</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <flux:icon name="home"
                                        class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                                </div>
                                <select wire:model.live="bedrooms"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">Any Bedrooms</option>
                                    <option value="1">1+ Bedroom</option>
                                    <option value="2">2+ Bedrooms</option>
                                    <option value="3">3+ Bedrooms</option>
                                    <option value="4">4+ Bedrooms</option>
                                    <option value="5">5+ Bedrooms</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>

                        {{-- <!-- Bathrooms -->
                        <div class="relative group">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bathrooms</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <flux:icon name="hand-thumb-up"
                                        class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                                </div>
                                <select wire:model.live="bathrooms"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">Any Bathrooms</option>
                                    <option value="1">1+ Bathroom</option>
                                    <option value="2">2+ Bathrooms</option>
                                    <option value="3">3+ Bathrooms</option>
                                    <option value="4">4+ Bathrooms</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div> --}}

                        <!-- Property Type -->
                        <div class="relative group">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property
                                Type</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <flux:icon name="building-office-2"
                                        class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                                </div>
                                <select wire:model.live="propertyType"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">All Property Types</option>
                                    @foreach ($propertyTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>

                        <!-- Floors -->
                        <div class="relative group">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Floors</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <flux:icon name="building-office"
                                        class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                                </div>
                                <select wire:model.live="floors"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">Any Floors</option>
                                    @foreach (range(1, 50) as $floor)
                                        <option value="{{ $floor }}">{{ $floor }}
                                            {{ Str::plural('Floor', $floor) }}</option>
                                    @endforeach
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>

                        <!-- Square Range -->
                        {{-- <div class="relative group">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Square
                                Range</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <flux:icon name="square-2-stack"
                                        class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                                </div>
                                <select wire:model.live="squareRange"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">Any Size</option>
                                    <option value="0-50">Up to 50 m²</option>
                                    <option value="50-100">50 - 100 m²</option>
                                    <option value="100-200">100 - 200 m²</option>
                                    <option value="200-300">200 - 300 m²</option>
                                    <option value="300-500">300 - 500 m²</option>
                                    <option value="500+">500+ m²</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div> --}}

                        <!-- Desktop Sort (hidden on mobile) -->
                        <div class="hidden md:flex justify-end flex-col">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort
                                By</label>
                            <div class="relative w-full" x-data="{ open: false }">
                                <select wire:model.live="sort"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">Sort By</option>
                                    <option value="name_asc">A to Z</option>
                                    <option value="name_desc">Z to A</option>
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <flux:icon name="arrows-up-down"
                                        class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                                </div>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>

                        {{-- <!-- Flat Range -->
                        <div class="relative group">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Square
                                Range</label>
                            <div class="relative">
                                <select wire:model.live="flatRange"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-4 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">All squares</option>
                                    <option value="0-100">0 - 100 m²</option>
                                    <option value="100-200">100 - 200 m²</option>
                                    <option value="200-300">200 - 300 m²</option>
                                    <option value="300-400">300 - 400 m²</option>
                                    <option value="400+">400+ m²</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div> --}}

                        <!-- Price Range -->
                        <div class="relative group">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price
                                Range</label>
                            <div class="relative">
                                <select wire:model.live="priceRange"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-4 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">Any Price</option>
                                    <option value="0-100000">$0 - $100,000</option>
                                    <option value="100000-300000">$100,000 - $300,000</option>
                                    <option value="300000-500000">$300,000 - $500,000</option>
                                    <option value="500000-1000000">$500,000 - $1,000,000</option>
                                    <option value="1000000+">$1,000,000+</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>

                        <!-- Amenities -->
                        {{-- <div class="relative group">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amenities</label>
                            <div class="relative">
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" type="button"
                                        class="relative w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-left text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                        <span class="flex items-center">
                                            <flux:icon name="squares-2x2"
                                                class="h-5 w-5 text-gray-400 absolute left-3" />
                                            <span
                                                class="ml-2">{{ count($amenities) > 0 ? count($amenities) . ' selected' : 'Select Amenities' }}</span>
                                        </span>
                                    </button>

                                    <div x-show="open" @click.away="open = false"
                                        class="absolute z-50 mt-2 w-full rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 dark:ring-white/10 p-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            @foreach ($allAmenities as $amenity)
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" wire:model.live="amenities"
                                                        value="{{ $amenity }}"
                                                        class="rounded border-gray-300 text-[#02c9c2] shadow-sm focus:border-[#02c9c2] focus:ring focus:ring-[#02c9c2] focus:ring-opacity-50">
                                                    <span
                                                        class="ml-2 text-sm text-gray-700 dark:text-gray-200">{{ $amenity }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        <!-- Property Base Type -->
                        <div class="relative group">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property Category</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <flux:icon name="home-modern" class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                                </div>
                                <select 
                                    wire:model.live="propertyBaseType"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                >
                                    <option value="">All Categories</option>
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="AirBNB">AirBNB</option>
                                    <option value="land">Land</option>
                                    <option value="industrial">Industrial</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>

                        <!-- Property Listing Type -->
                        <div class="relative group">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Listing Type</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <flux:icon name="tag" class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                                </div>
                                <select 
                                    wire:model.live="propertyListingType"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                >
                                    <option value="">All Listings</option>
                                    <option value="sale">For Sale</option>
                                    <option value="rent">For Rent</option>
                                    <option value="airbnb">Airbnb</option>
                                    <option value="commercial">Commercial</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200">
                                    <flux:icon name="chevron-down" class="h-5 w-5" />
                                </div>
                            </div>
                        </div>

                        <!-- Available Properties Toggle -->
                        <label
                            class="flex items-center gap-3 cursor-pointer group relative px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors duration-200">
                            <div class="relative">
                                <input wire:model.live="onlyAvailable" type="checkbox" class="sr-only peer">
                                <div
                                    class="w-10 h-5 rounded-full bg-gray-200 dark:bg-gray-700 peer-checked:bg-gradient-to-r from-[#02c9c2] to-[#02a8a2] after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all after:duration-300 peer-checked:after:translate-x-5 shadow-inner">
                                </div>
                            </div>
                            <span
                                class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors duration-200">
                                Available Properties Only
                            </span>
                        </label>
                        <!-- End of Filter Grid -->
                    </div>

                    <!-- Additional Filters Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Filter Actions -->
                        <div class="flex flex-col md:flex-row items-center justify-center gap-4 col-span-2 mt-2">

                            <!-- Reset Filters Button -->
                            <button wire:click="resetFilters"
                                class="group relative overflow-hidden rounded-lg bg-gradient-to-r from-[#02c9c2] to-[#02a8a2] px-5 py-2.5 text-sm font-medium text-white shadow-md hover:shadow-lg transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]">
                                <!-- Background animation on hover -->
                                <span
                                    class="absolute inset-0 translate-y-full bg-gradient-to-r from-[#012e2b] to-[#014e4a] group-hover:translate-y-0 transition-transform duration-300 ease-out"></span>
                                <!-- Content remains visible -->
                                <span class="relative flex items-center gap-2">
                                    <flux:icon name="arrow-path"
                                        class="h-4 w-4 transition-transform group-hover:rotate-180 duration-500" />
                                    <span>Clear All Filters</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div wire:loading.delay class="flex justify-center my-12">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#02c9c2]"></div>
        </div>

        <!-- Properties Grid with Modern Layout -->
        <div wire:loading.class="opacity-50 pointer-events-none" 
             class="grid auto-rows-fr grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8"
             wire:key="properties-grid">
            @forelse($properties as $property)
                <div wire:key="property-{{ $property->id }}" x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)"
                    x-show="show" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0" class="h-full">
                    <livewire:components.property.card :property="$property" :wire:key="'card-'.$property->id" />
                </div>
            @empty
                <!-- Enhanced Empty State -->
                <div
                    class="col-span-full flex flex-col items-center justify-center py-16 text-center bg-white/50 dark:bg-gray-800/50 rounded-2xl backdrop-blur-sm">
                    <div class="mb-4 rounded-full bg-[#02c9c2]/10 p-4">
                        <flux:icon name="building-office-2" class="h-8 w-8 text-[#02c9c2]" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No properties found</h3>
                    <p class="text-gray-600 dark:text-gray-400 max-w-md mb-6">
                        We couldn't find any properties matching your search criteria. Try adjusting your filters or
                        search term.
                    </p>
                    <button wire:click="resetFilters"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-[#02c9c2] text-white hover:bg-[#02c9c2]/90 transition-colors duration-200">
                        <flux:icon name="arrow-path" class="h-4 w-4 mr-2" />
                        Reset Filters
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Modern Pagination -->
        @if ($properties->hasPages())
            <div class="mt-12">
                {{ $properties->links('components.pagination') }}
            </div>
        @endif
    </div>

    <!-- Contact Call to Action -->
    <div
        class="mt-20 px-6 py-10 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 shadow-xl relative overflow-hidden">
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
                <a href="{{ route('contact') }}" wire:navigate
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-[#02c9c2] to-[#012e2b] hover:from-[#012e2b] hover:to-[#02c9c2] transition-all duration-300 shadow-md hover:shadow-lg">
                    Contact Us
                    <flux:icon name="arrow-right" class="ml-2 -mr-1 h-5 w-5" />
                </a>
            </div>
        </div>
    </div>
</div>
