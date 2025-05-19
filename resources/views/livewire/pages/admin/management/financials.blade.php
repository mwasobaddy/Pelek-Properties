<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state};
use App\Models\FinancialRecord;
use App\Services\FinancialService;
use Carbon\Carbon;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    #[State]
    public $currentMonthRevenue;

    #[State]
    public $dateFilter = 'current_month';

    #[State]
    public $typeFilter = 'all';

    #[State]
    public $search = '';

    #[State]
    public $showModal = false;

    #[State]
    public $selectedRecord = null;

    #[State]
    public $isLoading = false;

    #[State]
    public $sortField = 'transaction_date';

    #[State]
    public $sortDirection = 'desc';

    #[State]
    public $showFilters = false;

    #[State]
    public $filters = [
        'status' => '',
        'payment_method' => '',
        'transaction_type' => '',
        'date_range' => '',
    ];

    public function mount(FinancialService $financialService): void 
    {
        $this->authorize('manage_financial_records');
        $this->currentMonthRevenue = $financialService->getCurrentMonthRevenue();
    }

    #[Computed]
    public function records()
    {
        $query = FinancialRecord::with(['property', 'recordedBy'])
            ->where('status', 'completed');

        // Apply search filter
        if ($this->search) {
            $query->whereHas('property', function($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            })->orWhere('category', 'like', '%' . $this->search . '%');
        }

        // Apply status filter
        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        // Apply payment method filter
        if ($this->filters['payment_method']) {
            $query->where('payment_method', $this->filters['payment_method']);
        }

        // Apply transaction type filter
        if ($this->filters['transaction_type']) {
            $query->where('transaction_type', $this->filters['transaction_type']);
        }

        // Apply date range filter
        if ($this->filters['date_range']) {
            switch ($this->filters['date_range']) {
                case 'today':
                    $query->whereDate('transaction_date', Carbon::today());
                    break;
                case 'this_week':
                    $query->whereBetween('transaction_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('transaction_date', Carbon::now()->month)
                          ->whereYear('transaction_date', Carbon::now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('transaction_date', Carbon::now()->subMonth()->month)
                          ->whereYear('transaction_date', Carbon::now()->subMonth()->year);
                    break;
                case 'this_year':
                    $query->whereYear('transaction_date', Carbon::now()->year);
                    break;
            }
        }

        // Apply date filter
        if ($this->dateFilter === 'current_month') {
            $query->whereMonth('transaction_date', Carbon::now()->month)
                  ->whereYear('transaction_date', Carbon::now()->year);
        } elseif ($this->dateFilter === 'last_month') {
            $query->whereMonth('transaction_date', Carbon::now()->subMonth()->month)
                  ->whereYear('transaction_date', Carbon::now()->subMonth()->year);
        }

        // Apply type filter
        if ($this->typeFilter !== 'all') {
            $query->where('transaction_type', $this->typeFilter);
        }

        return $query->orderBy($this->sortField, $this->sortDirection)->paginate(10);
    }

    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters(): void
    {
        $this->reset('filters');
        $this->loadRecords();
    }

    public function loadRecords(): void
    {
        $query = FinancialRecord::with(['property', 'recordedBy'])
            ->where('status', 'completed');

        // Apply search filter
        if ($this->search) {
            $query->whereHas('property', function($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            })->orWhere('category', 'like', '%' . $this->search . '%');
        }

        // Apply status filter
        if ($this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        // Apply payment method filter
        if ($this->filters['payment_method']) {
            $query->where('payment_method', $this->filters['payment_method']);
        }

        // Apply transaction type filter
        if ($this->filters['transaction_type']) {
            $query->where('transaction_type', $this->filters['transaction_type']);
        }

        // Apply date range filter
        if ($this->filters['date_range']) {
            switch ($this->filters['date_range']) {
                case 'today':
                    $query->whereDate('transaction_date', Carbon::today());
                    break;
                case 'this_week':
                    $query->whereBetween('transaction_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('transaction_date', Carbon::now()->month)
                          ->whereYear('transaction_date', Carbon::now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('transaction_date', Carbon::now()->subMonth()->month)
                          ->whereYear('transaction_date', Carbon::now()->subMonth()->year);
                    break;
                case 'this_year':
                    $query->whereYear('transaction_date', Carbon::now()->year);
                    break;
            }
        }

        // Apply date filter
        if ($this->dateFilter === 'current_month') {
            $query->whereMonth('transaction_date', Carbon::now()->month)
                  ->whereYear('transaction_date', Carbon::now()->year);
        } elseif ($this->dateFilter === 'last_month') {
            $query->whereMonth('transaction_date', Carbon::now()->subMonth()->month)
                  ->whereYear('transaction_date', Carbon::now()->subMonth()->year);
        }

        // Apply type filter
        if ($this->typeFilter !== 'all') {
            $query->where('transaction_type', $this->typeFilter);
        }

        $this->records = $query->orderBy($this->sortField, $this->sortDirection)->paginate(10);
    }

    public function filterByDate($filter): void
    {
        $this->dateFilter = $filter;
        $this->loadRecords();
    }

    public function filterByType($type): void
    {
        $this->typeFilter = $type;
        $this->loadRecords();
    }

    public function viewRecord($id): void
    {
        $this->selectedRecord = FinancialRecord::with(['property', 'recordedBy'])->findOrFail($id);
        $this->showModal = true;
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

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
                    <flux:icon name="currency-dollar" class="w-8 h-8 text-[#02c9c2]" />
                    Financial Records
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Track and manage property financial records
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

        <!-- Summary Cards with Animation -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white/50 dark:bg-gray-800/50 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 backdrop-blur-xl">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Month Revenue</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                    KES {{ number_format($currentMonthRevenue, 2) }}
                </p>
            </div>
            <div class="bg-white/50 dark:bg-gray-800/50 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 backdrop-blur-xl">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                    KES {{ number_format($this->records->sum('amount'), 2) }}
                </p>
            </div>
            <div class="bg-white/50 dark:bg-gray-800/50 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 backdrop-blur-xl">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                    KES {{ number_format($this->records->where('transaction_type', 'expense')->sum('amount'), 2) }}
                </p>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="mt-8 space-y-4" x-data="{}" x-intersect="$el.classList.add('animate-fade-in')">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1 relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                        <flux:icon wire:loading.remove wire:target="search" name="magnifying-glass" class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                        <flux:icon wire:loading wire:target="search" name="arrow-path" class="h-5 h-5 text-[#02c9c2] animate-spin" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Search by property, category, or reference..."
                        class="block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                        aria-label="Search financial records"
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
                        {{ array_filter($filters) ? count(array_filter($filters)) : '0' }}
                    </span>
                </button>
            </div>

            <!-- Filters Panel -->
            <div x-show="$wire.showFilters"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 backdrop-blur-xl shadow-sm space-y-4"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select wire:model.live="filters.status" class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <!-- Payment Method Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="credit-card" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select wire:model.live="filters.payment_method" class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                <option value="">All Methods</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="mpesa">M-Pesa</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    <!-- Transaction Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Transaction Type</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="arrow-trending-up" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select wire:model.live="filters.transaction_type" class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                <option value="">All Types</option>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
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
                            <select wire:model.live="filters.date_range" class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
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

    <!-- Financial Records Table -->
    <div class="p-8">
        <div class="relative overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 backdrop-blur-xl">
            <!-- Loading Overlay -->
            <div wire:loading.delay wire:target="filterByDate, filterByType" 
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
                        <th class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <button wire:click="sortBy('property_name')" class="group inline-flex items-center space-x-1">
                                <span>Property</span>
                                @if($sortField === 'property_name')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">Type</th>
                        <th class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">Category</th>
                        <th class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <button wire:click="sortBy('amount')" class="group inline-flex items-center space-x-1">
                                <span>Amount</span>
                                @if($sortField === 'amount')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <button wire:click="sortBy('transaction_date')" class="group inline-flex items-center space-x-1">
                                <span>Date</span>
                                @if($sortField === 'transaction_date')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-4 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->records as $record)
                        <tr class="bg-white/50 dark:bg-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $record->property->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span @class([
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300' => $record->transaction_type === 'income',
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $record->transaction_type === 'expense'
                                ])>
                                    {{ ucfirst($record->transaction_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                {{ ucfirst($record->category) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                KES {{ number_format($record->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                {{ $record->transaction_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button
                                    wire:click="viewRecord({{ $record->id }})"
                                        class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-indigo-500 dark:bg-indigo-700/50 rounded-lg p-2"
                                >
                                    <flux:icon wire:loading.remove wire:target="viewRecord({{ $record->id }})" name="eye" class="w-4 h-4" />
                                    <flux:icon wire:loading wire:target="viewRecord({{ $record->id }})" name="arrow-path" class="w-4 h-4 animate-spin" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($this->records->hasPages())
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                    {{ $this->records->links('components.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Record Details Modal -->
    <flux:modal wire:model="showModal" class="w-full max-w-4xl" @close="$wire.resetForm()">
        <x-card class="w-fulloverflow-hidden rounded-xl">
            <x-card.header>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Record Details
                </h3>
            </x-card.header>

            <x-card.body>
                @if($selectedRecord)
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $selectedRecord->property->title }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Type</label>
                            <p class="mt-1">
                                <span @class([
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300' => $selectedRecord->transaction_type === 'income',
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $selectedRecord->transaction_type === 'expense'
                                ])>
                                    {{ ucfirst($selectedRecord->transaction_type) }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ ucfirst($selectedRecord->category) }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                KES {{ number_format($selectedRecord->amount, 2) }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $selectedRecord->transaction_date->format('M d, Y') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recorded By</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $selectedRecord->recordedBy->name }}
                            </p>
                        </div>

                        @if($selectedRecord->description)
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $selectedRecord->description }}
                            </p>
                        </div>
                        @endif
                    </div>
                @endif
            </x-card.body>

            <x-card.footer>
                <div class="flex justify-end">
                    <button 
                        type="button"
                        wire:click="$set('showModal', false)"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150"
                    >
                        Close
                    </button>
                </div>
            </x-card.footer>
        </x-card>
    </flux:modal>

    <!-- Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
</div>
