<?php

use function Livewire\Volt\{state, computed, rules};
use App\Services\ValuationService;

state([
    'propertyType' => '',
    'location' => '',
    'landSize' => '',
    'bedrooms' => '',
    'bathrooms' => '',
    'estimatedValue' => null,
    'isCalculating' => false,
    'factors' => [],
    'confidenceLevel' => '',
]);

rules([
    'propertyType' => 'required',
    'location' => 'required',
    'landSize' => 'required|numeric|min:0',
    'bedrooms' => 'nullable|integer|min:0',
    'bathrooms' => 'nullable|integer|min:0',
]);

$calculate = function (ValuationService $valuationService) {
    $this->isCalculating = true;

    try {
        // Create a temporary request object
        $request = new \App\Models\ValuationRequest([
            'property_type' => $this->propertyType,
            'location' => $this->location,
            'land_size' => $this->landSize,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
        ]);

        // Get market analysis
        $marketAnalysis = $valuationService->getMarketAnalysis($this->location, $this->propertyType);
        
        // Generate a report without saving
        $report = $valuationService->generateReport($request);
        
        $this->estimatedValue = $report->estimated_value;
        $this->factors = $report->valuation_factors;
        $this->confidenceLevel = $report->confidence_level;

    } catch (\Exception $e) {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'Error calculating property value. Please try again.'
        ]);
    }

    $this->isCalculating = false;
};

computed([
    'formattedValue' => fn() => $this->estimatedValue ? number_format($this->estimatedValue, 2) : null,
    'confidenceBadgeColor' => function() {
        return match($this->confidenceLevel) {
            'high' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'low' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
        };
    },
]);

?>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Property Value Calculator</h2>

    <form wire:submit="calculate" class="space-y-6">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="propertyType" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property Type</label>
                <select
                    wire:model="propertyType"
                    id="propertyType"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                >
                    <option value="">Select Type</option>
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                    <option value="land">Land</option>
                </select>
                @error('propertyType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                <select
                    wire:model="location"
                    id="location"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                >
                    <option value="">Select Location</option>
                    <option value="Nairobi">Nairobi</option>
                    <option value="Mombasa">Mombasa</option>
                    <option value="Kisumu">Kisumu</option>
                </select>
                @error('location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label for="landSize" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Land Size (sqft)</label>
                <input
                    wire:model="landSize"
                    type="number"
                    step="0.01"
                    id="landSize"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                >
                @error('landSize') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="bedrooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bedrooms</label>
                <input
                    wire:model="bedrooms"
                    type="number"
                    id="bedrooms"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                >
                @error('bedrooms') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="bathrooms" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bathrooms</label>
                <input
                    wire:model="bathrooms"
                    type="number"
                    id="bathrooms"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                >
                @error('bathrooms') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <button
                type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
            >
                <span wire:loading wire:target="calculate">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                Calculate Value
            </button>
        </div>
    </form>

    @if($estimatedValue)
        <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="text-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Estimated Property Value</h3>
                <p class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                    KES {{ $this->formattedValue }}
                </p>
                <span class="inline-flex items-center px-3 py-1 mt-2 rounded-full text-sm font-medium {{ $this->confidenceBadgeColor }}">
                    {{ ucfirst($confidenceLevel) }} Confidence
                </span>
            </div>

            @if($factors)
                <div class="mt-6">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Valuation Factors</h4>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($factors as $key => $value)
                            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white capitalize">{{ $value }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-6 text-center">
                <button
                    type="button"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
                    wire:click="$dispatch('openModal', { component: 'valuation-request-form' })"
                >
                    Request Detailed Valuation Report
                </button>
            </div>
        </div>
    @endif
</div>
