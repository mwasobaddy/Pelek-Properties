<?php

use function Livewire\Volt\{state, mount, computed};
use App\Services\AnalyticsService;

state(['loading' => true]);
state(['selectedPeriod' => 'month']);
state(['trends' => []]);
state(['revenue' => []]);
state(['occupancy' => []]);
state(['conversion' => []]);

mount(function (AnalyticsService $analyticsService) {
    $this->loading = true;
    $this->trends = $analyticsService->getPropertyMarketTrends();
    $this->revenue = $analyticsService->getRevenueAnalytics();
    $this->occupancy = $analyticsService->getOccupancyRates();
    $this->conversion = $analyticsService->getConversionMetrics();
    $this->loading = false;
});

$updatePeriod = function (string $period) {
    $this->selectedPeriod = $period;
    // Trigger data refresh based on new period
};

?>

<div class="p-6 space-y-6 bg-white dark:bg-gray-800 rounded-lg shadow-xl">
    <!-- Header Section -->
    <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Analytics Dashboard</h3>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Comprehensive overview of property performance and market insights</p>
    </div>

    <!-- Period Selector -->
    <div class="flex space-x-4 mb-6">
        <button 
            wire:click="updatePeriod('week')"
            @class([
                'px-4 py-2 text-sm font-medium rounded-md',
                'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300' => $selectedPeriod === 'week',
                'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' => $selectedPeriod !== 'week'
            ])
        >
            Weekly
        </button>
        <button 
            wire:click="updatePeriod('month')"
            @class([
                'px-4 py-2 text-sm font-medium rounded-md',
                'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300' => $selectedPeriod === 'month',
                'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' => $selectedPeriod !== 'month'
            ])
        >
            Monthly
        </button>
    </div>

    <!-- KPI Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Market Value Card -->
        <div class="bg-white dark:bg-gray-700 rounded-lg p-6 shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <flux:icon name="currency-dollar" class="h-6 w-6 text-white" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Average Property Value</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        KES {{ number_format($trends['average_price'] ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="bg-white dark:bg-gray-700 rounded-lg p-6 shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <flux:icon name="chart-bar" class="h-6 w-6 text-white" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Monthly Revenue</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        KES {{ number_format($revenue['monthly_revenue'] ?? 0, 2) }}
                    </p>
                    @if(($revenue['revenue_growth'] ?? 0) > 0)
                        <span class="text-green-600 dark:text-green-400 text-sm">
                            +{{ number_format($revenue['revenue_growth'], 1) }}% from last month
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Occupancy Rate Card -->
        <div class="bg-white dark:bg-gray-700 rounded-lg p-6 shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                    <flux:icon name="building-office-2" class="h-6 w-6 text-white" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Occupancy Rate</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($occupancy['overall_rate'] ?? 0, 1) }}%
                    </p>
                </div>
            </div>
        </div>

        <!-- Conversion Rate Card -->
        <div class="bg-white dark:bg-gray-700 rounded-lg p-6 shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                    <flux:icon name="arrow-path" class="h-6 w-6 text-white" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Viewing to Offer Rate</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($conversion['viewing_to_offer_rate'] ?? 0, 1) }}%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Revenue Trend Chart -->
        <div class="bg-white dark:bg-gray-700 rounded-lg p-6 shadow">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Revenue Trend</h4>
            <div class="h-80">
                <!-- Revenue chart will be rendered here -->
            </div>
        </div>

        <!-- Property Type Distribution -->
        <div class="bg-white dark:bg-gray-700 rounded-lg p-6 shadow">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Property Distribution</h4>
            <div class="h-80">
                <!-- Property distribution chart will be rendered here -->
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div wire:loading.flex class="fixed inset-0 bg-gray-900/50 items-center justify-center">
        <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-white"></div>
    </div>
</div>
