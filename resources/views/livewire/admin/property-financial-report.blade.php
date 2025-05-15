<?php

use App\Models\Property;
use App\Models\FinancialRecord;
use App\Services\FinancialService;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed, mount};

new class extends Component {
    public ?string $selectedPropertyId = null;
    public string $period = 'month';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public bool $showAddTransactionModal = false;
    
    #[Rule([
        'transactionData.type' => 'required|in:income,expense',
        'transactionData.amount' => 'required|numeric|min:0',
        'transactionData.category' => 'required|string',
        'transactionData.date' => 'required|date',
        'transactionData.description' => 'required|string',
        'transactionData.payment_method' => 'required|string',
    ])]
    public $transactionData = [
        'type' => 'income',
        'amount' => '',
        'category' => '',
        'date' => '',
        'description' => '',
        'payment_method' => '',
    ];
    
    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }
    
    #[computed]
    public function financialSummary()
    {
        if (!$this->selectedPropertyId) {
            return null;
        }
        
        return app(FinancialService::class)->getPropertyFinancialSummary(
            $this->selectedPropertyId,
            $this->startDate,
            $this->endDate
        );
    }
    
    #[computed]
    public function transactions()
    {
        if (!$this->selectedPropertyId) {
            return collect();
        }
        
        return FinancialRecord::where('property_id', $this->selectedPropertyId)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderByDesc('date')
            ->get();
    }
    
    public function setPeriod($period)
    {
        $this->period = $period;
        
        switch ($period) {
            case 'month':
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $this->startDate = Carbon::now()->startOfQuarter()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfYear()->format('Y-m-d');
                break;
        }
    }
    
    public function addTransaction()
    {
        $this->validate();
        
        app(FinancialService::class)->createFinancialRecord(
            $this->selectedPropertyId,
            $this->transactionData
        );
        
        $this->showAddTransactionModal = false;
        $this->resetTransactionForm();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Transaction added successfully'
        ]);
    }
    
    private function resetTransactionForm()
    {
        $this->transactionData = [
            'type' => 'income',
            'amount' => '',
            'category' => '',
            'date' => '',
            'description' => '',
            'payment_method' => '',
        ];
    }
} ?>

<div class="space-y-6">
    <!-- Property Selection -->
    <div>
        <label for="property-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Property</label>
        <select
            wire:model.live="selectedPropertyId"
            id="property-select"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        >
            <option value="">Select a property</option>
            @foreach($this->properties as $property)
                <option value="{{ $property->id }}">{{ $property->title }}</option>
            @endforeach
        </select>
    </div>

    @if($this->selectedPropertyId)
        <!-- Period Selection and Add Transaction Button -->
        <div class="flex justify-between items-center">
            <div class="flex space-x-4">
                <button
                    wire:click="setPeriod('month')"
                    @class([
                        'px-4 py-2 text-sm rounded-md',
                        'bg-primary-600 text-white' => $period === 'month',
                        'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' => $period !== 'month'
                    ])
                >
                    Month
                </button>
                <button
                    wire:click="setPeriod('quarter')"
                    @class([
                        'px-4 py-2 text-sm rounded-md',
                        'bg-primary-600 text-white' => $period === 'quarter',
                        'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' => $period !== 'quarter'
                    ])
                >
                    Quarter
                </button>
                <button
                    wire:click="setPeriod('year')"
                    @class([
                        'px-4 py-2 text-sm rounded-md',
                        'bg-primary-600 text-white' => $period === 'year',
                        'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' => $period !== 'year'
                    ])
                >
                    Year
                </button>
            </div>
            
            <button
                wire:click="$set('showAddTransactionModal', true)"
                class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md"
            >
                Add Transaction
            </button>
        </div>

        <!-- Financial Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Total Income</h3>
                <div class="text-3xl font-bold text-green-600">
                    KES {{ number_format($this->financialSummary['total_income'], 2) }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Total Expenses</h3>
                <div class="text-3xl font-bold text-red-600">
                    KES {{ number_format($this->financialSummary['total_expenses'], 2) }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Net Income</h3>
                <div class="text-3xl font-bold {{ $this->financialSummary['net_income'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    KES {{ number_format($this->financialSummary['net_income'], 2) }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Profit Margin</h3>
                <div class="text-3xl font-bold {{ $this->financialSummary['profit_margin'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($this->financialSummary['profit_margin'], 1) }}%
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-white">Transactions</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payment Method</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($this->transactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $transaction->date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span @class([
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            'bg-green-100 text-green-800' => $transaction->type === 'income',
                                            'bg-red-100 text-red-800' => $transaction->type === 'expense',
                                        ])>
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $transaction->category }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $transaction->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                        KES {{ number_format($transaction->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $transaction->payment_method }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No transactions found for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <flux:icon name="currency-dollar" class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Select a Property</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose a property to view its financial records.</p>
        </div>
    @endif

    <!-- Add Transaction Modal -->
    <div x-data="{ show: @entangle('showAddTransactionModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-lg w-full max-w-md shadow-xl">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Add Transaction
                    </h3>
                </div>

                <form wire:submit.prevent="addTransaction" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                        <select wire:model="transactionData.type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                        @error('transactionData.type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount (KES)</label>
                        <input type="number" wire:model="transactionData.amount" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        @error('transactionData.amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                        <select wire:model="transactionData.category" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="">Select category</option>
                            @if($transactionData['type'] === 'income')
                                <option value="rent">Rent</option>
                                <option value="management_fee">Management Fee</option>
                                <option value="security_deposit">Security Deposit</option>
                                <option value="late_fee">Late Fee</option>
                                <option value="other_income">Other Income</option>
                            @else
                                <option value="maintenance">Maintenance</option>
                                <option value="utilities">Utilities</option>
                                <option value="insurance">Insurance</option>
                                <option value="property_tax">Property Tax</option>
                                <option value="marketing">Marketing</option>
                                <option value="administrative">Administrative</option>
                                <option value="other_expense">Other Expense</option>
                            @endif
                        </select>
                        @error('transactionData.category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                        <input type="date" wire:model="transactionData.date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        @error('transactionData.date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea wire:model="transactionData.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
                        @error('transactionData.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                        <select wire:model="transactionData.payment_method" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="">Select payment method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="cheque">Cheque</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                        @error('transactionData.payment_method') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            wire:click="$set('showAddTransactionModal', false)"
                            class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-md"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md"
                        >
                            Add Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
