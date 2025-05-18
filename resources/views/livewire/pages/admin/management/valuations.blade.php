<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state};
use Livewire\WithPagination;
use App\Models\ValuationRequest;
use App\Models\ValuationReport;
use App\Services\PropertyService;

new class extends Component {
    use WithPagination;

    #[State]
    public $search = '';

    #[State]
    public $sortField = 'created_at';

    #[State]
    public $sortDirection = 'desc';

    #[State]
    public $showFilters = false;
    
    #[State]
    public $showDeleteModal = false;
    
    #[State]
    public $showFormModal = false;
    
    #[State]
    public $modalMode = 'create'; // create, edit, view

    #[State]
    public $selectedRequest = null;

    #[State]
    public $filters = [
        'status' => '',
        'purpose' => '',
        'date_range' => '',
    ];

    #[State]
    public $form = [
        'property_type' => '',
        'location' => '',
        'purpose' => '',
        'description' => '',
        'status' => 'pending'
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filters' => ['except' => ['status' => '', 'purpose' => '', 'date_range' => '']]
    ];

    public function mount(): void
    {
        $this->authorize('view_valuation_requests');
    }

    #[Computed]
    public function valuationRequests()
    {
        $this->isLoading = true;
        
        try {
            return ValuationRequest::query()
                ->with(['report'])
                ->when($this->filters['status'], fn($query) => 
                    $query->where('status', $this->filters['status'])
                )
                ->when($this->filters['purpose'], fn($query) => 
                    $query->where('purpose', $this->filters['purpose'])
                )
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('property_type', 'like', "%{$this->search}%")
                          ->orWhere('location', 'like', "%{$this->search}%");
                    });
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10);
        } finally {
            $this->isLoading = false;
        }
    }

    public function sort($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showFormModal = true;
    }

    public function edit($id)
    {
        $valuation = ValuationRequest::findOrFail($id);
        $this->form = $valuation->only(['property_type', 'location', 'purpose', 'description', 'status']);
        $this->modalMode = 'edit';
        $this->selectedRequest = $valuation;
        $this->showFormModal = true;
    }

    public function view($id)
    {
        $this->selectedRequest = ValuationRequest::with('report')->findOrFail($id);
        $this->form = $this->selectedRequest->only(['property_type', 'location', 'purpose', 'description', 'status']);
        $this->modalMode = 'view';
        $this->showFormModal = true;
    }

    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filters']);
    }

    public function confirmDelete($id)
    {
        $this->selectedRequest = ValuationRequest::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->selectedRequest->delete();
        $this->showDeleteModal = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Valuation request deleted successfully.']);
    }

    public function save()
    {
        $this->validate([
            'form.property_type' => 'required|string|max:255',
            'form.location' => 'required|string|max:255',
            'form.purpose' => 'required|in:sale,rent,mortgage',
            'form.description' => 'required|string',
            'form.status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);

        if ($this->modalMode === 'create') {
            ValuationRequest::create($this->form);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Valuation request created successfully.']);
        } else {
            $this->selectedRequest->update($this->form);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Valuation request updated successfully.']);
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->form = [
            'property_type' => '',
            'location' => '',
            'purpose' => '',
            'description' => '',
            'status' => 'pending'
        ];
        $this->selectedRequest = null;
    }
}; ?>

<div class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl shadow-xl overflow-hidden relative">
    <!-- Header Section with Gradient -->
    <div class="h-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b]"></div>
    
    <div class="p-8 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-2">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <flux:icon name="calculator" class="w-6 h-6 text-[#02c9c2]" />
                    Property Valuations
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Manage and track property valuation requests
                </p>
            </div>
            
            @can('create_valuation_request')
                <button 
                    wire:click="create"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                    wire:loading.attr="disabled"
                >
                    <flux:icon wire:loading.remove name="plus" class="w-5 h-5 mr-2" />
                    <flux:icon wire:loading name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                    New Valuation
                </button>
            @endcan
        </div>

        <!-- Search and Filters -->
        <div class="mt-8 space-y-4" 
             x-data="{}"
             x-intersect="$el.classList.add('animate-fade-in')">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <flux:icon wire:loading.remove wire:target="search" name="magnifying-glass" class="h-5 w-5 text-gray-400" />
                        <flux:icon wire:loading wire:target="search" name="arrow-path" class="h-5 w-5 text-[#02c9c2] animate-spin" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="search"
                        placeholder="Search valuations..."
                        class="block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                        aria-label="Search valuations"
                    >
                </div>

                <!-- Filter Toggle Button -->
                <button
                    wire:click="toggleFilters"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-sm backdrop-blur-xl"
                >
                    <flux:icon name="funnel" class="w-5 h-5 mr-2" />
                    Filters
                    <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full px-2 py-0.5">
                        {{ count(array_filter($filters)) }}
                    </span>
                </button>
            </div>

            <!-- Filters Panel -->
            <div x-show="$wire.showFilters"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 backdrop-blur-xl shadow-sm space-y-4"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.status"
                                id="status"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Purpose Filter -->
                    <div>
                        <label for="purpose" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Purpose</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="home" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.purpose"
                                id="purpose"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Purpose</option>
                                <option value="sale">Sale</option>
                                <option value="rental">Rental</option>
                                <option value="insurance">Insurance</option>
                            </select>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div>
                        <label for="date_range" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="calendar" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.date_range"
                                id="date_range"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Actions -->
                <div class="flex flex-col md:flex-row items-center justify-center gap-4 col-span-2 mt-2">

                    <!-- Reset Filters Button -->
                    <button wire:click="resetFilters"
                        class="group relative overflow-hidden rounded-lg bg-gradient-to-r from-[#02c9c2] to-[#02a8a2] px-5 py-2.5 text-sm font-medium text-white shadow-md hover:shadow-lg transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]">
                        <!-- Background animation on hover -->
                        <span
                            class="absolute inset-0 translate-y-full bg-gradient-to-r from-[#012e2b] to-[#014e4a] group-hover:translate-y-0 transition-transform duration-300 ease-out"></span>
                        <!-- Content remains visible -->
                        <span class="relative flex items-center gap-2">
                            <flux:icon name="arrow-path"
                                class="h-4 w-4 transition-transform group-hover:rotate-180 duration-500" />
                            <span>Clear All Filters</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Valuation Requests Table -->
    <div class="p-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <th wire:click="sort('property_type')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center gap-2">
                                Property Type
                                @if ($sortField === 'property_type')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('location')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center gap-2">
                                Location
                                @if ($sortField === 'location')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('purpose')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center gap-2">
                                Purpose
                                @if ($sortField === 'purpose')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3">Details</th>
                        <th wire:click="sort('status')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center gap-2">
                                Status
                                @if ($sortField === 'status')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('created_at')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center gap-2">
                                Date
                                @if ($sortField === 'created_at')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->valuationRequests() as $request)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-4 text-sm">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <flux:icon name="building-office" class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ ucfirst($request->property_type) }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            ID: {{ $request->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm">
                                {{ $request->location }}
                            </td>
                            <td class="px-4 py-4 text-sm">
                                <span @class([
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $request->purpose === 'sale',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $request->purpose === 'rental',
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' => $request->purpose === 'insurance'
                                ])>
                                    {{ ucfirst($request->purpose) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="space-y-1">
                                    @if($request->land_size)
                                        <div class="text-gray-500 dark:text-gray-400">
                                            {{ number_format($request->land_size, 2) }} sqft
                                        </div>
                                    @endif
                                    @if($request->bedrooms || $request->bathrooms)
                                        <div class="text-gray-500 dark:text-gray-400">
                                            {{ $request->bedrooms }} bed{{ $request->bedrooms !== 1 ? 's' : '' }} · 
                                            {{ $request->bathrooms }} bath{{ $request->bathrooms !== 1 ? 's' : '' }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span @class([
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $request->status === 'pending',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $request->status === 'in_progress',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $request->status === 'completed'
                                ])>
                                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm">
                                {{ $request->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end space-x-3">
                                    <button 
                                        wire:click="view({{ $request->id }})"
                                        class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-indigo-500 dark:bg-indigo-700/50 rounded-lg p-2"
                                        title="View request"
                                    >
                                        <flux:icon wire:loading.remove wire:target="view({{ $request->id }})" name="eye" class="w-5 h-5" />
                                        <flux:icon wire:loading wire:target="view({{ $request->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                    </button>
                                    
                                    @can('edit_valuation_request')
                                        <button 
                                            wire:click="edit({{ $request->id }})"
                                            class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-green-500 dark:bg-green-700/50 rounded-lg p-2"
                                            title="Edit request"
                                        >
                                            <flux:icon wire:loading.remove wire:target="edit({{ $request->id }})" name="pencil-square" class="w-5 h-5" />
                                            <flux:icon wire:loading wire:target="edit({{ $request->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                        </button>
                                    @endcan
                                    
                                    @can('delete_valuation_request')
                                        <button 
                                            wire:click="confirmDelete({{ $request->id }})"
                                            class="text-gray-200 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-500 transition-colors duration-150 bg-red-500 dark:bg-red-700/50 rounded-lg p-2"
                                            title="Delete request"
                                        >
                                            <flux:icon wire:loading.remove wire:target="confirmDelete({{ $request->id }})" name="trash" class="w-5 h-5" />
                                            <flux:icon wire:loading wire:target="confirmDelete({{ $request->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12">
                                <div class="text-center">
                                    <flux:icon name="folder-open" class="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No valuations found</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $search ? 'Try adjusting your search or filter criteria.' : 'Get started by creating a new valuation.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

            <!-- Pagination -->
            @if($this->valuationRequests()->hasPages())
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                    {{ $this->valuationRequests()->links('components.pagination') }}
                </div>
            @endif
    </div>

    <!-- Form Modal -->
    <flux:modal wire:model="showFormModal" class="w-full max-w-4xl" @close="$wire.resetForm()">
        <x-card>
            <x-card.header>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $modalMode === 'create' ? 'Create Maintenance Record' : ($modalMode === 'edit' ? 'Edit Maintenance Record' : 'View Maintenance Record') }}
                </h3>
            </x-card.header>

            <x-card.body>
                <div class="space-y-4">
                    <div>
                        <label for="property_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property Type</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="home" class="h-5 w-5 text-gray-400" />
                            </div>
                            <input id="property_type" wire:model="form.property_type" type="text" class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm" />
                        </div>
                        @error('form.property_type')
                        <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="map-pin" class="h-5 w-5 text-gray-400" />
                            </div>
                            <input id="location" wire:model="form.location" type="text" class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm" />
                        </div>
                        @error('form.location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="purpose" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Purpose</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="briefcase" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select id="purpose" wire:model="form.purpose" class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                <option value="">Select Purpose</option>
                                <option value="sale">Sale</option>
                                <option value="rent">Rent</option>
                                <option value="mortgage">Mortgage</option>
                            </select>
                        </div>
                        @error('form.purpose') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="document-text" class="h-5 w-5 text-gray-400" />
                            </div>
                            <textarea id="description" wire:model="form.description" class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"></textarea>
                        </div>
                        @error('form.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    @if($modalMode !== 'view')
                    <div class="flex justify-end space-x-3">
                        <flux:button wire:click="$set('showFormModal', false)">
                            Cancel
                        </flux:button>
                        <flux:button wire:click="save">
                            {{ $modalMode === 'create' ? 'Create' : 'Update' }}
                        </flux:button>
                    </div>
                    @else
                    <div class="flex justify-end">
                        <flux:button wire:click="$set('showFormModal', false)">
                            Close
                        </flux:button>
                    </div>
                    @endif
                </div>
            </x-card.body>
        </x-card>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal wire:model="showDeleteModal" class="w-full max-w-4xl">
        <x-card class="overflow-hidden rounded-xl">
            <x-card.header>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    <flux:icon name="exclamation-circle" class="w-6 h-6 text-red-600 mr-2" />
                    Confirm Deletion
                </h3>
            </x-card.header>

            <x-card.body>
                <div class="mt-4">
                    <p>Are you sure you want to delete this valuation request? This action cannot be undone.</p>
                </div>
                @if($selectedRequest)
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Property Type</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ ucfirst($selectedRequest->property_type) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ str_replace('_', ' ', ucfirst($selectedRequest->location)) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Details</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">
                                    @if($selectedRequest->land_size)
                                        <span>{{ number_format($selectedRequest->land_size, 2) }} sqft</span>
                                    @endif
                                    @if($selectedRequest->bedrooms || $selectedRequest->bathrooms)
                                        <span class="ml-2">{{ $selectedRequest->bedrooms }} bed{{ $selectedRequest->bedrooms !== 1 ? 's' : '' }} · 
                                            {{ $selectedRequest->bathrooms }} bath{{ $selectedRequest->bathrooms !== 1 ? 's' : '' }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif
            </x-card.body>

            <div class="mt-4 flex justify-end space-x-3">
                <flux:button wire:click="$set('showDeleteModal', false)">
                    Cancel
                </flux:button>
                <flux:button wire:click="delete">
                    Delete
                </flux:button>
            </div>
        </x-card>
    </flux:modal>

    <!-- Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
</div>