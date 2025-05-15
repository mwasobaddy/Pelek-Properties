<?php

use function Livewire\Volt\{state, computed};
use App\Services\PropertyManagementService;
use App\Services\MaintenanceService;
use App\Services\FinancialService;
use App\Models\Property;
use App\Models\ManagementContract;
use App\Models\MaintenanceRecord;

state([
    'properties' => fn() => [],
    'activeContracts' => fn() => 0,
    'pendingMaintenance' => fn() => 0,
    'monthlyRevenue' => fn() => 0.00,
    'selectedProperty' => null,
    'tab' => 'overview',
    'loading' => false,
]);

$mount = function (
    PropertyManagementService $propertyService,
    MaintenanceService $maintenanceService,
    FinancialService $financialService
) {
    abort_if(!auth()->user()->can('manage_properties'), 403);
    
    $this->properties = $propertyService->getManagedProperties();
    $this->activeContracts = ManagementContract::where('status', 'active')->count();
    $this->pendingMaintenance = MaintenanceRecord::where('status', 'pending')->count();
    $this->monthlyRevenue = $financialService->getCurrentMonthRevenue();
};

$selectProperty = function (Property $property) {
    $this->selectedProperty = $property;
};

$changeTab = function (string $tab) {
    $this->tab = $tab;
};

computed([
    'formattedRevenue' => fn() => number_format($this->monthlyRevenue, 2),
    'propertiesCount' => fn() => count($this->properties),
    'urgentMaintenanceCount' => fn() => MaintenanceRecord::where('priority', 'high')
        ->where('status', 'pending')
        ->count(),
]);

?>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl">
    <!-- Dashboard Header -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Property Management Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage your properties, maintenance requests, and financial reports</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-6">
        <!-- Properties Card -->
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Managed Properties</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $propertiesCount }}</p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Contracts Card -->
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Contracts</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $activeContracts }}</p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Maintenance Card -->
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Maintenance</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pendingMaintenance }}</p>
                    @if($urgentMaintenanceCount > 0)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                            {{ $urgentMaintenanceCount }} Urgent
                        </span>
                    @endif
                </div>
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue Card -->
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Monthly Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">KES {{ $this->formattedRevenue }}</p>
                </div>
                <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="flex -mb-px px-6" aria-label="Tabs">
            <button
                wire:click="changeTab('overview')"
                class="{{ $tab === 'overview' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400' }} flex-1 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm hover:text-gray-700 hover:border-gray-300 focus:outline-none"
            >
                Overview
            </button>
            <button
                wire:click="changeTab('maintenance')"
                class="{{ $tab === 'maintenance' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400' }} flex-1 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm hover:text-gray-700 hover:border-gray-300 focus:outline-none"
            >
                Maintenance
            </button>
            <button
                wire:click="changeTab('financials')"
                class="{{ $tab === 'financials' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400' }} flex-1 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm hover:text-gray-700 hover:border-gray-300 focus:outline-none"
            >
                Financials
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="p-6">
        @if($tab === 'overview')
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Managed Properties</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($properties as $property)
                        <div 
                            wire:key="property-{{ $property->id }}"
                            class="bg-white dark:bg-gray-700 rounded-lg shadow hover:shadow-md transition-shadow duration-200 cursor-pointer"
                            wire:click="selectProperty({{ $property->id }})"
                        >
                            <img 
                                src="{{ $property->featured_image_url }}" 
                                alt="{{ $property->title }}"
                                class="w-full h-48 object-cover rounded-t-lg"
                            >
                            <div class="p-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">{{ $property->title }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $property->location }}</p>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        Contract Status: 
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $property->management_contract?->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' }}">
                                            {{ ucfirst($property->management_contract?->status ?? 'No Contract') }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($tab === 'maintenance')
            <livewire:maintenance-request-form />
        @else
            <livewire:financial-reports />
        @endif
    </div>
</div>
