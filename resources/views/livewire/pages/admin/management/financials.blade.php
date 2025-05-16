<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state};
use App\Models\FinancialRecord;
use App\Services\FinancialService;
use Carbon\Carbon;

new class extends Component {
    public $records;
    public $currentMonthRevenue;
    public $dateFilter = 'current_month';
    public $typeFilter = 'all';

    public function mount(FinancialService $financialService): void 
    {
        $this->currentMonthRevenue = $financialService->getCurrentMonthRevenue();
        $this->loadRecords();
    }

    public function loadRecords(): void
    {
        $query = FinancialRecord::with(['property', 'recordedBy'])
            ->where('status', 'completed');

        // Apply date filter
        if ($this->dateFilter === 'current_month') {
            $query->whereMonth('transaction_date', Carbon::now()->month)
                  ->whereYear('transaction_date', Carbon::now()->year);
        } elseif ($this->dateFilter === 'last_month') {
            $query->whereMonth('transaction_date', Carbon::now()->subMonth()->month)
                  ->whereYear('transaction_date', Carbon::now()->subMonth()->year);
        } elseif ($this->dateFilter === 'last_3_months') {
            $query->where('transaction_date', '>=', Carbon::now()->subMonths(3));
        }

        // Apply type filter
        if ($this->typeFilter !== 'all') {
            $query->where('transaction_type', $this->typeFilter);
        }

        $this->records = $query->latest()->get();
    }

    public function filterByDate(string $period): void
    {
        $this->dateFilter = $period;
        $this->loadRecords();
    }

    public function filterByType(string $type): void
    {
        $this->typeFilter = $type;
        $this->loadRecords();
    }
}; ?>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Financial Records</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Track and manage property financial records</p>
    </div>

    <div class="p-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Month Revenue</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                    KES {{ number_format($currentMonthRevenue, 2) }}
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-4">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Time Period</label>
                <div class="flex space-x-4">
                    <button 
                        wire:click="filterByDate('current_month')"
                        class="{{ $dateFilter === 'current_month' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Current Month
                    </button>
                    <button 
                        wire:click="filterByDate('last_month')"
                        class="{{ $dateFilter === 'last_month' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Last Month
                    </button>
                    <button 
                        wire:click="filterByDate('last_3_months')"
                        class="{{ $dateFilter === 'last_3_months' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Last 3 Months
                    </button>
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Transaction Type</label>
                <div class="flex space-x-4">
                    <button 
                        wire:click="filterByType('all')"
                        class="{{ $typeFilter === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md text-sm font-medium"
                    >
                        All
                    </button>
                    <button 
                        wire:click="filterByType('income')"
                        class="{{ $typeFilter === 'income' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Income
                    </button>
                    <button 
                        wire:click="filterByType('expense')"
                        class="{{ $typeFilter === 'expense' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md text-sm font-medium"
                    >
                        Expenses
                    </button>
                </div>
            </div>
        </div>

        <!-- Financial Records Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Property</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($records as $record)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $record->property->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $record->transaction_type === 'income' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                    {{ ucfirst($record->transaction_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ ucfirst(str_replace('_', ' ', $record->category)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $record->transaction_type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                KES {{ number_format($record->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $record->transaction_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <button 
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                                    wire:click="viewRecord({{ $record->id }})"
                                >
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
