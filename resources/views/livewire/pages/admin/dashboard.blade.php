<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state};
use App\Services\PropertyManagementService;
use App\Services\MaintenanceService;
use App\Services\FinancialService;
use App\Models\Property;
use App\Models\ManagementContract;
use App\Models\MaintenanceRecord;
use Livewire\Attributes\Computed;

new class extends Component {
    /** @var \Illuminate\Database\Eloquent\Collection */
    public $properties;
    public $maintenanceRequests;
    public $recentTransactions;
    public int $activeContracts = 0;
    public int $pendingMaintenance = 0;
    public float $monthlyRevenue = 0.0;
    public ?Property $selectedProperty = null;
    public bool $loading = false;

    public function mount(): void
    {
        abort_if(!auth()->user()->can('manage_all_properties'), 403);
        
        $propertyService = app(PropertyManagementService::class);
        $financialService = app(FinancialService::class);
        
        $this->properties = $propertyService->getManagedProperties();
        $this->maintenanceRequests = MaintenanceRecord::where('status', '!=', 'completed')
            ->with('property')
            ->latest()
            ->get();
        $this->recentTransactions = $financialService->getRecentTransactions();
        $this->activeContracts = ManagementContract::where('status', 'active')->count();
        $this->pendingMaintenance = MaintenanceRecord::where('status', 'pending')->count();
        $this->monthlyRevenue = $financialService->getCurrentMonthRevenue();
    }

    public function selectProperty(Property $property): void
    {
        $this->selectedProperty = $property;
    }

    #[Computed]
    public function formattedRevenue(): string
    {
        return number_format($this->monthlyRevenue, 2);
    }

    #[Computed]
    public function propertiesCount(): int
    {
        return count($this->properties);
    }

    #[Computed]
    public function urgentMaintenanceCount(): int
    {
        return MaintenanceRecord::where('priority', 'high')->where('status', 'pending')->count();
    }
    
    #[Computed]
    public function occupancyRate(): int
    {
        // Calculate the occupancy rate based on your properties
        $totalProperties = $this->properties->count();
        if ($totalProperties === 0) {
            return 0;
        }
        $occupiedProperties = $this->properties->where('status', 'occupied')->count();
        return (int) round(($occupiedProperties / $totalProperties) * 100);
    }
};

?>

<div
    class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl shadow-xl overflow-hidden relative">
    <!-- Header Section with Gradient -->
    <div class="h-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b]"></div>
    <div class="p-8 border-b border-gray-200 dark:border-gray-700">
        <!-- Animated Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div class="space-y-2">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    Property Management Dashboard
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Manage your properties, maintenance requests, and financial reports
                </p>
            </div>
            <button wire:click="$refresh"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                wire:loading.attr="disabled">
                <flux:icon wire:loading.remove wire:target="$refresh" name="arrow-path" class="w-5 h-5 mr-2" />
                <flux:icon wire:loading wire:target="$refresh" name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="p-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">
            <!-- Properties Card -->
            <div
                class="bg-white/50 dark:bg-gray-800/50 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 backdrop-blur-xl p-4 transition-all duration-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Properties</p>
                        {{-- use db: to count total properties --}}
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{  \App\Models\Property::count() }}
                        </h3>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-[#02c9c2] to-[#012e2b] rounded-lg">
                        <flux:icon name="building-office-2" class="w-6 h-6 text-white" />
                    </div>
                </div>
            </div>

            <!-- Revenue Card -->
            <div
                class="bg-white/50 dark:bg-gray-800/50 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 backdrop-blur-xl p-4 transition-all duration-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Monthly Revenue</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">KES
                            {{ number_format($monthlyRevenue, 2) }}</h3>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-[#02c9c2] to-[#012e2b] rounded-lg">
                        <flux:icon name="banknotes" class="w-6 h-6 text-white" />
                    </div>
                </div>
            </div>

            <!-- Maintenance Card -->
            <div
                class="bg-white/50 dark:bg-gray-800/50 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 backdrop-blur-xl p-4 transition-all duration-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Maintenance</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $pendingMaintenance }}</h3>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-[#02c9c2] to-[#012e2b] rounded-lg">
                        <flux:icon name="wrench-screwdriver" class="w-6 h-6 text-white" />
                    </div>
                </div>
            </div>

            <!-- Occupancy Card -->
            <div
                class="bg-white/50 dark:bg-gray-800/50 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 backdrop-blur-xl p-4 transition-all duration-200 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Occupancy Rate</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $this->occupancyRate }}%
                        </h3>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-[#02c9c2] to-[#012e2b] rounded-lg">
                        <flux:icon name="chart-bar" class="w-6 h-6 text-white" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links Section -->
    <div class="px-8 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Links</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <!-- Add Property -->
            <a href="{{ route('admin.properties.index') }}" 
                class="flex flex-col items-center justify-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md hover:border-[#02c9c2] group">
                <div class="p-3 bg-[#02c9c2]/10 rounded-full mb-2 group-hover:bg-[#02c9c2]/20 transition-colors">
                    <flux:icon name="plus" class="w-5 h-5 text-[#02c9c2]" />
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Add Property</span>
            </a>
            
            <!-- New Maintenance -->
            <a href="{{ route('management.maintenance') }}" 
                class="flex flex-col items-center justify-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md hover:border-[#02c9c2] group">
                <div class="p-3 bg-[#02c9c2]/10 rounded-full mb-2 group-hover:bg-[#02c9c2]/20 transition-colors">
                    <flux:icon name="wrench-screwdriver" class="w-5 h-5 text-[#02c9c2]" />
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Maintenance</span>
            </a>
            
            <!-- New Contract -->
            <a href="{{ route('management.contracts') }}" 
                class="flex flex-col items-center justify-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md hover:border-[#02c9c2] group">
                <div class="p-3 bg-[#02c9c2]/10 rounded-full mb-2 group-hover:bg-[#02c9c2]/20 transition-colors">
                    <flux:icon name="document-text" class="w-5 h-5 text-[#02c9c2]" />
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">New Contract</span>
            </a>
            
            <!-- Record Payment -->
            <a href="{{ route('management.financials') }}" 
                class="flex flex-col items-center justify-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md hover:border-[#02c9c2] group">
                <div class="p-3 bg-[#02c9c2]/10 rounded-full mb-2 group-hover:bg-[#02c9c2]/20 transition-colors">
                    <flux:icon name="banknotes" class="w-5 h-5 text-[#02c9c2]" />
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Record Payment</span>
            </a>
            
            <!-- Reports -->
            <a
                {{-- href="{{ route('management.reports') }}"  --}}
                class="flex flex-col items-center justify-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md hover:border-[#02c9c2] group">
                <div class="p-3 bg-[#02c9c2]/10 rounded-full mb-2 group-hover:bg-[#02c9c2]/20 transition-colors">
                    <flux:icon name="chart-bar" class="w-5 h-5 text-[#02c9c2]" />
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Reports</span>
            </a>
            
            <!-- Settings -->
            <a href="{{ route('settings.profile') }}" 
                class="flex flex-col items-center justify-center p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md hover:border-[#02c9c2] group">
                <div class="p-3 bg-[#02c9c2]/10 rounded-full mb-2 group-hover:bg-[#02c9c2]/20 transition-colors">
                    <flux:icon name="cog-6-tooth" class="w-5 h-5 text-[#02c9c2]" />
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Settings</span>
            </a>
        </div>
    </div>

    <!-- Dashboard Overview Section -->
    <div class="px-8 pb-4">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Dashboard Overview</h3>
    </div>

    <!-- Content Section -->
    <div class="p-8 pt-0">
        <div wire:loading.flex class="items-center justify-center p-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#02c9c2]"></div>
        </div>

        <div wire:loading.remove>
            <!-- Overview Content -->
            <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
                <!-- Quick Stats -->
                <div
                    class="bg-white/50 dark:bg-gray-800/50 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 backdrop-blur-xl p-4">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Stats</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-2 rounded-lg bg-green-100 dark:bg-green-800/30 mr-3">
                                    <flux:icon name="currency-dollar"
                                        class="w-5 h-5 text-green-600 dark:text-green-400" />
                                </div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Total Revenue YTD</span>
                            </div>
                            <span class="font-semibold text-gray-900 dark:text-white">KES
                                {{ number_format($monthlyRevenue * 12 * 0.85, 2) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-2 rounded-lg bg-red-100 dark:bg-red-800/30 mr-3">
                                    <flux:icon name="exclamation-triangle"
                                        class="w-5 h-5 text-red-600 dark:text-red-400" />
                                </div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Urgent Maintenance</span>
                            </div>
                            <span
                                class="font-semibold text-gray-900 dark:text-white">{{ $this->urgentMaintenanceCount }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-800/30 mr-3">
                                    <flux:icon name="document-text"
                                        class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Active Contracts</span>
                            </div>
                            <span
                                class="font-semibold text-gray-900 dark:text-white">{{ $activeContracts }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="p-2 rounded-lg bg-yellow-100 dark:bg-yellow-800/30 mr-3">
                                    <flux:icon name="home"
                                        class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                                </div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Vacant Properties</span>
                            </div>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ $this->properties->where('status', '!=', 'occupied')->count() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div
                    class="bg-white/50 dark:bg-gray-800/50 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 backdrop-blur-xl p-4">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h4>
                    <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
                        <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="p-1.5 rounded-full bg-green-100 dark:bg-green-800/30">
                                <flux:icon name="banknotes" class="w-4 h-4 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Payment Received</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Oak Apartments - KES 45,000</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Today at 10:23 AM</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="p-1.5 rounded-full bg-blue-100 dark:bg-blue-800/30">
                                <flux:icon name="document-check"
                                    class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Contract Renewed</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Sunset View Apartments - Unit
                                    3B</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Yesterday at 2:45 PM</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="p-1.5 rounded-full bg-amber-100 dark:bg-amber-800/30">
                                <flux:icon name="wrench" class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Maintenance Completed
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pine Ridge Homes - Plumbing
                                    Issue</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Yesterday at 11:20 AM</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="p-1.5 rounded-full bg-red-100 dark:bg-red-800/30">
                                <flux:icon name="exclamation-circle"
                                    class="w-4 h-4 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">New Maintenance
                                    Request</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Maple Court - Electrical Issue
                                    (High Priority)</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">2 days ago at 9:15 AM</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="p-1.5 rounded-full bg-purple-100 dark:bg-purple-800/30">
                                <flux:icon name="user" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">New Tenant</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Cedar Heights - Unit 12A</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">3 days ago at 4:30 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>