<?php

use function Livewire\Volt\{state, computed};
use App\Services\SalePropertyService;
use App\Models\Property;

state(filters: [
    'price_min' => null,
    'price_max' => null,
    'development_status' => null,
    'ownership_type' => null,
    'mortgage_available' => false,
    'has_title_deed' => false,
]);

$properties = computed(function () {
    return app(SalePropertyService::class)->getSaleProperties($this->filters);
});

$resetFilters = fn() => $this->filters = [
    'price_min' => null,
    'price_max' => null,
    'development_status' => null,
    'ownership_type' => null,
    'mortgage_available' => false,
    'has_title_deed' => false,
];

?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filters Section -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Filter Properties</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price Range</label>
                    <div class="flex items-center gap-2 mt-1">
                        <input type="number" wire:model.live="filters.price_min" placeholder="Min" 
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <span class="dark:text-gray-400">to</span>
                        <input type="number" wire:model.live="filters.price_max" placeholder="Max"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Development Status</label>
                    <select wire:model.live="filters.development_status" 
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All</option>
                        <option value="ready">Ready</option>
                        <option value="under_construction">Under Construction</option>
                        <option value="off_plan">Off Plan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ownership Type</label>
                    <select wire:model.live="filters.ownership_type"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All</option>
                        <option value="freehold">Freehold</option>
                        <option value="leasehold">Leasehold</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model.live="filters.mortgage_available"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Mortgage Available</span>
                </label>

                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model.live="filters.has_title_deed"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Has Title Deed</span>
                </label>

                <button wire:click="resetFilters"
                    class="ml-auto inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Properties Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($this->properties as $property)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="aspect-w-16 aspect-h-9">
                        @if($property->featured_image)
                            <img src="{{ Storage::url($property->featured_image) }}" alt="{{ $property->title }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <span class="text-gray-400 dark:text-gray-500">No Image</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $property->title }}</h3>
                        <p class="mt-2 text-xl font-bold text-indigo-600 dark:text-indigo-400">KES {{ number_format($property->price, 2) }}</p>
                        
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                {{ ucfirst(str_replace('_', ' ', $property->development_status)) }}
                            </div>

                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ ucfirst($property->ownership_type) }}
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('properties.show', $property) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $this->properties->links() }}
        </div>
    </div>
</div>
