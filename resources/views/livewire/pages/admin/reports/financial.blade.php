<?php

use function Livewire\Volt\{state, computed};
use App\Services\FinancialService;
use Carbon\Carbon;

state([
    'period' => 'monthly',
    'startDate' => fn() => Carbon::now()->startOfMonth()->format('Y-m-d'),
    'endDate' => fn() => Carbon::now()->endOfMonth()->format('Y-m-d'),
    'reportData' => fn() => [],
    'loading' => false,
]);

$mount = function (FinancialService $financialService) {
    $this->loadReport($financialService);
};

$loadReport = function (FinancialService $financialService) {
    $this->loading = true;

    try {
        $this->reportData = $financialService->generateReport(
            $this->startDate,
            $this->endDate
        );
    } catch (\Exception $e) {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'Error loading financial report. Please try again.'
        ]);
    }

    $this->loading = false;
};

$setPeriod = function (string $period) {
    $this->period = $period;
    
    $now = Carbon::now();
    
    switch ($period) {
        case 'weekly':
            $this->startDate = $now->startOfWeek()->format('Y-m-d');
            $this->endDate = $now->endOfWeek()->format('Y-m-d');
            break;
        case 'monthly':
            $this->startDate = $now->startOfMonth()->format('Y-m-d');
            $this->endDate = $now->endOfMonth()->format('Y-m-d');
            break;
        case 'yearly':
            $this->startDate = $now->startOfYear()->format('Y-m-d');
            $this->endDate = $now->endOfYear()->format('Y-m-d');
            break;
    }

    $this->loadReport(app(FinancialService::class));
};

computed([
    'totalRevenue' => fn() => number_format(
        collect($this->reportData)->sum('amount'),
        2
    ),
    'totalExpenses' => fn() => number_format(
        collect($this->reportData)->where('type', 'expense')->sum('amount'),
        2
    ),
    'netIncome' => fn() => number_format(
        collect($this->reportData)->sum(fn($item) => 
            $item['type'] === 'income' ? $item['amount'] : -$item['amount']
        ),
        2
    ),
]);

?>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Financial Reports</h2>
        
        <div class="flex space-x-2">
            <button
                wire:click="setPeriod('weekly')"
                class="{{ $period === 'weekly' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300' : 'bg-white dark:bg-gray-700' }} px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none"
            >
                Weekly
            </button>
            <button
                wire:click="setPeriod('monthly')"
                class="{{ $period === 'monthly' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300' : 'bg-white dark:bg-gray-700' }} px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none"
            >
                Monthly
            </button>
            <button
                wire:click="setPeriod('yearly')"
                class="{{ $period === 'yearly' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300' : 'bg-white dark:bg-gray-700' }} px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none"
            >
                Yearly
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Total Revenue Card -->
        <div class="bg-green-50 dark:bg-green-900 rounded-lg p-4">
            <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Total Revenue</h3>
            <p class="mt-2 text-2xl font-bold text-green-900 dark:text-green-200">
                KES {{ $this->totalRevenue }}
            </p>
        </div>

        <!-- Total Expenses Card -->
        <div class="bg-red-50 dark:bg-red-900 rounded-lg p-4">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Total Expenses</h3>
            <p class="mt-2 text-2xl font-bold text-red-900 dark:text-red-200">
                KES {{ $this->totalExpenses }}
            </p>
        </div>

        <!-- Net Income Card -->
        <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Net Income</h3>
            <p class="mt-2 text-2xl font-bold text-blue-900 dark:text-blue-200">
                KES {{ $this->netIncome }}
            </p>
        </div>
    </div>

    <!-- Date Range Selector -->
    <div class="flex items-center space-x-4 mb-6">
        <div>
            <label for="startDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
            <input
                wire:model.live="startDate"
                type="date"
                id="startDate"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
            >
        </div>
        <div>
            <label for="endDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
            <input
                wire:model.live="endDate"
                type="date"
                id="endDate"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600"
            >
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Date</th>
                    <th scope="col" class="px-6 py-3">Description</th>
                    <th scope="col" class="px-6 py-3">Category</th>
                    <th scope="col" class="px-6 py-3">Type</th>
                    <th scope="col" class="px-6 py-3 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $transaction)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="px-6 py-4">{{ date('Y-m-d', strtotime($transaction['date'])) }}</td>
                        <td class="px-6 py-4">{{ $transaction['description'] }}</td>
                        <td class="px-6 py-4">{{ $transaction['category'] }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction['type'] === 'income' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                {{ ucfirst($transaction['type']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-medium {{ $transaction['type'] === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            KES {{ number_format($transaction['amount'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No transactions found for the selected period
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Loading Overlay -->
    @if($loading)
        <div class="absolute inset-0 bg-gray-900/50 flex items-center justify-center">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-white"></div>
        </div>
    @endif
</div>
