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

<div class="py-16 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @foreach(['sale', 'rent', 'airbnb'] as $type)
            @if(!empty($this->featuredProperties[$type]))
                <section class="mb-24 last:mb-0 relative">
                    <!-- Decorative element -->
                    <div class="absolute -left-4 top-12 w-1 h-16 bg-[#02c9c2] hidden lg:block" aria-hidden="true"></div>
                    
                    <div class="mb-12 max-w-2xl mx-auto text-center">
                        <h2 class="text-3xl sm:text-4xl font-bold mb-4 text-gray-900 dark:text-white tracking-tight">
                            {{ $sections[$type]['title'] }}
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-300">
                            {{ $sections[$type]['subtitle'] }}
                        </p>
                    </div>

                    <!-- Properties Grid with improved spacing and responsiveness -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 xl:gap-8">
                        @foreach($this->featuredProperties[$type] as $property)
                            <div class="transform transition duration-300 hover:scale-102 hover:-translate-y-1">
                                <livewire:components.property-card 
                                    :property="$property" 
                                    :key="'featured-'.$type.'-'.$property->id" 
                                />
                            </div>
                        @endforeach
                    </div>

                    <!-- Enhanced CTA with brand colors -->
                    <div class="text-center mt-12">
                        <a 
                            href="{{ route($sections[$type]['route']) }}"
                            class="group inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-[#02c9c2] hover:bg-[#012e2b] transition-all duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900"
                        >
                            {{ $sections[$type]['cta'] }}
                            <flux:icon name="arrow-right" class="ml-2 -mr-1 h-5 w-5 transform transition-transform duration-300 group-hover:translate-x-1" />
                        </a>
                    </div>
                </section>
            @endif
        @endforeach
    </div>
</div>
