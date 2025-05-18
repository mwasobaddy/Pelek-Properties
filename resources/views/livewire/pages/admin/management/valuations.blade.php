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
    public $filterStatus = 'all';

    #[State]
    public $showModal = false;

    #[State]
    public $isLoading = false;

    #[State]
    public $selectedRequest = null;

    public function mount(): void
    {
        $this->authorize('view_valuation_requests');
    }

    #[Computed]
    public function valuationRequests()
    {
        return ValuationRequest::query()
            ->with(['property', 'valuationReport'])
            ->when($this->filterStatus !== 'all', function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('property_type', 'like', "%{$this->search}%")
                      ->orWhere('location', 'like', "%{$this->search}%")
                      ->orWhereHas('property', function ($q) {
                          $q->where('title', 'like', "%{$this->search}%");
                      });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
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

    public function view($id): void
    {
        $this->selectedRequest = ValuationRequest::with(['property', 'valuationReport'])->find($id);
        $this->showModal = true;
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterStatus']);
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
                    :href="route('admin.management.valuations.create')"
                    wire:navigate
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
        <div class="mt-6 flex flex-col sm:flex-row sm:items-center gap-4">

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
                            placeholder="Search valuations..."
                            class="block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            aria-label="Search valuations"
                        >
                    </div>
                </div>

            <div class="flex items-center gap-4">
                <select
                    wire:model.live="filterStatus"
                    class="rounded-lg border-0 bg-white/5 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-[#02c9c2] dark:text-white dark:ring-white/10 dark:focus:ring-[#02c9c2] sm:text-sm"
                >
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>

                <flux:button 
                    secondary
                    wire:click="resetFilters"
                >
                    Reset Filters
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Valuation Requests Table -->
    <div class="p-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <th wire:click="sort('property_type')" class="px-4 py-3 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                            <div class="flex items-center gap-2">
                                Property Type
                                @if ($sortField === 'property_type')
                                    <flux:icon 
                                        name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" 
                                        class="w-4 h-4"
                                    />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('location')" class="px-4 py-3 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                            <div class="flex items-center gap-2">
                                Location
                                @if ($sortField === 'location')
                                    <flux:icon 
                                        name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" 
                                        class="w-4 h-4"
                                    />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('status')" class="px-4 py-3 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                            <div class="flex items-center gap-2">
                                Status
                                @if ($sortField === 'status')
                                    <flux:icon 
                                        name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" 
                                        class="w-4 h-4"
                                    />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('created_at')" class="px-4 py-3 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                            <div class="flex items-center gap-2">
                                Date
                                @if ($sortField === 'created_at')
                                    <flux:icon 
                                        name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" 
                                        class="w-4 h-4"
                                    />
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
                                {{ ucfirst($request->property_type) }}
                            </td>
                            <td class="px-4 py-4 text-sm">
                                {{ $request->location }}
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
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button 
                                        secondary
                                        size="xs"
                                        wire:click="view({{ $request->id }})"
                                    >
                                        View Details
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No valuation requests found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $this->valuationRequests()->links() }}
        </div>
    </div>

    <!-- View Details Modal -->
    <flux:modal wire:model.live="showModal" class="w-full max-w-4xl">
        @if($selectedRequest)
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Valuation Request Details
                </h3>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Property Type</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">{{ ucfirst($selectedRequest->property_type) }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Location</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">{{ $selectedRequest->location }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <span @class([
                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $selectedRequest->status === 'pending',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $selectedRequest->status === 'in_progress',
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $selectedRequest->status === 'completed'
                            ])>
                                {{ ucfirst(str_replace('_', ' ', $selectedRequest->status)) }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Date Requested</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">{{ $selectedRequest->created_at->format('M d, Y') }}</dd>
                    </div>

                    @if($selectedRequest->description)
                        <div class="col-span-2">
                            <dt class="font-medium text-gray-500 dark:text-gray-400">Description</dt>
                            <dd class="mt-1 text-gray-900 dark:text-white">{{ $selectedRequest->description }}</dd>
                        </div>
                    @endif

                    @if($selectedRequest->valuationReport)
                        <div class="col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Valuation Report</h4>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Estimated Value</dt>
                                    <dd class="mt-1 text-gray-900 dark:text-white">
                                        KES {{ number_format($selectedRequest->valuationReport->estimated_value, 2) }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Confidence Level</dt>
                                    <dd class="mt-1">
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $selectedRequest->valuationReport->confidence_level === 'high',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $selectedRequest->valuationReport->confidence_level === 'medium',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $selectedRequest->valuationReport->confidence_level === 'low'
                                        ])>
                                            {{ ucfirst($selectedRequest->valuationReport->confidence_level) }}
                                        </span>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    @endif
                </dl>
            </div>
        @endif
    </flux:modal>

    <!-- Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
</div>
