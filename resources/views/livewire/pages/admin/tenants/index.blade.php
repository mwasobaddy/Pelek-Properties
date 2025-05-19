<?php

use App\Models\TenantInfo;
use App\Models\Property;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed};
use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    #[State]
    public $search = '';

    #[State]
    public $filters = [
        'status' => '',
        'property_type' => '',
        'lease_type' => '',
    ];

    #[State]
    public $showFilters = false;

    #[State]
    public $showTenantModal = false;

    #[State]
    public $showTenantDetailsModal = false;

    #[State]
    public $modalMode = 'create';

    #[State]
    public $selectedTenant = null;

    #[State]
    public $form = [
        'name' => '',
        'email' => '',
        'phone' => '',
        'emergency_contact' => '',
        'lease_start' => '',
        'lease_end' => '',
        'monthly_rent' => '',
        'security_deposit' => '',
        'property_id' => '',
        'status' => 'active',
        'documents' => [],
        'notes' => '',
    ];

    public function mount()
    {
        $this->authorize('manage_tenants');
    }

    #[Computed]
    public function tenants()
    {
        return TenantInfo::query()
            ->with(['property'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filters['status'], fn($query) => $query->where('status', $this->filters['status']))
            ->when(
                $this->filters['property_type'],
                fn($query) => $query->whereHas('property', function ($q) {
                    $q->where('type', $this->filters['property_type']);
                }),
            )
            ->when($this->filters['lease_type'], fn($query) => $query->where('lease_type', $this->filters['lease_type']))
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function properties()
    {
        return Property::where('status', 'active')->get();
    }

    #[Computed]
    public function tenantsStats()
    {
        $allTenants = TenantInfo::count();
        $activeTenants = TenantInfo::where('status', 'active')->count();
        $pendingTenants = TenantInfo::where('status', 'pending')->count();
        $expiringLeases = TenantInfo::where('status', 'active')
            ->where('lease_end', '<=', Carbon::now()->addDays(30))
            ->count();

        return [
            'all' => $allTenants,
            'active' => $activeTenants,
            'pending' => $pendingTenants,
            'expiring' => $expiringLeases,
        ];
    }

    public function createTenant()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showTenantModal = true;
    }

    public function editTenant($id)
    {
        $this->selectedTenant = TenantInfo::findOrFail($id);
        $this->form = $this->selectedTenant->toArray();
        $this->modalMode = 'edit';
        $this->showTenantModal = true;
    }

    public function viewTenantDetails($id)
    {
        $this->selectedTenant = TenantInfo::with('property')->findOrFail($id);
        $this->showTenantDetailsModal = true;
    }

    public function saveTenant()
    {
        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.email' => 'required|email|max:255',
            'form.phone' => 'required|string|max:20',
            'form.emergency_contact' => 'required|string|max:255',
            'form.lease_start' => 'required|date',
            'form.lease_end' => 'required|date|after:form.lease_start',
            'form.monthly_rent' => 'required|numeric|min:0',
            'form.security_deposit' => 'required|numeric|min:0',
            'form.property_id' => 'required|exists:properties,id',
            'form.status' => 'required|in:active,inactive,pending',
            'form.notes' => 'nullable|string',
        ]);

        if ($this->modalMode === 'create') {
            TenantInfo::create($this->form);
            $this->dispatch('notify', type: 'success', message: 'Tenant created successfully.');
        } else {
            $this->selectedTenant->update($this->form);
            $this->dispatch('notify', type: 'success', message: 'Tenant updated successfully.');
        }

        $this->showTenantModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->form = [
            'name' => '',
            'email' => '',
            'phone' => '',
            'emergency_contact' => '',
            'lease_start' => '',
            'lease_end' => '',
            'monthly_rent' => '',
            'security_deposit' => '',
            'property_id' => '',
            'status' => 'active',
            'documents' => [],
            'notes' => '',
        ];
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filters']);
    }

    public function updateTenantStatus($id, $status)
    {
        $tenant = TenantInfo::findOrFail($id);
        $tenant->update(['status' => $status]);

        $this->dispatch('notify', type: 'success', message: 'Tenant status updated successfully.');

        if ($this->showTenantDetailsModal) {
            $this->showTenantDetailsModal = false;
        }
    }
}; ?>

<div
    class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl shadow-xl overflow-hidden relative">
    <!-- Header Section with Gradient -->
    <div class="h-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b]"></div>

    <div class="p-8 border-b border-gray-200 dark:border-gray-700">
        <!-- Animated Header -->
        <div class="sm:flex sm:items-center sm:justify-between" x-data="{}"
            x-intersect="$el.classList.add('animate-fade-in')">
            <div class="space-y-2">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <flux:icon name="users" class="w-8 h-8 text-[#02c9c2]" />
                    Tenant Management
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Manage all your tenants and lease agreements in one place
                </p>
            </div>

            <button wire:click="createTenant"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                wire:loading.attr="disabled">
                <flux:icon wire:loading.remove name="plus" class="w-5 h-5 mr-2" />
                <flux:icon wire:loading name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                Add New Tenant
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Tenants -->
            <div
                class="bg-white dark:bg-gray-800/50 rounded-xl shadow-sm p-6 border border-gray-200/50 dark:border-gray-700/50 hover:border-[#02c9c2]/50 dark:hover:border-[#02c9c2]/50 transition-colors duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tenants</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ $this->tenantsStats()['all'] }}
                        </p>
                    </div>
                    <div
                        class="h-12 w-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <flux:icon name="users" class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <!-- Active Tenants -->
            <div
                class="bg-white dark:bg-gray-800/50 rounded-xl shadow-sm p-6 border border-gray-200/50 dark:border-gray-700/50 hover:border-[#02c9c2]/50 dark:hover:border-[#02c9c2]/50 transition-colors duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Tenants</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ $this->tenantsStats()['active'] }}
                        </p>
                    </div>
                    <div
                        class="h-12 w-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center text-green-600 dark:text-green-400">
                        <flux:icon name="check-circle" class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <!-- Pending Tenants -->
            <div
                class="bg-white dark:bg-gray-800/50 rounded-xl shadow-sm p-6 border border-gray-200/50 dark:border-gray-700/50 hover:border-[#02c9c2]/50 dark:hover:border-[#02c9c2]/50 transition-colors duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Tenants</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ $this->tenantsStats()['pending'] }}
                        </p>
                    </div>
                    <div
                        class="h-12 w-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                        <flux:icon name="clock" class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <!-- Expiring Leases -->
            <div
                class="bg-white dark:bg-gray-800/50 rounded-xl shadow-sm p-6 border border-gray-200/50 dark:border-gray-700/50 hover:border-[#02c9c2]/50 dark:hover:border-[#02c9c2]/50 transition-colors duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expiring Soon</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ $this->tenantsStats()['expiring'] }}
                        </p>
                    </div>
                    <div
                        class="h-12 w-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center text-red-600 dark:text-red-400">
                        <flux:icon name="calendar" class="w-6 h-6" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="px-8 pb-8">
        <!-- Search and Filters -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <flux:icon wire:loading.remove wire:target="search" name="magnifying-glass"
                            class="h-5 w-5 text-gray-400" />
                        <flux:icon wire:loading wire:target="search" name="arrow-path"
                            class="h-5 w-5 text-[#02c9c2] animate-spin" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="search"
                        placeholder="Search by name, email or phone..."
                        class="block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                </div>

                <!-- Filter Toggle Button -->
                <button wire:click="toggleFilters"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 transition-all duration-150 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900">
                    <flux:icon name="funnel" class="w-5 h-5 mr-2 text-gray-500 dark:text-gray-400" />
                    Filters
                    <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-600 rounded-full px-2 py-0.5">
                        {{ count(array_filter($filters)) }}
                    </span>
                </button>

                <!-- Add Tenant Button (Mobile) -->
                <button wire:click="createTenant"
                    class="inline-flex md:hidden items-center justify-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-sm">
                    <flux:icon name="plus" class="w-5 h-5 mr-2" />
                    Add Tenant
                </button>
            </div>

            <!-- Filters Panel -->
            @if ($showFilters)
                <div x-data="{}" x-init="$el.classList.add('animate-fade-in')"
                    class="mt-4 p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <!-- Status Filter -->
                        <div>
                            <label for="status"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tenant
                                Status</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                                </div>
                                <select wire:model.live="filters.status" id="status"
                                    class="appearance-none block w-full rounded-lg border-0 py-2.5 pl-10 pr-10 bg-white/50 dark:bg-gray-700/50 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                    <flux:icon name="chevron-down" class="h-5 w-5 text-gray-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Property Type Filter -->
                        <div>
                            <label for="property_type"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property
                                Type</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="building-office" class="h-5 w-5 text-gray-400" />
                                </div>
                                <select wire:model.live="filters.property_type" id="property_type"
                                    class="appearance-none block w-full rounded-lg border-0 py-2.5 pl-10 pr-10 bg-white/50 dark:bg-gray-700/50 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">All Property Types</option>
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                    <flux:icon name="chevron-down" class="h-5 w-5 text-gray-400" />
                                </div>
                            </div>
                        </div>

                        <!-- Lease Type Filter -->
                        <div>
                            <label for="lease_type"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lease
                                Type</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="document-text" class="h-5 w-5 text-gray-400" />
                                </div>
                                <select wire:model.live="filters.lease_type" id="lease_type"
                                    class="appearance-none block w-full rounded-lg border-0 py-2.5 pl-10 pr-10 bg-white/50 dark:bg-gray-700/50 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                    <option value="">All Lease Types</option>
                                    <option value="fixed">Fixed Term</option>
                                    <option value="month_to_month">Month to Month</option>
                                    <option value="commercial">Commercial</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                    <flux:icon name="chevron-down" class="h-5 w-5 text-gray-400" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reset Filters Button -->
                    <div class="flex justify-end pt-2">
                        <button wire:click="resetFilters"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] transition-colors duration-150">
                            <flux:icon name="arrow-path" class="w-4 h-4 mr-2" />
                            Reset Filters
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Tenants Table Card -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <th
                                class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tenant</th>
                            <th
                                class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Property</th>
                            <th
                                class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Lease Period</th>
                            <th
                                class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Monthly Rent</th>
                            <th
                                class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($this->tenants() as $tenant)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/60"
                                wire:key="tenant-{{ $tenant->id }}">
                                <td class="px-6 py-4">
                                    <button wire:click="viewTenantDetails({{ $tenant->id }})"
                                        class="flex items-center text-left group">
                                        <div
                                            class="h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mr-3 group-hover:bg-[#02c9c2]/10 dark:group-hover:bg-[#02c9c2]/20 transition-colors duration-200">
                                            <flux:icon name="user"
                                                class="w-5 h-5 text-gray-500 dark:text-gray-400 group-hover:text-[#02c9c2] transition-colors duration-200" />
                                        </div>
                                        <div>
                                            <div
                                                class="font-medium text-gray-900 dark:text-white group-hover:text-[#02c9c2] dark:group-hover:text-[#02c9c2] transition-colors duration-200">
                                                {{ $tenant->name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $tenant->email }}
                                            </div>
                                        </div>
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white font-medium">
                                        {{ $tenant->property->title }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                        <flux:icon name="map-pin" class="w-4 h-4" />
                                        <span class="truncate max-w-[200px]">{{ $tenant->property->location }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $leaseStart = Carbon::parse($tenant->lease_start);
                                        $leaseEnd = Carbon::parse($tenant->lease_end);
                                        $daysLeft = Carbon::now()->diffInDays($leaseEnd, false);
                                        $isExpiringSoon = $daysLeft > 0 && $daysLeft <= 30;
                                        $isExpired = $daysLeft < 0;
                                    @endphp

                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $leaseStart->format('M d, Y') }} - {{ $leaseEnd->format('M d, Y') }}
                                    </div>

                                    @if ($isExpired)
                                        <div
                                            class="text-xs font-medium text-red-600 dark:text-red-400 flex items-center mt-1">
                                            <flux:icon name="exclamation-triangle" class="w-4 h-4 mr-1" />
                                            Expired {{ abs($daysLeft) }} days ago
                                        </div>
                                    @elseif($isExpiringSoon)
                                        <div
                                            class="text-xs font-medium text-amber-600 dark:text-amber-400 flex items-center mt-1">
                                            <flux:icon name="clock" class="w-4 h-4 mr-1" />
                                            Expires in {{ $daysLeft }} days
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $leaseStart->diffForHumans($leaseEnd, ['parts' => 1]) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        KES {{ number_format($tenant->monthly_rent, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Security deposit: KES {{ number_format($tenant->security_deposit, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span @class([
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' =>
                                            $tenant->status === 'active',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' =>
                                            $tenant->status === 'pending',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300' =>
                                            $tenant->status === 'inactive',
                                    ])>
                                        {{ ucfirst($tenant->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-3">
                                        <button wire:click="viewTenantDetails({{ $tenant->id }})"
                                            class="text-gray-400 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150"
                                            title="View Details">
                                            <flux:icon name="eye" class="w-5 h-5" />
                                        </button>
                                        <button wire:click="editTenant({{ $tenant->id }})"
                                            class="text-gray-400 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150"
                                            title="Edit Tenant">
                                            <flux:icon name="pencil-square" class="w-5 h-5" />
                                        </button>
                                        <button
                                            class="text-gray-400 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150"
                                            title="Lease Documents">
                                            <flux:icon name="document-text" class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16">
                                    <div class="text-center">
                                        <div
                                            class="mx-auto h-20 w-20 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                            <flux:icon name="users"
                                                class="h-10 w-10 text-gray-400 dark:text-gray-500" />
                                        </div>
                                        <h3 class="mt-2 text-base font-medium text-gray-900 dark:text-white">No tenants
                                            found</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                                            {{ $search || count(array_filter($filters)) ? 'Try adjusting your search or filter criteria.' : 'Get started by adding a new tenant.' }}
                                        </p>
                                        @if ($search || count(array_filter($filters)))
                                            <button wire:click="resetFilters"
                                                class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2]">
                                                Clear filters
                                            </button>
                                        @else
                                            <button wire:click="createTenant"
                                                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-[#02c9c2] to-[#012e2b] hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2]">
                                                <flux:icon name="plus" class="w-5 h-5 mr-2" />
                                                Add your first tenant
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($this->tenants()->hasPages())
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    {{ $this->tenants()->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Tenant Form Modal -->
    <flux:modal wire:model="showTenantModal" class="w-full max-w-4xl" @close="$wire.resetForm()">
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
            <div
                class="bg-gradient-to-r from-[#02c9c2]/20 to-[#012e2b]/20 dark:from-[#02c9c2]/30 dark:to-[#012e2b]/30 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="{{ $modalMode === 'create' ? 'user-plus' : 'user-circle' }}"
                            class="w-5 h-5 text-[#02c9c2]" />
                        {{ $modalMode === 'create' ? 'Add New Tenant' : 'Edit Tenant Details' }}
                    </h3>
                </div>
            </div>

            <div class="p-6">
                <form wire:submit="saveTenant" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Tenant Information Section -->
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                <h4
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                                    Tenant Information</h4>

                                <div class="space-y-4">
                                    <div>
                                        <label for="name"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full
                                            Name</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <flux:icon name="user" class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <input type="text" wire:model="form.name" id="name"
                                                class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                                placeholder="Enter tenant's full name">
                                        </div>
                                        @error('form.name')
                                            <span
                                                class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="email"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email
                                            Address</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <flux:icon name="envelope" class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <input type="email" wire:model="form.email" id="email"
                                                class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                                placeholder="Email address">
                                        </div>
                                        @error('form.email')
                                            <span
                                                class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="phone"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone
                                            Number</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <flux:icon name="phone" class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <input type="tel" wire:model="form.phone" id="phone"
                                                class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                                placeholder="Phone number">
                                        </div>
                                        @error('form.phone')
                                            <span
                                                class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="emergency_contact"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Emergency
                                            Contact</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <flux:icon name="phone-arrow-up-right"
                                                    class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <input type="text" wire:model="form.emergency_contact"
                                                id="emergency_contact"
                                                class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                                placeholder="Emergency contact details">
                                        </div>
                                        @error('form.emergency_contact')
                                            <span
                                                class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="notes"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Additional
                                    Notes</label>
                                <div class="relative">
                                    <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                                        <flux:icon name="document-text" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <textarea wire:model="form.notes" id="notes" rows="4"
                                        class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Add any additional information about this tenant"></textarea>
                                </div>
                                @error('form.notes')
                                    <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Property and Lease Section -->
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                <h4
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">
                                    Property & Lease Details</h4>

                                <div class="space-y-4">
                                    <div>
                                        <label for="property_id"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <flux:icon name="building-office" class="h-5 w-5 text-gray-400" />
                                            </div>
                                                <select wire:model="form.property_id" id="property_id"
                                                    class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                                    <option value="">Select Property</option>
                                                    @foreach ($this->properties() as $property)
                                                        <option value="{{ $property->id }}">{{ $property->title }}</option>
                                                    @endforeach
                                                </select>
                                            <div
                                                class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <flux:icon name="chevron-down" class="h-5 w-5 text-gray-400" />
                                            </div>
                                        </div>
                                        @error('form.property_id')
                                            <span
                                                class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="status"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tenant
                                            Status</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <select wire:model="form.status" id="status"
                                                class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                                <option value="active">Active</option>
                                                <option value="pending">Pending</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                            <div
                                                class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <flux:icon name="chevron-down" class="h-5 w-5 text-gray-400" />
                                            </div>
                                        </div>
                                        @error('form.status')
                                            <span
                                                class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="lease_start"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lease
                                                Start</label>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <flux:icon name="calendar" class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <input type="date" wire:model="form.lease_start" id="lease_start"
                                                    class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                            </div>
                                            @error('form.lease_start')
                                                <span
                                                    class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="lease_end"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lease
                                                End</label>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <flux:icon name="calendar" class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <input type="date" wire:model="form.lease_end" id="lease_end"
                                                    class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                            </div>
                                            @error('form.lease_end')
                                                <span
                                                    class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="monthly_rent"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monthly
                                                Rent (KES)</label>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <input type="number" wire:model="form.monthly_rent"
                                                    id="monthly_rent"
                                                    class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                                    step="0.01" placeholder="0.00">
                                            </div>
                                            @error('form.monthly_rent')
                                                <span
                                                    class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="security_deposit"
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Security
                                                Deposit (KES)</label>
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                                </div>
                                                <input type="number" wire:model="form.security_deposit"
                                                    id="security_deposit"
                                                    class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                                    step="0.01" placeholder="0.00">
                                            </div>
                                            @error('form.security_deposit')
                                                <span
                                                    class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- File upload section could go here -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                                <div class="flex items-center mb-2">
                                    <flux:icon name="document-duplicate" class="h-5 w-5 text-gray-400 mr-2" />
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Documents</h4>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    Upload lease agreements and other related documents.
                                </p>
                                <button type="button"
                                    class="text-sm text-[#02c9c2] hover:underline flex items-center">
                                    <flux:icon name="arrow-up-tray" class="h-4 w-4 mr-1" />
                                    Add Documents
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" wire:click="$set('showTenantModal', false)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white rounded-lg text-sm font-medium hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] shadow-sm">
                            <flux:icon wire:loading wire:target="saveTenant" name="arrow-path"
                                class="w-4 h-4 mr-2 animate-spin" />
                            {{ $modalMode === 'create' ? 'Create Tenant' : 'Update Tenant' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </flux:modal>

    <!-- Tenant Details Modal -->
    <flux:modal wire:model="showTenantDetailsModal" class="w-full max-w-4xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
            @if ($selectedTenant)
                <div @class([
                    'bg-gradient-to-r px-6 py-4 border-b border-gray-200 dark:border-gray-700',
                    'from-green-500/20 to-green-600/20 dark:from-green-900/30 dark:to-green-700/30' =>
                        $selectedTenant->status === 'active',
                    'from-yellow-500/20 to-yellow-600/20 dark:from-yellow-900/30 dark:to-yellow-700/30' =>
                        $selectedTenant->status === 'pending',
                    'from-gray-500/20 to-gray-600/20 dark:from-gray-900/30 dark:to-gray-700/30' =>
                        $selectedTenant->status === 'inactive',
                ])>
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <flux:icon name="user-circle" class="w-5 h-5 text-[#02c9c2]" />
                            Tenant Details
                        </h3>

                        <span @class([
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                            'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' =>
                                $selectedTenant->status === 'active',
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' =>
                                $selectedTenant->status === 'pending',
                            'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300' =>
                                $selectedTenant->status === 'inactive',
                        ])>
                            {{ ucfirst($selectedTenant->status) }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                        <!-- Tenant Info Column -->
                        <div class="md:col-span-3 space-y-6">
                            <div class="flex items-start space-x-4 pb-5 border-b border-gray-200 dark:border-gray-700">
                                <div
                                    class="h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                    <flux:icon name="user" class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                                </div>
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ $selectedTenant->name }}</h4>
                                    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap gap-y-1 sm:gap-x-4">
                                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                            <flux:icon name="envelope"
                                                class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" />
                                            {{ $selectedTenant->email }}
                                        </div>
                                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                            <flux:icon name="phone"
                                                class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" />
                                            {{ $selectedTenant->phone }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Details -->
                            <div>
                                <h4
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                                    Property Details</h4>
                                <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-4">
                                    <div class="flex items-start space-x-3">
                                        <div
                                            class="h-10 w-10 rounded-md bg-[#02c9c2]/10 flex items-center justify-center flex-shrink-0">
                                            <flux:icon name="building-office" class="h-5 w-5 text-[#02c9c2]" />
                                        </div>
                                        <div>
                                            <h5 class="text-base font-medium text-gray-900 dark:text-white">
                                                {{ $selectedTenant->property->title }}</h5>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                {{ $selectedTenant->property->location }}</p>

                                            <div class="mt-3 grid grid-cols-2 gap-4">
                                                <div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">Property Type
                                                    </p>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ ucfirst($selectedTenant->property->type ?? 'Residential') }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">Property Size
                                                    </p>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $selectedTenant->property->size ?? '1200' }} sq ft
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lease Details -->
                            <div>
                                <h4
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                                    Lease Information</h4>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-4">
                                            <div class="flex items-center mb-1">
                                                <flux:icon name="calendar" class="h-4 w-4 text-gray-400 mr-1" />
                                                <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400">LEASE
                                                    PERIOD</h5>
                                            </div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ Carbon::parse($selectedTenant->lease_start)->format('M d, Y') }} -
                                                {{ Carbon::parse($selectedTenant->lease_end)->format('M d, Y') }}
                                            </p>

                                            @php
                                                $leaseStart = Carbon::parse($selectedTenant->lease_start);
                                                $leaseEnd = Carbon::parse($selectedTenant->lease_end);
                                                $daysLeft = Carbon::now()->diffInDays($leaseEnd, false);
                                                $isExpiringSoon = $daysLeft > 0 && $daysLeft <= 30;
                                                $isExpired = $daysLeft < 0;
                                            @endphp

                                            @if ($isExpired)
                                                <div
                                                    class="text-xs font-medium text-red-600 dark:text-red-400 flex items-center mt-1">
                                                    <flux:icon name="exclamation-triangle" class="w-4 h-4 mr-1" />
                                                    Expired {{ abs($daysLeft) }} days ago
                                                </div>
                                            @elseif($isExpiringSoon)
                                                <div
                                                    class="text-xs font-medium text-amber-600 dark:text-amber-400 flex items-center mt-1">
                                                    <flux:icon name="clock" class="w-4 h-4 mr-1" />
                                                    Expires in {{ $daysLeft }}days
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ $leaseEnd->diffForHumans(['parts' => 1]) }} remaining
                                                </div>
                                            @endif
                                        </div>

                                        <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-4">
                                            <div class="flex items-center mb-1">
                                                <flux:icon name="currency-dollar"
                                                    class="h-4 w-4 text-gray-400 mr-1" />
                                                <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    PAYMENT DETAILS</h5>
                                            </div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                Monthly rent: KES {{ number_format($selectedTenant->monthly_rent, 2) }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Security deposit: KES
                                                {{ number_format($selectedTenant->security_deposit, 2) }}
                                            </p>
                                        </div>
                                    </div>

                                    @if ($selectedTenant->notes)
                                        <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-4">
                                            <div class="flex items-center mb-2">
                                                <flux:icon name="document-text" class="h-4 w-4 text-gray-400 mr-1" />
                                                <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400">NOTES
                                                </h5>
                                            </div>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                                {{ $selectedTenant->notes }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Actions Column -->
                        <div class="md:col-span-2 space-y-5">
                            <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Actions</h4>
                                <div class="space-y-3">
                                    <button wire:click="editTenant({{ $selectedTenant->id }})"
                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                        <flux:icon name="pencil-square" class="h-5 w-5 text-gray-400 mr-2" />
                                        Edit Tenant Details
                                    </button>

                                    @if ($selectedTenant->status === 'active')
                                        <button wire:click="updateTenantStatus({{ $selectedTenant->id }}, 'inactive')"
                                            class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                            <flux:icon name="x-circle" class="h-5 w-5 text-gray-400 mr-2" />
                                            Mark as Inactive
                                        </button>
                                    @elseif($selectedTenant->status === 'inactive')
                                        <button wire:click="updateTenantStatus({{ $selectedTenant->id }}, 'active')"
                                            class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                            <flux:icon name="check-circle" class="h-5 w-5 text-gray-400 mr-2" />
                                            Reactivate Tenant
                                        </button>
                                    @elseif($selectedTenant->status === 'pending')
                                        <button wire:click="updateTenantStatus({{ $selectedTenant->id }}, 'active')"
                                            class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                            <flux:icon name="check-circle" class="h-5 w-5 text-gray-400 mr-2" />
                                            Approve Tenant
                                        </button>
                                    @endif

                                    <button
                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                        <flux:icon name="document-duplicate" class="h-5 w-5 text-gray-400 mr-2" />
                                        View Documents
                                    </button>

                                    <button
                                        class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                        <flux:icon name="printer" class="h-5 w-5 text-gray-400 mr-2" />
                                        Print Tenant Details
                                    </button>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Emergency Contact
                                </h4>
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $selectedTenant->emergency_contact }}
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-800/40 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Documents</h4>
                                    <button class="text-xs text-[#02c9c2] hover:underline">
                                        Upload
                                    </button>
                                </div>

                                @if (count($selectedTenant->documents ?? []) > 0)
                                    <ul class="space-y-2">
                                        @foreach ($selectedTenant->documents as $document)
                                            <li class="flex items-center justify-between text-sm">
                                                <div class="flex items-center">
                                                    <flux:icon name="document" class="h-4 w-4 text-gray-400 mr-2" />
                                                    {{ $document['name'] }}
                                                </div>
                                                <button class="text-gray-400 hover:text-[#02c9c2]">
                                                    <flux:icon name="arrow-down-tray" class="h-4 w-4" />
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        No documents uploaded yet.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-5 mt-6 border-t border-gray-200 dark:border-gray-700">
                        <button wire:click="$set('showTenantDetailsModal', false)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Close
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
