<?php

use function Livewire\Volt\{state, computed};
use App\Models\PropertyOffer;
use App\Models\Property;
use App\Services\SalePropertyService;

state([
    'offers' => fn() => [],
    'selectedOffer' => null,
    'showDetails' => false,
    'statusFilter' => 'all',
    'loading' => false,
    'search' => '',
    'sortField' => 'created_at',
    'sortDirection' => 'desc',
    'filters' => [
        'dateRange' => null,
        'minAmount' => null,
        'maxAmount' => null,
        'paymentMethod' => null
    ]
]);

$mount = function (SalePropertyService $saleService) {
    abort_if(!auth()->user()->can('manage_properties'), 403);
    $this->loadOffers();
};

$loadOffers = function () {
    $this->loading = true;
    
    $query = PropertyOffer::with(['property', 'user'])
        ->when($this->statusFilter !== 'all', function ($query) {
            return $query->where('status', $this->statusFilter);
        })
        ->when($this->search, function ($query) {
            return $query->whereHas('property', function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            })->orWhere('client_name', 'like', '%' . $this->search . '%');
        })
        ->when($this->filters['dateRange'], function ($query) {
            // Parse date range and apply filter
            $dates = explode(' - ', $this->filters['dateRange']);
            return $query->whereBetween('created_at', [$dates[0], $dates[1]]);
        })
        ->when($this->filters['minAmount'], function ($query) {
            return $query->where('offer_amount', '>=', $this->filters['minAmount']);
        })
        ->when($this->filters['maxAmount'], function ($query) {
            return $query->where('offer_amount', '<=', $this->filters['maxAmount']);
        })
        ->when($this->filters['paymentMethod'], function ($query) {
            return $query->where('payment_method', $this->filters['paymentMethod']);
        })
        ->orderBy($this->sortField, $this->sortDirection);
    
    $this->offers = $query->get();
    $this->loading = false;
};

$updateOfferStatus = function (int $offerId, string $status) {
    $offer = PropertyOffer::findOrFail($offerId);
    
    abort_if(!auth()->user()->can('manage_properties'), 403);
    
    try {
        $offer->update(['status' => $status]);
        
        if ($status === 'accepted') {
            // Mark other offers for this property as rejected
            PropertyOffer::where('property_id', $offer->property_id)
                ->where('id', '!=', $offer->id)
                ->update(['status' => 'rejected']);
                
            // Update property status
            $offer->property->update(['status' => 'sold']);
        }
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Offer status updated successfully'
        ]);
        
        $this->loadOffers();
    } catch (\Exception $e) {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'Error updating offer status'
        ]);
    }
};

$showOfferDetails = function (PropertyOffer $offer) {
    $this->selectedOffer = $offer;
    $this->showDetails = true;
};

$closeDetails = function () {
    $this->showDetails = false;
    $this->selectedOffer = null;
};

$sort = function (string $field) {
    if ($this->sortField === $field) {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }
    
    $this->loadOffers();
};

$resetFilters = function () {
    $this->reset('filters', 'search', 'statusFilter');
    $this->loadOffers();
};

$totalOffers = computed(fn() => count($this->offers));
$pendingOffers = computed(fn() => collect($this->offers)->where('status', 'pending')->count());
$acceptedOffers = computed(fn() => collect($this->offers)->where('status', 'accepted')->count());
$totalValue = computed(fn() => collect($this->offers)->where('status', 'pending')->sum('offer_amount'));

?>

<div class="relative">
    <div class="p-6 bg-white dark:bg-gray-800 shadow-xl rounded-lg">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Property Offers</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Manage and track all property offers
                </p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="flex space-x-4">
                <div class="px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Offers</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $this->totalOffers }}</p>
                </div>
                <div class="px-4 py-2 bg-yellow-50 dark:bg-yellow-900 rounded-lg">
                    <p class="text-sm text-yellow-600 dark:text-yellow-400">Pending</p>
                    <p class="text-lg font-semibold text-yellow-700 dark:text-yellow-300">{{ $this->pendingOffers }}</p>
                </div>
                <div class="px-4 py-2 bg-green-50 dark:bg-green-900 rounded-lg">
                    <p class="text-sm text-green-600 dark:text-green-400">Accepted</p>
                    <p class="text-lg font-semibold text-green-700 dark:text-green-300">{{ $this->acceptedOffers }}</p>
                </div>
                <div class="px-4 py-2 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <p class="text-sm text-blue-600 dark:text-blue-400">Total Value</p>
                    <p class="text-lg font-semibold text-blue-700 dark:text-blue-300">KES {{ number_format($this->totalValue, 2) }}</p>
                </div>
            </div>
        </div>
        
        <!-- Search and Filters -->
        <div class="mb-6 space-y-4">
            <div class="flex gap-4">
                <div class="flex-1">
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search by property or client name..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                </div>
                <select
                    wire:model.live="statusFilter"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="rejected">Rejected</option>
                    <option value="withdrawn">Withdrawn</option>
                </select>
                <button 
                    wire:click="resetFilters"
                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                >
                    Reset Filters
                </button>
            </div>

            <!-- Advanced Filters -->
            <div class="grid grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
                    <input
                        wire:model.live="filters.dateRange"
                        type="text"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Select date range"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Amount</label>
                    <input
                        wire:model.live="filters.minAmount"
                        type="number"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Min amount"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Amount</label>
                    <input
                        wire:model.live="filters.maxAmount"
                        type="number"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Max amount"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                    <select
                        wire:model.live="filters.paymentMethod"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="mortgage">Mortgage</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Offers Table -->
        <div class="overflow-x-auto relative">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sort('created_at')">
                            Date
                            @if($sortField === 'created_at')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Property
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sort('client_name')">
                            Client
                            @if($sortField === 'client_name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sort('offer_amount')">
                            Amount
                            @if($sortField === 'offer_amount')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($offers as $offer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $offer->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $offer->property->title }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $offer->property->location }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $offer->client_name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $offer->client_phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                KES {{ number_format($offer->offer_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span @class([
                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $offer->status === 'pending',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $offer->status === 'accepted',
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $offer->status === 'rejected',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300' => $offer->status === 'withdrawn',
                                ])>
                                    {{ ucfirst($offer->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    wire:click="showOfferDetails({{ $offer->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                >
                                    View Details
                                </button>
                                @if($offer->status === 'pending')
                                    <button
                                        wire:click="updateOfferStatus({{ $offer->id }}, 'accepted')"
                                        class="ml-3 text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                    >
                                        Accept
                                    </button>
                                    <button
                                        wire:click="updateOfferStatus({{ $offer->id }}, 'rejected')"
                                        class="ml-3 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        Reject
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No offers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Offer Details Modal -->
    @if($showDetails && $selectedOffer)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="mt-3 sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Offer Details
                            </h3>

                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property</label>
                                    <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $selectedOffer->property->title }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client Information</label>
                                    <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $selectedOffer->client_name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Phone: {{ $selectedOffer->client_phone }}<br>
                                        Email: {{ $selectedOffer->client_email ?? 'Not provided' }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Offer Details</label>
                                    <p class="mt-1 text-base text-gray-900 dark:text-white">Amount: KES {{ number_format($selectedOffer->offer_amount, 2) }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Payment Method: {{ ucfirst($selectedOffer->payment_method) }}<br>
                                        Valid Until: {{ $selectedOffer->valid_until ? $selectedOffer->valid_until->format('M d, Y') : 'No expiry date' }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Terms & Conditions</label>
                                    <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $selectedOffer->terms_conditions ?? 'No specific terms' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Additional Notes</label>
                                    <p class="mt-1 text-base text-gray-900 dark:text-white">{{ $selectedOffer->notes ?? 'No additional notes' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6">
                        <button
                            wire:click="closeDetails"
                            class="w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:text-sm"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading class="absolute inset-0 bg-gray-200 dark:bg-gray-700 bg-opacity-50 flex items-center justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
    </div>
</div>
