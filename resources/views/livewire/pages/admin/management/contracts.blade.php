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
        $this->authorize('terminate_management_contract');
        $this->selectedContract = ManagementContract::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete(): void {
        $this->authorize('terminate_management_contract');
        $this->selectedContract->delete();
        $this->showDeleteModal = false;
        $this->selectedContract = null;
        $this->dispatch('notify', type: 'success', message: 'Contract deleted successfully.');
    }
    
    public function sort($field): void {
        $this->isLoading = true;
        
        if ($this->sortField === $field) {
            // Toggle direction if clicking on the same field
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new field and default to ascending
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        // Reset pagination when sorting changes
        $this->resetPage();
        
        $this->isLoading = false;
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

    // public function toggleFilters(): void {
    //     $this->showFilters = !$this->showFilters;
    // }

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

<div class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl shadow-xl overflow-hidden">
    <!-- Header Section with Gradient -->
    <div class="h-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b]"></div>
    
    <div class="p-8 border-b border-gray-200 dark:border-gray-700">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div class="space-y-2">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Management Contracts</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View and manage property management contracts</p>
            </div>
            
            @can('create_management_contract')
                <button 
                    wire:click="create" 
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-teal-600 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wider hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition ease-in-out duration-150 shadow-sm"
                >
                    <svg wire:loading wire:target="create" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg class="w-5 h-5 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" wire:loading.remove wire:target="create">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Create Contract</span>
                </button>
            @endcan
        </div>

        <!-- Enhanced Search and Filters -->
        <div class="mt-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Search with loading indicator -->
                <div class="relative w-full md:w-96">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search contracts..." 
                        class="w-full px-4 py-2 pl-10 pr-10 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-teal-500"
                    >
                    <!-- Search icon -->
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <!-- Spinner when searching -->
                    <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="animate-spin h-5 w-5 text-teal-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Filter Button with loading indicator -->
                <button 
                    wire:click="toggleFilters" 
                    wire:loading.attr="disabled" 
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg font-medium text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition ease-in-out duration-150"
                >
                    <svg wire:loading wire:target="toggleFilters" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-gray-500 dark:text-gray-400" wire:loading.remove wire:target="toggleFilters" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>{{ $showFilters ? 'Hide Filters' : 'Show Filters' }}</span>
                </button>
            </div>

            <!-- Animated Filters Panel -->
            <div 
                x-show="$wire.showFilters"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="mt-4 p-6 rounded-xl bg-white/80 dark:bg-gray-800/50 backdrop-blur-xl shadow-lg ring-1 ring-black/5 dark:ring-white/10"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select
                            wire:model="filters.status"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="expired">Expired</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>

                    <!-- Contract Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contract Type</label>
                        <select
                            wire:model="filters.contract_type"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500"
                        >
                            <option value="">All Types</option>
                            <option value="full_service">Full Service</option>
                            <option value="maintenance_only">Maintenance Only</option>
                            <option value="financial_only">Financial Only</option>
                        </select>
                    </div>

                    <!-- Reset Filters -->
                    <div class="flex items-end">
                        <button
                            wire:click="resetFilters"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-transparent rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition ease-in-out duration-150"
                        >
                            <svg wire:loading wire:target="resetFilters" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Reset Filters</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contract Table with Modern Styling -->
    <div class="p-8">
        <div class="overflow-x-auto">
            <!-- Empty state handling -->
            @if($this->contracts->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <svg class="h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-200">No contracts found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $search || $filters['status'] || $filters['contract_type'] ? 'Try adjusting your search or filters.' : 'Get started by creating your first contract.' }}
                    </p>
                    @can('create_management_contract')
                        @if(!$search && !$filters['status'] && !$filters['contract_type'])
                            <button 
                                wire:click="create"
                                wire:loading.attr="disabled"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-teal-600 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wider hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition ease-in-out duration-150 shadow-sm"
                            >
                                <svg wire:loading wire:target="create" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg class="w-5 h-5 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" wire:loading.remove wire:target="create">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>Create Contract</span>
                            </button>
                        @endif
                    @endcan
                </div>
            @else
                <!-- Global loading indicator for the entire table -->
                <div wire:loading wire:target="sort, contracts" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center rounded-lg z-10 backdrop-blur-sm">
                    <div class="flex flex-col items-center">
                        <svg class="animate-spin h-10 w-10 text-teal-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">Loading contracts...</span>
                    </div>
                </div>
                
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 relative">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer select-none" wire:click="sort('property.title')">
                                <div class="flex items-center space-x-1">
                                    <span>Property</span>
                                    <span class="flex flex-col">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ $sortField === 'property.title' && $sortDirection === 'asc' ? 'text-teal-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 -mt-1 {{ $sortField === 'property.title' && $sortDirection === 'desc' ? 'text-teal-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer select-none" wire:click="sort('contract_type')">
                                <div class="flex items-center space-x-1">
                                    <span>Contract Type</span>
                                    <span class="flex flex-col">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ $sortField === 'contract_type' && $sortDirection === 'asc' ? 'text-teal-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 -mt-1 {{ $sortField === 'contract_type' && $sortDirection === 'desc' ? 'text-teal-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer select-none" wire:click="sort('start_date')">
                                <div class="flex items-center space-x-1">
                                    <span>Start Date</span>
                                    <span class="flex flex-col">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ $sortField === 'start_date' && $sortDirection === 'asc' ? 'text-teal-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 -mt-1 {{ $sortField === 'start_date' && $sortDirection === 'desc' ? 'text-teal-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer select-none" wire:click="sort('end_date')">
                                <div class="flex items-center space-x-1">
                                    <span>End Date</span>
                                    <span class="flex flex-col">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ $sortField === 'end_date' && $sortDirection === 'asc' ? 'text-teal-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 -mt-1 {{ $sortField === 'end_date' && $sortDirection === 'desc' ? 'text-teal-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer select-none" wire:click="sort('status')">
                                <div class="flex items-center space-x-1">
                                    <span>Status</span>
                                    <span class="flex flex-col">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ $sortField === 'status' && $sortDirection === 'asc' ? 'text-teal-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" d="M5 15l7-7 7 7" />
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 -mt-1 {{ $sortField === 'status' && $sortDirection === 'desc' ? 'text-teal-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->contracts as $contract)
                            <tr wire:key="{{ $contract->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                                    {{ $contract->property->title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ ucfirst(str_replace('_', ' ', $contract->contract_type)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $contract->start_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $contract->end_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $contract->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : '' }}
                                        {{ $contract->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : '' }}
                                        {{ $contract->status === 'expired' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                        {{ $contract->status === 'terminated' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : '' }}
                                    ">
                                        {{ ucfirst($contract->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button 
                                        wire:click="view({{ $contract->id }})"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-700 text-xs font-medium rounded text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition ease-in-out duration-150"
                                    >
                                        <svg wire:loading wire:target="view({{ $contract->id }})" class="animate-spin -ml-1 mr-1 h-3 w-3 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg wire:loading.remove wire:target="view({{ $contract->id }})" class="h-4 w-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>

                                    @can('edit_management_contract')
                                        <button 
                                            wire:click="edit({{ $contract->id }})"
                                            wire:loading.attr="disabled" 
                                            class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-700 text-xs font-medium rounded text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition ease-in-out duration-150"
                                        >
                                            <svg wire:loading wire:target="edit({{ $contract->id }})" class="animate-spin -ml-1 mr-1 h-3 w-3 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg wire:loading.remove wire:target="edit({{ $contract->id }})" class="h-4 w-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>
                                    @endcan

                                    @can('terminate_management_contract')
                                        <button 
                                            wire:click="confirmDelete({{ $contract->id }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition ease-in-out duration-150"
                                        >
                                            <svg wire:loading wire:target="confirmDelete({{ $contract->id }})" class="animate-spin -ml-1 mr-1 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg wire:loading.remove wire:target="confirmDelete({{ $contract->id }})" class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $this->contracts->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Enhanced Contract Form Modal -->
    <flux:modal wire:model="showFormModal" class="w-full max-w-4xl" @close="$wire.resetForm()">
        <x-card class="w-full overflow-hidden rounded-xl">
            <!-- Modal Header with Gradient -->
            <div class="h-1.5 w-full bg-gradient-to-r from-[#02c9c2] to-[#012e2b]"></div>
            
            <x-card.header class="bg-gray-50 dark:bg-gray-800 py-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                    {{ $modalMode === 'create' ? 'Create Contract' : ($modalMode === 'edit' ? 'Edit Contract' : 'View Contract') }}
                </h3>
            </x-card.header>

            <x-card.body class="p-6">
                <form wire:submit="save" class="space-y-6">
                    <!-- Loading Overlay -->
                    <div wire:loading wire:target="save" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center rounded-lg z-10 backdrop-blur-sm">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-10 w-10 text-teal-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $modalMode === 'create' ? 'Creating' : 'Updating' }} contract...</span>
                        </div>
                    </div>
                
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property</label>
                            <select
                                wire:model="form.property_id"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500"
                                {{ $modalMode === 'view' ? 'disabled' : '' }}
                            >
                                <option value="">Select a property</option>
                                @foreach($this->properties as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('form.property_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contract Type</label>
                            <select
                                wire:model="form.contract_type"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500"
                                {{ $modalMode === 'view' ? 'disabled' : '' }}
                            >
                                <option value="">Select type</option>
                                <option value="full_service">Full Service</option>
                                <option value="maintenance_only">Maintenance Only</option>
                                <option value="financial_only">Financial Only</option>
                            </select>
                            @error('form.contract_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Management Fee (%)</label>
                            <input 
                                type="number" 
                                wire:model="form.management_fee_percentage"
                                min="0"
                                max="100"
                                step="0.01"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500"
                                {{ $modalMode === 'view' ? 'disabled' : '' }}
                            >
                            @error('form.management_fee_percentage') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Base Fee</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input
                                    type="number" 
                                    wire:model="form.base_fee"
                                    min="0"
                                    step="0.01"
                                    class="pl-7 block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500"
                                    {{ $modalMode === 'view' ? 'disabled' : '' }}
                                >
                            </div>
                            @error('form.base_fee') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                            <input 
                                type="date" 
                                wire:model="form.start_date"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500"
                                {{ $modalMode === 'view' ? 'disabled' : '' }}
                            >
                            @error('form.start_date') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                            <input 
                                type="date" 
                                wire:model="form.end_date"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500"
                                {{ $modalMode === 'view' ? 'disabled' : '' }}
                            >
                            @error('form.end_date') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Schedule</label>
                            <select
                                wire:model="form.payment_schedule"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500"
                                {{ $modalMode === 'view' ? 'disabled' : '' }}
                            >
                                <option value="">Select schedule</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                            @error('form.payment_schedule') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select
                                wire:model="form.status"
                                class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500"
                                {{ $modalMode === 'view' ? 'disabled' : '' }}
                            >
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="expired">Expired</option>
                                <option value="terminated">Terminated</option>
                            </select>
                            @error('form.status') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Services Included</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @php
                                $serviceOptions = [
                                    'tenant_management' => 'Tenant Management',
                                    'maintenance' => 'Maintenance',
                                    'financial_reporting' => 'Financial Reporting', 
                                    'marketing' => 'Marketing',
                                    'legal_compliance' => 'Legal Compliance'
                                ];
                            @endphp
                            @foreach($serviceOptions as $value => $label)
                                <label class="inline-flex items-center">
                                    <input 
                                        type="checkbox" 
                                        wire:model="form.services_included" 
                                        value="{{ $value }}" 
                                        class="rounded border-gray-300 text-teal-600 shadow-sm focus:border-teal-300 focus:ring focus:ring-teal-200 focus:ring-opacity-50 dark:border-gray-700 dark:bg-gray-900"
                                        {{ $modalMode === 'view' ? 'disabled' : '' }}
                                    >
                                    <span class="ml-2 text-gray-700 dark:text-gray-300 text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('form.services_included') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Special Terms</label>
                        <textarea 
                            wire:model="form.special_terms"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500"
                            {{ $modalMode === 'view' ? 'disabled' : '' }}
                        ></textarea>
                        @error('form.special_terms') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    @if($modalMode !== 'view')
                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                wire:click="$set('showFormModal', false)"
                                class="inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-700 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
                            >
                                Cancel
                            </button>
                            
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ $modalMode === 'create' ? 'Create Contract' : 'Update Contract' }}</span>
                            </button>
                        </div>
                    @else
                        <div class="flex justify-end">
                            <button
                                type="button"
                                wire:click="$set('showFormModal', false)"
                                class="inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-700 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
                            >
                                Close
                            </button>
                        </div>
                    @endif
                </form>
            </x-card.body>
        </x-card>
    </flux:modal>

    <!-- Enhanced Delete Confirmation Modal -->
    <flux:modal wire:model="showDeleteModal" max-width="md">
        <x-card class="overflow-hidden rounded-xl">
            <div class="h-1.5 w-full bg-gradient-to-r from-red-500 to-red-700"></div>
            
            <x-card.header class="bg-red-50 dark:bg-red-900/20">
                <h3 class="text-lg leading-6 font-medium text-red-800 dark:text-red-200">
                    Delete Contract
                </h3>
            </x-card.header>

            <x-card.body class="p-6">
                <!-- Loading overlay -->
                <div wire:loading wire:target="delete" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center z-10 backdrop-blur-sm">
                    <svg class="animate-spin h-10 w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                            Confirm Deletion
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Are you sure you want to delete the contract for 
                                <span class="font-semibold text-gray-900 dark:text-gray-200">{{ $selectedContract?->property?->title }}</span>? 
                                This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
            </x-card.body>

            <x-card.footer class="flex justify-end space-x-3">
                <button 
                    type="button"
                    wire:click="$set('showDeleteModal', false)"
                    wire:loading.attr="disabled"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-700 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition"
                >
                    Cancel
                </button>
                <button 
                    type="button" 
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg wire:loading wire:target="delete" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Delete Contract</span>
                </button>
            </x-card.footer>
        </x-card>
    </flux:modal>

    <!-- Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
</div>