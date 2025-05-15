<?php

use function Livewire\Volt\{state, rules, computed};
use App\Services\MaintenanceService;
use App\Models\Property;

state([
    'property_id' => '',
    'title' => '',
    'description' => '',
    'priority' => 'medium',
    'scheduled_date' => '',
    'estimated_cost' => '',
    'status' => 'pending',
    'properties' => fn() => Property::with('managementContract')->get(),
    'isSubmitting' => false,
]);

rules([
    'property_id' => 'required|exists:properties,id',
    'title' => 'required|string|max:255',
    'description' => 'required|string',
    'priority' => 'required|in:low,medium,high',
    'scheduled_date' => 'required|date|after:today',
    'estimated_cost' => 'required|numeric|min:0',
]);

$submit = function (MaintenanceService $maintenanceService) {
    $this->isSubmitting = true;

    try {
        $maintenanceService->createMaintenanceRequest([
            'property_id' => $this->property_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'scheduled_date' => $this->scheduled_date,
            'estimated_cost' => $this->estimated_cost,
            'status' => $this->status,
            'created_by' => auth()->id(),
        ]);

        $this->reset(['title', 'description', 'priority', 'scheduled_date', 'estimated_cost']);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Maintenance request created successfully!'
        ]);
    } catch (\Exception $e) {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'Error creating maintenance request. Please try again.'
        ]);
    }

    $this->isSubmitting = false;
};

computed([
    'formattedDate' => fn() => $this->scheduled_date ? date('Y-m-d', strtotime($this->scheduled_date)) : '',
    'selectedProperty' => fn() => $this->property_id ? $this->properties->firstWhere('id', $this->property_id) : null,
]);

?>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Create Maintenance Request</h2>

    <form wire:submit="submit" class="space-y-6">
        <div>
            <label for="property_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property</label>
            <select
                wire:model="property_id"
                id="property_id"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
            >
                <option value="">Select Property</option>
                @foreach($properties as $property)
                    <option value="{{ $property->id }}">{{ $property->title }}</option>
                @endforeach
            </select>
            @error('property_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Issue Title</label>
            <input
                wire:model="title"
                type="text"
                id="title"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                placeholder="Brief description of the issue"
            >
            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Detailed Description</label>
            <textarea
                wire:model="description"
                id="description"
                rows="4"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                placeholder="Provide detailed information about the maintenance issue"
            ></textarea>
            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                <select
                    wire:model="priority"
                    id="priority"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                >
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
                @error('priority') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="scheduled_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Scheduled Date</label>
                <input
                    wire:model="scheduled_date"
                    type="date"
                    id="scheduled_date"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                >
                @error('scheduled_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="estimated_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Cost (KES)</label>
                <input
                    wire:model="estimated_cost"
                    type="number"
                    step="0.01"
                    id="estimated_cost"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                    placeholder="0.00"
                >
                @error('estimated_cost') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
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
                Submit Maintenance Request
            </button>
        </div>
    </form>

    @if($selectedProperty)
        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Selected Property Details</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Location</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $selectedProperty->location }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Contract Status</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ ucfirst($selectedProperty->managementContract?->status ?? 'No Contract') }}
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
