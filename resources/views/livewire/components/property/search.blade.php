<?php

use App\Models\PropertyType;
use App\Services\PropertySearchService;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed};

new class extends Component {
    public string $search = '';
    public ?string $type = '';
    public ?string $location = '';
    public ?int $minPrice = null;
    public ?int $maxPrice = null;
    public array $selectedAmenities = [];
    public string $listingType = 'all';

    public array $propertyTypes = [];
    public array $locations = [];
    public array $propertyStats = [];
    public array $priceRanges = [];
    public array $amenities = [];

    public function takeToSearch()
    {
        // Format price range
        $priceRange = null;
        if ($this->minPrice !== null || $this->maxPrice !== null) {
            $priceRange = ($this->minPrice ?? '') . '-' . ($this->maxPrice ?? '');
        }

        $params = array_filter([
            'search' => $this->search,
            'propertyType' => $this->type,
            'location' => $this->location,
            'priceRange' => $priceRange,
            'amenities' => !empty($this->selectedAmenities) ? implode(',', $this->selectedAmenities) : null,
            'propertyListingType' => $this->listingType !== 'all' ? $this->listingType : null,
        ], function($value) {
            return $value !== '' && $value !== null;
        });

        return $this->redirect(route('properties.index', $params), navigate: true);
    }

    public function mount()
    {
        $this->loadComputedProperties();
    }

    public function loadComputedProperties()
    {
        $searchService = app(PropertySearchService::class);
        $this->propertyTypes = PropertyType::orderBy('name')->get()->toArray();
        $this->locations = $searchService->getAvailableLocations();
        $this->propertyStats = $searchService->getPropertyCountsByType();
        $this->priceRanges = $searchService->getPriceRanges($this->listingType !== 'all' ? $this->listingType : null);
        $this->amenities = $searchService->getAvailableAmenities();
    }

    public function updatedListingType($value)
    {
        $this->priceRanges = app(PropertySearchService::class)->getPriceRanges($value !== 'all' ? $value : null);
        $this->minPrice = null;
        $this->maxPrice = null;
    }

    public function with(): array
    {
        return [
            'propertyTypes' => $this->propertyTypes,
            'locations' => $this->locations,
            'propertyStats' => $this->propertyStats,
            'priceRanges' => $this->priceRanges,
            'amenities' => $this->amenities,
        ];
    }
} ?>

<div x-data="{ activeTab: 'all' }" class="relative isolate overflow-hidden bg-gradient-to-br from-zinc-900 to-[#012e2b] dark:from-zinc-950 dark:to-[#012e2b]">
    {{-- Enhanced hero background with parallax effect --}}
    <div class="absolute inset-0" x-data="{}" x-on:scroll.window="$el.style.transform = `translateY(${window.scrollY * 0.1}px)`">
        <div class="absolute inset-0 bg-gradient-to-br from-zinc-900/85 via-[#012e2b]/75 to-[#02c9c2]/30 dark:from-zinc-950/90 dark:via-[#012e2b]/80 dark:to-[#02c9c2]/20 backdrop-blur-sm"></div>
        <img 
            src="{{ asset('images/placeholder.webp') }}" 
            alt="Luxury Property in Nairobi"
            class="h-full w-full object-cover object-center transition-all duration-700 filter"
            loading="eager"
        />
    </div>
    
    {{-- Enhanced decorative elements with branded colors --}}
    <div aria-hidden="true" class="absolute inset-0 overflow-hidden">
        <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-[#02c9c2]/20 dark:bg-[#02c9c2]/15 blur-3xl"></div>
        <div class="absolute right-0 bottom-0 h-80 w-80 rounded-full bg-[#02c9c2]/15 dark:bg-[#02c9c2]/10 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/3 h-64 w-64 rounded-full bg-[#02c9c2]/10 dark:bg-[#02c9c2]/5 blur-2xl"></div>
        <div class="absolute -right-20 top-1/4 h-72 w-72 rounded-full bg-[#02c9c2]/5 dark:bg-[#02c9c2]/5 blur-3xl"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-32">
        {{-- Hero Content with enhanced typography and animations --}}
        <div class="max-w-2xl transform transition-all duration-700 animate-fade-in">
            <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">Premier Property Search</span>
            <h1 class="font-display text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                Find Your <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#02c9c2] to-[#018f8a]">Perfect Property</span> in Nairobi
            </h1>
            {{-- <p class="mt-5 text-lg leading-relaxed text-zinc-300 dark:text-zinc-200 sm:text-xl max-w-xl">
                Explore premium properties for sale, rent, or short stays in Nairobi's most desirable neighborhoods.
            </p> --}}
        </div>

        {{-- Enhanced Search Form with glassmorphism design --}}
        <div class="mt-10 max-w-4xl transform transition-all duration-500 hover:translate-y-[-2px]">
            <div class="rounded-2xl bg-white/8 dark:bg-white/5 backdrop-blur-xl p-6 shadow-2xl ring-1 ring-white/20 dark:ring-white/10 transition-all duration-300">
                {{-- Listing Type Tabs with modern design --}}
                <div class="mb-6">
                    <label class="text-sm font-medium text-white/90 dark:text-white/80">I'm looking for:</label>
                    <div class="mt-2.5 flex flex-wrap gap-2 rounded-xl bg-white/5 dark:bg-zinc-800/30 p-1.5">
                        @foreach(['all' => 'All Properties', 'sale' => 'Sale', 'rent' => 'Rent', 'airbnb' => 'Airbnb', 'commercial' => 'Commercial'] as $value => $label)
                            <button
                                wire:click="$set('listingType', '{{ $value }}')"
                                @click="activeTab = '{{ $value }}'"
                                type="button"
                                @class([
                                    'rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-300 flex items-center',
                                    'bg-[#02c9c2] text-zinc-900 shadow-lg shadow-[#02c9c2]/20 scale-105' => $listingType === $value,
                                    'text-white/80 hover:bg-white/15 hover:text-white dark:text-white/70 dark:hover:bg-white/10' => $listingType !== $value,
                                ])
                            >
                                @if($value === 'all')
                                    <flux:icon name="home" class="h-4 w-4 mr-1.5" />
                                @elseif($value === 'sale')
                                    <flux:icon name="shopping-bag" class="h-4 w-4 mr-1.5" />
                                @elseif($value === 'rent')
                                    <flux:icon name="home" class="h-4 w-4 mr-1.5" />
                                @elseif($value === 'airbnb')
                                    <flux:icon name="calendar" class="h-4 w-4 mr-1.5" />
                                @elseif($value === 'commercial')
                                    <flux:icon name="briefcase" class="h-4 w-4 mr-1.5" />
                                @endif
                                {{ $label }}
                                @if(isset($this->propertyStats[$value]))
                                    <span class="ml-1.5 rounded-full bg-zinc-900/30 dark:bg-white/20 px-2 py-0.5 text-xs">{{ $this->propertyStats[$value] }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
                
                <div 
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform translate-y-4"
                    class="grid gap-5 sm:gap-6 md:grid-cols-2 lg:grid-cols-4"
                >
                    {{-- Property Type Selection with modernized dropdown --}}
                    <div>
                        <label class="block text-sm font-medium text-white/90 dark:text-white/80 mb-1.5">Property Type</label>
                        <div class="relative group">
                            <select 
                                wire:model="type"
                                class="w-full appearance-none rounded-lg border-0 bg-white/10 dark:bg-zinc-800/50 py-3 pl-4 pr-10 text-white ring-1 ring-white/20 dark:ring-white/10 transition-all duration-200 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:bg-white/15 dark:focus:bg-zinc-800/80 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm group-hover:ring-[#02c9c2]/50"
                            >
                                <option value="">Any Type</option>
                                @foreach($propertyTypes as $propertyType)
                                    <option value="{{ $propertyType['id'] }}">{{ $propertyType['name'] }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400 group-hover:text-[#02c9c2] transition-colors duration-200">
                                <flux:icon name="chevron-down" class="h-5 w-5" />
                            </div>
                        </div>
                    </div>

                    {{-- Location Selection with modernized dropdown --}}
                    <div>
                        <label class="block text-sm font-medium text-white/90 dark:text-white/80 mb-1.5">Location</label>
                        <div class="relative group">
                            <select 
                                wire:model="location"
                                class="w-full appearance-none rounded-lg border-0 bg-white/10 dark:bg-zinc-800/50 py-3 pl-4 pr-10 text-white ring-1 ring-white/20 dark:ring-white/10 transition-all duration-200 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:bg-white/15 dark:focus:bg-zinc-800/80 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm group-hover:ring-[#02c9c2]/50"
                            >
                                <option value="">Any Location</option>
                                @foreach($locations as $availableLocation)
                                    <option value="{{ $availableLocation }}">{{ $availableLocation }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400 group-hover:text-[#02c9c2] transition-colors duration-200">
                                <flux:icon name="map-pin" class="h-5 w-5" />
                            </div>
                        </div>
                    </div>

                    {{-- Search Input with modern styling --}}
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-white/90 dark:text-white/80 mb-1.5">Keywords</label>
                        <div class="relative group">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-400 group-hover:text-[#02c9c2] transition-colors duration-200">
                                <flux:icon name="magnifying-glass" class="h-4 w-4" />
                            </div>
                            <input 
                                type="text" 
                                wire:model="search"
                                placeholder="Search by location, title or features..."
                                class="block w-full rounded-lg border-0 bg-white/10 dark:bg-zinc-800/50 py-3 pl-10 pr-3 text-white ring-1 ring-white/20 dark:ring-white/10 transition-all duration-200 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:bg-white/15 dark:focus:bg-zinc-800/80 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm group-hover:ring-[#02c9c2]/50"
                            >
                        </div>
                    </div>

                    {{-- Price Range Inputs --}}
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-white/90 dark:text-white/80 mb-1.5">Price Range</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-400">
                                    <span class="text-sm">From</span>
                                </div>
                                <input 
                                    type="number" 
                                    wire:model="minPrice"
                                    placeholder="{{ isset($priceRanges['min']) ? number_format($priceRanges['min']) : '0' }}"
                                    class="block w-full rounded-lg border-0 bg-white/10 dark:bg-zinc-800/50 py-3 pl-16 pr-3 text-white ring-1 ring-white/20 dark:ring-white/10 transition-all duration-200 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:bg-white/15 dark:focus:bg-zinc-800/80 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm group-hover:ring-[#02c9c2]/50"
                                >
                            </div>
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-400">
                                    <span class="text-sm">To</span>
                                </div>
                                <input 
                                    type="number" 
                                    wire:model="maxPrice"
                                    placeholder="{{ isset($priceRanges['max']) ? number_format($priceRanges['max']) : '0' }}"
                                    class="block w-full rounded-lg border-0 bg-white/10 dark:bg-zinc-800/50 py-3 pl-12 pr-3 text-white ring-1 ring-white/20 dark:ring-white/10 transition-all duration-200 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:bg-white/15 dark:focus:bg-zinc-800/80 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm group-hover:ring-[#02c9c2]/50"
                                >
                            </div>
                        </div>
                    </div>
                    
                    {{-- Amenities Multi-Select --}}
                    {{-- <div class="lg:col-span-4">
                        <label class="block text-sm font-medium text-white/90 dark:text-white/80 mb-1.5">Amenities</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($amenities as $amenity)
                                <button
                                    type="button"
                                    wire:click="$set('selectedAmenities', {{ 
                                        in_array($amenity, $selectedAmenities) 
                                            ? json_encode(array_values(array_diff($selectedAmenities, [$amenity]))) 
                                            : json_encode(array_merge($selectedAmenities, [$amenity])) 
                                    }})"
                                    @class([
                                        'rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-300',
                                        'bg-[#02c9c2] text-zinc-900 shadow-lg shadow-[#02c9c2]/20' => in_array($amenity, $selectedAmenities),
                                        'bg-white/10 text-white hover:bg-white/15' => !in_array($amenity, $selectedAmenities),
                                    ])
                                >
                                    {{ $amenity }}
                                </button>
                            @endforeach
                        </div>
                    </div> --}}
                </div>
                
                {{-- Modern Search Button with animation --}}
                <div class="mt-6">
                    <button
                        wire:click="takeToSearch"
                        class="group w-full rounded-lg bg-gradient-to-r from-[#02c9c2] to-[#02c9c2]/80 dark:from-[#02c9c2] dark:to-[#02c9c2]/90 px-4 py-3 font-medium text-zinc-900 dark:text-zinc-900 shadow-lg shadow-[#02c9c2]/20 transition-all duration-300 hover:shadow-xl hover:shadow-[#02c9c2]/30 focus:outline-none focus:ring-2 focus:ring-[#02c9c2] focus:ring-offset-2 focus:ring-offset-zinc-900"
                    >
                        <span class="flex items-center justify-center">
                            <flux:icon name="magnifying-glass" class="mr-2.5 h-5 w-5 transition-transform duration-300 group-hover:scale-110" />
                            <span class="text-base">Find Your Perfect Property</span>
                            <flux:icon name="arrow-right" class="ml-2 h-5 w-5 transform transition-transform duration-300 opacity-0 group-hover:opacity-100 group-hover:translate-x-1" />
                        </span>
                    </button>
                </div>
            </div>

            {{-- Property Stats with enhanced card design --}}
            <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach([
                    'sale' => ['For Sale', 'shopping-bag', 'from-teal-400 to-emerald-500'],
                    'rent' => ['For Rent', 'home', 'from-cyan-400 to-teal-500'],
                    'airbnb' => ['Airbnb', 'calendar', 'from-emerald-400 to-teal-500'],
                    'commercial' => ['Commercial', 'briefcase', 'from-emerald-400 to-teal-500'],
                ] as $type => $data)
                    <div 
                        @click="activeTab = '{{ $type }}'; $wire.set('listingType', '{{ $type }}')"
                        class="group cursor-pointer flex items-center gap-5 rounded-xl bg-white/8 dark:bg-white/5 backdrop-blur-md p-5 transition-all duration-300 hover:bg-white/12 dark:hover:bg-white/8 hover:shadow-xl hover:shadow-[#02c9c2]/5 hover:scale-[1.02] transform"
                    >
                        <div class="rounded-xl bg-gradient-to-br {{ $data[2] }} bg-opacity-10 p-3.5 text-white dark:text-white transition-all duration-300">
                            <flux:icon name="{{ $data[1] }}" class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="text-sm font-medium text-zinc-300 dark:text-zinc-300">{{ $data[0] }}</div>
                            <div class="mt-1 text-2xl font-bold text-white dark:text-white transition-all duration-300 group-hover:text-[#02c9c2] dark:group-hover:text-[#02c9c2]">
                                {{ number_format($this->propertyStats[$type] ?? 0) }}
                                <span class="text-sm font-normal text-zinc-400 dark:text-zinc-400">properties</span>
                            </div>
                        </div>
                        <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <flux:icon name="arrow-right" class="h-5 w-5 text-[#02c9c2]" />
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Subtle call-to-action section --}}
            <div class="mt-10 text-center">
                <p class="text-zinc-400 dark:text-zinc-400 mb-4">Can't find what you're looking for?</p>
                <a href="{{ route('contact') }}" class="inline-flex items-center text-[#02c9c2] hover:text-[#02c9c2]/80 transition-colors duration-200">
                    <span>Contact our property specialists</span>
                    <flux:icon name="arrow-right" class="ml-2 h-4 w-4 transform transition-transform duration-300 group-hover:translate-x-1" />
                </a>
            </div>
        </div>
    </div>
    
    <!-- Additional Decorative Elements -->
    <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-zinc-900/80 to-transparent"></div>
</div>
