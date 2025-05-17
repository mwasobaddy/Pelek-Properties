@php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Services\RentalPropertyService;
use App\Services\CommercialPropertyService;
use Carbon\Carbon;

new #[Layout('components.layouts.app')] class extends Component {
    public $dateRange = 'year';
    public $propertyType = 'all';
    
    protected $rentalPropertyService;
    protected $commercialPropertyService;

    public function mount(RentalPropertyService $rentalService, CommercialPropertyService $commercialService)  
    {
        $this->authorize('generate_occupancy_reports');
        $this->rentalPropertyService = $rentalService;
        $this->commercialPropertyService = $commercialService;
    }

    #[computed]
    public function occupancyData()
    {
        $data = ['rental' => [], 'commercial' => []];

        // Get data based on selected time range
        $startDate = match($this->dateRange) {
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->subYear()
        };

        if ($this->propertyType === 'rental' || $this->propertyType === 'all') {
            $rentalProperties = \App\Models\Property::where('listing_type', 'rent')
                ->with(['tenantInfo', 'maintenanceRecords'])
                ->get();

            foreach ($rentalProperties as $property) {
                $data['rental'][] = [
                    'property' => $property,
                    'occupancy_rate' => $this->rentalPropertyService->getPropertyAnalytics($property)['occupancy_rate'],
                    'current_tenant' => $property->tenantInfo ? [
                        'name' => $property->tenantInfo->tenant_name,
                        'lease_end' => Carbon::parse($property->tenantInfo->lease_end)
                    ] : null
                ];
            }
        }

        if ($this->propertyType === 'commercial' || $this->propertyType === 'all') {
            $commercialProperties = \App\Models\Property::where('listing_type', 'commercial')
                ->with(['leaseHistory'])
                ->get();

            foreach ($commercialProperties as $property) {
                $data['commercial'][] = [
                    'property' => $property,
                    'occupancy_rate' => $this->commercialPropertyService->calculateOccupancyRate($property),
                    'current_lease' => $property->leaseHistory()
                        ->whereDate('start_date', '<=', now())
                        ->whereDate('end_date', '>=', now())
                        ->first()
                ];
            }
        }

        return $data;
    }
}
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold">Occupancy Reports</h2>
                    <div class="flex space-x-4">
                        <select wire:model.live="dateRange" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                            <option value="month">This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                        </select>
                        <select wire:model.live="propertyType" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                            <option value="all">All Properties</option>
                            <option value="rental">Rental Properties</option>
                            <option value="commercial">Commercial Properties</option>
                        </select>
                    </div>
                </div>

                @if($this->occupancyData['rental'])
                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-4">Rental Properties</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Property</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Occupancy Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Tenant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lease End Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($this->occupancyData['rental'] as $rental)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $rental['property']->title }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $rental['property']->property_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-100 text-green-800' => $rental['occupancy_rate'] >= 80,
                                            'bg-yellow-100 text-yellow-800' => $rental['occupancy_rate'] >= 50 && $rental['occupancy_rate'] < 80,
                                            'bg-red-100 text-red-800' => $rental['occupancy_rate'] < 50,
                                        ])>
                                            {{ number_format($rental['occupancy_rate'], 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $rental['current_tenant'] ? $rental['current_tenant']['name'] : 'Vacant' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $rental['current_tenant'] ? $rental['current_tenant']['lease_end']->format('M d, Y') : '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No rental properties found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if($this->occupancyData['commercial'])
                <div>
                    <h3 class="text-lg font-medium mb-4">Commercial Properties</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Property</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Occupancy Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Lease</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lease End Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($this->occupancyData['commercial'] as $commercial)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $commercial['property']->title }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $commercial['property']->property_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span @class([
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-100 text-green-800' => $commercial['occupancy_rate'] >= 80,
                                            'bg-yellow-100 text-yellow-800' => $commercial['occupancy_rate'] >= 50 && $commercial['occupancy_rate'] < 80,
                                            'bg-red-100 text-red-800' => $commercial['occupancy_rate'] < 50,
                                        ])>
                                            {{ number_format($commercial['occupancy_rate'], 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $commercial['current_lease'] ? 'Active Lease' : 'Vacant' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $commercial['current_lease'] ? Carbon::parse($commercial['current_lease']->end_date)->format('M d, Y') : '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No commercial properties found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
