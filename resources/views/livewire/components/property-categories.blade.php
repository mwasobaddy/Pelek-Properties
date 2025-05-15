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

<div x-data="{ activeTab: 'sale' }" class="py-20 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="mb-16 max-w-3xl mx-auto text-center">
            <h2 class="text-4xl sm:text-5xl font-bold mb-6 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-[#02c9c2] to-[#012e2b] inline-block">
                Explore Our Property Categories
            </h2>
            <p class="text-xl text-gray-600 dark:text-gray-300 leading-relaxed">
                Find the perfect property that matches your needs
            </p>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-12 flex flex-wrap justify-center gap-2 sm:gap-4">
            @foreach(['sale', 'rent', 'airbnb'] as $type)
                <button 
                    @click="activeTab = '{{ $type }}'" 
                    :class="activeTab === '{{ $type }}' ? 'bg-[#02c9c2] text-white shadow-lg scale-105' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="px-6 py-3 rounded-full font-medium text-sm sm:text-base transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 flex items-center shadow-md"
                >
                    <flux:icon name="{{ $categories[$type]['icon'] }}" class="h-5 w-5 mr-2" />
                    {{ ucfirst($type) }} 
                    @if(isset($this->propertyCounts[$type]))
                        <span class="ml-2 bg-white bg-opacity-20 text-xs py-0.5 px-2 rounded-full">
                            {{ $this->propertyCounts[$type] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        <!-- Tab Content -->
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
                    <!-- Section Title -->
                    <div class="relative mb-10">
                        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-12 h-1 bg-gradient-to-r {{ $categories[$type]['accent'] }} rounded-full hidden lg:block"></div>
                        <div class="text-center lg:text-left lg:ml-16">
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">{{ $categories[$type]['title'] }}</h3>
                            <p class="text-gray-600 dark:text-gray-300">{{ $categories[$type]['description'] }}</p>
                        </div>
                    </div>

                    <!-- Features Card with Enhanced Design -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden transform transition duration-500 hover:shadow-2xl">
                        <div class="h-2 bg-gradient-to-r {{ $categories[$type]['accent'] }}"></div>
                        <div class="p-8">
                            <div class="grid md:grid-cols-2 gap-8">
                                <!-- Left Column: Features -->
                                <div class="space-y-6">
                                    <h4 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                        <flux:icon name="sparkles" class="h-5 w-5 mr-2 text-{{ $categories[$type]['color'] }}-500" />
                                        Key Features
                                    </h4>
                                    <ul class="space-y-4">
                                        @foreach($categories[$type]['features'] as $feature)
                                            <li class="flex items-center p-3 rounded-lg bg-gray-50 dark:bg-gray-700 group transform transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                                                <div class="flex-shrink-0 p-2 rounded-full bg-gradient-to-br {{ $categories[$type]['accent'] }} text-white">
                                                    <flux:icon name="check" class="h-4 w-4" />
                                                </div>
                                                <span class="ml-4 text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <!-- Right Column: Stats & CTA -->
                                <div class="flex flex-col justify-between">
                                    @if(isset($this->propertyCounts[$type]) && $this->propertyCounts[$type] > 0)
                                        <div class="mb-8">
                                            <div class="flex items-center justify-center h-36 bg-gray-50 dark:bg-gray-700 rounded-xl mb-4 overflow-hidden relative group">
                                                <!-- Subtle background pattern -->
                                                <div class="absolute inset-0 opacity-5 bg-[radial-gradient(#000_1px,transparent_1px)] [background-size:16px_16px] dark:opacity-10"></div>
                                                
                                                <div class="text-center relative z-10 transform transition-transform duration-500 group-hover:scale-110">
                                                    <div class="text-5xl font-bold bg-clip-text text-transparent bg-gradient-to-r {{ $categories[$type]['accent'] }} mb-2">
                                                        {{ $this->propertyCounts[$type] }}
                                                    </div>
                                                    <div class="text-gray-600 dark:text-gray-300">Available Properties</div>
                                                </div>
                                            </div>
                                            
                                            <div class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                                Updated properties in our database
                                            </div>
                                        </div>
                                    @else
                                        <!-- Empty State with Visual Enhancement -->
                                        <div class="flex items-center justify-center h-36 bg-gray-50 dark:bg-gray-700 rounded-xl mb-4">
                                            <div class="text-center">
                                                <flux:icon name="calendar" class="h-8 w-8 mx-auto text-gray-400 dark:text-gray-500 mb-2" />
                                                <div class="text-gray-500 dark:text-gray-400">No properties available</div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Enhanced CTA -->
                                    <div class="text-center">
                                        <div class="relative inline-block w-full">
                                            <!-- Shadow/Accent Element -->
                                            <div class="absolute inset-0 bg-gradient-to-r {{ $categories[$type]['accent'] }} blur-lg opacity-30 rounded-lg transform scale-105"></div>
                                            
                                            <a 
                                                href="{{ route($categories[$type]['route']) }}"
                                                class="group relative inline-flex items-center justify-center w-full px-8 py-4 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-[#02c9c2] to-[#012e2b] hover:from-[#012e2b] hover:to-[#02c9c2] transition-all duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900"
                                            >
                                                Explore {{ ucfirst($type) }} Properties
                                                <flux:icon name="arrow-right" class="ml-2 -mr-1 h-5 w-5 transform transition-transform duration-300 group-hover:translate-x-2" />
                                            </a>
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

    <!-- Decorative Background Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
</div>
