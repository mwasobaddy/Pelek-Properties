<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.guest')] class extends Component {
    public array $sections = [
        [
            'icon' => 'shield-check',
            'title' => 'Data Protection',
            'content' => 'We implement industry-standard security measures to protect your personal information.'
        ],
        [
            'icon' => 'document-text',
            'title' => 'Information Usage',
            'content' => 'Your data is used only for providing and improving our real estate services.'
        ],
        [
            'icon' => 'user-circle',
            'title' => 'Your Rights',
            'content' => 'You have full control over your personal data, including the right to access and delete.'
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
                <img src="{{ asset('images/placeholder.webp') }}" alt="Privacy Policy" class="h-full w-full object-cover opacity-40">
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
                        Privacy & Security
                    </span>
                    <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl font-display">
                        Privacy Policy
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-zinc-300">
                        Your privacy matters to us. Learn about how we collect, use, and protect your personal information at Pelek Properties.
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
                                <!-- Information Collection Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="document-text" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            Information We Collect
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach(['Contact details (name, email, phone)', 'Property preferences', 'Browser and device information', 'Usage data and analytics'] as $item)
                                                <li class="flex items-start gap-3">
                                                    <flux:icon name="check-circle" class="w-5 h-5 text-[#02c9c2] flex-shrink-0 mt-1" />
                                                    <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </section>

                                <!-- Data Usage Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="chart-bar" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            How We Use Your Information
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach([
                                                'Personalizing your property search experience',
                                                'Processing property viewing requests and bookings',
                                                'Sending relevant property updates and recommendations',
                                                'Improving our services and user experience',
                                                'Communicating important updates about our services'
                                            ] as $item)
                                                <li class="flex items-start gap-3">
                                                    <flux:icon name="check-circle" class="w-5 h-5 text-[#02c9c2] flex-shrink-0 mt-1" />
                                                    <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </section>

                                <!-- Data Protection Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="shield-check" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            How We Protect Your Data
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach([
                                                'SSL encryption for all data transmissions',
                                                'Regular security audits and updates',
                                                'Strict access controls and authentication',
                                                'Data backup and disaster recovery protocols',
                                                'Compliance with international data protection standards'
                                            ] as $item)
                                                <li class="flex items-start gap-3">
                                                    <flux:icon name="check-circle" class="w-5 h-5 text-[#02c9c2] flex-shrink-0 mt-1" />
                                                    <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </section>

                                <!-- Your Rights Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="user-circle" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            Your Privacy Rights
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach([
                                                'Right to access your personal data',
                                                'Right to request data correction or deletion',
                                                'Right to opt-out of marketing communications',
                                                'Right to data portability',
                                                'Right to withdraw consent at any time'
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
                                            Contact Our Privacy Team
                                        </h2>
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <a href="mailto:privacy@pelekproperties.com" class="flex items-center gap-3 p-4 rounded-lg bg-white/50 dark:bg-gray-800/50 hover:bg-white/80 dark:hover:bg-gray-700/50 transition-all duration-300 group">
                                            <flux:icon name="envelope" class="w-5 h-5 text-[#02c9c2] group-hover:scale-110 transition-transform duration-300" />
                                            <span class="text-gray-600 dark:text-gray-300">privacy@pelekproperties.com</span>
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
