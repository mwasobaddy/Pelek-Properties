<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state};
use App\Models\MaintenanceRecord;
use App\Services\MaintenanceService;

new class extends Component {
    public $records;
    public $statusFilter = 'all';

    public function mount(MaintenanceService $maintenanceService): void 
    {
        $this->loadRecords();
    }

    public function loadRecords(): void
    {
        $query = MaintenanceRecord::with(['property', 'reportedBy'])
            ->latest();
            
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $this->records = $query->get();
    }

    public function filterByStatus(string $status): void
    {
        $this->statusFilter = $status;
        $this->loadRecords();
    }
}; ?>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Maintenance Management</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Track and manage property maintenance requests</p>
    </div>

    <div class="p-6">
        <!-- Status Filter -->
        <div class="mb-6">
            <div class="flex space-x-4">
                <button 
                    wire:click="filterByStatus('all')"
                    class="{{ $statusFilter === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md text-sm font-medium"
                >
                    All
                </button>
                <button 
                    wire:click="filterByStatus('pending')"
                    class="{{ $statusFilter === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md text-sm font-medium"
                >
                    Pending
                </button>
                <button 
                    wire:click="filterByStatus('in_progress')"
                    class="{{ $statusFilter === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md text-sm font-medium"
                >
                    In Progress
                </button>
                <button 
                    wire:click="filterByStatus('completed')"
                    class="{{ $statusFilter === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }} px-4 py-2 rounded-md text-sm font-medium"
                >
                    Completed
                </button>
            </div>
        </div>

        <!-- Maintenance Records Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Property</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Issue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reported On</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($records as $record)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $record->property->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $record->issue_description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $record->priority === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300' }}">
                                    {{ ucfirst($record->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $record->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : '' }}
                                    {{ $record->status === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : '' }}
                                    {{ $record->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $record->created_at->format('M d, Y') }}
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
