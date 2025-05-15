<?php

use function Livewire\Volt\{state, computed};
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Services\SalePropertyService;

state([
    'salePropertyService' => fn() => app(SalePropertyService::class),
    'activeTab' => 'overview'
]);

$viewingsData = computed(function () {
    return $this->salePropertyService->getViewingsDashboard();
});

$offersData = computed(function () {
    return $this->salePropertyService->getOffersDashboard();
});

$properties = computed(function () {
    return Property::forSale()
        ->withCount(['viewingAppointments', 'offers'])
        ->latest()
        ->take(5)
        ->get();
});

$updateOfferStatus = function ($offerId, $status) {
    $offer = PropertyOffer::findOrFail($offerId);
    $this->salePropertyService->updateOfferStatus($offer, $status);
    $this->dispatch('offer-updated', $offerId);
};

?>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Sale Properties Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Navigation Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button wire:click="$set('activeTab', 'overview')" 
                        class="{{ $activeTab === 'overview' ? 'border-indigo-500 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Overview
                    </button>
                    <button wire:click="$set('activeTab', 'offers')"
                        class="{{ $activeTab === 'offers' ? 'border-indigo-500 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Offer Management
                    </button>
                </nav>
            </div>

            @if($activeTab === 'overview')
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                    <!-- Viewing Stats -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Viewings</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ count($this->viewingsData['today']) }}
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">This Week's Viewings</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ count($this->viewingsData['this_week']) }}
                        </div>
                    </div>

                    <!-- Offer Stats -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Offers</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ $this->offersData['active_offers'] }}
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Offer Value</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            KES {{ number_format($this->offersData['total_value'], 2) }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Today's Viewings -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Today's Viewings</h3>
                        
                        @if($this->viewingsData['today']->isNotEmpty())
                            <div class="space-y-4">
                                @foreach($this->viewingsData['today'] as $appointment)
                                    <div class="border-l-4 border-indigo-400 bg-indigo-50 dark:bg-indigo-900/50 p-4">
                                        <div class="flex justify-between">
                                            <div class="text-sm font-medium text-indigo-800 dark:text-indigo-200">
                                                {{ $appointment->property->title }}
                                            </div>
                                            <div class="text-sm text-indigo-600 dark:text-indigo-300">
                                                {{ $appointment->scheduled_at->format('h:i A') }}
                                            </div>
                                        </div>
                                        <div class="mt-2 text-sm text-indigo-700 dark:text-indigo-300">
                                            Client: {{ $appointment->client->name }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400">No viewings scheduled for today.</p>
                        @endif
                    </div>

                    <!-- Recent Offers -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Offers</h3>
                        
                        @if($this->offersData['recent_offers']->isNotEmpty())
                            <div class="space-y-4">
                                @foreach($this->offersData['recent_offers'] as $offer)
                                    <div class="border-l-4 border-green-400 bg-green-50 dark:bg-green-900/50 p-4">
                                        <div class="flex justify-between">
                                            <div class="text-sm font-medium text-green-800 dark:text-green-200">
                                                {{ $offer->property->title }}
                                            </div>
                                            <div class="text-sm text-green-600 dark:text-green-300">
                                                KES {{ number_format($offer->amount, 2) }}
                                            </div>
                                        </div>
                                        <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                            From: {{ $offer->client->name }}
                                        </div>
                                        <div class="mt-2 flex gap-2">
                                            <button wire:click="updateOfferStatus({{ $offer->id }}, 'accepted')"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:border-green-700 focus:shadow-outline-green active:bg-green-800 transition ease-in-out duration-150">
                                                Accept
                                            </button>
                                            <button wire:click="updateOfferStatus({{ $offer->id }}, 'rejected')"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-800 transition ease-in-out duration-150">
                                                Reject
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400">No recent offers.</p>
                        @endif
                    </div>
                </div>
            @else
                <!-- Offer Management Component -->
                <livewire:admin.components.property-offer-manager />
            @endif
        </div>
    </div>
</x-app-layout>
