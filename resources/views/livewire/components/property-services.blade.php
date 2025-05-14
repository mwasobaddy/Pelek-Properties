<?php

use Livewire\Volt\Component;

new class extends Component {
    public array $services = [
        [
            'title' => 'Furnished Airbnb Stays',
            'description' => 'Luxury furnished accommodations for both short and extended stays',
            'icon' => 'building-library',
            'color' => 'light-green',
            'features' => [
                'Fully furnished premium units',
                'Flexible booking periods',
                'Professional cleaning service',
                '24/7 concierge support',
                'Airport transfer services'
            ]
        ],
        [
            'title' => 'Rental Properties',
            'description' => 'Quality homes and apartments for long-term rental',
            'icon' => 'home',
            'color' => 'dark-green',
            'features' => [
                'Houses and apartments',
                'Flexible lease terms',
                'Background-checked properties',
                'Transparent pricing',
                'Virtual tours available'
            ]
        ],
        [
            'title' => 'Commercial Spaces',
            'description' => 'Prime commercial offices and retail spaces in strategic locations',
            'icon' => 'building-office-2',
            'color' => 'light-green',
            'features' => [
                'Office spaces & retail units',
                'Prime business locations',
                'Customizable spaces',
                'High-speed internet ready',
                'Meeting room facilities'
            ]
        ],
        [
            'title' => 'Property Management',
            'description' => 'Comprehensive property management solutions for property owners',
            'icon' => 'cog-6-tooth',
            'color' => 'dark-green',
            'features' => [
                'Tenant screening & placement',
                'Rent collection & accounting',
                'Maintenance coordination',
                'Regular property inspections',
                'Legal compliance handling'
            ]
        ]
    ];
} ?>

<div class="py-24 bg-gradient-to-b from-white to-gray-50 dark:from-zinc-800 dark:to-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                Comprehensive Property Solutions
            </h2>
            <p class="mt-4 text-lg leading-6 text-gray-600 dark:text-gray-400">
                Expert services tailored to your property needs in Nairobi
            </p>
        </div>

        <div class="mt-16 grid gap-8 md:grid-cols-2 lg:grid-cols-4">
            @foreach($services as $service)
                <div class="group relative rounded-2xl p-6 hover:shadow-lg transition-all duration-300
                    {{ $service['color'] === 'light-green' ? 'bg-[#02c9c2]/10 dark:bg-[#02c9c2]/5' : '' }}
                    {{ $service['color'] === 'dark-green' ? 'bg-[#012e2b]/10 dark:bg-[#012e2b]/5' : '' }}"
                >
                    {{-- Service Icon --}}
                    <div class="mb-6">
                        <div class="inline-flex items-center justify-center p-3 rounded-xl
                            {{ $service['color'] === 'light-green' ? 'bg-[#02c9c2]/20 dark:bg-[#02c9c2]/10' : '' }}
                            {{ $service['color'] === 'dark-green' ? 'bg-[#012e2b]/20 dark:bg-[#012e2b]/10' : '' }}"
                        >
                            <flux:icon 
                                :name="$service['icon']" 
                                class="h-8 w-8
                                    {{ $service['color'] === 'light-green' ? 'text-[#02c9c2] dark:text-[#02c9c2]' : '' }}
                                    {{ $service['color'] === 'dark-green' ? 'text-[#012e2b] dark:text-[#012e2b]' : '' }}"
                            />
                        </div>
                    </div>

                    {{-- Service Title & Description --}}
                    <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">
                        {{ $service['title'] }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        {{ $service['description'] }}
                    </p>

                    {{-- Features List --}}
                    <ul class="space-y-3">
                        @foreach($service['features'] as $feature)
                            <li class="flex items-center text-gray-600 dark:text-gray-400">
                                <flux:icon
                                    name="check-circle"
                                    class="h-5 w-5 mr-2 {{ $service['color'] === 'light-green' ? 'text-[#02c9c2]' : 'text-[#012e2b]' }}"
                                />
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    {{-- Hover Overlay with CTA --}}
                    <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300
                        {{ $service['color'] === 'light-green' ? 'bg-[#02c9c2]/90' : 'bg-[#012e2b]/90' }} flex items-center justify-center">
                        <a href="#" class="px-6 py-3 bg-white text-gray-900 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                            Learn More
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
