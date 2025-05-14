<?php

use App\Services\PropertyService;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed};

new class extends Component {
    public array $sections = [
        'sale' => [
            'title' => 'Featured Properties for Sale',
            'subtitle' => 'Discover our premium properties available for purchase',
            'cta' => 'View All Properties for Sale',
            'route' => 'properties.sale'
        ],
        'rent' => [
            'title' => 'Featured Rental Properties',
            'subtitle' => 'Find your perfect rental home',
            'cta' => 'Explore Rental Properties',
            'route' => 'properties.rent'
        ],
        'airbnb' => [
            'title' => 'Featured Airbnb Stays',
            'subtitle' => 'Premium furnished accommodations for short stays',
            'cta' => 'Book Your Stay',
            'route' => 'properties.airbnb'
        ]
    ];

    public function with(): array 
    {
        return [
            'featuredProperties' => computed(function () {
                return app(PropertyService::class)->getAllFeaturedProperties(4);
            }),
        ];
    }
} ?>

<div class="py-12 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @foreach(['sale', 'rent', 'airbnb'] as $type)
            @if(!empty($this->featuredProperties[$type]))
                <section class="mb-16 last:mb-0">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">
                            {{ $sections[$type]['title'] }}
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-400">
                            {{ $sections[$type]['subtitle'] }}
                        </p>
                    </div>

                    {{-- Properties Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($this->featuredProperties[$type] as $property)
                            <livewire:components.property-card 
                                :property="$property" 
                                :key="'featured-'.$type.'-'.$property->id" 
                            />
                        @endforeach
                    </div>

                    {{-- View All CTA --}}
                    <div class="text-center mt-8">
                        <a 
                            href="{{ route($sections[$type]['route']) }}"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900"
                        >
                            {{ $sections[$type]['cta'] }}
                            <x-flux-icon name="arrow-right" class="ml-2 -mr-1 h-5 w-5" />
                        </a>
                    </div>
                </section>
            @endif
        @endforeach
    </div>
</div>
