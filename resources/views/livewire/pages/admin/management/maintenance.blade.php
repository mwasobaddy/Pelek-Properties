<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state};
use App\Models\MaintenanceRecord;
use App\Models\Property;
use App\Services\MaintenanceService;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    
    #[State]
    public $search = '';
    
    #[State]
    public $sortField = 'created_at';
    
    #[State]
    public $sortDirection = 'desc';
    
    #[State]
    public $statusFilter = 'all';
    
    #[State]
    public $isLoading = false;

    #[State]
    public $showModal = false;

    #[State]
    public $showDeleteModal = false;

    #[State]
    public $selectedRecord = null;

    #[State]
    public $modalMode = 'view'; // view, edit

    #[State]
    public $properties = [];

    #[State]
    public $newStatus = '';
    
    #[State]
    public $form = [
        'property_id' => '',
        'status' => '',
        'description' => '',
        'issue_type' => '',
        'priority' => '',
        'requested_by' => '',
        'completed_date' => null,
        'created_at' => null,
        'updated_at' => null
    ];

    protected $availableStatuses = [
        'pending' => 'Pending',
        'scheduled' => 'Scheduled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ];

    public function mount(MaintenanceService $maintenanceService): void 
    {
        $this->authorize('manage_maintenance_records');
        $this->properties = Property::select('id', 'title')
            ->orderBy('title')
            ->get();
    }

    public function getRecordsProperty()
    {
        $query = MaintenanceRecord::with('property')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->whereHas('property', function($q) {
                        $q->where('title', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('issue_type', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('requested_by', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function($query) {
                $query->where('status', $this->statusFilter);
            });

        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate(10);
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

    public function getAvailableStatusesProperty()
    {
        return $this->availableStatuses;
    }

    public function confirmStatusChange($recordId): void
    {
        $this->authorize('update_maintenance_status');
        $this->selectedRecord = MaintenanceRecord::findOrFail($recordId);
        $this->newStatus = $this->selectedRecord->status;
    }

    public function confirmDelete($recordId): void
    {
        $this->authorize('delete_maintenance_request');
        $this->selectedRecord = MaintenanceRecord::findOrFail($recordId);
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $this->authorize('delete_maintenance_request');
        $this->selectedRecord->delete();
        $this->showDeleteModal = false;
        $this->selectedRecord = null;
        $this->dispatch('notify', type: 'success', message: 'Maintenance record deleted successfully.');
        $this->loadRecords();
    }

    public function view($id): void
    {
        $record = MaintenanceRecord::findOrFail($id);
        $this->selectedRecord = $record;
        $this->form = [
            'property_id' => $record->property_id,
            'status' => $record->status,
            'description' => $record->description,
            'issue_type' => $record->issue_type,
            'priority' => $record->priority,
            'requested_by' => $record->requested_by,
            'completed_date' => $record->completed_date,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at
        ];
        $this->modalMode = 'view';
        $this->showModal = true;
    }

    public function edit($id): void
    {
        $this->authorize('update_maintenance_status');
        $record = MaintenanceRecord::findOrFail($id);
        $this->selectedRecord = $record;
        $this->form = [
            'property_id' => $record->property_id,
            'status' => $record->status,
            'description' => $record->description,
            'issue_type' => $record->issue_type,
            'priority' => $record->priority,
            'requested_by' => $record->requested_by,
            'completed_date' => $record->completed_date,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at
        ];
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function updateStatus(): void
    {
        if (!$this->selectedRecord) {
            return;
        }

        $this->authorize('update_maintenance_status');

        $this->validate([
            'form.status' => 'required|string|in:' . implode(',', array_keys($this->availableStatuses)),
            'form.property_id' => 'required|exists:properties,id',
        ]);

        $updateData = [
            'property_id' => $this->form['property_id'],
            'status' => $this->form['status'],
            'description' => $this->form['description'],
            'issue_type' => $this->form['issue_type'],
            'priority' => $this->form['priority'],
            'requested_by' => $this->form['requested_by'],
            'completed_date' => $this->form['status'] === 'completed' ? now() : null
        ];

        $this->selectedRecord->update($updateData);

        $this->showModal = false;
        $this->selectedRecord = null;
        $this->resetForm();
        
        $this->dispatch('notify', 
            type: 'success', 
            message: 'Maintenance status updated successfully.'
        );
    }

    public function resetForm(): void
    {
        $this->form = [
            'property_id' => '',
            'status' => '',
            'description' => '',
            'issue_type' => '',
            'priority' => '',
            'requested_by' => '',
            'completed_date' => null,
            'created_at' => null,
            'updated_at' => null
        ];
        $this->selectedRecord = null;
        $this->modalMode = 'view';
    }

    public function loadRecords(): void
    {
        $this->isLoading = true;
        
        try {
            $query = MaintenanceRecord::with('property')
                ->when($this->search, function($query) {
                    $query->where(function($q) {
                        $q->whereHas('property', function($q) {
                            $q->where('title', 'like', '%' . $this->search . '%');
                        })
                        ->orWhere('issue_type', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('requested_by', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->statusFilter !== 'all', function($query) {
                    $query->where('status', $this->statusFilter);
                });

            // Handle sorting
            $query->orderBy($this->sortField, $this->sortDirection);

            $this->records = $query->paginate(10);
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
        
        $this->loadRecords();
    }

    public function filterByStatus(string $status): void
    {
        $this->statusFilter = $status;
        $this->loadRecords();
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
                    <flux:icon name="wrench-screwdriver" class="w-8 h-8 text-[#02c9c2]" />
                    Maintenance Management
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Track and manage property maintenance requests
                </p>
            </div>
            
            <button 
                wire:click="$refresh"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                wire:loading.attr="disabled"
            >
                <flux:icon wire:loading.remove wire:target="$refresh" name="arrow-path" class="w-5 h-5 mr-2" />
                <flux:icon wire:loading wire:target="$refresh" name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                Refresh
            </button>
        </div>

        <!-- Status Filter Tabs with Animation -->
        <div class="mt-8 space-y-4" x-data="{}" x-intersect="$el.classList.add('animate-fade-in')">
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
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Search by property, issue type, or description..."
                        class="block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                        aria-label="Search maintenance records"
                    >
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button 
                    wire:click="filterByStatus('all')"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150
                        {{ $statusFilter === 'all' 
                            ? 'bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white shadow-lg' 
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    <flux:icon wire:loading.remove name="list-bullet" class="w-5 h-5 mr-2" />
                    <flux:icon wire:loading wire:target="filterByStatus" name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                    All <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full px-2 py-0.5">{{ $this->records->total() }}</span>
                </button>

                <button 
                    wire:click="filterByStatus('pending')"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150
                        {{ $statusFilter === 'pending' 
                            ? 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white shadow-lg' 
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    <flux:icon wire:loading.remove name="clock" class="w-5 h-5 mr-2" />
                    <flux:icon wire:loading wire:target="filterByStatus" name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                    Pending <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full px-2 py-0.5">{{ MaintenanceRecord::where('status', 'pending')->count() }}</span>
                </button>
                
                <button 
                    wire:click="filterByStatus('in_progress')"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150
                        {{ $statusFilter === 'in_progress' 
                            ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' 
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    <flux:icon wire:loading.remove name="arrow-path" class="w-5 h-5 mr-2" />
                    <flux:icon wire:loading wire:target="filterByStatus" name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                    In Progress <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full px-2 py-0.5">{{ MaintenanceRecord::where('status', 'in_progress')->count() }}</span>
                </button>
                
                <button 
                    wire:click="filterByStatus('completed')"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-150
                        {{ $statusFilter === 'completed' 
                            ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg' 
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    <flux:icon wire:loading.remove name="check-circle" class="w-5 h-5 mr-2" />
                    <flux:icon wire:loading wire:target="filterByStatus" name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                    Completed <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full px-2 py-0.5">{{ MaintenanceRecord::where('status', 'completed')->count() }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Maintenance Records Table -->
    <div class="p-8">
        <div class="relative overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 backdrop-blur-xl">
            <!-- Loading Overlay -->
            <div wire:loading.delay wire:target="filterByStatus" 
                 class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="flex items-center space-x-4">
                    <flux:icon name="arrow-path" class="w-8 h-8 text-[#02c9c2] animate-spin" />
                    <span class="text-gray-600 dark:text-gray-300 font-medium">Loading records...</span>
                </div>
            </div>

            <!-- Table -->
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-300 text-sm">
                    <tr>
                        <th scope="col" class="px-6 py-4">
                            <button wire:click="sort('property_name')" class="group inline-flex items-center space-x-1">
                                <span>Property</span>
                                <span class="flex-none rounded {{ $sortField === 'property_name' ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                                    @if($sortField === 'property_name')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-5 h-5 text-[#02c9c2]" />
                                    @else
                                        <flux:icon name="chevron-up-down" class="w-5 h-5 text-gray-400 group-hover:text-[#02c9c2]" />
                                    @endif
                                </span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-4">
                            <button wire:click="sort('description')" class="group inline-flex items-center space-x-1">
                                <span>Description</span>
                                <span class="flex-none rounded {{ $sortField === 'description' ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                                    @if($sortField === 'description')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-5 h-5 text-[#02c9c2]" />
                                    @else
                                        <flux:icon name="chevron-up-down" class="w-5 h-5 text-gray-400 group-hover:text-[#02c9c2]" />
                                    @endif
                                </span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-4">
                            <button wire:click="sort('priority')" class="group inline-flex items-center space-x-1">
                                <span>Priority</span>
                                <span class="flex-none rounded {{ $sortField === 'priority' ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                                    @if($sortField === 'priority')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-5 h-5 text-[#02c9c2]" />
                                    @else
                                        <flux:icon name="chevron-up-down" class="w-5 h-5 text-gray-400 group-hover:text-[#02c9c2]" />
                                    @endif
                                </span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-4">
                            <button wire:click="sort('status')" class="group inline-flex items-center space-x-1">
                                <span>Status</span>
                                <span class="flex-none rounded {{ $sortField === 'status' ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                                    @if($sortField === 'status')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-5 h-5 text-[#02c9c2]" />
                                    @else
                                        <flux:icon name="chevron-up-down" class="w-5 h-5 text-gray-400 group-hover:text-[#02c9c2]" />
                                    @endif
                                </span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-4">
                            <button wire:click="sort('scheduled_date')" class="group inline-flex items-center space-x-1">
                                <span>Scheduled</span>
                                <span class="flex-none rounded {{ $sortField === 'scheduled_date' ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                                    @if($sortField === 'scheduled_date')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-5 h-5 text-[#02c9c2]" />
                                    @else
                                        <flux:icon name="chevron-up-down" class="w-5 h-5 text-gray-400 group-hover:text-[#02c9c2]" />
                                    @endif
                                </span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-4">
                            <button wire:click="sort('cost')" class="group inline-flex items-center space-x-1">
                                <span>Cost</span>
                                <span class="flex-none rounded {{ $sortField === 'cost' ? 'bg-gray-200 dark:bg-gray-700' : '' }}">
                                    @if($sortField === 'cost')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-5 h-5 text-[#02c9c2]" />
                                    @else
                                        <flux:icon name="chevron-up-down" class="w-5 h-5 text-gray-400 group-hover:text-[#02c9c2]" />
                                    @endif
                                </span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->records as $record)
                        <tr class="bg-white dark:bg-gray-800/30 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4">
                                {{ $record->property->title }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="line-clamp-2">
                                    {{ $record->description }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span @class([
                                    'px-3 py-1 rounded-full text-xs font-medium',
                                    'bg-red-100 text-red-800' => $record->priority === 'urgent',
                                    'bg-orange-100 text-orange-800' => $record->priority === 'high',
                                    'bg-yellow-100 text-yellow-800' => $record->priority === 'medium',
                                    'bg-green-100 text-green-800' => $record->priority === 'low',
                                ])>
                                    {{ ucfirst($record->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span @class([
                                    'px-3 py-1 rounded-full text-xs font-medium',
                                    'bg-yellow-100 text-yellow-800' => $record->status === 'pending',
                                    'bg-blue-100 text-blue-800' => $record->status === 'in_progress',
                                    'bg-green-100 text-green-800' => $record->status === 'completed',
                                    'bg-gray-100 text-gray-800' => $record->status === 'cancelled',
                                ])>
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                {{ $record->scheduled_date ? \Carbon\Carbon::parse($record->scheduled_date)->format('M d, Y') : 'Not scheduled' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $record->cost ? '$' . number_format($record->cost, 2) : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap">
                                <div class="flex items-center space-x-2">
                                    <button 
                                        wire:click="view({{ $record->id }})"
                                        class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-indigo-500 dark:bg-indigo-700/50 rounded-lg p-2"
                                        title="View record"
                                    >
                                        <flux:icon name="eye" class="w-5 h-5" />
                                    </button>

                                    @can('update_maintenance_status')
                                    <button 
                                        wire:click="edit({{ $record->id }})"
                                        class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-green-500 dark:bg-green-700/50 rounded-lg p-2"
                                        title="Edit record"
                                    >
                                        <flux:icon name="pencil-square" class="w-5 h-5" />
                                    </button>
                                    @endcan

                                    @can('delete_maintenance_record')
                                    <button 
                                        wire:click="confirmDelete({{ $record->id }})"
                                        class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-red-500 dark:bg-red-700/50 rounded-lg p-2"
                                        title="Delete record"
                                    >
                                        <flux:icon name="trash" class="w-5 h-5" />
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No maintenance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($this->records->hasPages())
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                    {{ $this->records->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <flux:modal wire:model.live="showDeleteModal" class="w-full max-w-4xl" @close="$wire.resetForm()">
        <x-card.header>Delete Maintenance Record</x-card.header>
        <x-card.body>
            <div class="space-y-4">
                <p class="text-sm text-gray-500">Are you sure you want to delete this maintenance record? This action cannot be undone.</p>
            </div>
        </x-card.body>
        <x-card.footer>
            <div class="flex justify-end space-x-2">
                <x-button type="button" wire:click="$set('showDeleteModal', false)" variant="primary">Cancel</x-button>
                <x-button type="button" wire:click="delete" variant="danger">Delete</x-button>
            </div>
        </x-card.footer>
    </flux:modal>

    <!-- View/Edit Modal -->
    <flux:modal wire:model.live="showModal" class="w-full max-w-4xl">
        <x-card>
            <x-card.header>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $modalMode === 'edit' ? 'Edit Maintenance Record' : 'View Maintenance Record' }}
                </h3>
            </x-card.header>

            <x-card.body>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="building-office" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                wire:model="form.property_id"
                                :disabled="$modalMode === 'view'"
                            >
                                <option value="">Select a property</option>
                                @foreach($this->properties as $property)
                                    <option value="{{ $property->id }}">{{ $property->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('form.property_id') 
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="document-text" class="h-5 w-5 text-gray-400" />
                            </div>
                            <textarea
                                id="description"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                wire:model="form.description"
                                :disabled="$modalMode === 'view'">
                            </textarea>
                        </div>
                        @error('form.description') 
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                                </div>
                                <select
                                    id="status"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                    wire:model="form.status"
                                    :disabled="$modalMode === 'view'"
                                >
                                    <option value="">Select status</option>
                                    @foreach($this->availableStatuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('form.status') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="flag" class="h-5 w-5 text-gray-400" />
                                </div>
                                <select id="priority" class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm" wire:model="form.priority" :disabled="$modalMode === 'view'">
                                    <option value="">Select priority</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            @error('form.priority') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if($modalMode === 'view')
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Created At</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="calendar" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        @disabled($modalMode === 'view')
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        value="{{ isset($form['created_at']) ? \Carbon\Carbon::parse($form['created_at'])->format('M d, Y H:i') : 'N/A' }}"
                                    >
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Updated At</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="calendar" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        @disabled($modalMode === 'view')
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        value="{{ isset($form['updated_at']) ? \Carbon\Carbon::parse($form['updated_at'])->format('M d, Y H:i') : 'N/A' }}"
                                    >
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </x-card.body>

            <x-card.footer>
                <div class="flex justify-end space-x-2">
                    @if($modalMode === 'edit')
                        <x-button type="button" wire:click="$set('showModal', false)" variant="primary">
                            Cancel
                        </x-button>
                        <x-button type="button" wire:click="updateStatus" variant="primary">
                            Update
                        </x-button>
                    @else
                        <x-button type="button" wire:click="$set('showModal', false)" variant="primary">
                            Close
                        </x-button>
                    @endif
                </div>
            </x-card.footer>
        </x-card>
    </flux:modal>

    <!-- Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
</div>
