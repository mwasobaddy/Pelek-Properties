<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state};

new class extends Component {
    public array $actions = [
        [
            'title' => 'List Your Property',
            'description' => 'Whether you're selling or renting out your property, we'll help you reach the right audience.',
            'icon' => 'squares-plus',
            'color' => 'emerald',
            'buttons' => [
                [
                    'text' => 'List for Sale',
                    'route' => 'properties.create',
                    'params' => ['type' => 'sale'],
                    'style' => 'primary'
                ],
                [
                    'text' => 'List for Rent',
                    'route' => 'properties.create',
                    'params' => ['type' => 'rent'],
                    'style' => 'secondary'
                ]
            ]
        ],
        [
            'title' => 'Property Management',
            'description' => 'Let us handle your property with our comprehensive management services.',
            'icon' => 'building-office',
            'color' => 'blue',
            'buttons' => [
                [
                    'text' => 'Learn More',
                    'route' => 'services.management',
                    'style' => 'primary'
                ],
                [
                    'text' => 'Contact Us',
                    'route' => 'contact',
                    'params' => ['subject' => 'Property Management'],
                    'style' => 'secondary'
                ]
            ]
        ],
        [
            'title' => 'List on Airbnb',
            'description' => 'Maximize your property's earning potential with our Airbnb management service.',
            'icon' => 'calendar-days',
            'color' => 'purple',
            'buttons' => [
                [
                    'text' => 'Get Started',
                    'route' => 'properties.create',
                    'params' => ['type' => 'airbnb'],
                    'style' => 'primary'
                ],
                [
                    'text' => 'Learn More',
                    'route' => 'services.airbnb',
                    'style' => 'secondary'
                ]
            ]
        ]
    ];
} ?>

<div class="py-16 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                Ready to Get Started?
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                Choose how you'd like to work with Pelek Properties
            </p>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            @foreach($actions as $action)
                <div class="relative group">
                    <div class="h-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm transition duration-300 hover:shadow-lg">
                        {{-- Icon --}}
                        <div class="mb-6">
                            <div class="inline-flex items-center justify-center p-3 rounded-xl
                                {{ $action['color'] === 'emerald' ? 'bg-emerald-100 dark:bg-emerald-800/30' : '' }}
                                {{ $action['color'] === 'blue' ? 'bg-blue-100 dark:bg-blue-800/30' : '' }}
                                {{ $action['color'] === 'purple' ? 'bg-purple-100 dark:bg-purple-800/30' : '' }}"
                            >
                                <x-flux-icon 
                                    :name="$action['icon']" 
                                    class="h-8 w-8
                                        {{ $action['color'] === 'emerald' ? 'text-emerald-600 dark:text-emerald-400' : '' }}
                                        {{ $action['color'] === 'blue' ? 'text-blue-600 dark:text-blue-400' : '' }}
                                        {{ $action['color'] === 'purple' ? 'text-purple-600 dark:text-purple-400' : '' }}"
                                />
                            </div>
                        </div>

                        {{-- Content --}}
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                            {{ $action['title'] }}
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-8">
                            {{ $action['description'] }}
                        </p>

                        {{-- Buttons --}}
                        <div class="space-y-3 sm:space-y-0 sm:space-x-3 sm:flex mt-auto">
                            @foreach($action['buttons'] as $button)
                                <a 
                                    href="{{ route($button['route'], $button['params'] ?? []) }}"
                                    @class([
                                        'flex-1 inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
                                        'text-white shadow-sm' => $button['style'] === 'primary',
                                        'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500' => $button['style'] === 'primary' && $action['color'] === 'emerald',
                                        'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' => $button['style'] === 'primary' && $action['color'] === 'blue',
                                        'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500' => $button['style'] === 'primary' && $action['color'] === 'purple',
                                        'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600' => $button['style'] === 'secondary',
                                    ])
                                >
                                    {{ $button['text'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
