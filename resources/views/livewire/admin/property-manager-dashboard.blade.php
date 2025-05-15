<?php

use App\Services\PropertyManagementService;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed, mount};

new class extends Component {
    public ?string $selectedPropertyId = null;
    
    public $showContractModal = false;
    public $showMaintenanceModal = false;
    public $showLeaseModal = false;
    
    #[Rule([
        'contractData.contract_type' => 'required|string|in:full_service,maintenance_only,financial_only',
        'contractData.management_fee_percentage' => 'required|numeric|min:0|max:100',
        'contractData.base_fee' => 'nullable|numeric|min:0',
        'contractData.start_date' => 'required|date',
        'contractData.end_date' => 'required|date|after:contractData.start_date',
        'contractData.payment_schedule' => 'required|in:monthly,quarterly,yearly',
        'contractData.services_included' => 'required|array',
        'contractData.special_terms' => 'nullable|string',
    ])]
    public array $contractData = [
        'contract_type' => '',
        'management_fee_percentage' => '',
        'base_fee' => '',
        'start_date' => '',
        'end_date' => '',
        'payment_schedule' => 'monthly',
        'services_included' => [],
        'special_terms' => '',
    ];
    
    #[Rule([
        'maintenanceData.issue_type' => 'required|string|max:255',
        'maintenanceData.description' => 'required|string',
        'maintenanceData.priority' => 'required|in:low,medium,high,urgent',
        'maintenanceData.requested_by' => 'required|string|max:255',
        'maintenanceData.scheduled_date' => 'nullable|date',
        'maintenanceData.cost' => 'nullable|numeric|min:0',
        'maintenanceData.notes' => 'nullable|string',
    ])]
    public array $maintenanceData = [
        'issue_type' => '',
        'description' => '',
        'priority' => 'medium',
        'requested_by' => '',
        'scheduled_date' => '',
        'cost' => '',
        'notes' => '',
    ];
    
    protected $propertyManagementService;
    protected $rentalPropertyService;
    protected $commercialPropertyService;

    public function mount(
        PropertyManagementService $propertyManagementService,
        RentalPropertyService $rentalService,
        CommercialPropertyService $commercialService
    ) {
        $this->authorize('manage-properties');
        $this->propertyManagementService = $propertyManagementService;
        $this->rentalPropertyService = $rentalService;
        $this->commercialPropertyService = $commercialService;
    }

    #[computed]
    public function summary()
    {
        return $this->propertyManagementService->getManagedPropertiesSummary();
    }
    
    #[computed]
    public function recentMaintenanceRequests()
    {
        return \App\Models\MaintenanceRecord::with('property')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }
    
    #[computed]
    public function upcomingLeasesEnding()
    {
        return \App\Models\TenantInfo::with('property')
            ->whereDate('lease_end', '>=', now())
            ->orderBy('lease_end')
            ->limit(5)
            ->get();
    }

    #[computed]
    public function selectedProperty()
    {
        if (!$this->selectedPropertyId) {
            return null;
        }

        return Property::with(['managementContracts', 'financialRecords', 'maintenanceRecords', 'tenantInfo'])
            ->findOrFail($this->selectedPropertyId);
    }

    #[computed]
    public function analytics()
    {
        if (!$this->selectedProperty) {
            return null;
        }

        return $this->propertyManagementService->getContractAnalytics($this->selectedProperty);
    }
    
    #[computed]
    public function properties()
    {
        // Get all managed properties
        return Property::whereHas('managementContracts', function($query) {
            $query->where('status', 'active');
        })->get();
    }

    public function createContract()
    {
        $this->validate();

        $this->propertyManagementService->createContract(
            $this->selectedProperty,
            $this->contractData
        );

        $this->showContractModal = false;
        $this->resetContractForm();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Management contract created successfully'
        ]);
    }

    public function updateContractStatus($contractId, $status)
    {
        $contract = ManagementContract::findOrFail($contractId);
        $this->propertyManagementService->updateContractStatus($contract, $status);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Contract status updated successfully'
        ]);
    }
    
    public function createMaintenanceRecord()
    {
        $this->validate();
        
        $this->propertyManagementService->createMaintenanceRecord(
            $this->selectedProperty,
            $this->maintenanceData
        );
        
        $this->showMaintenanceModal = false;
        $this->resetMaintenanceForm();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Maintenance record created successfully'
        ]);
    }

    private function resetContractForm()
    {
        $this->contractData = [
            'contract_type' => '',
            'management_fee_percentage' => '',
            'base_fee' => '',
            'start_date' => '',
            'end_date' => '',
            'payment_schedule' => 'monthly',
            'services_included' => [],
            'special_terms' => '',
        ];
    }
    
    private function resetMaintenanceForm()
    {
        $this->maintenanceData = [
            'issue_type' => '',
            'description' => '',
            'priority' => 'medium',
            'requested_by' => '',
            'scheduled_date' => '',
            'cost' => '',
            'notes' => '',
        ];
    }
} ?>

<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Total Properties</h3>
            <div class="text-3xl font-bold text-primary-600">
                {{ $this->summary['total_properties'] }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Total Revenue</h3>
            <div class="text-3xl font-bold text-primary-600">
                KES {{ number_format($this->summary['total_revenue'], 2) }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Total Expenses</h3>
            <div class="text-3xl font-bold text-primary-600">
                KES {{ number_format($this->summary['total_expenses'], 2) }}
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Pending Maintenance</h3>
            <div class="text-3xl font-bold text-primary-600">
                {{ $this->summary['maintenance_stats']['pending_requests'] }}
            </div>
        </div>
    </div>

    <!-- Property Selection -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Property Management</h2>
            <button
                wire:click="$set('showContractModal', true)"
                @disabled(!$selectedPropertyId)
                class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md disabled:opacity-50"
            >
                Create Contract
            </button>
        </div>

        <select wire:model.live="selectedPropertyId" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            <option value="">Select a property</option>
            @foreach($this->properties as $property)
                <option value="{{ $property->id }}">{{ $property->title }}</option>
            @endforeach
        </select>
    </div>

    @if($this->selectedProperty && $this->analytics)
        <!-- Contract Details -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium mb-6 text-gray-900 dark:text-white">Contract Details</h3>

            @if($this->analytics['has_active_contract'])
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Fee Structure</p>
                        <div class="mt-2">
                            <p class="text-gray-900 dark:text-white">
                                Base Fee: KES {{ number_format($this->analytics['current_fee_structure']['base_fee'] ?? 0, 2) }}
                            </p>
                            <p class="text-gray-900 dark:text-white">
                                Management Fee: {{ $this->analytics['current_fee_structure']['percentage'] }}%
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon name="document-text" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Active Contract</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a new management contract to get started.</p>
                </div>
            @endif

            @if($this->analytics['contract_history']->isNotEmpty())
                <div class="mt-8">
                    <h4 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Contract History</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($this->analytics['contract_history'] as $contract)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ str_replace('_', ' ', ucfirst($contract['type'])) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $contract['start_date'] }} - {{ $contract['end_date'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span @class([
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                'bg-green-100 text-green-800' => $contract['status'] === 'active',
                                                'bg-yellow-100 text-yellow-800' => $contract['status'] === 'pending',
                                                'bg-gray-100 text-gray-800' => $contract['status'] === 'expired',
                                                'bg-red-100 text-red-800' => $contract['status'] === 'terminated',
                                            ])>
                                                {{ ucfirst($contract['status']) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                wire:click="updateContractStatus({{ $contract['id'] }}, 'terminated')"
                                                @class([
                                                    'text-red-600 hover:text-red-900',
                                                    'hidden' => $contract['status'] !== 'active'
                                                ])
                                            >
                                                Terminate
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Maintenance Requests -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Maintenance Requests</h3>
                <button
                    wire:click="$set('showMaintenanceModal', true)"
                    class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md"
                >
                    New Request
                </button>
            </div>

            @if($this->selectedProperty->maintenanceRecords->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Issue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scheduled</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->selectedProperty->maintenanceRecords->sortByDesc('created_at') as $record)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $record->issue_type }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($record->description, 50) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            'bg-red-100 text-red-800' => $record->priority === 'urgent',
                                            'bg-yellow-100 text-yellow-800' => $record->priority === 'high',
                                            'bg-blue-100 text-blue-800' => $record->priority === 'medium',
                                            'bg-gray-100 text-gray-800' => $record->priority === 'low',
                                        ])>
                                            {{ ucfirst($record->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select
                                            wire:change="updateMaintenanceStatus({{ $record->id }}, $event.target.value)"
                                            class="block w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="pending" {{ $record->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="scheduled" {{ $record->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="in_progress" {{ $record->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="completed" {{ $record->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $record->scheduled_date?->format('M d, Y') ?? 'Not scheduled' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            wire:click="editMaintenanceRecord({{ $record->id }})"
                                            class="text-primary-600 hover:text-primary-900"
                                        >
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon name="wrench" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Maintenance Records</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a new maintenance request to get started.</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Contract Modal -->
    <div x-data="{ show: @entangle('showContractModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl shadow-xl">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Create Management Contract
                    </h3>
                </div>

                <form wire:submit.prevent="createContract" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contract Type</label>
                            <select wire:model="contractData.contract_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">Select type</option>
                                <option value="full_service">Full Service</option>
                                <option value="maintenance_only">Maintenance Only</option>
                                <option value="financial_only">Financial Only</option>
                            </select>
                            @error('contractData.contract_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Management Fee (%)</label>
                            <input type="number" wire:model="contractData.management_fee_percentage" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('contractData.management_fee_percentage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Base Fee (Optional)</label>
                            <input type="number" wire:model="contractData.base_fee" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('contractData.base_fee') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Schedule</label>
                            <select wire:model="contractData.payment_schedule" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                            @error('contractData.payment_schedule') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                            <input type="date" wire:model="contractData.start_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('contractData.start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                            <input type="date" wire:model="contractData.end_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('contractData.end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Special Terms</label>
                            <textarea wire:model="contractData.special_terms" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
                            @error('contractData.special_terms') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Services Included</label>
                            <div class="space-y-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" wire:model="contractData.services_included" value="tenant_management" class="rounded border-gray-300 text-primary-600 dark:border-gray-700 dark:bg-gray-900">
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">Tenant Management</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" wire:model="contractData.services_included" value="maintenance" class="rounded border-gray-300 text-primary-600 dark:border-gray-700 dark:bg-gray-900">
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">Maintenance</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" wire:model="contractData.services_included" value="financial_reporting" class="rounded border-gray-300 text-primary-600 dark:border-gray-700 dark:bg-gray-900">
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">Financial Reporting</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" wire:model="contractData.services_included" value="marketing" class="rounded border-gray-300 text-primary-600 dark:border-gray-700 dark:bg-gray-900">
                                    <span class="ml-2 text-gray-700 dark:text-gray-300">Marketing</span>
                                </label>
                            </div>
                            @error('contractData.services_included') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            wire:click="$set('showContractModal', false)"
                            class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-md"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md"
                        >
                            Create Contract
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Maintenance Request Modal -->
    <div x-data="{ show: @entangle('showMaintenanceModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl shadow-xl">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $editingMaintenanceId ? 'Edit Maintenance Request' : 'New Maintenance Request' }}
                    </h3>
                </div>

                <form wire:submit.prevent="saveMaintenance" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Issue Type</label>
                            <input type="text" wire:model="maintenanceData.issue_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('maintenanceData.issue_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                            <select wire:model="maintenanceData.priority" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            @error('maintenanceData.priority') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea wire:model="maintenanceData.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
                            @error('maintenanceData.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Scheduled Date</label>
                            <input type="date" wire:model="maintenanceData.scheduled_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('maintenanceData.scheduled_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Cost (KES)</label>
                            <input type="number" wire:model="maintenanceData.cost" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('maintenanceData.cost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <textarea wire:model="maintenanceData.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
                            @error('maintenanceData.notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            wire:click="$set('showMaintenanceModal', false)"
                            class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-md"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md"
                        >
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
