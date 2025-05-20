<?php
use App\Models\Property;
use App\Models\PropertyType;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[State]
    public $showFilters = false;
    
    #[State]
    public $showFormModal = false;
    
    #[State]
    public $showDeleteModal = false;
    
    #[State]
    public $modalMode = 'create'; // create, edit, view
    
    #[State]
    public $search = '';
    
    #[State]
    public $filters = [
        'status' => '',
        'property_type' => '',
        'price_range' => '',
    ];
    
    #[State]
    public $selectedProperty = null;
    
    #[State]
    public $sortField = 'created_at';
    
    #[State]
    public $sortDirection = 'desc';
    
    #[State]
    public $isLoading = false;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'filters' => ['except' => ['status' => '', 'property_type' => '', 'price_range' => '']]
    ];

    #[State]
    public $form = [
        'title' => '',
        'property_type_id' => '',
        'price' => '',
        'description' => '',
        'status' => 'active'
    ];

    public function with(): array
    {
        $this->isLoading = true;
        
        try {
            $query = Property::with(['propertyType', 'images'])
                ->when($this->search, function($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                })
                ->when($this->filters['status'], fn($query) => 
                    $query->where('status', $this->filters['status'])
                )
                ->when($this->filters['property_type'], fn($query) => 
                    $query->where('property_type_id', $this->filters['property_type'])
                )
                ->when($this->filters['price_range'], function($query) {
                    [$min, $max] = explode('-', $this->filters['price_range']);
                    return $query->whereBetween('price', [$min, $max]);
                });
                
            // Handle the sorting
            if ($this->sortField === 'property_type') {
                $query->join('property_types', 'properties.property_type_id', '=', 'property_types.id')
                    ->select('properties.*')
                    ->orderBy('property_types.name', $this->sortDirection);
            } else {
                $query->orderBy($this->sortField, $this->sortDirection);
            }
            
            return [
                'properties' => $query->paginate(9),
                'propertyTypes' => PropertyType::pluck('name', 'id'),
                'priceRanges' => [
                    '' => 'All Prices',
                    '0-1000000' => 'Under 1M',
                    '1000000-5000000' => '1M - 5M',
                    '5000000-10000000' => '5M - 10M',
                    '10000000-999999999' => 'Over 10M'
                ]
            ];
        } finally {
            $this->isLoading = false;
        }
    }

    public function sort($field): void 
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters(): void
    {
        $this->reset('filters');
    }

    public function rules()
    {
        return [
            'form.title' => 'required|string|max:255',
            'form.property_type_id' => 'required|exists:property_types,id',
            'form.price' => 'required|numeric|min:0',
            'form.description' => 'required|string',
            'form.status' => 'required|in:active,inactive'
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showFormModal = true;
    }

    public function edit($id)
    {
        $this->selectedProperty = Property::findOrFail($id);
        $this->form = $this->selectedProperty->only(['title', 'property_type_id', 'price', 'description', 'status']);
        $this->modalMode = 'edit';
        $this->showFormModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->modalMode === 'create') {
            Property::create($this->form);
            $this->dispatch('notify', type: 'success', message: 'Property created successfully.');
        } else {
            $this->selectedProperty->update($this->form);
            $this->dispatch('notify', type: 'success', message: 'Property updated successfully.');
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->selectedProperty = Property::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->selectedProperty->delete();
        $this->showDeleteModal = false;
        $this->selectedProperty = null;
        $this->dispatch('notify', type: 'success', message: 'Property deleted successfully.');
    }

    private function resetForm()
    {
        $this->form = [
            'title' => '',
            'property_type_id' => '',
            'price' => '',
            'description' => '',
            'status' => 'active'
        ];
        $this->selectedProperty = null;
    }
} ?>

<div class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl shadow-xl overflow-hidden relative">
    <!-- Header Section with Gradient -->
    <div class="h-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b]"></div>
    
    <div class="p-8 border-b border-gray-200 dark:border-gray-700">
        <!-- Animated Header -->
        <div class="sm:flex sm:items-center sm:justify-between" 
             x-data="{}"
             x-intersect="$el.classList.add('animate-fade-in')">
            <div class="space-y-2">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <flux:icon name="building-office-2" class="w-8 h-8 text-[#02c9c2]" />
                    Property Portfolio
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Manage and oversee all property listings
                </p>
            </div>
            
            <button 
                wire:click="create"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                wire:loading.attr="disabled"
            >
                <flux:icon wire:loading.remove name="plus" class="w-5 h-5 mr-2" />
                <flux:icon wire:loading name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                Add New Property
            </button>
        </div>

        <!-- Enhanced Search and Filters with Animation -->
        <div class="mt-8 space-y-4" 
             x-data="{}"
             x-intersect="$el.classList.add('animate-fade-in')">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <flux:icon wire:loading.remove wire:target="search" name="magnifying-glass" class="h-5 w-5 text-gray-400" />
                        <flux:icon wire:loading wire:target="search" name="arrow-path" class="h-5 w-5 text-[#02c9c2] animate-spin" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="search"
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-[#02c9c2] focus:ring-[#02c9c2] sm:text-sm"
                           placeholder="Search properties...">
                </div>

                <!-- Filter Toggle Button -->
                <button
                    wire:click="toggleFilters"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-sm"
                >
                    <flux:icon name="funnel" class="w-5 h-5 mr-2" />
                    Filters
                    <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full px-2 py-0.5">
                        {{ array_filter($filters) ? count(array_filter($filters)) : '0' }}
                    </span>
                </button>
            </div>

            <!-- Filters Panel -->
            <div x-show="$wire.showFilters"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 backdrop-blur-xl shadow-sm space-y-4"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select wire:model.live="filters.status"
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 focus:outline-none focus:ring-[#02c9c2] focus:border-[#02c9c2] sm:text-sm rounded-md bg-white dark:bg-gray-800">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Property Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property Type</label>
                        <select wire:model.live="filters.property_type"
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 focus:outline-none focus:ring-[#02c9c2] focus:border-[#02c9c2] sm:text-sm rounded-md bg-white dark:bg-gray-800">
                            <option value="">All Types</option>
                            @foreach($propertyTypes as $id => $type)
                                <option value="{{ $id }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Price Range Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price Range</label>
                        <select wire:model.live="filters.price_range"
                                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 focus:outline-none focus:ring-[#02c9c2] focus:border-[#02c9c2] sm:text-sm rounded-md bg-white dark:bg-gray-800">
                            @foreach($priceRanges as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Filter Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="resetFilters"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2]">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="p-8">
        <!-- Property Grid with Modern Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($properties as $property)
                <div class="bg-white dark:bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-lg group">
                    <!-- Property Image with Hover Effects -->
                    <div class="relative h-52 overflow-hidden">
                        @if($property->images->isNotEmpty())
                            <img 
                                src="{{ asset('storage/' . $property->images->first()->image_path) }}" 
                                alt="{{ $property->title }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            >
                            <!-- Modern Gradient Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 flex items-center justify-center">
                                <flux:icon name="photo" class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                            </div>
                        @endif
                        
                        <!-- Enhanced Property Type Badge -->
                        <div class="absolute top-4 left-4 z-10">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/90 dark:bg-gray-900/90 text-gray-700 dark:text-gray-300 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50">
                                <flux:icon name="home" class="w-3 h-3 mr-1" />
                                {{ $property->propertyType->name }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Enhanced Property Details -->
                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-1">
                                {{ $property->title }}
                            </h3>
                            <div class="flex justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center text-[#02c9c2] font-bold text-xl">
                                        KES {{ number_format($property->price) }}
                                    </span>
                                    <!-- Add Property Status Badge -->
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                        Active
                                    </span>
                                </div>
                                <!-- Dropdown Menu -->
                                <div class="relative" x-data="{ open: false }">
                                    <button 
                                        @click="open = !open"
                                        class="p-2 text-gray-500 hover:text-[#02c9c2] hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-150"
                                    >
                                        <flux:icon name="ellipsis-vertical" class="w-5 h-5" />
                                    </button>
                                    
                                    <!-- Dropdown Content -->
                                    <div 
                                        x-show="open" 
                                        @click.away="open = false"
                                        x-transition
                                        class="absolute right-0 mt-2 w-48 rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-gray-200 dark:ring-gray-700 py-1 z-10"
                                    >
                                        <a href="#" wire:click.prevent="edit({{ $property->id }})" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <flux:icon name="pencil" class="w-4 h-4 mr-2" />
                                            Edit Details
                                        </a>
                                        <a href="#" wire:click.prevent="confirmDelete({{ $property->id }})" class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <flux:icon name="trash" class="w-4 h-4 mr-2" />
                                            Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Action Buttons -->
                        <div class="flex items-center gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <a 
                                href="{{ route('admin.properties.photos', $property) }}" 
                                wire:navigate
                                class="flex-1 inline-flex justify-center items-center px-4 py-2 text-sm font-medium rounded-lg bg-[#02c9c2]/10 text-[#02c9c2] hover:bg-[#02c9c2]/20 transition-colors duration-150"
                            >
                                <flux:icon name="photo" class="w-4 h-4 mr-2" />
                                Manage Photos
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Enhanced Empty State -->
                <div class="col-span-full py-16 flex flex-col items-center justify-center text-center px-4">
                    <div class="h-24 w-24 rounded-full bg-gradient-to-br from-[#02c9c2]/20 to-[#012e2b]/20 flex items-center justify-center mb-6">
                        <flux:icon name="building-office" class="w-12 h-12 text-[#02c9c2]" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No properties listed</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mb-6">
                        Get started by adding your first property listing to the portfolio.
                    </p>
                    <a 
                        href="{{ route('admin.properties.manage') }}" 
                        wire:navigate
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 transition-all duration-150 shadow-lg"
                    >
                        <flux:icon name="plus" class="w-5 h-5 mr-2" />
                        Add Property
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Loading Overlay -->
        <div wire:loading.delay class="fixed inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
            <div class="flex items-center space-x-4">
                <flux:icon name="arrow-path" class="w-8 h-8 text-[#02c9c2] animate-spin" />
                <span class="text-gray-600 dark:text-gray-300 font-medium">Loading properties...</span>
            </div>
        </div>

        <!-- Pagination -->
        @if($properties->hasPages())
            <div class="mt-6">
                {{ $properties->links() }}
            </div>
        @endif
    </div>

    <!-- Property Form Modal -->
    <flux:modal wire:model="showFormModal" class="w-full max-w-4xl !p-0" @close="$wire.resetForm()">
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
            <div class="bg-gradient-to-r from-[#02c9c2]/20 to-[#012e2b]/20 dark:from-[#02c9c2]/30 dark:to-[#012e2b]/30 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="{{ $modalMode === 'create' ? 'plus' : 'pencil' }}" class="w-5 h-5 text-[#02c9c2]" />
                        {{ $modalMode === 'create' ? 'New Property' : ($modalMode === 'edit' ? 'Edit Property' : 'View Property') }}
                    </h3>
                </div>
            </div>

            <div class="p-6">
                <form wire:submit="save" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Title -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property Title</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="building-office" class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="text"
                                    wire:model="form.title"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                    placeholder="Enter property title"
                                >
                                @error('form.title')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Property Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property Type</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="home" class="h-5 w-5 text-gray-400" />
                                </div>
                                <select
                                    wire:model="form.property_type_id"
                                    id="property_type_id"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                >
                                    <option value="">Select Type</option>
                                    @foreach($propertyTypes as $id => $type)
                                        <option value="{{ $id }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('form.property_type_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price (KES)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="number"
                                    wire:model="form.price"
                                    id="price"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                    placeholder="Enter property price (KES)"
                                    min="0"
                                >
                                @error('form.price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <div class="relative">
                                    <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                                    <flux:icon name="document-text" class="h-5 w-5 text-gray-400" />
                                </div>
                                <textarea
                                    wire:model="form.description"
                                    id="description"
                                    rows="4"
                                rows="4"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm">
                                </textarea>
                                @error('form.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" wire:click="$set('showFormModal', false)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900">
                            Cancel
                        </button>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-md text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150">
                            {{ $modalMode === 'create' ? 'Create Property' : 'Update Property' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal wire:model="showDeleteModal" max-width="md" class="!p-0">
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
            <div class="bg-gradient-to-r from-red-500/20 to-red-600/20 dark:from-red-900/30 dark:to-red-700/30 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-circle" class="w-6 h-6 text-red-600" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Confirm Deletion
                    </h3>
                </div>
            </div>

            <div class="p-6">
                <p class="text-gray-600 dark:text-gray-300">
                    Are you sure you want to delete this property? This action cannot be undone.
                </p>
                @if($selectedProperty)
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $selectedProperty->title }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Price: KES {{ number_format($selectedProperty->price) }}</p>
                        @if($selectedProperty->propertyType)
                            <p class="text-sm text-gray-600 dark:text-gray-400">Type: {{ $selectedProperty->propertyType->name }}</p>
                        @endif
                    </div>
                @endif

                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900">
                        Cancel
                    </button>
                    <button type="button" wire:click="delete"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900">
                        <flux:icon name="trash" class="w-4 h-4 mr-1.5" />
                        Delete Property
                    </button>
                </div>
            </div>
        </div>
    </flux:modal>

    <!-- Enhanced Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10"></div>
</div>
