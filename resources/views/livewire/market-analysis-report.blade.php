<?php

use function Livewire\Volt\{state, computed};
use App\Models\MarketAnalysis;

state([
    'location' => '',
    'propertyType' => '',
    'analysis' => null,
]);

$loadAnalysis = function () {
    if ($this->location && $this->propertyType) {
        $this->analysis = MarketAnalysis::where('location', $this->location)
            ->where('property_type', $this->propertyType)
            ->first();
    }
};

computed([
    'trend' => fn() => $this->analysis?->getMarketTrend() ?? 'stable',
    'trendColor' => function() {
        return match($this->trend) {
            'increasing' => 'text-green-600',
            'decreasing' => 'text-red-600',
            default => 'text-yellow-600'
        };
    },
    'formattedAveragePrice' => fn() => $this->analysis ? number_format($this->analysis->average_price, 2) : '0.00',
    'formattedPricePerSqft' => fn() => $this->analysis ? number_format($this->analysis->price_per_sqft, 2) : '0.00',
]);

?>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Market Analysis Report</h2>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                <select
                    wire:model.live="location"
                    id="location"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                >
                    <option value="">Select Location</option>
                    <option value="Nairobi">Nairobi</option>
                    <option value="Mombasa">Mombasa</option>
                    <option value="Kisumu">Kisumu</option>
                </select>
            </div>

            <div>
                <label for="propertyType" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property Type</label>
                <select
                    wire:model.live="propertyType"
                    id="propertyType"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                >
                    <option value="">Select Type</option>
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                    <option value="land">Land</option>
                </select>
            </div>
        </div>
    </div>

    @if($analysis)
        <div class="grid grid-cols-2 gap-6">
            <!-- Market Overview -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Market Overview</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Average Price</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">KES {{ $this->formattedAveragePrice }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Price per Sq Ft</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">KES {{ $this->formattedPricePerSqft }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Market Trend</p>
                        <p class="text-lg font-semibold {{ $this->trendColor }} capitalize">
                            {{ $this->trend }}
                            @if($this->trend === 'increasing')
                                <svg class="inline w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                </svg>
                            @elseif($this->trend === 'decreasing')
                                <svg class="inline w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Market Statistics -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Market Statistics</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Listings</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $analysis->total_listings }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Average Days on Market</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $analysis->days_on_market }} days</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Market Activity</p>
                        <div class="relative pt-1">
                            @php
                                $activityPercent = min(($analysis->total_listings / 100) * 100, 100);
                            @endphp
                            <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200 dark:bg-gray-600">
                                <div 
                                    style="width: {{ $activityPercent }}%"
                                    class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Price Trends Chart -->
        @if($analysis->price_trends)
            <div class="mt-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Price Trends</h3>
                <div class="h-64 relative">
                    <!-- In a real application, you would integrate a charting library like Chart.js here -->
                    <p class="text-gray-600 dark:text-gray-400 text-center py-8">
                        Price trend visualization would be displayed here using Chart.js or a similar library
                    </p>
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-8">
            <p class="text-gray-600 dark:text-gray-400">
                Select a location and property type to view market analysis
            </p>
        </div>
    @endif
</div>
