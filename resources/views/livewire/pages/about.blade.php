<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.guest')] class extends Component {
    public function mount()
    {
        // Initialize any data needed for the About page
    }

    // Statistics for the company
    public array $stats = [
        'properties_managed' => 500,
        'client_satisfaction' => 98,
        'years_in_business' => 15,
        'client_base' => 1000,
    ];
} ?>

<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
    <!-- Enhanced Hero Section with Parallax Effect -->
    <div class="relative overflow-hidden bg-gradient-to-br from-zinc-900 to-[#012e2b] dark:from-zinc-950 dark:to-[#012e2b]">
        <!-- Background elements with parallax effect -->
        <div class="absolute inset-0" x-data="{}"
            x-on:scroll.window="$el.style.transform = `translateY(${window.scrollY * 0.1}px)`">
            <div class="absolute inset-0 bg-gradient-to-br from-zinc-900/85 via-[#012e2b]/75 to-[#02c9c2]/30 backdrop-blur-sm"></div>
            <!-- Consider using a specific about page image instead of placeholder -->
            <img src="{{ asset('images/placeholder.webp') }}" alt="About Pelek Properties"
                class="h-full w-full object-cover opacity-40">
        </div>

        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-[#02c9c2]/20 blur-3xl"></div>
            <div class="absolute right-0 bottom-0 h-80 w-80 rounded-full bg-[#02c9c2]/15 blur-3xl"></div>
        </div>

        <!-- Enhanced Content with Animation -->
        <div class="relative mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8 flex justify-center">
            <div class="mx-auto max-w-2xl lg:mx-0" 
                 x-data="{}" 
                 x-intersect="$el.classList.add('animate-fade-in')">
                <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">
                    Established {{ date('Y') - $stats['years_in_business'] }}
                </span>
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl font-display">
                    About Pelek Properties
                </h1>
                <p class="mt-6 text-lg leading-8 text-zinc-300">
                    Pelek Properties is a dynamic real estate company specializing in diverse property solutions across Kenya.
                    With years of experience and dedication to excellence, we're transforming the real estate landscape.
                </p>
            </div>
        </div>
    </div>

    <!-- Our Story Section (New) -->
    <div class="py-24 relative">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="relative" x-data="{}" x-intersect="$el.classList.add('animate-fade-in')">
                    <!-- Image with design elements -->
                    <div class="absolute -inset-4 bg-gradient-to-r from-[#02c9c2]/30 to-[#02c9c2]/10 rounded-2xl blur-xl"></div>
                    <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                        <img src="{{ asset('images/placeholder.webp') }}" alt="Pelek Properties Office"
                            class="w-full h-[500px] object-cover transform hover:scale-105 transition-transform duration-700">
                    </div>
                    <!-- Floating badge -->
                    <div class="absolute -bottom-6 -right-6 bg-white dark:bg-gray-800 rounded-full p-4 shadow-xl flex items-center justify-center">
                        <span class="text-xl font-bold text-[#02c9c2]">{{ $stats['years_in_business'] }}</span>
                        <span class="text-sm text-gray-600 dark:text-gray-300 ml-1">years</span>
                    </div>
                </div>

                <div x-data="{}" x-intersect="$el.classList.add('animate-fade-in')" class="space-y-6">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Our Story</h2>
                    <div class="h-1 w-20 bg-[#02c9c2]"></div>
                    <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed">
                        Founded in {{ date('Y') - $stats['years_in_business'] }}, Pelek Properties began with a vision to transform the Kenyan real estate landscape. 
                        What started as a small agency has grown into one of the country's most trusted real estate companies.
                    </p>
                    <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed">
                        Our journey has been defined by innovation, integrity, and a commitment to excellence. 
                        We've helped thousands of clients find their perfect properties and have developed a reputation 
                        for delivering exceptional service in all aspects of real estate management.
                    </p>
                    <div class="pt-6">
                        <a href="{{ route('contact') }}" wire:navigate class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-[#02c9c2] to-[#012e2b] hover:from-[#012e2b] hover:to-[#02c9c2] transition-all duration-300 shadow-md hover:shadow-lg">
                            <span>Get in Touch</span>
                            <flux:icon name="arrow-right" class="ml-2 -mr-1 h-5 w-5" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mission & Vision Section with Modern Cards -->
    <div class="py-24 relative bg-gray-50 dark:bg-gray-800/50">
        <!-- Decorative background elements -->
        <div class="absolute inset-0 bg-[#02c9c2]/5 dark:bg-[#02c9c2]/2"></div>
        
        <div class="relative max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16" x-data="{}" x-intersect="$el.classList.add('animate-fade-in')">
                <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">
                    What Drives Us
                </span>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Our Mission & Vision</h2>
                <div class="h-1 w-20 bg-[#02c9c2] mx-auto mt-4"></div>
            </div>

            <div class="grid md:grid-cols-2 gap-12">
                <!-- Mission Card -->
                <div x-data="{}" 
                     x-intersect="$el.classList.add('animate-fade-in')"
                     class="group rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-xl p-8 shadow-xl ring-1 ring-black/5 dark:ring-white/10 transition-all duration-300 hover:shadow-2xl hover:scale-[1.02]">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-[#02c9c2] to-[#012e2b] rounded-2xl flex items-center justify-center mr-4 transform group-hover:rotate-6 transition-transform duration-300">
                            <flux:icon name="bolt" class="w-8 h-8 text-white" />
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Our Mission</h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-lg">
                        Simplifying and enhancing real estate experiences in Kenya through innovative solutions, exceptional service, and trusted partnerships.
                    </p>
                    <ul class="mt-6 space-y-3">
                        <li class="flex items-start">
                            <flux:icon name="check-circle" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2" />
                            <span class="text-gray-600 dark:text-gray-300">Create exceptional property experiences</span>
                        </li>
                        <li class="flex items-start">
                            <flux:icon name="check-circle" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2" />
                            <span class="text-gray-600 dark:text-gray-300">Provide innovative real estate solutions</span>
                        </li>
                        <li class="flex items-start">
                            <flux:icon name="check-circle" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2" />
                            <span class="text-gray-600 dark:text-gray-300">Build lasting client relationships</span>
                        </li>
                    </ul>
                </div>

                <!-- Vision Card -->
                <div x-data="{}" 
                     x-intersect="$el.classList.add('animate-fade-in')" 
                     class="group rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-xl p-8 shadow-xl ring-1 ring-black/5 dark:ring-white/10 transition-all duration-300 hover:shadow-2xl hover:scale-[1.02]">
                    <div class="flex items-center mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-[#02c9c2] to-[#012e2b] rounded-2xl flex items-center justify-center mr-4 transform group-hover:rotate-6 transition-transform duration-300">
                            <flux:icon name="light-bulb" class="w-8 h-8 text-white" />
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Our Vision</h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-lg">
                        To become Kenya's most trusted real estate partner, setting the standard for excellence, innovation, and customer satisfaction in the industry.
                    </p>
                    <ul class="mt-6 space-y-3">
                        <li class="flex items-start">
                            <flux:icon name="check-circle" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2" />
                            <span class="text-gray-600 dark:text-gray-300">Lead the Kenyan real estate market</span>
                        </li>
                        <li class="flex items-start">
                            <flux:icon name="check-circle" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2" />
                            <span class="text-gray-600 dark:text-gray-300">Set new standards for industry excellence</span>
                        </li>
                        <li class="flex items-start">
                            <flux:icon name="check-circle" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2" />
                            <span class="text-gray-600 dark:text-gray-300">Drive innovation in property solutions</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Services Overview Section -->
    <div class="py-24 bg-white dark:bg-gray-900 relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-[#02c9c2]/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-64 h-64 bg-[#02c9c2]/5 rounded-full blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16" x-data="{}" x-intersect="$el.classList.add('animate-fade-in')">
                <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">
                    What We Offer
                </span>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Our Services</h2>
                <p class="text-gray-600 dark:text-gray-300 max-w-2xl mx-auto text-lg">
                    Comprehensive real estate solutions tailored to meet your needs
                </p>
                <div class="h-1 w-20 bg-[#02c9c2] mx-auto mt-4"></div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Property Management Card -->
                <div x-data="{}" 
                     x-intersect="$el.classList.add('animate-fade-in')"
                     class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl ring-1 ring-black/5 dark:ring-white/10 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
                    <div class="w-16 h-16 bg-gradient-to-br from-[#02c9c2] to-[#012e2b] rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 group-hover:rotate-3 duration-300">
                        <flux:icon name="building-office" class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Property Management</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">Comprehensive property management with real-time monitoring, maintenance, and tenant relations.</p>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-start">
                            <flux:icon name="check" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2 flex-shrink-0" />
                            <span class="text-gray-600 dark:text-gray-300">Regular maintenance & inspections</span>
                        </li>
                        <li class="flex items-start">
                            <flux:icon name="check" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2 flex-shrink-0" />
                            <span class="text-gray-600 dark:text-gray-300">Tenant screening & placement</span>
                        </li>
                        <li class="flex items-start">
                            <flux:icon name="check" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2 flex-shrink-0" />
                            <span class="text-gray-600 dark:text-gray-300">Financial reporting & rent collection</span>
                        </li>
                    </ul>
                    <a href="#" class="inline-flex items-center text-[#02c9c2] group-hover:text-[#012e2b] transition-colors">
                        <span>Learn more</span>
                        <flux:icon name="arrow-right" class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </a>
                </div>
                
                <!-- Real Estate Sales Card -->
                <div x-data="{}" 
                     x-intersect="$el.classList.add('animate-fade-in')"
                     class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl ring-1 ring-black/5 dark:ring-white/10 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
                    <div class="w-16 h-16 bg-gradient-to-br from-[#02c9c2] to-[#012e2b] rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 group-hover:rotate-3 duration-300">
                        <flux:icon name="home" class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Real Estate Sales</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">Innovative property matching and seamless buying experience with expert market guidance.</p>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-start">
                            <flux:icon name="check" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2 flex-shrink-0" />
                            <span class="text-gray-600 dark:text-gray-300">Professional property valuations</span>
                        </li>
                        <li class="flex items-start">
                            <flux:icon name="check" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2 flex-shrink-0" />
                            <span class="text-gray-600 dark:text-gray-300">Strategic marketing campaigns</span>
                        </li>
                        <li class="flex items-start">
                            <flux:icon name="check" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2 flex-shrink-0" />
                            <span class="text-gray-600 dark:text-gray-300">Negotiation & closing support</span>
                        </li>
                    </ul>
                    <a href="{{ route('properties.index') }}" wire:navigate class="inline-flex items-center text-[#02c9c2] group-hover:text-[#012e2b] transition-colors">
                        <span>View properties</span>
                        <flux:icon name="arrow-right" class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </a>
                </div>

                <!-- Valuation Services Card -->
                <div x-data="{}" 
                     x-intersect="$el.classList.add('animate-fade-in')"
                     class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl ring-1 ring-black/5 dark:ring-white/10 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
                    <div class="w-16 h-16 bg-gradient-to-br from-[#02c9c2] to-[#012e2b] rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 group-hover:rotate-3 duration-300">
                        <flux:icon name="calculator" class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Valuation Services</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">Expert property valuation with comprehensive market analysis and detailed reporting.</p>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-start">
                            <flux:icon name="check" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2 flex-shrink-0" />
                            <span class="text-gray-600 dark:text-gray-300">Detailed property assessment</span>
                        </li>
                        <li class="flex items-start">
                            <flux:icon name="check" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2 flex-shrink-0" />
                            <span class="text-gray-600 dark:text-gray-300">Market trend analysis</span>
                        </li>
                        <li class="flex items-start">
                            <flux:icon name="check" class="h-5 w-5 text-[#02c9c2] mt-0.5 mr-2 flex-shrink-0" />
                            <span class="text-gray-600 dark:text-gray-300">Investment opportunity evaluation</span>
                        </li>
                    </ul>
                    <a href="#" class="inline-flex items-center text-[#02c9c2] group-hover:text-[#012e2b] transition-colors">
                        <span>Learn more</span>
                        <flux:icon name="arrow-right" class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Statistics Section with Animation -->
    <div class="py-24 relative overflow-hidden bg-[url('/images/pattern.svg')] bg-fixed bg-center bg-no-repeat bg-cover">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-gray-50/95 to-white/95 dark:from-gray-900/95 dark:to-gray-800/95"></div>
        
        <div class="relative max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16" x-data="{}" x-intersect="$el.classList.add('animate-fade-in')">
                <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">
                    By The Numbers
                </span>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Our Impact</h2>
                <div class="h-1 w-20 bg-[#02c9c2] mx-auto mt-4"></div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8" x-data="{ shown: false }" x-intersect="shown = true">
                <!-- Properties Managed -->
                <div class="text-center p-8 rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-xl shadow-xl ring-1 ring-black/5 dark:ring-white/10" 
                     x-show="shown" 
                     x-transition:enter="transition ease-out duration-500" 
                     x-transition:enter-start="opacity-0 transform translate-y-8" 
                     x-transition:enter-end="opacity-100 transform translate-y-0">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#02c9c2]/10 rounded-full mb-4">
                        <flux:icon name="building-office-2" class="h-8 w-8 text-[#02c9c2]" />
                    </div>
                    <div class="text-5xl font-bold text-[#02c9c2] mb-3" 
                         x-data="{ shown: false, number: 0 }" 
                         x-intersect="shown = true; if(shown) { 
                            const interval = setInterval(() => {
                                number = number + ({{ $stats['properties_managed'] }} - number) / 10;
                                if (Math.abs({{ $stats['properties_managed'] }} - number) < 1) {
                                    number = {{ $stats['properties_managed'] }};
                                    clearInterval(interval);
                                }
                            }, 25);
                         }">
                        <span x-text="Math.round(number)">0</span>+
                    </div>
                    <div class="text-gray-600 dark:text-gray-300 font-medium">Properties Managed</div>
                </div>

                <!-- Client Satisfaction -->
                <div class="text-center p-8 rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-xl shadow-xl ring-1 ring-black/5 dark:ring-white/10" 
                     x-show="shown" 
                     x-transition:enter="transition ease-out duration-500 delay-150" 
                     x-transition:enter-start="opacity-0 transform translate-y-8" 
                     x-transition:enter-end="opacity-100 transform translate-y-0">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#02c9c2]/10 rounded-full mb-4">
                        <flux:icon name="face-smile" class="h-8 w-8 text-[#02c9c2]" />
                    </div>
                    <div class="text-5xl font-bold text-[#02c9c2] mb-3" 
                         x-data="{ shown: false, number: 0 }" 
                         x-intersect="shown = true; if(shown) { 
                            const interval = setInterval(() => {
                                number = number + ({{ $stats['client_satisfaction'] }} - number) / 10;
                                if (Math.abs({{ $stats['client_satisfaction'] }} - number) < 0.5) {
                                    number = {{ $stats['client_satisfaction'] }};
                                    clearInterval(interval);
                                }
                            }, 25);
                         }">
                        <span x-text="Math.round(number)">0</span>%
                    </div>
                    <div class="text-gray-600 dark:text-gray-300 font-medium">Client Satisfaction</div>
                </div>

                <!-- Years in Business -->
                <div class="text-center p-8 rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-xl shadow-xl ring-1 ring-black/5 dark:ring-white/10" 
                     x-show="shown" 
                     x-transition:enter="transition ease-out duration-500 delay-300" 
                     x-transition:enter-start="opacity-0 transform translate-y-8" 
                     x-transition:enter-end="opacity-100 transform translate-y-0">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#02c9c2]/10 rounded-full mb-4">
                        <flux:icon name="clock" class="h-8 w-8 text-[#02c9c2]" />
                    </div>
                    <div class="text-5xl font-bold text-[#02c9c2] mb-3" 
                         x-data="{ shown: false, number: 0 }" 
                         x-intersect="shown = true; if(shown) { 
                            const interval = setInterval(() => {
                                number = number + ({{ $stats['years_in_business'] }} - number) / 10;
                                if (Math.abs({{ $stats['years_in_business'] }} - number) < 0.5) {
                                    number = {{ $stats['years_in_business'] }};
                                    clearInterval(interval);
                                }
                            }, 40);
                         }">
                        <span x-text="Math.round(number)">0</span>+
                    </div>
                    <div class="text-gray-600 dark:text-gray-300 font-medium">Years in Business</div>
                </div>

                <!-- Happy Clients -->
                <div class="text-center p-8 rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-xl shadow-xl ring-1 ring-black/5 dark:ring-white/10" 
                     x-show="shown" 
                     x-transition:enter="transition ease-out duration-500 delay-450" 
                     x-transition:enter-start="opacity-0 transform translate-y-8" 
                     x-transition:enter-end="opacity-100 transform translate-y-0">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#02c9c2]/10 rounded-full mb-4">
                        <flux:icon name="users" class="h-8 w-8 text-[#02c9c2]" />
                    </div>
                    <div class="text-5xl font-bold text-[#02c9c2] mb-3" 
                         x-data="{ shown: false, number: 0 }" 
                         x-intersect="shown = true; if(shown) { 
                            const interval = setInterval(() => {
                                number = number + ({{ $stats['client_base'] }} - number) / 10;
                                if (Math.abs({{ $stats['client_base'] }} - number) < 1) {
                                    number = {{ $stats['client_base'] }};
                                    clearInterval(interval);
                                }
                            }, 15);
                         }">
                        <span x-text="Math.round(number)">0</span>+
                    </div>
                    <div class="text-gray-600 dark:text-gray-300 font-medium">Happy Clients</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Section (New) -->
    <div class="py-24 bg-white dark:bg-gray-900 relative overflow-hidden">
        <div class="relative max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16" x-data="{}" x-intersect="$el.classList.add('animate-fade-in')">
                <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">
                    Meet The Team
                </span>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Our Leadership</h2>
                <p class="text-gray-600 dark:text-gray-300 max-w-2xl mx-auto text-lg">
                    The experienced professionals driving our vision forward
                </p>
                <div class="h-1 w-20 bg-[#02c9c2] mx-auto mt-4"></div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Team Member 1 -->
                <div class="group" x-data="{}" x-intersect="$el.classList.add('animate-fade-in')">
                    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-xl ring-1 ring-black/5 dark:ring-white/10">
                        <!-- Image Frame with overlay -->
                        <div class="relative h-80 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent z-10"></div>
                            <img src="{{ asset('images/placeholder.webp') }}" alt="Team Member" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700">
                            
                            <!-- Social links that appear on hover -->
                            <div class="absolute bottom-4 left-0 right-0 z-20 flex justify-center space-x-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <a href="#" class="bg-white/90 p-2 rounded-full hover:bg-[#02c9c2] hover:text-white transition-colors">
                                    <flux:icon name="link" class="h-5 w-5" />
                                </a>
                                <a href="#" class="bg-white/90 p-2 rounded-full hover:bg-[#02c9c2] hover:text-white transition-colors">
                                    <flux:icon name="envelope" class="h-5 w-5" />
                                </a>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Jane Doe</h3>
                            <p class="text-[#02c9c2] font-medium">Chief Executive Officer</p>
                            <p class="mt-3 text-gray-600 dark:text-gray-300">
                                With over 20 years of experience in real estate, Jane leads our team with vision and expertise.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Team Member 2 -->
                <div class="group" x-data="{}" x-intersect="$el.classList.add('animate-fade-in')">
                    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-xl ring-1 ring-black/5 dark:ring-white/10">
                        <!-- Image Frame with overlay -->
                        <div class="relative h-80 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent z-10"></div>
                            <img src="{{ asset('images/placeholder.webp') }}" alt="Team Member" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700">
                            
                            <!-- Social links that appear on hover -->
                            <div class="absolute bottom-4 left-0 right-0 z-20 flex justify-center space-x-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <a href="#" class="bg-white/90 p-2 rounded-full hover:bg-[#02c9c2] hover:text-white transition-colors">
                                    <flux:icon name="link" class="h-5 w-5" />
                                </a>
                                <a href="#" class="bg-white/90 p-2 rounded-full hover:bg-[#02c9c2] hover:text-white transition-colors">
                                    <flux:icon name="envelope" class="h-5 w-5" />
                                </a>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">John Smith</h3>
                            <p class="text-[#02c9c2] font-medium">Chief Operations Officer</p>
                            <p class="mt-3 text-gray-600 dark:text-gray-300">
                                John oversees our operations, ensuring we deliver exceptional service to every client.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Team Member 3 -->
                <div class="group" x-data="{}" x-intersect="$el.classList.add('animate-fade-in')">
                    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-xl ring-1 ring-black/5 dark:ring-white/10">
                        <!-- Image Frame with overlay -->
                        <div class="relative h-80 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent z-10"></div>
                            <img src="{{ asset('images/placeholder.webp') }}" alt="Team Member" class="object-cover w-full h-full group-hover:scale-110 transition-transform duration-700">
                            
                            <!-- Social links that appear on hover -->
                            <div class="absolute bottom-4 left-0 right-0 z-20 flex justify-center space-x-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <a href="#" class="bg-white/90 p-2 rounded-full hover:bg-[#02c9c2] hover:text-white transition-colors">
                                    <flux:icon name="link" class="h-5 w-5" />
                                </a>
                                <a href="#" class="bg-white/90 p-2 rounded-full hover:bg-[#02c9c2] hover:text-white transition-colors">
                                    <flux:icon name="envelope" class="h-5 w-5" />
                                </a>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Sarah Johnson</h3>
                            <p class="text-[#02c9c2] font-medium">Head of Property Management</p>
                            <p class="mt-3 text-gray-600 dark:text-gray-300">
                                Sarah leads our property management team, ensuring our clients' properties are well-maintained.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Call to Action (enhanced from the index page reference) -->
    <div class="mt-20 px-6 py-12 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 shadow-xl relative overflow-hidden mx-6 lg:mx-auto max-w-7xl">
        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-[#02c9c2]/10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-[#02c9c2]/5 rounded-full blur-xl"></div>

        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="mb-6 md:mb-0 md:mr-8">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    Ready to start your property journey?
                </h3>
                <p class="text-gray-600 dark:text-gray-300">
                    Our team is ready to assist with all your real estate needs
                </p>
            </div>
            <div class="flex gap-4 md:flex-shrink-0">
                <a href="{{ route('properties.index') }}" wire:navigate
                    class="inline-flex items-center px-6 py-3 border border-[#02c9c2] text-base font-medium rounded-lg text-[#02c9c2] hover:bg-[#02c9c2]/5 transition-all duration-300">
                    Browse Properties
                </a>
                <a href="{{ route('contact') }}" wire:navigate
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-[#02c9c2] to-[#012e2b] hover:from-[#012e2b] hover:to-[#02c9c2] transition-all duration-300 shadow-md hover:shadow-lg">
                    Contact Us
                    <flux:icon name="arrow-right" class="ml-2 -mr-1 h-5 w-5" />
                </a>
            </div>
        </div>
    </div>
</div>
