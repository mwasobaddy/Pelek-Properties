<?php

use function Livewire\Volt\{state, rules, mount};
use App\Services\ValuationService;
use App\Models\ValuationRequest;

state([
    'property_type' => '',
    'location' => '',
    'land_size' => null,
    'bedrooms' => null,
    'bathrooms' => null,
    'description' => '',
    'purpose' => 'sale',
    'isSubmitting' => false,
    'showThankYou' => false,
]);

rules([
    'property_type' => 'required|string',
    'location' => 'required|string',
    'land_size' => 'nullable|numeric|min:0',
    'bedrooms' => 'nullable|integer|min:0',
    'bathrooms' => 'nullable|integer|min:0',
    'description' => 'nullable|string|max:500',
    'purpose' => 'required|in:sale,rental,insurance',
]);

$submit = function (ValuationService $valuationService) {
    $this->isSubmitting = true;

    try {
        $request = $valuationService->createRequest([
            'user_id' => auth()->id(),
            'property_type' => $this->property_type,
            'location' => $this->location,
            'land_size' => $this->land_size,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'description' => $this->description,
            'purpose' => $this->purpose,
        ]);

        $this->reset(['property_type', 'location', 'land_size', 'bedrooms', 'bathrooms', 'description']);
        $this->showThankYou = true;
        
        // Show success message
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Your valuation request has been submitted successfully!'
        ]);
    } catch (\Exception $e) {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'There was an error submitting your request. Please try again.'
        ]);
    }

    $this->isSubmitting = false;
};

?>

<div class="max-w-2xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
    @if($showThankYou)
        <div class="text-center py-8">
            <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Thank You!</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-6">
                Your valuation request has been submitted. Our team will process it and get back to you shortly.
            </p>
            <button
                wire:click="$set('showThankYou', false)"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md"
            >
                Request Another Valuation
            </button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            <div>
                <label for="property_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property Type</label>
                <select
                    wire:model="property_type"
                    id="property_type"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                >
                    <option value="">Select Property Type</option>
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                    <option value="land">Land</option>
                </select>
                @error('property_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                <input
                    wire:model="location"
                    type="text"
                    id="location"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                    placeholder="Enter property location"
                >
                @error('location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="land_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Land Size (sqft)</label>
                    <input
                        wire:model="land_size"
                        type="number"
                        step="0.01"
                        id="land_size"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                    >
                    @error('land_size') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                <label for="purpose" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valuation Purpose</label>
                <select
                    wire:model="purpose"
                    id="purpose"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                >
                    <option value="sale">Sale</option>
                    <option value="rental">Rental</option>
                    <option value="insurance">Insurance</option>
                </select>
                @error('purpose') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Additional Details</label>
                <textarea
                    wire:model="description"
                    id="description"
                    rows="4"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                    placeholder="Any additional information about the property..."
                ></textarea>
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <button
                    type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                >
                    <span wire:loading wire:target="submit">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    Request Valuation
                </button>
            </div>
        </form>
    @endif
</div>
