<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.guest')] class extends Component {
    public array $sections = [
        [
            'icon' => 'scale',
            'title' => 'Service Terms',
            'content' => 'These terms outline the rules and regulations for using Pelek Properties services.'
        ],
        [
            'icon' => 'user-group',
            'title' => 'User Obligations',
            'content' => 'Users must provide accurate information and comply with our property viewing and booking policies.'
        ],
        [
            'icon' => 'document-check',
            'title' => 'Legal Compliance',
            'content' => 'All transactions and interactions must comply with applicable real estate and property laws.'
        ]
    ];

    public function mount()
    {
        // No initialization needed as data is defined in the property
    }
};

?>

<div>
    <div x-cloak class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
        <!-- Enhanced Hero Section with Parallax Effect -->
        <div class="relative overflow-hidden bg-gradient-to-br from-zinc-900 to-[#012e2b] dark:from-zinc-950 dark:to-[#012e2b]">
            <!-- Background elements with parallax effect -->
            <div class="absolute inset-0" x-data="{}"
                x-on:scroll.window="$el.style.transform = `translateY(${window.scrollY * 0.1}px)`">
                <div class="absolute inset-0 bg-gradient-to-br from-zinc-900/85 via-[#012e2b]/75 to-[#02c9c2]/30 backdrop-blur-sm"></div>
                <img src="{{ asset('images/placeholder.webp') }}" alt="Terms of Service" class="h-full w-full object-cover opacity-40">
            </div>

            <!-- Decorative Elements -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-[#02c9c2]/20 blur-3xl"></div>
                <div class="absolute right-0 bottom-0 h-80 w-80 rounded-full bg-[#02c9c2]/15 blur-3xl"></div>
            </div>

            <!-- Enhanced Content with Animation -->
            <div class="relative mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8">
                <div class="mx-auto max-w-2xl lg:mx-0" 
                     x-data="{}" 
                     x-intersect="$el.classList.add('animate-fade-in')">
                    <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">
                        Legal Agreement
                    </span>
                    <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl font-display">
                        Terms of Service
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-zinc-300">
                        Please read these terms carefully before using our services at Pelek Properties.
                    </p>
                </div>
            </div>
        </div>

        <!-- Enhanced Main Content Section -->
        <div class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <!-- Key Features Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    @foreach($sections as $section)
                        <div class="relative group">
                            <div class="rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-xl p-6 shadow-xl ring-1 ring-black/5 dark:ring-white/10 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="p-3 rounded-xl bg-[#02c9c2]/10 text-[#02c9c2]">
                                        <flux:icon :name="$section['icon']" class="w-6 h-6" />
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                        {{ $section['title'] }}
                                    </h3>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ $section['content'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Main Content Card -->
                <div class="mx-auto max-w-3xl">
                    <div class="rounded-2xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-xl p-8 shadow-xl ring-1 ring-black/5 dark:ring-white/10">
                        <div class="prose dark:prose-invert prose-lg max-w-none">
                            <!-- Content sections with enhanced styling -->
                            <div class="space-y-12">
                                <!-- Service Usage Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="building-office" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            Service Usage Guidelines
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach([
                                                'Property viewing appointments must be scheduled in advance',
                                                'Booking cancellations require 24-hour notice',
                                                'Documentation requirements for property transactions',
                                                'Guidelines for using online booking features'
                                            ] as $item)
                                                <li class="flex items-start gap-3">
                                                    <flux:icon name="check-circle" class="w-5 h-5 text-[#02c9c2] flex-shrink-0 mt-1" />
                                                    <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </section>

                                <!-- User Responsibilities Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="user-group" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            User Responsibilities
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                                            When using Pelek Properties services, you agree to:
                                        </p>
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach([
                                                'Provide accurate and up-to-date information',
                                                'Maintain the confidentiality of your account',
                                                'Comply with all applicable real estate laws',
                                                'Respect property viewing schedules and guidelines',
                                                'Use the service for legitimate property purposes only'
                                            ] as $item)
                                                <li class="flex items-start gap-3">
                                                    <flux:icon name="check-circle" class="w-5 h-5 text-[#02c9c2] flex-shrink-0 mt-1" />
                                                    <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </section>

                                <!-- Property Bookings Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="calendar" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            Property Bookings & Viewings
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                                            Our booking and viewing policies include:
                                        </p>
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach([
                                                '24-hour notice required for viewing cancellations',
                                                'Maximum of 3 active viewing requests per property',
                                                'Valid identification required for viewings',
                                                'Respect for property owners and current tenants',
                                                'Adherence to safety protocols during viewings'
                                            ] as $item)
                                                <li class="flex items-start gap-3">
                                                    <flux:icon name="check-circle" class="w-5 h-5 text-[#02c9c2] flex-shrink-0 mt-1" />
                                                    <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </section>

                                <!-- Legal Compliance Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="scale" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            Legal Compliance & Liability
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                                            Important legal information:
                                        </p>
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach([
                                                'Compliance with real estate regulations',
                                                'Property information accuracy disclaimer',
                                                'Limitation of liability for transactions',
                                                'Dispute resolution procedures',
                                                'Terms modification and notification policy'
                                            ] as $item)
                                                <li class="flex items-start gap-3">
                                                    <flux:icon name="check-circle" class="w-5 h-5 text-[#02c9c2] flex-shrink-0 mt-1" />
                                                    <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </section>

                                <!-- Contact Section -->
                                <section class="rounded-xl bg-gradient-to-br from-[#02c9c2]/10 to-transparent p-6">
                                    <div class="flex items-center gap-3 mb-6">
                                        <flux:icon name="phone" class="w-6 h-6 text-[#02c9c2]" />
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            Legal Inquiries
                                        </h2>
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <a href="mailto:legal@pelekproperties.com" class="flex items-center gap-3 p-4 rounded-lg bg-white/50 dark:bg-gray-800/50 hover:bg-white/80 dark:hover:bg-gray-700/50 transition-all duration-300 group">
                                            <flux:icon name="envelope" class="w-5 h-5 text-[#02c9c2] group-hover:scale-110 transition-transform duration-300" />
                                            <span class="text-gray-600 dark:text-gray-300">legal@pelekproperties.com</span>
                                        </a>
                                        <a href="tel:+254712345678" class="flex items-center gap-3 p-4 rounded-lg bg-white/50 dark:bg-gray-800/50 hover:bg-white/80 dark:hover:bg-gray-700/50 transition-all duration-300 group">
                                            <flux:icon name="phone" class="w-5 h-5 text-[#02c9c2] group-hover:scale-110 transition-transform duration-300" />
                                            <span class="text-gray-600 dark:text-gray-300">+254 712 345 678</span>
                                        </a>
                                    </div>
                                </section>

                                <!-- Last Updated Section -->
                                <div class="flex items-center gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <flux:icon name="clock" class="w-5 h-5 text-[#02c9c2]" />
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Last updated: May 16, 2025
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
