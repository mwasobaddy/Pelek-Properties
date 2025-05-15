<?php

use function Livewire\Volt\{state, computed};
use App\Models\Property;
use App\Models\PropertyOffer;
use App\Services\SalePropertyService;

state([
    'activeTab' => 'overview',
]);

$viewingsData = computed(function () {
    return app(SalePropertyService::class)->getViewingsDashboard();
});

$offersData = computed(function () {
    return app(SalePropertyService::class)->getOffersDashboard();
});

$properties = computed(function () {
    return Property::forSale()
        ->withCount(['viewingAppointments', 'offers'])
        ->latest()
        ->take(5)
        ->get();
});

$updateOfferStatus = function (int $offerId, string $status) {
    $offer = PropertyOffer::findOrFail($offerId);
    app(SalePropertyService::class)->updateOfferStatus($offer, $status);
    $this->dispatch('offer-updated', ['offerId' => $offerId]);
};

?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="$set('activeTab', 'overview')" 
                @class([
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                    'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' => $activeTab === 'overview',
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' => $activeTab !== 'overview'
                ])>
                Overview
            </button>
            <button wire:click="$set('activeTab', 'offers')"
                @class([
                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                    'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' => $activeTab === 'offers',
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' => $activeTab !== 'offers'
                ])>
                Offer Management
            </button>
        </nav>
    </div>

    @if($activeTab === 'overview')
        <!-- Stats Overview -->
        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Viewing Stats -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Viewings</h3>
                <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                    {{ count($this->viewingsData['today']) }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">This Week's Viewings</h3>
                <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                    {{ count($this->viewingsData['this_week']) }}
                </div>
            </div>

            <!-- Offer Stats -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Offers</h3>
                <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                    {{ $this->offersData['active_offers'] }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Offer Value</h3>
                <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                    KES {{ number_format($this->offersData['total_value'], 2) }}
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Today's Viewings -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Today's Viewings</h3>
                
                @if($this->viewingsData['today']->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($this->viewingsData['today'] as $appointment)
                            <div class="border-l-4 border-indigo-400 bg-indigo-50 dark:bg-indigo-900/50 p-4 rounded-r-lg">
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
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Offers</h3>
                
                @if($this->offersData['recent_offers']->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($this->offersData['recent_offers'] as $offer)
                            <div class="border-l-4 border-green-400 bg-green-50 dark:bg-green-900/50 p-4 rounded-r-lg">
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
                                @if($offer->status === 'pending')
                                    <div class="mt-2 flex gap-2">
                                        <button wire:click="updateOfferStatus({{ $offer->id }}, 'accepted')"
                                            class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Accept
                                        </button>
                                        <button wire:click="updateOfferStatus({{ $offer->id }}, 'rejected')"
                                            class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            Reject
                                        </button>
                                    </div>
                                @endif
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
