<?php

use App\Models\Property;
use App\Models\MaintenanceRecord;
use App\Services\RentalPropertyService;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed, mount};

new class extends Component {
    #[Rule(['required', 'exists:properties,id'])]
    public $selectedPropertyId = '';

    #[Rule([
        'tenantData.name' => 'required|string|max:255',
        'tenantData.phone' => 'nullable|string|max:255',
        'tenantData.email' => 'nullable|email|max:255',
        'tenantData.lease_start' => 'required|date',
        'tenantData.lease_end' => 'required|date|after:tenantData.lease_start',
        'tenantData.monthly_rent' => 'required|numeric|min:0',
        'tenantData.security_deposit' => 'required|numeric|min:0',
        'tenantData.payment_status' => 'nullable|string|in:pending,paid,late,defaulted',
        'tenantData.notes' => 'nullable|string',
    ])]
    public $tenantData = [
        'name' => '',
        'phone' => '',
        'email' => '',
        'lease_start' => '',
        'lease_end' => '',
        'monthly_rent' => '',
        'security_deposit' => '',
        'payment_status' => 'pending',
        'notes' => '',
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
    public $maintenanceData = [
        'issue_type' => '',
        'description' => '',
        'priority' => 'medium',
        'requested_by' => '',
        'scheduled_date' => '',
        'cost' => '',
        'notes' => '',
    ];

    public $showTenantModal = false;
    public $showMaintenanceModal = false;
    public $editingMaintenanceId = null;
    
    protected $rentalPropertyService;

    public function mount(RentalPropertyService $rentalPropertyService)
    {
        $this->rentalPropertyService = $rentalPropertyService;
    }

    #[computed]
    public function properties()
    {
        return $this->rentalPropertyService->getAdminRentalProperties();
    }

    #[computed]
    public function selectedProperty()
    {
        if (!$this->selectedPropertyId) {
            return null;
        }

        return Property::with(['tenantInfo', 'maintenanceRecords'])
            ->findOrFail($this->selectedPropertyId);
    }

    #[computed]
    public function analytics()
    {
        if (!$this->selectedProperty) {
            return null;
        }

        return $this->rentalPropertyService->getPropertyAnalytics($this->selectedProperty);
    }

    public function updateAvailability($isAvailable)
    {
        $this->rentalPropertyService->updateAvailabilityStatus(
            $this->selectedProperty,
            $isAvailable
        );

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Property availability updated successfully'
        ]);
    }

    public function saveTenant()
    {
        $this->validate();

        $this->rentalPropertyService->updateTenantInfo(
            $this->selectedProperty,
            $this->tenantData
        );

        $this->showTenantModal = false;
        $this->resetTenantForm();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Tenant information saved successfully'
        ]);
    }

    public function saveMaintenanceRecord()
    {
        $this->validate();

        $this->rentalPropertyService->recordMaintenanceRequest(
            $this->selectedProperty,
            $this->maintenanceData
        );

        $this->showMaintenanceModal = false;
        $this->resetMaintenanceForm();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Maintenance record saved successfully'
        ]);
    }

    public function updateMaintenanceStatus($recordId, $status)
    {
        $this->rentalPropertyService->updateMaintenanceStatus(
            $recordId,
            $status,
            $status === 'completed' ? now() : null
        );

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Maintenance status updated successfully'
        ]);
    }

    public function editTenant()
    {
        $tenant = $this->selectedProperty->tenantInfo;
        if ($tenant) {
            $this->tenantData = [
                'name' => $tenant->tenant_name,
                'phone' => $tenant->tenant_phone,
                'email' => $tenant->tenant_email,
                'lease_start' => $tenant->lease_start->format('Y-m-d'),
                'lease_end' => $tenant->lease_end->format('Y-m-d'),
                'monthly_rent' => $tenant->monthly_rent,
                'security_deposit' => $tenant->security_deposit,
                'payment_status' => $tenant->payment_status,
                'notes' => $tenant->notes,
            ];
            $this->showTenantModal = true;
        }
    }

    public function editMaintenanceRecord($recordId)
    {
        $record = MaintenanceRecord::find($recordId);
        if ($record) {
            $this->maintenanceData = [
                'issue_type' => $record->issue_type,
                'description' => $record->description,
                'priority' => $record->priority,
                'requested_by' => $record->requested_by,
                'scheduled_date' => $record->scheduled_date?->format('Y-m-d'),
                'cost' => $record->cost,
                'notes' => $record->notes,
            ];
            $this->editingMaintenanceId = $recordId;
            $this->showMaintenanceModal = true;
        }
    }

    private function resetTenantForm()
    {
        $this->tenantData = [
            'name' => '',
            'phone' => '',
            'email' => '',
            'lease_start' => '',
            'lease_end' => '',
            'monthly_rent' => '',
            'security_deposit' => '',
            'payment_status' => 'pending',
            'notes' => '',
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
        $this->editingMaintenanceId = null;
    }
} ?>

<div class="space-y-6">
    <!-- Property Selection -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Manage Rental Properties</h2>
        
        <select wire:model.live="selectedPropertyId" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            <option value="">Select a property</option>
            @foreach($this->properties as $property)
                <option value="{{ $property->id }}">{{ $property->title }}</option>
            @endforeach
        </select>
    </div>

    @if($this->selectedProperty)
        <!-- Property Details & Analytics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Analytics Cards -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Occupancy Rate</h3>
                <div class="text-3xl font-bold text-primary-600">
                    {{ number_format($this->analytics['occupancy_rate'], 1) }}%
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Maintenance Costs</h3>
                <div class="text-3xl font-bold text-primary-600">
                    KES {{ number_format($this->analytics['total_maintenance_cost'], 2) }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-white">Pending Maintenance</h3>
                <div class="text-3xl font-bold text-primary-600">
                    {{ $this->analytics['pending_maintenance'] }}
                </div>
            </div>
        </div>

        <!-- Tenant Information -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tenant Information</h3>
                <button 
                    wire:click="$set('showTenantModal', true)"
                    class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md"
                >
                    {{ $this->selectedProperty->tenantInfo ? 'Update Tenant' : 'Add Tenant' }}
                </button>
            </div>

            @if($this->selectedProperty->tenantInfo)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tenant Name</p>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $this->selectedProperty->tenantInfo->tenant_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact</p>
                        <p class="mt-1 text-gray-900 dark:text-white">
                            {{ $this->selectedProperty->tenantInfo->tenant_phone }}<br>
                            {{ $this->selectedProperty->tenantInfo->tenant_email }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Lease Period</p>
                        <p class="mt-1 text-gray-900 dark:text-white">
                            {{ $this->selectedProperty->tenantInfo->lease_start->format('M d, Y') }} -
                            {{ $this->selectedProperty->tenantInfo->lease_end->format('M d, Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Status</p>
                        <p @class([
                            'mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                            'bg-green-100 text-green-800' => $this->selectedProperty->tenantInfo->payment_status === 'paid',
                            'bg-yellow-100 text-yellow-800' => $this->selectedProperty->tenantInfo->payment_status === 'pending',
                            'bg-red-100 text-red-800' => in_array($this->selectedProperty->tenantInfo->payment_status, ['late', 'defaulted']),
                        ])>
                            {{ ucfirst($this->selectedProperty->tenantInfo->payment_status) }}
                        </p>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <flux:icon name="users" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Tenant Information</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add tenant information to start tracking occupancy.</p>
                </div>
            @endif
        </div>

        <!-- Maintenance Records -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Maintenance Records</h3>
                <button
                    wire:click="$set('showMaintenanceModal', true)"
                    class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md"
                >
                    Add Maintenance Record
                </button>
            </div>

            @if($this->selectedProperty->maintenanceRecords->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Issue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scheduled</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cost</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->selectedProperty->maintenanceRecords as $record)
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
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            'bg-gray-100 text-gray-800' => $record->status === 'pending',
                                            'bg-yellow-100 text-yellow-800' => $record->status === 'scheduled',
                                            'bg-blue-100 text-blue-800' => $record->status === 'in_progress',
                                            'bg-green-100 text-green-800' => $record->status === 'completed',
                                            'bg-red-100 text-red-800' => $record->status === 'cancelled',
                                        ])>
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $record->scheduled_date?->format('M d, Y') ?? 'Not scheduled' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $record->cost ? 'KES ' . number_format($record->cost, 2) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            wire:click="editMaintenanceRecord({{ $record->id }})"
                                            class="text-primary-600 hover:text-primary-900 dark:hover:text-primary-400"
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
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Start tracking maintenance requests and repairs.</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Tenant Modal -->
    <div x-data="{ show: @entangle('showTenantModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl shadow-xl">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $this->selectedProperty?->tenantInfo ? 'Update Tenant Information' : 'Add New Tenant' }}
                    </h3>
                </div>

                <form wire:submit.prevent="saveTenant" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tenant Name</label>
                            <input type="text" wire:model="tenantData.name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('tenantData.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                            <input type="text" wire:model="tenantData.phone" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('tenantData.phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" wire:model="tenantData.email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('tenantData.email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Status</label>
                            <select wire:model="tenantData.payment_status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="late">Late</option>
                                <option value="defaulted">Defaulted</option>
                            </select>
                            @error('tenantData.payment_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lease Start</label>
                            <input type="date" wire:model="tenantData.lease_start" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('tenantData.lease_start') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lease End</label>
                            <input type="date" wire:model="tenantData.lease_end" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('tenantData.lease_end') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monthly Rent</label>
                            <input type="number" wire:model="tenantData.monthly_rent" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('tenantData.monthly_rent') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Security Deposit</label>
                            <input type="number" wire:model="tenantData.security_deposit" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('tenantData.security_deposit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                        <textarea wire:model="tenantData.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
                        @error('tenantData.notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            wire:click="$set('showTenantModal', false)"
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

    <!-- Maintenance Modal -->
    <div x-data="{ show: @entangle('showMaintenanceModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl shadow-xl">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $editingMaintenanceId ? 'Edit Maintenance Record' : 'Add Maintenance Record' }}
                    </h3>
                </div>

                <form wire:submit.prevent="saveMaintenanceRecord" class="p-6 space-y-4">
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Requested By</label>
                            <input type="text" wire:model="maintenanceData.requested_by" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('maintenanceData.requested_by') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Scheduled Date</label>
                            <input type="date" wire:model="maintenanceData.scheduled_date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('maintenanceData.scheduled_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost (KES)</label>
                            <input type="number" wire:model="maintenanceData.cost" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            @error('maintenanceData.cost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
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
