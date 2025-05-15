<?php

use App\Services\PropertySearchService;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed};

new class extends Component {
    public array $categories = [
        'sale' => [
            'title' => 'Properties for Sale',
            'description' => 'Find your dream home in Nairobi\'s most desirable neighborhoods',
            'icon' => 'home',
            'color' => 'emerald',
            'route' => 'properties.sale',
            'accent' => 'from-emerald-400 to-green-500',
            'features' => [
                'Premium locations',
                'Verified properties',
                'Flexible financing options',
                'Expert property guidance'
            ]
        ],
        'rent' => [
            'title' => 'Rental Properties',
            'description' => 'Discover quality rental homes for long-term stays',
            'icon' => 'building-office',
            'color' => 'blue',
            'route' => 'properties.rent',
            'accent' => 'from-blue-400 to-indigo-500',
            'features' => [
                'Monthly & yearly leases',
                'Well-maintained properties',
                'Secure neighborhoods',
                'Professional management'
            ]
        ],
        'airbnb' => [
            'title' => 'Airbnb Stays',
            'description' => 'Luxury furnished accommodations for your short stay',
            'icon' => 'calendar',
            'color' => 'purple',
            'route' => 'properties.airbnb',
            'accent' => 'from-purple-400 to-fuchsia-500',
            'features' => [
                'Fully furnished',
                'Instant booking',
                'Premium amenities',
                'Flexible stay duration'
            ]
        ]
    ];

    public function with(): array 
    {
        return [
            'propertyCounts' => computed(function () {
                return app(PropertySearchService::class)->getPropertyCountsByType();
            }),
        ];
    }
} ?>

<div x-data="{ activeTab: 'sale' }" class="py-24 bg-gradient-to-b from-gray-50/80 to-white dark:from-gray-900 dark:to-gray-800 relative overflow-hidden">
    <!-- Decorative blobs -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-[#02c9c2]/5 rounded-full blur-3xl -z-10 animate-pulse"></div>
    <div class="absolute bottom-0 right-1/4 w-72 h-72 bg-[#012e2b]/5 rounded-full blur-3xl -z-10 animate-pulse" style="animation-delay: 2s;"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="mb-16 max-w-3xl mx-auto text-center">
            <span class="text-[#02c9c2] font-medium tracking-wider text-sm uppercase mb-3 inline-block">Find Your Perfect Space</span>
            <h2 class="text-4xl sm:text-5xl font-bold mb-6 tracking-tight text-gray-900 dark:text-white">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#02c9c2] to-[#012e2b]">Explore Property Categories</span>
            </h2>
            <p class="text-xl text-gray-600 dark:text-gray-300 leading-relaxed max-w-2xl mx-auto">
                Discover curated properties that match your lifestyle and investment goals
            </p>
        </div>

        <!-- Modern Tab Navigation -->
        <div class="flex justify-center mb-16">
            <div class="inline-flex bg-white dark:bg-gray-800 p-1.5 rounded-xl shadow-md">
                @foreach(['sale', 'rent', 'airbnb'] as $type)
                    <button 
                        @click="activeTab = '{{ $type }}'" 
                        :class="activeTab === '{{ $type }}' ? 'bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white shadow-lg' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="px-6 py-3 rounded-lg font-medium transition-all duration-300 flex items-center group mx-1"
                    >
                        <flux:icon name="{{ $categories[$type]['icon'] }}" 
                                  :class="activeTab === '{{ $type }}' ? 'text-white' : 'text-[#02c9c2] group-hover:text-[#02c9c2]/80'" 
                                  class="h-5 w-5 mr-2 transition-all" />
                        <span>{{ ucfirst($type) }}</span> 
                        @if(isset($this->propertyCounts[$type]))
                            <span class="ml-2 bg-white bg-opacity-20 text-xs py-0.5 px-2 rounded-full">
                                {{ $this->propertyCounts[$type] }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Enhanced Tab Content -->
        <div class="relative">
            @foreach(['sale', 'rent', 'airbnb'] as $type)
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
                    <!-- Section Title with visual element -->
                    <div class="relative mb-12">
                        <div class="flex items-center justify-center lg:justify-start">
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-br {{ $categories[$type]['accent'] }} flex items-center justify-center shadow-lg mr-4">
                                <flux:icon name="{{ $categories[$type]['icon'] }}" class="h-6 w-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $categories[$type]['title'] }}</h3>
                                <p class="text-gray-600 dark:text-gray-300 mt-1">{{ $categories[$type]['description'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modern Glass Card Design -->
                    <div class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-md rounded-2xl shadow-xl overflow-hidden transform transition border border-gray-100 dark:border-gray-700">
                        <div class="h-1 bg-gradient-to-r {{ $categories[$type]['accent'] }}"></div>
                        <div class="p-8 md:p-10">
                            <div class="grid md:grid-cols-2 gap-10">
                                <!-- Left Column: Enhanced Features -->
                                <div class="space-y-7">
                                    <h4 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                        <flux:icon name="sparkles" class="h-5 w-5 mr-2 text-[#02c9c2]" />
                                        Key Benefits
                                    </h4>
                                    <ul class="space-y-3.5">
                                        @foreach($categories[$type]['features'] as $feature)
                                            <li class="flex items-center p-4 rounded-xl bg-gradient-to-r from-gray-50 to-white dark:from-gray-700 dark:to-gray-700/50 group transition-all duration-300 hover:shadow-md">
                                                <div class="flex-shrink-0 p-2 rounded-full bg-[#02c9c2]/10 text-[#02c9c2]">
                                                    <flux:icon name="check" class="h-4 w-4" />
                                                </div>
                                                <span class="ml-4 text-gray-700 dark:text-gray-200 font-medium">{{ $feature }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <!-- Right Column: Stats & CTA with enhanced design -->
                                <div class="flex flex-col justify-between">
                                    @if(isset($this->propertyCounts[$type]) && $this->propertyCounts[$type] > 0)
                                        <div class="mb-8">
                                            <div class="flex items-center justify-center h-40 rounded-xl overflow-hidden relative group bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700">
                                                <!-- Abstract pattern background -->
                                                <div class="absolute inset-0 opacity-[0.03] dark:opacity-10" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgdmlld0JveD0iMCAwIDYwIDYwIj48ZyBmaWxsPSJjdXJyZW50Q29sb3IiPjxwYXRoIGQ9Ik0wIDBMMzAgMzBMMCAwek02MCA2MEwzMCAzMEw2MCA2MHoiLz48L2c+PC9zdmc+')"></div>
                                                
                                                <div class="text-center relative z-10 transform transition-all duration-500 group-hover:scale-110">
                                                    <div class="text-6xl font-bold text-[#02c9c2] mb-2">
                                                        {{ $this->propertyCounts[$type] }}
                                                    </div>
                                                    <div class="text-gray-600 dark:text-gray-300 font-medium">Available Properties</div>
                                                </div>
                                            </div>
                                            
                                            <div class="text-sm text-gray-500 dark:text-gray-400 text-center mt-2 flex justify-center items-center">
                                                <flux:icon name="refresh" class="h-3.5 w-3.5 mr-1" />
                                                Updated daily
                                            </div>
                                        </div>
                                    @else
                                        <!-- Empty State with Visual Enhancement -->
                                        <div class="flex items-center justify-center h-40 rounded-xl mb-8 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700">
                                            <div class="text-center px-6 py-8">
                                                <div class="p-3 rounded-full bg-gray-100 dark:bg-gray-700 inline-block mb-3">
                                                    <flux:icon name="calendar" class="h-6 w-6 text-[#02c9c2]" />
                                                </div>
                                                <div class="text-gray-600 dark:text-gray-400">Properties coming soon</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-500 mt-2">Check back later</div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Modern CTA Button -->
                                    <div class="text-center">
                                        <a 
                                            href="{{ route($categories[$type]['route']) }}"
                                            class="group relative inline-flex w-full items-center justify-center px-8 py-4 rounded-xl text-base font-medium text-white overflow-hidden transition-all duration-300"
                                        >
                                            <!-- Button background with animated gradient -->
                                            <span class="absolute inset-0 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] group-hover:bg-gradient-to-l transition-all duration-500"></span>
                                            
                                            <!-- Button shine effect -->
                                            <span class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-transparent via-white to-transparent opacity-10 group-hover:animate-shine"></span>
                                            
                                            <!-- Button text and icon -->
                                            <span class="relative flex items-center">
                                                Explore {{ ucfirst($type) }} Properties
                                                <flux:icon name="arrow-right" class="ml-2 h-5 w-5 transform transition-transform duration-300 group-hover:translate-x-1" />
                                            </span>
                                        </a>
                                        
                                        <!-- Subtle help text -->
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-3 flex justify-center items-center">
                                            <flux:icon name="shield-check" class="h-3 w-3 mr-1" />
                                            Verified listings only
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
