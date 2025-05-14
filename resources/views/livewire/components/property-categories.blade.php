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

<div class="py-12 bg-white dark:bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                Explore Our Property Categories
            </h2>
            <p class="mt-4 text-lg leading-6 text-gray-600 dark:text-gray-400">
                Find the perfect property that matches your needs
            </p>
        </div>

        <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach(['sale', 'rent', 'airbnb'] as $type)
                <div class="group relative rounded-2xl p-6 hover:shadow-lg transition-shadow duration-300
                    {{ $type === 'sale' ? 'bg-emerald-50 dark:bg-emerald-900/20' : '' }}
                    {{ $type === 'rent' ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}
                    {{ $type === 'airbnb' ? 'bg-purple-50 dark:bg-purple-900/20' : '' }}"
                >
                    {{-- Category Icon --}}
                    <div class="mb-6">
                        <div class="inline-flex items-center justify-center p-3 rounded-xl
                            {{ $type === 'sale' ? 'bg-emerald-100 dark:bg-emerald-800' : '' }}
                            {{ $type === 'rent' ? 'bg-blue-100 dark:bg-blue-800' : '' }}
                            {{ $type === 'airbnb' ? 'bg-purple-100 dark:bg-purple-800' : '' }}"
                        >
                            <x-flux-icon 
                                :name="$categories[$type]['icon']" 
                                class="h-8 w-8
                                    {{ $type === 'sale' ? 'text-emerald-600 dark:text-emerald-300' : '' }}
                                    {{ $type === 'rent' ? 'text-blue-600 dark:text-blue-300' : '' }}
                                    {{ $type === 'airbnb' ? 'text-purple-600 dark:text-purple-300' : '' }}"
                            />
                        </div>
                    </div>

                    {{-- Category Title & Description --}}
                    <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">
                        {{ $categories[$type]['title'] }}
                        @if(isset($this->propertyCounts[$type]))
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                ({{ $this->propertyCounts[$type] }})
                            </span>
                        @endif
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        {{ $categories[$type]['description'] }}
                    </p>

                    {{-- Features List --}}
                    <ul class="space-y-3 mb-8">
                        @foreach($categories[$type]['features'] as $feature)
                            <li class="flex items-center text-gray-600 dark:text-gray-400">
                                <x-flux-icon 
                                    name="check-circle" 
                                    class="h-5 w-5 mr-2
                                        {{ $type === 'sale' ? 'text-emerald-500' : '' }}
                                        {{ $type === 'rent' ? 'text-blue-500' : '' }}
                                        {{ $type === 'airbnb' ? 'text-purple-500' : '' }}"
                                />
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    {{-- CTA Button --}}
                    <div class="mt-auto">
                        <a 
                            href="{{ route($categories[$type]['route']) }}"
                            class="inline-flex items-center justify-center w-full px-4 py-2 text-base font-medium rounded-md shadow-sm transition-colors
                                {{ $type === 'sale' ? 'text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500' : '' }}
                                {{ $type === 'rent' ? 'text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' : '' }}
                                {{ $type === 'airbnb' ? 'text-white bg-purple-600 hover:bg-purple-700 focus:ring-purple-500' : '' }}
                                focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            Explore {{ $categories[$type]['title'] }}
                            <x-flux-icon name="arrow-right" class="ml-2 -mr-1 h-5 w-5" />
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
