<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use function Livewire\Volt\{state};
use App\Models\ManagementContract;
use App\Models\Property;
use App\Services\PropertyManagementService;

new class extends Component {
    use WithPagination;

    #[State]
    public $showFilters = false;
    
    #[State]
    public $showDeleteModal = false;
    
    #[State]
    public $showFormModal = false;
    
    #[State]
    public $modalMode = 'create'; // create, edit, view
    
    #[State]
    public $search = '';
    
    #[State]
    public $filters = [
        'status' => '',
        'contract_type' => '',
        'date_range' => '',
    ];
    
    #[State]
    public $selectedContract = null;
    
    #[State]
    public $sortField = 'created_at';
    
    #[State]
    public $sortDirection = 'desc';
    
    #[State]
    public $isLoading = false;
    
    #[State]
    public $form = [
        'property_id' => '',
        'contract_type' => '',
        'management_fee_percentage' => '',
        'base_fee' => '',
        'start_date' => '',
        'end_date' => '',
        'payment_schedule' => '',
        'services_included' => [],
        'special_terms' => '',
        'status' => 'active'
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filters' => ['except' => ['status' => '', 'contract_type' => '', 'date_range' => '']]
    ];

    public function mount() {
        $this->authorize('view_management_contract');
    }

    #[Computed]
    public function contracts()
    {
        $this->isLoading = true;
        
        try {
            $query = ManagementContract::with(['property', 'admin'])
                ->when($this->search, function($query) {
                    $query->whereHas('property', function($q) {
                        $q->where('title', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->filters['status'], fn($query) => 
                    $query->where('status', $this->filters['status'])
                )
                ->when($this->filters['contract_type'], fn($query) => 
                    $query->where('contract_type', $this->filters['contract_type'])
                );
                
            // Handle the sorting
            if ($this->sortField === 'property_name') {
                $query->join('properties', 'management_contracts.property_id', '=', 'properties.id')
                    ->select('management_contracts.*')
                    ->orderBy('properties.title', $this->sortDirection);
            } else {
                $query->orderBy($this->sortField, $this->sortDirection);
            }
            
            return $query->paginate(10);
        } finally {
            $this->isLoading = false;
        }
    }

    #[Computed]
    public function properties()
    {
        // Ensure we're returning an associative array, not a collection
        $props = Property::pluck('title', 'id')->toArray();
        
        // Safety check to ensure we have proper structure
        if (empty($props)) {
            return ['0' => 'No properties available'];
        }
        
        return $props;
    }

    public function rules() {
        return [
            'form.property_id' => 'required',
            'form.contract_type' => 'required|in:full_service,maintenance_only,financial_only',
            'form.management_fee_percentage' => 'required|numeric|min:0|max:100',
            'form.base_fee' => 'nullable|numeric|min:0',
            'form.start_date' => 'required|date',
            'form.end_date' => 'required|date|after:form.start_date',
            'form.payment_schedule' => 'required|in:monthly,quarterly,yearly',
            'form.services_included' => 'required|array|min:1',
            'form.special_terms' => 'nullable|string',
            'form.status' => 'required|in:active,pending,expired,terminated'
        ];
    }

    public function create(): void {
        $this->authorize('create_management_contract');
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showFormModal = true;
    }

    public function edit($id): void {
        $this->authorize('edit_management_contract');
        $this->selectedContract = ManagementContract::findOrFail($id);
        $this->form = $this->selectedContract->toArray();
        
        // Ensure services_included is an array
        if (!isset($this->form['services_included']) || !is_array($this->form['services_included'])) {
            $this->form['services_included'] = [];
        }
        
        $this->modalMode = 'edit';
        $this->showFormModal = true;
    }

    public function view($id): void {
        $this->selectedContract = ManagementContract::findOrFail($id);
        $this->form = $this->selectedContract->toArray();
        
        // Ensure services_included is an array
        if (!isset($this->form['services_included']) || !is_array($this->form['services_included'])) {
            $this->form['services_included'] = [];
        }
        
        $this->modalMode = 'view';
        $this->showFormModal = true;
    }

    public function save(): void {
        if ($this->modalMode === 'view') return;
        
        $this->validate();

        if ($this->modalMode === 'create') {
            $this->authorize('create_management_contract');
            ManagementContract::create($this->form);
            $this->dispatch('notify', type: 'success', message: 'Contract created successfully.');
        } else {
            $this->authorize('edit_management_contract');
            $this->selectedContract->update($this->form);
            $this->dispatch('notify', type: 'success', message: 'Contract updated successfully.');
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id): void {
        $this->authorize('delete_management_contract');
        $this->selectedContract = ManagementContract::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete(): void {
        $this->authorize('delete_management_contract');
        $this->selectedContract->delete();
        $this->showDeleteModal = false;
        $this->selectedContract = null;
        $this->dispatch('notify', type: 'success', message: 'Contract deleted successfully.');
    }
    
    public function sort($field): void {
        if ($this->sortField === $field) {
            // Toggle direction if clicking on the same field
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new field and default to ascending
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleFilters(): void {
        $this->showFilters = !$this->showFilters;
    }

    public function resetForm(): void {
        $this->form = [
            'property_id' => '',
            'contract_type' => '',
            'management_fee_percentage' => '',
            'base_fee' => '',
            'start_date' => '',
            'end_date' => '',
            'payment_schedule' => '',
            'services_included' => [], // Initialize as empty array
            'special_terms' => '',
            'status' => 'active'
        ];
        $this->selectedContract = null;
    }

    public function resetFilters(): void {
        $this->reset('filters');
    }
    
    /**
     * Ensure form fields are properly typed after Livewire hydration
     */
    public function hydrateForm($property)
    {
        // Always ensure services_included is an array
        if (isset($this->form['services_included']) && !is_array($this->form['services_included'])) {
            if (is_string($this->form['services_included'])) {
                $this->form['services_included'] = json_decode($this->form['services_included'], true) ?? [];
            } else {
                $this->form['services_included'] = [];
            }
        }
    }
    
    /**
     * Lifecycle hook that runs after hydration on every request
     */
    public function hydrate()
    {
        $this->hydrateForm('services_included');
    }
}; ?>

<div class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl shadow-xl overflow-hidden relative">
    <!-- Header Section with Gradient -->
    <div class="h-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b]"></div>
    
    <div class="p-8 border-b border-gray-200 dark:border-gray-700">
        <!-- Animated Header -->
        <div class="sm:flex sm:items-center sm:justify-between" 
             x-data="{}"
             x-intersect="$el.classList.add('animate-fade-in')">
            <div class="space-y-2">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <flux:icon name="document-text" class="w-8 h-8 text-[#02c9c2]" />
                    Management Contracts
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Manage and track property management agreements
                </p>
            </div>
            
            @can('create_management_contract')
                <button 
                    wire:click="create"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                    wire:loading.attr="disabled"
                >
                    <flux:icon wire:loading.remove name="plus" class="w-5 h-5 mr-2" />
                    <flux:icon wire:loading name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                    New Contract
                </button>
            @endcan
        </div>

        <!-- Enhanced Search and Filters with Animation -->
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
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                            <flux:icon name="magnifying-glass"
                                class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="search"
                            placeholder="Search contracts..."
                            class="block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            aria-label="Search contracts"
                        >
                    </div>
                </div>

                <!-- Filter Toggle Button -->
                <button
                    wire:click="toggleFilters"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-sm backdrop-blur-xl"
                >
                    <flux:icon name="funnel" class="w-5 h-5 mr-2" />
                    Filters
                    <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full px-2 py-0.5">
                        {{ array_filter($filters) ? count(array_filter($filters)) : '0' }}
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.status"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="expired">Expired</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>

                    <!-- Contract Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contract Type</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="document-text" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.contract_type"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Types</option>
                                <option value="full_service">Full Service</option>
                                <option value="maintenance_only">Maintenance Only</option>
                                <option value="financial_only">Financial Only</option>
                            </select>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="calendar" class="h-5 w-5 text-gray-400" />
                                </div>
                                <select
                                    wire:model.live="filters.date_range"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                >
                                    <option value="">All Time</option>
                                    <option value="today">Today</option>
                                    <option value="this_week">This Week</option>
                                    <option value="this_month">This Month</option>
                                    <option value="this_year">This Year</option>
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

    <!-- Contract Table with Modern Styling -->
    <div class="p-8">
        <div class="relative overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 backdrop-blur-xl">
            <!-- Loading Overlay -->
            <div wire:loading.delay class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="flex items-center space-x-4">
                    <flux:icon name="arrow-path" class="w-8 h-8 text-[#02c9c2] animate-spin" />
                    <span class="text-gray-600 dark:text-gray-300 font-medium">Loading contracts...</span>
                </div>
            </div>

            <!-- Table -->
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-300 text-sm">
                    <tr>
                        <th wire:click="sort('property_name')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Property</span>
                                @if($sortField === 'property_name')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('contract_type')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Type</span>
                                @if($sortField === 'contract_type')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('start_date')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Start Date</span>
                                @if($sortField === 'start_date')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('end_date')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>End Date</span>
                                @if($sortField === 'end_date')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('status')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Status</span>
                                @if($sortField === 'status')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->contracts as $contract)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <flux:icon name="building-office" class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $contract->property->title }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            ID: {{ $contract->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ match($contract->contract_type) {
                                        'full_service' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                                        'maintenance_only' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300',
                                        'financial_only' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300'
                                    }
                                    }}">
                                    {{ str_replace('_', ' ', ucfirst($contract->contract_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ date('M d, Y', strtotime($contract->start_date)) }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ date('M d, Y', strtotime($contract->end_date)) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ match($contract->status) {
                                        'active' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
                                        'expired' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300',
                                        'terminated' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'
                                    };
                                    }}">
                                    {{ ucfirst($contract->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-3">
                                    <button 
                                        wire:click="view({{ $contract->id }})"
                                        class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-indigo-500 dark:bg-indigo-700/50 rounded-lg p-2"
                                        title="View Contract"
                                    >
                                        <flux:icon wire:loading.remove wire:target="view({{ $contract->id }})" name="eye" class="w-5 h-5" />
                                        <flux:icon wire:loading wire:target="view({{ $contract->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                    </button>
                                    
                                    @can('edit_management_contract')
                                        <button 
                                            wire:click="edit({{ $contract->id }})"
                                            class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-green-500 dark:bg-green-700/50 rounded-lg p-2"
                                            title="Edit Contract"
                                        >
                                            <flux:icon wire:loading.remove wire:target="edit({{ $contract->id }})" name="pencil-square" class="w-5 h-5" />
                                            <flux:icon wire:loading wire:target="edit({{ $contract->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                        </button>
                                    @endcan
                                    
                                    @can('delete_management_contract')
                                        <button 
                                            wire:click="confirmDelete({{ $contract->id }})"
                                            class="text-gray-200 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-500 transition-colors duration-150 bg-red-500 dark:bg-red-700/50 rounded-lg p-2"
                                            title="Delete Contract"
                                        >
                                            <flux:icon wire:loading.remove wire:target="confirmDelete({{ $contract->id }})" name="trash" class="w-5 h-5" />
                                            <flux:icon wire:loading wire:target="confirmDelete({{ $contract->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
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
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No contracts found</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $search ? 'Try adjusting your search or filter criteria.' : 'Get started by creating a new contract.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($this->contracts->hasPages())
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                    {{ $this->contracts->links('components.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Enhanced Contract Form Modal -->
    <flux:modal wire:model="showFormModal" class="w-full max-w-4xl !p-0" @close="$wire.resetForm()">
        <x-card class="w-full overflow-hidden rounded-xl">
            <x-card.header>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $modalMode === 'create' ? 'New Contract' : ($modalMode === 'edit' ? 'Edit Contract' : 'View Contract') }}
                </h3>
            </x-card.header>

            <x-card.body>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="building-office" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model="form.property_id"
                                @disabled($modalMode === 'view')
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">Select Property</option>
                                @foreach($this->properties as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('form.property_id') 
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contract Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contract Type</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="document-text" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model="form.contract_type"
                                @disabled($modalMode === 'view')
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">Select Type</option>
                                <option value="full_service">Full Service</option>
                                <option value="maintenance_only">Maintenance Only</option>
                                <option value="financial_only">Financial Only</option>
                            </select>
                        </div>
                        @error('form.contract_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Management Fee -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Management Fee (%)</label>
                        <input
                            type="number"
                            wire:model="form.management_fee_percentage"
                            @disabled($modalMode === 'view')
                            class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            min="0"
                            max="100"
                            step="0.01"
                        >
                        @error('form.management_fee_percentage')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Base Fee -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Base Fee</label>
                        <input
                            type="number"
                            wire:model="form.base_fee"
                            @disabled($modalMode === 'view')
                            class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            min="0"
                            step="0.01"
                        >
                        @error('form.base_fee')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                        <input
                            type="date"
                            wire:model="form.start_date"
                            @disabled($modalMode === 'view')
                            class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                        >
                        @error('form.start_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                        <input
                            type="date"
                            wire:model="form.end_date"
                            @disabled($modalMode === 'view')
                            class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                        >
                        @error('form.end_date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Schedule -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Schedule</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="calendar" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model="form.payment_schedule"
                                @disabled($modalMode === 'view')
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">Select Schedule</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        @error('form.payment_schedule')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model="form.status"
                                @disabled($modalMode === 'view')
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="expired">Expired</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                        @error('form.status')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Special Terms -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Special Terms</label>
                        <textarea
                            wire:model="form.special_terms"
                            @disabled($modalMode === 'view')
                            rows="4"
                            class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            placeholder="Enter any special terms or conditions..."
                        ></textarea>
                        @error('form.special_terms')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card.body>

            <x-card.footer>
                <div class="flex justify-end space-x-3">
                    @if($modalMode !== 'view')
                        <flux:button wire:click="$set('showFormModal', false)">
                            Cancel
                        </flux:button>
                        <flux:button wire:click="save" variant="primary" class="bg-[#02c9c2] hover:bg-[#02c9c2]/90">
                            <flux:icon wire:loading wire:target="save" name="arrow-path" class="w-4 h-4 mr-2 animate-spin" />
                            {{ $modalMode === 'create' ? 'Create Contract' : 'Update Contract' }}
                        </flux:button>
                    @else
                        <flux:button wire:click="$set('showFormModal', false)">
                            Close
                        </flux:button>
                    @endif
                </div>
            </x-card.footer>
        </x-card>
    </flux:modal>

    <!-- Enhanced Delete Confirmation Modal -->
    <flux:modal wire:model="showDeleteModal" max-width="md" class="!p-0">
        <x-card class="w-fulloverflow-hidden rounded-xl">
            <x-card.header>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                    <flux:icon name="exclamation-circle" class="w-6 h-6 text-red-600 mr-2" />
                    Confirm Deletion
                </h3>
            </x-card.header>

            <x-card.body>
                <p class="text-gray-600 dark:text-gray-300">
                    Are you sure you want to delete this contract? This action cannot be undone.
                </p>
                @if($selectedContract)
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Property</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $selectedContract->property->title }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contract Type</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ str_replace('_', ' ', ucfirst($selectedContract->contract_type)) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Duration</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">
                                    {{ date('M d, Y', strtotime($selectedContract->start_date)) }} - 
                                    {{ date('M d, Y', strtotime($selectedContract->end_date)) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif
            </x-card.body>

            <x-card.footer>
                <div class="flex justify-end space-x-3">
                    <flux:button wire:click="$set('showDeleteModal', false)">
                        Cancel
                    </flux:button>
                    <flux:button wire:click="delete" variant="danger" class="bg-red-600 hover:bg-red-700">
                        <flux:icon wire:loading wire:target="delete" name="arrow-path" class="w-4 h-4 mr-2 animate-spin" />
                        Delete Contract
                    </flux:button>
                </div>
            </x-card.footer>
        </x-card>
    </flux:modal>

    <!-- Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
</div>