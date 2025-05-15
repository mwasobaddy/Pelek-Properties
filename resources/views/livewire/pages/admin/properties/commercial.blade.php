<?php

use App\Models\Property;
use App\Models\CommercialLease;
use App\Models\MaintenanceRecord;
use App\Services\CommercialPropertyService;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed, mount};

new class extends Component {
    #[Rule(['required', 'exists:properties,id'])]
    public $selectedPropertyId = '';

    #[Rule([
        'leaseData.tenant_name' => 'required|string|max:255',
        'leaseData.tenant_business' => 'required|string|max:255',
        'leaseData.tenant_contact' => 'required|string|max:255',
        'leaseData.start_date' => 'required|date',
        'leaseData.end_date' => 'required|date|after:leaseData.start_date',
        'leaseData.monthly_rate' => 'required|numeric|min:0',
        'leaseData.security_deposit' => 'required|numeric|min:0',
        'leaseData.lease_type' => 'required|in:net,gross,modified_gross',
        'leaseData.terms_conditions' => 'required|array',
        'leaseData.notes' => 'nullable|string',
    ])]
    public $leaseData = [
        'tenant_name' => '',
        'tenant_business' => '',
        'tenant_contact' => '',
        'start_date' => '',
        'end_date' => '',
        'monthly_rate' => '',
        'security_deposit' => '',
        'lease_type' => 'net',
        'terms_conditions' => [],
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

    public $showLeaseModal = false;
    public $showMaintenanceModal = false;
    public $editingLeaseId = null;
    public $editingMaintenanceId = null;
    
    protected $commercialPropertyService;

    public function mount(CommercialPropertyService $commercialPropertyService)
    {
        $this->commercialPropertyService = $commercialPropertyService;
    }

    #[computed]
    public function properties()
    {
        return $this->commercialPropertyService->getAdminCommercialProperties();
    }

    #[computed]
    public function selectedProperty()
    {
        if (!$this->selectedPropertyId) {
            return null;
        }

        return Property::with(['facilities', 'maintenanceRecords', 'leaseHistory'])
            ->findOrFail($this->selectedPropertyId);
    }

    #[computed]
    public function facilityAnalytics()
    {
        if (!$this->selectedProperty) {
            return null;
        }

        return $this->commercialPropertyService->getFacilityAnalytics($this->selectedProperty);
    }

    #[computed]
    public function leaseAnalytics()
    {
        if (!$this->selectedProperty) {
            return null;
        }

        return $this->commercialPropertyService->getLeaseAnalytics($this->selectedProperty);
    }

    public function saveLease()
    {
        $this->validate();

        // Calculate duration in months
        $startDate = Carbon::parse($this->leaseData['start_date']);
        $endDate = Carbon::parse($this->leaseData['end_date']);
        $durationMonths = $startDate->diffInMonths($endDate);

        if ($this->editingLeaseId) {
            $lease = CommercialLease::findOrFail($this->editingLeaseId);
            $lease->update([
                ...$this->leaseData,
                'duration_months' => $durationMonths,
            ]);
        } else {
            $this->selectedProperty->leaseHistory()->create([
                ...$this->leaseData,
                'duration_months' => $durationMonths,
            ]);
        }

        $this->showLeaseModal = false;
        $this->resetLeaseForm();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Lease information saved successfully'
        ]);
    }

    public function saveMaintenance()
    {
        $this->validate();

        if ($this->editingMaintenanceId) {
            $record = MaintenanceRecord::findOrFail($this->editingMaintenanceId);
            $record->update($this->maintenanceData);
        } else {
            $this->selectedProperty->maintenanceRecords()->create($this->maintenanceData);
        }

        $this->showMaintenanceModal = false;
        $this->resetMaintenanceForm();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Maintenance record saved successfully'
        ]);
    }

    public function editLease($leaseId)
    {
        $lease = CommercialLease::findOrFail($leaseId);
        $this->leaseData = [
            'tenant_name' => $lease->tenant_name,
            'tenant_business' => $lease->tenant_business,
            'tenant_contact' => $lease->tenant_contact,
            'start_date' => $lease->start_date->format('Y-m-d'),
            'end_date' => $lease->end_date->format('Y-m-d'),
            'monthly_rate' => $lease->monthly_rate,
            'security_deposit' => $lease->security_deposit,
            'lease_type' => $lease->lease_type,
            'terms_conditions' => $lease->terms_conditions,
            'notes' => $lease->notes,
        ];
        $this->editingLeaseId = $leaseId;
        $this->showLeaseModal = true;
    }

    public function editMaintenance($recordId)
    {
        $record = MaintenanceRecord::findOrFail($recordId);
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

    public function updateMaintenanceStatus($recordId, $status)
    {
        $record = MaintenanceRecord::findOrFail($recordId);
        $record->update([
            'status' => $status,
            'completed_date' => $status === 'completed' ? now() : null,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Maintenance status updated successfully'
        ]);
    }

    private function resetLeaseForm()
    {
        $this->leaseData = [
            'tenant_name' => '',
            'tenant_business' => '',
            'tenant_contact' => '',
            'start_date' => '',
            'end_date' => '',
            'monthly_rate' => '',
            'security_deposit' => '',
            'lease_type' => 'net',
            'terms_conditions' => [],
            'notes' => '',
        ];
        $this->editingLeaseId = null;
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

<div class="p-4 sm:p-6 lg:p-8 bg-white dark:bg-gray-900">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Commercial Property Management</h1>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                Manage commercial properties, leases, and maintenance records.
            </p>
        </div>
    </div>

    <!-- Property Selection -->
    <div class="mt-6">
        <label for="property-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Property</label>
        <select
            wire:model.live="selectedPropertyId"
            id="property-select"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        >
            <option value="">Select a property</option>
            @foreach($this->properties as $property)
                <option value="{{ $property->id }}">{{ $property->name }} - {{ $property->address }}</option>
            @endforeach
        </select>
    </div>

    @if($selectedProperty)
        <!-- Property Overview -->
        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Property Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Property Details</h3>
                    <dl class="mt-2 divide-y divide-gray-200 dark:divide-gray-700">
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedProperty->status }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $selectedProperty->type->name }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Area</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($selectedProperty->total_area) }} sq ft</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Lease Analytics Card -->
            @if($this->leaseAnalytics)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Lease Analytics</h3>
                    <dl class="mt-2 divide-y divide-gray-200 dark:divide-gray-700">
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Occupancy Rate</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ number_format($this->leaseAnalytics['occupancy_rate'], 1) }}%</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Leases</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $this->leaseAnalytics['active_leases'] }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Revenue</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($this->leaseAnalytics['monthly_revenue']) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
            @endif

            <!-- Facility Analytics Card -->
            @if($this->facilityAnalytics)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Facility Analytics</h3>
                    <dl class="mt-2 divide-y divide-gray-200 dark:divide-gray-700">
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Facilities</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $this->facilityAnalytics['total_facilities'] }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Maintenance</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $this->facilityAnalytics['pending_maintenance'] }}</dd>
                        </div>
                        <div class="py-3 flex justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Maintenance Cost</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($this->facilityAnalytics['monthly_maintenance_cost']) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex space-x-4">
            <button
                wire:click="$set('showLeaseModal', true)"
                type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900"
            >
                <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                New Lease
            </button>

            <button
                wire:click="$set('showMaintenanceModal', true)"
                type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900"
            >
                <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                </svg>
                New Maintenance Record
            </button>
        </div>

        <!-- Lease Records -->
        @if($selectedProperty->leaseHistory->isNotEmpty())
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Lease Records</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Business</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monthly Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($selectedProperty->leaseHistory as $lease)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $lease->tenant_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $lease->tenant_business }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $lease->start_date->format('M d, Y') }} - {{ $lease->end_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${{ number_format($lease->monthly_rate) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $lease->end_date->isFuture() ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}">
                                    {{ $lease->end_date->isFuture() ? 'Active' : 'Expired' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    wire:click="editLease({{ $lease->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                >
                                    Edit
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Maintenance Records -->
        @if($selectedProperty->maintenanceRecords->isNotEmpty())
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Maintenance Records</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Issue Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cost</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($selectedProperty->maintenanceRecords as $record)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $record->issue_type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ match($record->priority) {
                                        'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                        'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    } }}">
                                    {{ ucfirst($record->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $record->requested_by }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <select
                                    wire:change="updateMaintenanceStatus({{ $record->id }}, $event.target.value)"
                                    class="block w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="pending" {{ $record->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ $record->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $record->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${{ number_format($record->cost ?? 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    wire:click="editMaintenance({{ $record->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                >
                                    Edit
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @endif

    <!-- Lease Modal -->
    <div>
        <x-modal wire:model="showLeaseModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ $editingLeaseId ? 'Edit Lease' : 'New Lease' }}
                </h2>
                <div class="mt-6 space-y-6">
                    <div>
                        <x-label for="tenant_name" value="Tenant Name" />
                        <x-input wire:model="leaseData.tenant_name" id="tenant_name" type="text" class="mt-1 block w-full" />
                        <x-input-error for="leaseData.tenant_name" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="tenant_business" value="Business Name" />
                        <x-input wire:model="leaseData.tenant_business" id="tenant_business" type="text" class="mt-1 block w-full" />
                        <x-input-error for="leaseData.tenant_business" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="tenant_contact" value="Contact Information" />
                        <x-input wire:model="leaseData.tenant_contact" id="tenant_contact" type="text" class="mt-1 block w-full" />
                        <x-input-error for="leaseData.tenant_contact" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <x-label for="start_date" value="Start Date" />
                            <x-input wire:model="leaseData.start_date" id="start_date" type="date" class="mt-1 block w-full" />
                            <x-input-error for="leaseData.start_date" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="end_date" value="End Date" />
                            <x-input wire:model="leaseData.end_date" id="end_date" type="date" class="mt-1 block w-full" />
                            <x-input-error for="leaseData.end_date" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <x-label for="monthly_rate" value="Monthly Rate ($)" />
                            <x-input wire:model="leaseData.monthly_rate" id="monthly_rate" type="number" min="0" step="0.01" class="mt-1 block w-full" />
                            <x-input-error for="leaseData.monthly_rate" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="security_deposit" value="Security Deposit ($)" />
                            <x-input wire:model="leaseData.security_deposit" id="security_deposit" type="number" min="0" step="0.01" class="mt-1 block w-full" />
                            <x-input-error for="leaseData.security_deposit" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-label for="lease_type" value="Lease Type" />
                        <select
                            wire:model="leaseData.lease_type"
                            id="lease_type"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="net">Net Lease</option>
                            <option value="gross">Gross Lease</option>
                            <option value="modified_gross">Modified Gross Lease</option>
                        </select>
                        <x-input-error for="leaseData.lease_type" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="notes" value="Notes" />
                        <x-textarea wire:model="leaseData.notes" id="notes" class="mt-1 block w-full" rows="3"></x-textarea>
                        <x-input-error for="leaseData.notes" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-4">
                    <x-secondary-button wire:click="$set('showLeaseModal', false)">
                        Cancel
                    </x-secondary-button>
                    <x-button wire:click="saveLease">
                        Save
                    </x-button>
                </div>
            </div>
        </x-modal>
    </div>

    <!-- Maintenance Modal -->
    <div>
        <x-modal wire:model="showMaintenanceModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ $editingMaintenanceId ? 'Edit Maintenance Record' : 'New Maintenance Record' }}
                </h2>
                <div class="mt-6 space-y-6">
                    <div>
                        <x-label for="issue_type" value="Issue Type" />
                        <x-input wire:model="maintenanceData.issue_type" id="issue_type" type="text" class="mt-1 block w-full" />
                        <x-input-error for="maintenanceData.issue_type" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="description" value="Description" />
                        <x-textarea wire:model="maintenanceData.description" id="description" class="mt-1 block w-full" rows="3"></x-textarea>
                        <x-input-error for="maintenanceData.description" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="priority" value="Priority" />
                        <select
                            wire:model="maintenanceData.priority"
                            id="priority"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <x-input-error for="maintenanceData.priority" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="requested_by" value="Requested By" />
                        <x-input wire:model="maintenanceData.requested_by" id="requested_by" type="text" class="mt-1 block w-full" />
                        <x-input-error for="maintenanceData.requested_by" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <x-label for="scheduled_date" value="Scheduled Date" />
                            <x-input wire:model="maintenanceData.scheduled_date" id="scheduled_date" type="date" class="mt-1 block w-full" />
                            <x-input-error for="maintenanceData.scheduled_date" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="cost" value="Cost ($)" />
                            <x-input wire:model="maintenanceData.cost" id="cost" type="number" min="0" step="0.01" class="mt-1 block w-full" />
                            <x-input-error for="maintenanceData.cost" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-label for="maintenance_notes" value="Notes" />
                        <x-textarea wire:model="maintenanceData.notes" id="maintenance_notes" class="mt-1 block w-full" rows="3"></x-textarea>
                        <x-input-error for="maintenanceData.notes" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-4">
                    <x-secondary-button wire:click="$set('showMaintenanceModal', false)">
                        Cancel
                    </x-secondary-button>
                    <x-button wire:click="saveMaintenance">
                        Save
                    </x-button>
                </div>
            </div>
        </x-modal>
    </div>
</div>
