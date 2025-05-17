<?php

use function Livewire\Volt\{state, mount, rules};
use App\Models\Property;

state([
    'property_id' => '',
    'property_type' => '',
    'location' => '',
    'land_size' => '',
    'bedrooms' => '',
    'bathrooms' => '',
    'description' => '',
    'purpose' => 'sale',
    'properties' => []
]);

mount(function () {
    $this->properties = Property::select('id', 'title', 'location')->get();
});

rules([
    'property_id' => 'required|exists:properties,id',
    'purpose' => 'required|in:sale,rental,insurance',
    'description' => 'nullable|string|max:1000'
]);

$createValuationRequest = function () {
    $validated = $this->validate();
    
    $valuationRequest = ValuationRequest::create([
        'property_id' => $this->property_id,
        'user_id' => auth()->id(),
        'purpose' => $this->purpose,
        'description' => $this->description
    ]);

    $this->dispatch('notify', [
        'type' => 'success',
        'message' => __('Valuation request created successfully')
    ]);

    $this->redirect(route('admin.management.valuations.show', $valuationRequest));
};

?>

<div class="p-6 space-y-6">
    <header>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            {{ __('New Valuation Request') }}
        </h1>
    </header>

    <form wire:submit="createValuationRequest" class="max-w-xl space-y-6">
        <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
            <!-- Property Selection -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ __('Select Property') }}
                </label>
                <select
                    wire:model="property_id"
                    class="w-full rounded-lg border-0 bg-white/5 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-[#02c9c2] dark:text-white dark:ring-white/10 dark:focus:ring-[#02c9c2] sm:text-sm"
                >
                    <option value="">{{ __('Select a property') }}</option>
                    @foreach($this->properties as $property)
                        <option value="{{ $property->id }}">
                            {{ $property->title }} ({{ $property->location }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Purpose -->
            <div class="mt-4 space-y-2">
                <label class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ __('Valuation Purpose') }}
                </label>
                <select
                    wire:model="purpose"
                    class="w-full rounded-lg border-0 bg-white/5 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-[#02c9c2] dark:text-white dark:ring-white/10 dark:focus:ring-[#02c9c2] sm:text-sm"
                >
                    <option value="sale">{{ __('Sale') }}</option>
                    <option value="rental">{{ __('Rental') }}</option>
                    <option value="insurance">{{ __('Insurance') }}</option>
                </select>
            </div>

            <!-- Description -->
            <div class="mt-4 space-y-2">
                <label class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ __('Additional Notes') }}
                </label>
                <textarea
                    wire:model="description"
                    rows="4"
                    class="w-full rounded-lg border-0 bg-white/5 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-[#02c9c2] dark:text-white dark:ring-white/10 dark:focus:ring-[#02c9c2] sm:text-sm"
                    placeholder="{{ __('Any additional information about the valuation request...') }}"
                ></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4 rtl:space-x-reverse">
            <flux:button 
                secondary
                :href="route('admin.management.valuations')"
                wire:navigate
            >
                {{ __('Cancel') }}
            </flux:button>

            <flux:button 
                primary
                type="submit"
            >
                {{ __('Create Request') }}
            </flux:button>
        </div>
    </form>
</div>
