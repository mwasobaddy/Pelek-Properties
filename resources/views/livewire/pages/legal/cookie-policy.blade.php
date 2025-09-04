<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.guest')] class extends Component {
    public array $sections = [
        [
            'icon' => 'clipboard-document-check',
            'title' => 'Cookie Usage',
            'content' => 'We use cookies to enhance your browsing experience and provide personalized services.'
        ],
        [
            'icon' => 'shield-check',
            'title' => 'Cookie Security',
            'content' => 'Our cookies are secure and only store essential information needed for site functionality.'
        ],
        [
            'icon' => 'adjustments-horizontal',
            'title' => 'Cookie Control',
            'content' => 'You can manage your cookie preferences through your browser settings at any time.'
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
                <img src="{{ asset('images/placeholder.webp') }}" alt="Cookie Policy" class="h-full w-full object-cover opacity-40">
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
                        Cookies & Tracking
                    </span>
                    <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl font-display">
                        Cookie Policy
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-zinc-300">
                        Learn about how we use cookies and similar technologies to improve your experience.
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
                                <!-- Cookie Types Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="table-cells" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            Types of Cookies We Use
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach([
                                                'Essential cookies for site functionality',
                                                'Performance cookies for analytics and improvements',
                                                'Functionality cookies for your preferences',
                                                'Third-party cookies for enhanced features'
                                            ] as $item)
                                                <li class="flex items-start gap-3">
                                                    <flux:icon name="check-circle" class="w-5 h-5 text-[#02c9c2] flex-shrink-0 mt-1" />
                                                    <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </section>

                                <!-- Essential Cookies Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="key" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            Essential Cookies
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                                            These cookies are necessary for the website to function and cannot be switched off. They are usually set in response to actions you take, such as:
                                        </p>
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach([
                                                'Setting your privacy preferences',
                                                'Logging in or filling in forms',
                                                'Basic site functionality',
                                                'Security and fraud prevention'
                                            ] as $item)
                                                <li class="flex items-start gap-3">
                                                    <flux:icon name="check-circle" class="w-5 h-5 text-[#02c9c2] flex-shrink-0 mt-1" />
                                                    <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </section>

                                <!-- Performance Cookies Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="chart-bar" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            Performance & Analytics
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                                            These cookies allow us to analyze site usage and improve your experience. They collect anonymous data about:
                                        </p>
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach([
                                                'Number of visitors and page views',
                                                'Popular property listings and searches',
                                                'Error messages and loading times',
                                                'User navigation patterns'
                                            ] as $item)
                                                <li class="flex items-start gap-3">
                                                    <flux:icon name="check-circle" class="w-5 h-5 text-[#02c9c2] flex-shrink-0 mt-1" />
                                                    <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </section>

                                <!-- Managing Cookies Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="p-2 rounded-xl bg-[#02c9c2]/10">
                                            <flux:icon name="adjustments-horizontal" class="w-6 h-6 text-[#02c9c2]" />
                                        </div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white m-0">
                                            Managing Your Cookies
                                        </h2>
                                    </div>
                                    <div class="ml-11">
                                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                                            You can control and manage cookies in various ways:
                                        </p>
                                        <ul class="space-y-4 list-none pl-0">
                                            @foreach([
                                                'Browser settings to block or delete cookies',
                                                'Cookie consent preferences on our website',
                                                'Individual opt-out for analytics cookies',
                                                'Third-party cookie management tools'
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
                                            Cookie Questions?
                                        </h2>
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <a href="mailto:sales@pelekproperties.co.ke" class="flex items-center gap-3 p-4 rounded-lg bg-white/50 dark:bg-gray-800/50 hover:bg-white/80 dark:hover:bg-gray-700/50 transition-all duration-300 group">
                                            <flux:icon name="envelope" class="w-5 h-5 text-[#02c9c2] group-hover:scale-110 transition-transform duration-300" />
                                            <span class="text-gray-600 dark:text-gray-300">sales@pelekproperties.co.ke</span>
                                        </a>
                                        <a href="tel:+254711614099" class="flex items-center gap-3 p-4 rounded-lg bg-white/50 dark:bg-gray-800/50 hover:bg-white/80 dark:hover:bg-gray-700/50 transition-all duration-300 group">
                                            <flux:icon name="phone" class="w-5 h-5 text-[#02c9c2] group-hover:scale-110 transition-transform duration-300" />
                                            <span class="text-gray-600 dark:text-gray-300">+254 711 614 099</span>
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
