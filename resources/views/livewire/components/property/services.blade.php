<?php

use Livewire\Volt\Component;

new class extends Component {
    public array $services = [
        [
            'title' => 'Furnished Airbnb Stays',
            'description' => 'Luxury furnished accommodations for both short and extended stays',
            'icon' => 'building-library',
            'color' => 'teal',
            'accent' => 'from-cyan-400 to-teal-500',
            'route' => 'home',
            'modal_type' => 'airbnb',
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
            'color' => 'teal',
            'accent' => 'from-teal-400 to-emerald-500',
            'route' => 'home',
            'modal_type' => 'rent',
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
            'color' => 'cyan',
            'accent' => 'from-blue-400 to-cyan-500',
            'route' => 'home',
            'modal_type' => 'inquiry',
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
            'color' => 'green',
            'accent' => 'from-emerald-400 to-green-500',
            'route' => 'home',
            'modal_type' => 'management',
            'features' => [
                'Tenant screening & placement',
                'Rent collection & accounting',
                'Maintenance coordination',
                'Regular property inspections',
                'Legal compliance handling'
            ]
        ],
        [
            'title' => 'Houses and Plots for Sale',
            'description' => 'Premium residential properties and land plots in prime locations',
            'icon' => 'home-modern',
            'color' => 'blue',
            'accent' => 'from-indigo-400 to-blue-500',
            'route' => 'home',
            'modal_type' => 'sale',
            'features' => [
            'Residential homes & land plots',
            'Prime locations with growth potential',
            'Transparent pricing & documentation',
            'Financing assistance available',
            'Property inspection support'
            ]
        ],
        [
            'title' => 'Property Valuation',
            'description' => 'Accurate property valuation services for buyers and sellers',
            'icon' => 'calculator',
            'color' => 'purple',
            'accent' => 'from-purple-400 to-pink-500',
            'route' => 'home',
            'modal_type' => 'valuation',
            'features' => [
                'Comprehensive market analysis',
                'Detailed property reports',
                'Expert appraisals',
                'Competitive pricing strategy',
                'Free initial consultation'
            ]
        ]
    ];

    public function showWhatsAppModal($type)
    {
        // This method will be called when buttons are clicked
        $this->dispatch('open-whatsapp-modal', type: $type);
    }
} ?>

<div class="py-20 bg-white dark:bg-gray-900 relative overflow-hidden">
    <!-- Decorative Background Elements -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <!-- Section Header -->
        <div class="mb-16 max-w-3xl mx-auto text-center">
            <h2 class="text-4xl sm:text-5xl font-bold mb-6 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-[#02c9c2] to-[#012e2b] inline-block">
                Our Premium Property Services
            </h2>
            <p class="text-xl text-gray-600 dark:text-gray-300 leading-relaxed">
                We offer comprehensive solutions to help you maximize your property's potential and investment returns
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-12">
            @foreach($services as $index => $service)
                <div class="flex flex-col group bg-gradient-to-b from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-8 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-center items-center mb-6 w-16 h-16 rounded-full bg-gradient-to-br {{ $service['accent'] }} text-white">
                        <flux:icon 
                            :name="$service['icon']" 
                            class="w-8 h-8"
                        />
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ $service['title'] }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-5 leading-relaxed">
                        {{ $service['description'] }}
                    </p>
                    
                    <!-- Features List with Icons -->
                    <ul class="space-y-3 mb-6 flex-1 flex-col">
                        @foreach(array_slice($service['features'], 0, 5) as $feature)
                            <li class="flex items-start text-gray-600 dark:text-gray-300">
                                <flux:icon 
                                    name="check-circle" 
                                    class="h-5 w-5 mt-0.5 mr-3 text-{{ $service['color'] }}-500" 
                                />
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                    
                    <button 
                        wire:click="showWhatsAppModal('{{ $service['modal_type'] }}')"
                        class="group inline-flex items-center text-{{ $service['color'] }}-600 dark:text-{{ $service['color'] }}-400 hover:text-{{ $service['color'] }}-800 dark:hover:text-{{ $service['color'] }}-300 font-medium"
                    >
                        Learn more about {{ strtolower($service['title']) }}
                        <svg class="w-5 h-5 ml-2 transform transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
</div>
