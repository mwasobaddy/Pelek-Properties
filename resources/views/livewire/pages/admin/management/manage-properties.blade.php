<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state};
use App\Services\PropertyManagementService;
use App\Models\Property;

new class extends Component {
    /** @var \Illuminate\Database\Eloquent\Collection */
    public $properties;
    public ?Property $selectedProperty = null;
    
    public function mount(PropertyManagementService $propertyService): void
    {
        abort_if(!auth()->user()->can('manage_all_properties'), 403);
        $this->properties = $propertyService->getManagedProperties();
    }

    public function selectProperty(Property $property): void
    {
        $this->selectedProperty = $property;
    }
    
    public function viewProperty($propertyId): void
    {
        // Handle viewing the property
    }
};

?>

<div
    class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl shadow-xl overflow-hidden relative">
    <!-- Header Section with Gradient -->
    <div class="h-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b]"></div>
    <div class="p-8 border-b border-gray-200 dark:border-gray-700">
        <!-- Animated Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div class="space-y-2">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    Manage Properties
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    View and manage your property portfolio
                </p>
            </div>
            <button wire:click="$refresh"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                wire:loading.attr="disabled">
                <flux:icon wire:loading.remove wire:target="$refresh" name="arrow-path" class="w-5 h-5 mr-2" />
                <flux:icon wire:loading wire:target="$refresh" name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                Refresh
            </button>
        </div>
    </div>

    <!-- Content Section -->
    <div class="p-8">
        <div wire:loading.flex class="items-center justify-center p-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#02c9c2]"></div>
        </div>

        <div wire:loading.remove>
            <!-- Properties Grid -->
            <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($properties as $property)
                    <div
                        class="bg-white/50 dark:bg-gray-800/50 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 backdrop-blur-xl p-4 transition-all duration-200 hover:shadow-md">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $property->title }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $property->address }}
                                </p>
                            </div>
                            <span
                                class="px-2.5 py-1 text-xs font-medium rounded-full {{ 
                                    $property->status === 'rented' ? 'bg-green-100 text-green-800' : 
                                    ($property->status === 'available' ? 'bg-blue-100 text-blue-800' : 
                                    ($property->status === 'under_contract' ? 'bg-purple-100 text-purple-800' : 
                                    'bg-red-100 text-red-800')) 
                                }}">
                                {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                            </span>
                        </div>
                        <div class="mt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                @if ($property->listing_type === 'airbnb')
                                <div class="flex flex-col space-y-1">
                                        <span class="text-gray-600 dark:text-gray-400">Price:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->price, 2) }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">Daily Rent:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->airbnb_price_nightly, 2) }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">Weekly Rent:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->airbnb_price_weekly, 2) }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">Monthly Rent:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->airbnb_price_monthly, 2) }}</span>
                                </div>
                                @elseif ($property->listing_type === 'rent')
                                <div class="flex flex-col space-y-1">
                                    <span class="text-gray-600 dark:text-gray-400">Price:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->price, 2) }}</span>
                                    <span class="text-gray-600 dark:text-gray-400">Daily Rent:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->rental_price_daily, 2) }}</span>
                                    <span class="text-gray-600 dark:text-gray-400">Monthly Rent:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->rental_price_monthly, 2) }}</span>
                                </div>
                                @elseif ($property->listing_type === 'sale')
                                    <span class="text-gray-600 dark:text-gray-400">Selling Price:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->price, 2) }}</span>
                                @elseif ($property->listing_type === 'commercial')
                                <div class="flex flex-col space-y-1">
                                    <span class="text-gray-600 dark:text-gray-400">Selling Price:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->price, 2) }}</span>
                                    <span class="text-gray-600 dark:text-gray-400">Monthly Rent:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->commercial_price_monthly, 2) }}</span>
                                    <span class="text-gray-600 dark:text-gray-400">Annual Rent:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->commercial_price_annually, 2) }}</span>
                                </div>
                                @else
                                    <span class="text-gray-600 dark:text-gray-400">Price:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">KES {{ number_format($property->price, 2) }}</span>
                                @endif
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Last Payment:</span>
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ $property->last_payment_date?->format('d M Y') ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button wire:click="viewProperty({{ $property->id }})"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-[#02c9c2] hover:text-[#012e2b] transition-colors duration-150">
                                View Details
                                <flux:icon name="chevron-right" class="w-4 h-4 ml-1.5" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>