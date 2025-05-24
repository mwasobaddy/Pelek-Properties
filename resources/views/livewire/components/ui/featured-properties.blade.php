<?php

use App\Services\PropertyService;
use Livewire\Volt\Component;
use function Livewire\Volt\{state};

new class extends Component {
    #[State]
    public $featuredProperties = [];

    // Enhanced section data with additional UI-specific properties
    public array $sections = [
        'sale' => [
            'title' => 'Featured Properties for Sale',
            'subtitle' => 'Discover our premium properties available for purchase',
            'cta' => 'View All Properties for Sale',
            'route' => 'properties.sale',
            'icon' => 'home',
            'accent' => 'from-teal-400 to-emerald-500'
        ],
        'rent' => [
            'title' => 'Featured Rental Properties',
            'subtitle' => 'Find your perfect rental home',
            'cta' => 'Explore Rental Properties',
            'route' => 'properties.rent',
            'icon' => 'key',
            'accent' => 'from-cyan-400 to-teal-500'
        ],
        'airbnb' => [
            'title' => 'Featured Airbnb Stays',
            'subtitle' => 'Premium furnished accommodations for short stays',
            'cta' => 'Book Your Stay',
            'route' => 'properties.airbnb',
            'icon' => 'calendar',
            'accent' => 'from-emerald-400 to-teal-500'
        ],
        'commercial' => [
            'title' => 'Commercial Spaces for Rent',
            'subtitle' => 'Prime locations for your business needs',
            'cta' => 'View Commercial Listings',
            'route' => 'properties.commercial',
            'icon' => 'building-office-2',
            'accent' => 'from-blue-400 to-cyan-500'
        ]
    ];

    public function mount()
    {
        $this->featuredProperties = app(PropertyService::class)->getAllFeaturedProperties(4);
    }

    public function with(): array 
    {
        return [];
    }
} ?>

<div x-data="{ activeTab: 'sale' }" class="py-20 bg-gradient-to-b from-[#02c9c2]/10 to-[#012e2b]/20 dark:from-[#012e2b]/80 dark:to-[#02c9c2]/50 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="mb-16 max-w-3xl mx-auto text-center">
            <h2 class="text-4xl sm:text-5xl font-bold mb-6 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-[#02c9c2] to-[#012e2b] inline-block">
                Discover Your Next Property
            </h2>
            <p class="text-xl text-gray-600 dark:text-gray-300 leading-relaxed">
                Browse our handpicked selection of premium properties across different categories
            </p>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-12 flex flex-wrap justify-center gap-2 sm:gap-4">
            @php $hasAnyProperties = false; @endphp
            @foreach(['sale', 'rent', 'airbnb', 'commercial'] as $type)
                @if(isset($this->featuredProperties[$type]) && count($this->featuredProperties[$type]) > 0)
                    @php $hasAnyProperties = true; @endphp
                    <button 
                        @click="activeTab = '{{ $type }}'" 
                        :class="activeTab === '{{ $type }}' ? 'bg-[#02c9c2] text-white shadow-lg scale-105' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="px-6 py-3 rounded-full font-medium text-sm sm:text-base transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 flex items-center shadow-md"
                    >
                        <flux:icon name="{{ $sections[$type]['icon'] }}" class="h-5 w-5 mr-2" />
                        {{ ucfirst($type) }}
                    </button>
                @endif
            @endforeach
        </div>

        @if($hasAnyProperties)
            <!-- Tab Content -->
            <div class="relative">
                @foreach(['sale', 'rent', 'airbnb', 'commercial'] as $type)
                    @if(isset($this->featuredProperties[$type]) && count($this->featuredProperties[$type]) > 0)
                        <div 
                            x-show="activeTab === '{{ $type }}'" 
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform translate-y-4"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform translate-y-4"
                            class="space-y-12"
                        >
                            <!-- Section Title -->
                            <div class="relative mb-10">
                                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-12 h-1 bg-gradient-to-r {{ $sections[$type]['accent'] }} rounded-full hidden lg:block lg:ml-16"></div>
                                <div class="text-center lg:text-left lg:ml-16">
                                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">{{ $sections[$type]['title'] }}</h3>
                                    <p class="text-gray-600 dark:text-gray-300">{{ $sections[$type]['subtitle'] }}</p>
                                </div>
                            </div>

                            <!-- Properties Grid with Enhanced Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 xl:gap-8">
                                @foreach($this->featuredProperties[$type] as $property)
                                    <div class="group transform transition duration-500 hover:scale-105 hover:-translate-y-1">
                                        <livewire:components.property.card 
                                            :property="$property" 
                                            :key="'featured-'.$type.'-'.$property->id" 
                                        />
                                    </div>
                                @endforeach
                            </div>

                            <!-- Enhanced CTA -->
                            <div class="text-center mt-16">
                                <div class="relative inline-block">
                                    <!-- Shadow/Accent Element -->
                                    <div class="absolute inset-0 bg-gradient-to-r {{ $sections[$type]['accent'] }} blur-lg opacity-30 rounded-lg transform scale-105"></div>
                                    
                                    <a 
                                        href="{{ url($type) }}"
                                        class="group relative inline-flex items-center px-8 py-4 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-[#02c9c2] to-[#012e2b] hover:from-[#012e2b] hover:to-[#02c9c2] transition-all duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900"
                                    >
                                        {{ $sections[$type]['cta'] }}
                                        <flux:icon name="arrow-right" class="ml-2 -mr-1 h-5 w-5 transform transition-transform duration-300 group-hover:translate-x-2" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <!-- Empty State with Visual Enhancement -->
            <div class="text-center py-16 px-4 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 shadow-sm">
                <div class="flex flex-col items-center">
                    <flux:icon name="home" class="h-16 w-16 text-gray-400 dark:text-gray-500 mb-4" />
                    <h3 class="text-xl font-medium text-gray-700 dark:text-gray-300 mb-2">No Featured Properties</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md">We're currently updating our featured listings. Please check back soon for new properties.</p>
                </div>
            </div>
        @endif

        <!-- Decorative Element -->
        <div class="hidden lg:block absolute right-0 bottom-0 w-64 h-64 bg-gradient-to-tl from-[#02c9c2]/10 to-transparent rounded-full blur-3xl"></div>
    </div>

    <!-- Decorative Background Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
</div>