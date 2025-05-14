<?php

use App\Services\PropertySearchService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.guest')] class extends Component {
    use WithPagination;

    protected $queryString = [
        'search' => ['except' => ''],
        'propertyType' => ['except' => ''],
        'priceRange' => ['except' => ''],
        'onlyAvailable' => ['except' => false],
    ];

    public $priceRange;
    public $search = '';
    public $propertyType = null;
    public $onlyAvailable = false;
    
    public function mount()
    {
        // No need to store properties in mount
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->propertyType = null;
        $this->priceRange = null;
        $this->onlyAvailable = false;
    }

    public function with(): array
    {
        $min_price = null;
        $max_price = null;
        $priceRange = $this->priceRange;

        if ($priceRange !== null && $priceRange !== '') {
            $range = explode('-', (string) $priceRange);
            if (!empty($range[0])) {
                $min_price = (int) $range[0];
            }
            if (!empty($range[1])) {
                $max_price = (int) $range[1];
            }
        }

        return [
            'properties' => app(PropertySearchService::class)->search([
                'search' => $this->search,
                'property_type_id' => $this->propertyType,
                'min_price' => $min_price,
                'max_price' => $max_price,
                'status' => $this->onlyAvailable ? 'available' : null,
            ])
        ];
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Search and Filters -->
    <div class="mb-8 space-y-4">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input 
                    wire:model.live="search" 
                    type="text" 
                    placeholder="Search properties..." 
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                >
            </div>
            <div class="flex gap-4">
                <button 
                    wire:click="resetFilters"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800"
                >
                    Clear Filters
                </button>
            </div>
        </div>
        
        <div class="flex flex-wrap gap-4">
            <label class="flex items-center">
                <input 
                    wire:model.live="onlyAvailable" 
                    type="checkbox" 
                    class="form-checkbox h-5 w-5 text-indigo-600"
                >
                <span class="ml-2">Show only available</span>
            </label>
        </div>
    </div>

    <!-- Properties Grid -->
    <div wire:loading.delay.class="opacity-50">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($properties as $property)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Property Card -->
                    <livewire:components.property-card :property="$property" />
                </div>
            @empty
                <div class="col-span-1 md:col-span-2 lg:col-span-3 xl:col-span-4">
                    <p class="text-center text-gray-500">No properties found.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $properties->links() }}
        </div>
    </div>
</div>
