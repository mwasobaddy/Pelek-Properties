<?php

use App\Models\Property;
use App\Models\FinancialRecord;
use App\Services\FinancialService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed};

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public $selectedPropertyId = '';
    public $dateRange = 'current_month';
    public $transactionType = '';
    public $startDate;
    public $endDate;
    public $category = '';
    public $status = '';
    public $showTransactionModal = false;

    #[Rule([
        'transactionData.type' => 'required|in:income,expense',
        'transactionData.category' => 'required|string',
        'transactionData.amount' => 'required|numeric|min:0',
        'transactionData.date' => 'required|date',
        'transactionData.payment_method' => 'nullable|string',
        'transactionData.status' => 'required|in:pending,completed',
        'transactionData.description' => 'nullable|string',
        'transactionData.reference_number' => 'nullable|string',
    ])]
    public $transactionData = [
        'type' => 'income',
        'category' => '',
        'amount' => '',
        'date' => '',
        'payment_method' => '',
        'status' => 'pending',
        'description' => '',
        'reference_number' => '',
    ];

    public function mount(FinancialService $financialService)
    {
        $this->authorize('manage-properties');
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->transactionData['date'] = now()->format('Y-m-d');
    }

    public function updatedDateRange()
    {
        $today = now();
        
        switch ($this->dateRange) {
            case 'current_month':
                $this->startDate = $today->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $today->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = $today->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = $today->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_3_months':
                $this->startDate = $today->copy()->subMonths(3)->startOfMonth()->format('Y-m-d');
                $this->endDate = $today->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'year_to_date':
                $this->startDate = $today->copy()->startOfYear()->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
        }
    }

    #[computed]
    public function properties()
    {
        return Property::whereHas('managementContracts', function($q) {
            $q->where('status', 'active');
        })->get();
    }

    #[computed]
    public function records()
    {
        $query = FinancialRecord::with(['property', 'recorder'])
            ->when($this->selectedPropertyId, function($q) {
                $q->where('property_id', $this->selectedPropertyId);
            })
            ->when($this->transactionType, function($q) {
                $q->where('transaction_type', $this->transactionType);
            })
            ->when($this->category, function($q) {
                $q->where('category', $this->category);
            })
            ->when($this->status, function($q) {
                $q->where('status', $this->status);
            })
            ->whereBetween('transaction_date', [$this->startDate, $this->endDate])
            ->latest('transaction_date');

        return $query->paginate(10);
    }

    #[computed]
    public function summary()
    {
        $records = FinancialRecord::when($this->selectedPropertyId, function($q) {
                $q->where('property_id', $this->selectedPropertyId);
            })
            ->whereBetween('transaction_date', [$this->startDate, $this->endDate])
            ->get();

        return [
            'total_income' => $records->where('transaction_type', 'income')->sum('amount'),
            'total_expenses' => $records->where('transaction_type', 'expense')->sum('amount'),
            'net_income' => $records->where('transaction_type', 'income')->sum('amount') - 
                          $records->where('transaction_type', 'expense')->sum('amount'),
            'pending_payments' => $records->where('status', 'pending')->count(),
        ];
    }

    public function recordTransaction(FinancialService $financialService)
    {
        $this->validate();

        $property = Property::findOrFail($this->selectedPropertyId);
        $financialService->recordTransaction($property, $this->transactionData);

        $this->showTransactionModal = false;
        $this->reset('transactionData');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Transaction recorded successfully'
        ]);
    }

    public function exportReport()
    {
        // TO DO: Implement export functionality
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Export functionality coming soon'
        ]);
    }
} ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Page Header -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Financial Reports</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">View and manage financial records for your properties.</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Property Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property</label>
                        <select wire:model.live="selectedPropertyId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="">All Properties</option>
                            @foreach($this->properties as $property)
                                <option value="{{ $property->id }}">{{ $property->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
                        <select wire:model.live="dateRange" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="current_month">Current Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="last_3_months">Last 3 Months</option>
                            <option value="year_to_date">Year to Date</option>
                        </select>
                    </div>

                    <!-- Transaction Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction Type</label>
                        <select wire:model.live="transactionType" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="">All Types</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select wire:model.live="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button
                        wire:click="exportReport"
                        class="px-4 py-2 text-sm text-gray-700 bg-white dark:bg-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
                    >
                        Export Report
                    </button>
                    <button
                        wire:click="$set('showTransactionModal', true)"
                        @disabled(!$selectedPropertyId)
                        class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md disabled:opacity-50"
                    >
                        Record Transaction
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Income -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <flux:icon name="arrow-up-circle" class="h-6 w-6 text-white" />
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                KES {{ number_format($this->summary['total_income']) }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Income</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Expenses -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                            <flux:icon name="arrow-down-circle" class="h-6 w-6 text-white" />
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                KES {{ number_format($this->summary['total_expenses']) }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Expenses</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Net Income -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <flux:icon name="calculator" class="h-6 w-6 text-white" />
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                KES {{ number_format($this->summary['net_income']) }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Net Income</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Payments -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <flux:icon name="clock" class="h-6 w-6 text-white" />
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $this->summary['pending_payments'] }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pending Payments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Property</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($this->records as $record)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ $record->transaction_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ $record->property->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span @class([
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $record->transaction_type === 'income',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $record->transaction_type === 'expense',
                                        ])>
                                            {{ ucfirst($record->transaction_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        {{ str_replace('_', ' ', ucfirst($record->category)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        KES {{ number_format($record->amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span @class([
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' => $record->status === 'pending',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $record->status === 'completed',
                                        ])>
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $record->reference_number ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No financial records found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $this->records->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Modal -->
    <div x-data="{ show: @entangle('showTransactionModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl shadow-xl">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Record Transaction
                    </h3>
                </div>

                <form wire:submit.prevent="recordTransaction" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                            <select wire:model="transactionData.type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                            @error('transactionData.type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                            <select wire:model="transactionData.category" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Select category</option>
                                @if($transactionData['type'] === 'income')
                                    <option value="rent">Rent</option>
                                    <option value="deposit">Security Deposit</option>
                                    <option value="late_fee">Late Fee</option>
                                    <option value="other_income">Other Income</option>
                                @else
                                    <option value="maintenance">Maintenance</option>
                                    <option value="utilities">Utilities</option>
                                    <option value="management_fee">Management Fee</option>
                                    <option value="insurance">Insurance</option>
                                    <option value="taxes">Taxes</option>
                                    <option value="other_expense">Other Expense</option>
                                @endif
                            </select>
                            @error('transactionData.category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount (KES)</label>
                            <input type="number" wire:model="transactionData.amount" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('transactionData.amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                            <input type="date" wire:model="transactionData.date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('transactionData.date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                            <select wire:model="transactionData.payment_method" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Select method</option>
                                <option value="mpesa">M-Pesa</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                            </select>
                            @error('transactionData.payment_method') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select wire:model="transactionData.status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                            </select>
                            @error('transactionData.status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference Number</label>
                            <input type="text" wire:model="transactionData.reference_number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('transactionData.reference_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea wire:model="transactionData.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
                            @error('transactionData.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            wire:click="$set('showTransactionModal', false)"
                            class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-md"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md"
                        >
                            Save Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
