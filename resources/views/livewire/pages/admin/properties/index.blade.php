<?php
use App\Models\Property;
use App\Models\PropertyType;
use App\Services\PropertyService;
use App\Services\PropertyImageService;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination;
    use WithFileUploads;
    
    protected function getListeners()
    {
        return ['notify' => 'handleNotification'];
    }
    
    public function handleNotification($data)
    {
        $this->dispatch('showToast', 
            type: $data['type'] ?? 'success',
            message: $data['message'] ?? 'Operation completed',
            timer: $data['timer'] ?? 3000
        );
    }
    
    public function notifySuccess(string $message, int $timer = 3000): void
    {
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message,
            'timer' => $timer
        ]);
    }
    
    public function notifyError(string $message, int $timer = 5000): void
    {
        $this->dispatch('notify', [
            'type' => 'error',
            'message' => $message,
            'timer' => $timer
        ]);
    }

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
    
    // Image management states
    #[State]
    public $images = [];
    
    #[State]
    public $temporaryImages = [];
    
    #[State]
    public $imageUploads = [];
    
    #[State]
    public $showImageDeleteModal = false;
    
    #[State]
    public $selectedImage = null;

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
        'description' => '',
        'type' => '',
        'listing_type' => 'sale',
        'price' => '',
        'size' => '',
        'square_range' => '',
        'location' => '',
        'city' => '',
        'address' => '',
        'neighborhood' => '',
        'bedrooms' => '',
        'bathrooms' => '',
        'available' => true,
        'whatsapp_number' => '',
        'status' => 'available',
        
        // Commercial fields
        'commercial_type' => '',
        'commercial_size' => '',
        'commercial_price_monthly' => '',
        'commercial_price_annually' => '',
        'floors' => '',
        'maintenance_status' => '',
        'last_renovation' => '',
        'has_parking' => false,
        'parking_spaces' => '',
        'commercial_amenities' => [],
        'zoning_info' => [],
        'price_per_square_foot' => '',
        
        // Rental fields
        'is_furnished' => false,
        'rental_price_daily' => '',
        'rental_price_monthly' => '',
        'security_deposit' => '',
        'lease_terms' => '',
        'utilities_included' => [],
        'available_from' => '',
        'minimum_lease_period' => '',
        'rental_requirements' => [],
        'amenities_condition' => [],
        
        // Airbnb fields
        'airbnb_price_nightly' => '',
        'airbnb_price_weekly' => '',
        'airbnb_price_monthly' => '',
        'availability_calendar' => [],
        
        'is_featured' => false,
        'additional_features' => [],
    ];


    // mount to display the toast if it working
    public function mount(): void
    {
        // $this->dispatch('showToast', [
        //     'type' => 'success',
        //     'message' => 'Component mounted successfully!',
        //     'timer' => 3000
        // ]);
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Property created successfully',
            'timer' => 3000 // optional, defaults to 3000ms
        ]);
    }

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
            
            $minPrice = Property::min('price') ?: 0;
            $maxPrice = Property::max('price') ?: 0;
            
            // Create dynamic range segments
            $rangeStep = ($maxPrice - $minPrice) / 4; // Divide into 4 segments
            $ranges = [
                '' => 'All Prices',
                "{$minPrice}-" . ($minPrice + $rangeStep) => 'Low Range: ' . number_format($minPrice) . ' - ' . number_format($minPrice + $rangeStep),
                ($minPrice + $rangeStep + 1) . '-' . ($minPrice + (2 * $rangeStep)) => 'Mid-Low: ' . number_format($minPrice + $rangeStep + 1) . ' - ' . number_format($minPrice + (2 * $rangeStep)),
                ($minPrice + (2 * $rangeStep) + 1) . '-' . ($minPrice + (3 * $rangeStep)) => 'Mid-High: ' . number_format($minPrice + (2 * $rangeStep) + 1) . ' - ' . number_format($minPrice + (3 * $rangeStep)),
                ($minPrice + (3 * $rangeStep) + 1) . "-{$maxPrice}" => 'High Range: ' . number_format($minPrice + (3 * $rangeStep) + 1) . ' - ' . number_format($maxPrice),
            ];

            return [
                'properties' => $query->paginate(9),
                'propertyTypes' => PropertyType::pluck('name', 'id'),
                'priceRanges' => $ranges
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
        $rules = [
            'form.title' => 'required|string|max:255',
            'form.property_type_id' => 'required|exists:property_types,id',
            'form.description' => 'required|string',
            'form.type' => 'required|string',
            'form.listing_type' => 'required|in:sale,rent,airbnb,commercial',
            'form.price' => 'required|numeric|min:0',
            'form.size' => 'nullable|numeric',
            'form.square_range' => 'nullable|string',
            'form.location' => 'required|string',
            'form.city' => 'required|string',
            'form.address' => 'nullable|string',
            'form.neighborhood' => 'nullable|string',
            'form.bedrooms' => 'nullable|integer|min:0',
            'form.bathrooms' => 'nullable|integer|min:0',
            'form.available' => 'boolean',
            'form.whatsapp_number' => 'required|string',
            'form.status' => 'required|in:available,under_contract,sold,rented',
            'form.is_featured' => 'boolean',
        ];

        // Add conditional rules based on listing type
        if ($this->form['listing_type'] === 'commercial') {
            $rules = array_merge($rules, [
                'form.commercial_type' => 'required|in:office,retail,industrial,warehouse,mixed_use',
                'form.commercial_size' => 'required|string',
                'form.commercial_price_monthly' => 'required|numeric|min:0',
                'form.commercial_price_annually' => 'required|numeric|min:0',
                'form.floors' => 'nullable|integer|min:0',
                'form.maintenance_status' => 'nullable|string',
                'form.last_renovation' => 'nullable|date',
                'form.has_parking' => 'boolean',
                'form.parking_spaces' => 'required_if:form.has_parking,true|nullable|integer|min:0',
                'form.commercial_amenities' => 'nullable|array',
                'form.zoning_info' => 'nullable|array',
                'form.price_per_square_foot' => 'nullable|numeric|min:0',
            ]);
        } elseif ($this->form['listing_type'] === 'rent') {
            $rules = array_merge($rules, [
                'form.is_furnished' => 'boolean',
                'form.rental_price_monthly' => 'required|numeric|min:0',
                'form.security_deposit' => 'nullable|numeric|min:0',
                'form.lease_terms' => 'nullable|string',
                'form.utilities_included' => 'nullable|array',
                'form.available_from' => 'nullable|date',
                'form.minimum_lease_period' => 'nullable|integer|min:0',
                'form.rental_requirements' => 'nullable|array',
                'form.amenities_condition' => 'nullable|array',
            ]);
        } elseif ($this->form['listing_type'] === 'airbnb') {
            $rules = array_merge($rules, [
                'form.airbnb_price_nightly' => 'required|numeric|min:0',
                'form.airbnb_price_weekly' => 'nullable|numeric|min:0',
                'form.airbnb_price_monthly' => 'nullable|numeric|min:0',
                'form.availability_calendar' => 'nullable|array',
            ]);
        }

        return $rules;
    }

    public function updatedImageUploads()
    {
        $this->validate([
            'imageUploads.*' => 'image|max:5120', // 5MB max
        ]);

        foreach ($this->imageUploads as $image) {
            try {
                $this->temporaryImages[] = [
                    'url' => $image->temporaryUrl(),
                    'file' => $image,
                    'name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'type' => $image->getMimeType(),
                ];
            } catch (\Exception $e) {
                logger()->error('Error creating temporary image preview', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Error creating temporary image preview: ' . $e->getMessage(),
                    'timer' => 3000
                ]);
            }
        }
        $this->imageUploads = [];
    }

    public function removeTemporaryImage($index)
    {
        unset($this->temporaryImages[$index]);
        $this->temporaryImages = array_values($this->temporaryImages);
    }

    public function confirmDeleteImage($imageId)
    {
        $this->selectedImage = $imageId;
        $this->showImageDeleteModal = true;
    }

    public function deleteImage()
    {
        if ($this->selectedProperty && $this->selectedImage) {
            $image = $this->selectedProperty->images()->find($this->selectedImage);
            if ($image) {
                app(PropertyImageService::class)->delete($image);
            }
        }
        $this->selectedImage = null;
        $this->showImageDeleteModal = false;
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Image deleted successfully.',
            'timer' => 3000
        ]);
    }

    public function setFeaturedImage($imageId)
    {
        if ($this->selectedProperty) {
            $image = $this->selectedProperty->images()->find($imageId);
            if ($image) {
                app(PropertyImageService::class)->setFeatured($image);
            }
        }
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Image set as featured successfully.',
            'timer' => 3000
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showFormModal = true;
    }

    public function edit($id)
    {
        $property = Property::with('images')->findOrFail($id);
        $this->selectedProperty = $property;
        
        // Extract property data before resetting the form
        $propertyData = $property->only([
            'title', 'property_type_id', 'description', 'type', 'listing_type',
            'price', 'size', 'square_range', 'location', 'city', 'address',
            'neighborhood', 'bedrooms', 'bathrooms', 'available', 'whatsapp_number',
            'status', 'commercial_type', 'commercial_size', 'commercial_price_monthly',
            'commercial_price_annually', 'floors', 'maintenance_status', 'last_renovation',
            'has_parking', 'parking_spaces', 'commercial_amenities', 'zoning_info',
            'price_per_square_foot', 'is_furnished', 'rental_price_daily',
            'rental_price_monthly', 'security_deposit', 'lease_terms', 'utilities_included',
            'available_from', 'minimum_lease_period', 'rental_requirements',
            'amenities_condition', 'airbnb_price_nightly', 'airbnb_price_weekly',
            'airbnb_price_monthly', 'availability_calendar', 'is_featured',
            'additional_features'
        ]);
        
        $this->resetForm();
        $this->selectedProperty = $property; // Restore selected property after form reset
        $this->form = array_merge($this->form, $propertyData);
        $this->modalMode = 'edit';
        $this->showFormModal = true;
    }
    
    public function view($id)
    {
        $property = Property::with('images')->findOrFail($id);
        $this->selectedProperty = $property;
        
        // Extract property data before resetting the form
        $propertyData = $property->only([
            'title', 'property_type_id', 'description', 'type', 'listing_type',
            'price', 'size', 'square_range', 'location', 'city', 'address',
            'neighborhood', 'bedrooms', 'bathrooms', 'available', 'whatsapp_number',
            'status', 'commercial_type', 'commercial_size', 'commercial_price_monthly',
            'commercial_price_annually', 'floors', 'maintenance_status', 'last_renovation',
            'has_parking', 'parking_spaces', 'commercial_amenities', 'zoning_info',
            'price_per_square_foot', 'is_furnished', 'rental_price_daily',
            'rental_price_monthly', 'security_deposit', 'lease_terms', 'utilities_included',
            'available_from', 'minimum_lease_period', 'rental_requirements',
            'amenities_condition', 'airbnb_price_nightly', 'airbnb_price_weekly',
            'airbnb_price_monthly', 'availability_calendar', 'is_featured',
            'additional_features'
        ]);
        
        $this->resetForm();
        $this->selectedProperty = $property; // Restore selected property after form reset
        $this->form = array_merge($this->form, $propertyData);
        $this->modalMode = 'view';
        $this->showFormModal = true;
    }
    
    public function confirmDelete($id)
    {
        $this->selectedProperty = Property::findOrFail($id);
        $this->showDeleteModal = true;
    }
    
    public function delete()
    {
        if ($this->selectedProperty) {
            // Get the property service to handle deletion logic
            $propertyService = app(PropertyService::class);
            
            try {
                $propertyService->delete($this->selectedProperty);
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Property deleted successfully.',
                    'timer' => 3000
                ]);
            } catch (\Exception $e) {
                logger()->error('Error deleting property', [
                    'property_id' => $this->selectedProperty->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Error deleting property: ' . $e->getMessage()
                ]);
            }
            
            $this->showDeleteModal = false;
            $this->selectedProperty = null;
            $this->dispatch('propertyListUpdated');
        }
    }

    public function save()
    {
        try {
            $this->validate();

            // Clean up form data
            $formData = collect($this->form)->map(function ($value) {
                if (is_array($value) && empty($value)) {
                    return [];
                }
                return $value === '' ? null : $value;
            })->toArray();

            // Add user_id
            $formData['user_id'] = auth()->id() ?? 1; // Fallback to admin user if not authenticated

            // Handle specific fields based on listing type
            if ($formData['listing_type'] !== 'commercial') {
                $formData['commercial_type'] = null;
            }

            // Sanitize enum fields
            if (isset($formData['status']) && !in_array($formData['status'], ['available', 'under_contract', 'sold', 'rented'])) {
                $formData['status'] = 'available';
            }

            $propertyService = app(PropertyService::class);
            $imageService = app(PropertyImageService::class);

            DB::beginTransaction();

            try {
                if ($this->modalMode === 'create') {
                    $property = $propertyService->create($formData);
                    
                    // Handle new image uploads
                    if (!empty($this->temporaryImages)) {
                        foreach ($this->temporaryImages as $index => $tempImage) {
                            try {
                                if (isset($tempImage['file']) && $tempImage['file']) {
                                    $imageService->store(
                                        $property, 
                                        $tempImage['file'], 
                                        $index === 0 // First image is featured
                                    );
                                }
                            } catch (\Exception $e) {
                                logger()->error('Failed to upload image', [
                                    'property_id' => $property->id,
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                            }
                        }
                    }
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => 'Property created successfully.',
                        'timer' => 3000
                    ]);
                    logger()->info('Property created successfully', ['property_id' => $property->id]);
                } else {
                    $propertyService->update($this->selectedProperty, $formData);
                    
                    // Handle new image uploads for existing property
                    if (!empty($this->temporaryImages)) {
                        foreach ($this->temporaryImages as $index => $tempImage) {
                            try {
                                if (isset($tempImage['file']) && $tempImage['file']) {
                                    $imageService->store(
                                        $this->selectedProperty, 
                                        $tempImage['file'], 
                                        empty($this->selectedProperty->images) && $index === 0 // Set as featured if first image for property
                                    );
                                }
                            } catch (\Exception $e) {
                                logger()->error('Failed to upload image for existing property', [
                                    'property_id' => $this->selectedProperty->id,
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                            }
                        }
                    }
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => 'Property updated successfully.',
                        'timer' => 3000
                    ]);
                    logger()->info('Property updated successfully', ['property_id' => $this->selectedProperty->id]);
                }
                
                DB::commit();
                $this->resetForm();
                $this->showFormModal = false;
                $this->dispatch('propertyListUpdated');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Transaction failed: ' . $e->getMessage(),
                    'timer' => 5000
                ]);
                logger()->error('Transaction failed in property save', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            logger()->error('Error saving property', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_data' => $this->form
            ]);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error saving property: ' . $e->getMessage(),
                'timer' => 5000
            ]);
        }
    }

    public function resetForm()
    {
        $this->form = [
            'title' => '',
            'property_type_id' => '',
            'description' => '',
            'type' => '',
            'listing_type' => 'sale',
            'price' => '',
            'size' => '',
            'square_range' => '',
            'location' => '',
            'city' => '',
            'address' => '',
            'neighborhood' => '',
            'bedrooms' => '',
            'bathrooms' => '',
            'available' => true,
            'whatsapp_number' => '',
            'status' => 'available',
            
            // Commercial fields
            'commercial_type' => '',
            'commercial_size' => '',
            'commercial_price_monthly' => '',
            'commercial_price_annually' => '',
            'floors' => '',
            'maintenance_status' => '',
            'last_renovation' => '',
            'has_parking' => false,
            'parking_spaces' => '',
            'commercial_amenities' => [],
            'zoning_info' => [],
            'price_per_square_foot' => '',
            
            // Rental fields
            'is_furnished' => false,
            'rental_price_daily' => '',
            'rental_price_monthly' => '',
            'security_deposit' => '',
            'lease_terms' => '',
            'utilities_included' => [],
            'available_from' => '',
            'minimum_lease_period' => '',
            'rental_requirements' => [],
            'amenities_condition' => [],
            
            // Airbnb fields
            'airbnb_price_nightly' => '',
            'airbnb_price_weekly' => '',
            'airbnb_price_monthly' => '',
            'availability_calendar' => [],
            
            'is_featured' => false,
            'additional_features' => [],
        ];
        $this->selectedProperty = null;
        $this->temporaryImages = [];
        $this->imageUploads = [];
        return $this->form;
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
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-sm backdrop-blur-xl"
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
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.status"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Property Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property Type</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="home" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.property_type"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Types</option>
                                @foreach($propertyTypes as $id => $type)
                                    <option value="{{ $id }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Price Range Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price Range</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.price_range"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                @foreach($priceRanges as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Actions -->
                <div class="flex flex-col md:flex-row items-center justify-center gap-4 col-span-2 mt-2">

                    <!-- Reset Filters Button -->
                    <button wire:click="resetFilters"
                        class="group relative overflow-hidden rounded-lg bg-gradient-to-r from-[#02c9c2] to-[#02a8a2] px-5 py-2.5 text-sm font-medium text-white shadow-md hover:shadow-lg transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]">
                        <!-- Background animation on hover -->
                        <span
                            class="absolute inset-0 translate-y-full bg-gradient-to-r from-[#012e2b] to-[#014e4a] group-hover:translate-y-0 transition-transform duration-300 ease-out"></span>
                        <!-- Content remains visible -->
                        <span class="relative flex items-center gap-2">
                            <flux:icon name="arrow-path"
                                class="h-4 w-4 transition-transform group-hover:rotate-180 duration-500" />
                            <span>Clear All Filters</span>
                        </span>
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
                                src="{{ Storage::disk('property_images')->url($property->images->first()->image_path) }}" 
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
                            </div>
                        </div>

                        <!-- Enhanced Action Buttons -->
                        <div class="flex items-center gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button
                                wire:click.prevent="view({{ $property->id }})"
                                class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-indigo-500 dark:bg-indigo-700/50 rounded-lg p-2"
                                wire:loading.attr="disabled"
                            >
                                <flux:icon wire:loading.remove wire:target="view({{ $property->id }})" name="eye" class="w-5 h-5" />
                                <flux:icon wire:loading wire:target="view({{ $property->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                            </button>
                            <button
                                wire:click.prevent="edit({{ $property->id }})"
                                class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-green-500 dark:bg-green-700/50 rounded-lg p-2"
                                wire:loading.attr="disabled"
                            >
                                <flux:icon wire:loading.remove wire:target="edit({{ $property->id }})" name="pencil-square" class="w-5 h-5" />
                                <flux:icon wire:loading wire:target="edit({{ $property->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                            </button>
                            <button
                                wire:click.prevent="confirmDelete({{ $property->id }})"
                                class="text-gray-200 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-500 transition-colors duration-150 bg-red-500 dark:bg-red-700/50 rounded-lg p-2"
                                wire:loading.attr="disabled"
                            >
                                <flux:icon wire:loading.remove wire:target="confirmDelete({{ $property->id }})" name="trash" class="w-5 h-5" />
                                <flux:icon wire:loading wire:target="confirmDelete({{ $property->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                            </button>
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
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                {{ $properties->links('components.pagination') }}
            </div>
        @endif
    </div>

    <!-- Property Form Modal -->
    <flux:modal wire:model="showFormModal" class="w-full max-w-4xl !p-0" @close="$wire.resetForm()">
        <div
            class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden" 
            x-data="{
                isViewMode: function() { return '{{ $modalMode }}' === 'view' },
                init() {
                    // If in view mode, disable all form elements
                    if (this.isViewMode()) {
                        this.$nextTick(() => {
                            this.$el.querySelectorAll('input, select, textarea').forEach(el => {
                                el.disabled = true;
                            });
                        });
                    }
                }
            }"
        >
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    @if($modalMode === 'create')
                        Add New Property
                    @elseif($modalMode === 'edit')
                        Edit Property
                    @else
                        View Property Details
                    @endif
                </h3>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Basic Property Information -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Basic Information
                        </h4>
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
                                        @if($modalMode === 'view') disabled @endif
                                    >
                                    @error('form.title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        @if($modalMode === 'view') disabled @endif
                                    >
                                        <option value="">Select Type</option>
                                        @foreach($propertyTypes as $id => $type)
                                            <option value="{{ $id }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.property_type_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property Category</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="squares-2x2" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model="form.type"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="E.g. Apartment, House, Land"
                                    >
                                    @error('form.type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Listing Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Listing Type</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="tag" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <select
                                        wire:model="form.listing_type"
                                        x-data="{}"
                                        x-init="$watch('$wire.form.listing_type', () => $wire.$refresh())"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                    >
                                        <option value="sale">For Sale</option>
                                        <option value="rent">For Rent</option>
                                        <option value="airbnb">Airbnb</option>
                                        <option value="commercial">Commercial</option>
                                    </select>
                                    @error('form.listing_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price (KES)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="number"
                                        wire:model="form.price"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Enter property price (KES)"
                                        min="0"
                                    >
                                    @error('form.price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Size -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Size (sqm)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="square-2-stack" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="number"
                                        wire:model="form.size"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Property size"
                                        min="0"
                                    >
                                    @error('form.size') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Square Range -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Square Range</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="arrows-pointing-out" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model="form.square_range"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="E.g. 100-150 sqm"
                                    >
                                    @error('form.square_range') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <div class="relative">
                                    <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                                        <flux:icon name="document-text" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <textarea
                                        wire:model="form.description"
                                        rows="4"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Describe the property"
                                    ></textarea>
                                    @error('form.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Section -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Location Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Location -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="map-pin" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model="form.location"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="General location"
                                    >
                                    @error('form.location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- City -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="building-office-2" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <select
                                        wire:model="form.city"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                    >
                                        <option value="">Select City</option>
                                        <option value="Nairobi">Nairobi</option>
                                        <option value="Mombasa">Mombasa</option>
                                        <option value="Kisumu">Kisumu</option>
                                        <option value="Nakuru">Nakuru</option>
                                        <option value="Eldoret">Eldoret</option>
                                        <option value="Malindi">Malindi</option>
                                        <option value="Kitale">Kitale</option>
                                        <option value="Garissa">Garissa</option>
                                        <option value="Thika">Thika</option>
                                        <option value="Nyeri">Nyeri</option>
                                        <option value="Kakamega">Kakamega</option>
                                        <option value="Kisii">Kisii</option>
                                        <option value="Machakos">Machakos</option>
                                        <option value="Meru">Meru</option>
                                        <option value="Lamu">Lamu</option>
                                        <option value="Naivasha">Naivasha</option>
                                        <option value="Athi River">Athi River</option>
                                        <option value="Bungoma">Bungoma</option>
                                        <option value="Kericho">Kericho</option>
                                        <option value="Kilifi">Kilifi</option>
                                        <option value="Voi">Voi</option>
                                        <option value="Webuye">Webuye</option>
                                        <option value="Ruiru">Ruiru</option>
                                    </select>
                                    @error('form.city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Address -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="home-modern" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model="form.address"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Street address"
                                    >
                                    @error('form.address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Neighborhood -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Neighborhood</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="map" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model="form.neighborhood"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Neighborhood or area"
                                    >
                                    @error('form.neighborhood') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Property Features -->
                    <div>
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Property Features
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Bedrooms -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bedrooms</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="moon" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="number"
                                        wire:model="form.bedrooms"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Number of bedrooms"
                                        min="0"
                                    >
                                    @error('form.bedrooms') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Bathrooms -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bathrooms</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="beaker" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="number"
                                        wire:model="form.bathrooms"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Number of bathrooms"
                                        min="0"
                                    >
                                    @error('form.bathrooms') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- WhatsApp Number -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">WhatsApp Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="phone" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model="form.whatsapp_number"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Contact WhatsApp number"
                                    >
                                    @error('form.whatsapp_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <select
                                        wire:model="form.status"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                    >
                                        <option value="available">Available</option>
                                        <option value="under_contract">Under Contract</option>
                                        <option value="sold">Sold</option>
                                        <option value="rented">Rented</option>
                                    </select>
                                    @error('form.status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Available -->
                            <div class="flex items-center">
                                <div class="relative flex items-start pt-6">
                                    <div class="flex items-center h-5">
                                        <input 
                                            type="checkbox" 
                                            wire:model="form.available"
                                            class="h-5 w-5 rounded border-gray-300 dark:border-gray-600 text-[#02c9c2] focus:ring-[#02c9c2]"
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-gray-700 dark:text-gray-300">Available for viewing</label>
                                    </div>
                                </div>
                                @error('form.available') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Featured -->
                            <div class="flex items-center">
                                <div class="relative flex items-start pt-6">
                                    <div class="flex items-center h-5">
                                        <input 
                                            type="checkbox" 
                                            wire:model="form.is_featured"
                                            class="h-5 w-5 rounded border-gray-300 dark:border-gray-600 text-[#02c9c2] focus:ring-[#02c9c2]"
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-gray-700 dark:text-gray-300">Featured property</label>
                                    </div>
                                </div>
                                @error('form.is_featured') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Commercial Fields Section (conditional) -->
                    <div x-show="$wire.form.listing_type === 'commercial'">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Commercial Property Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Commercial Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Commercial Type</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="building-storefront" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <select
                                        wire:model="form.commercial_type"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                    >
                                        <option value="">Select Type</option>
                                        <option value="office">Office</option>
                                        <option value="retail">Retail</option>
                                        <option value="industrial">Industrial</option>
                                        <option value="warehouse">Warehouse</option>
                                        <option value="mixed_use">Mixed Use</option>
                                    </select>
                                    @error('form.commercial_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Commercial Size -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Commercial Size</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="arrows-pointing-out" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model="form.commercial_size"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Size description"
                                    >
                                    @error('form.commercial_size') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Monthly Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monthly Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model="form.commercial_price_monthly"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Monthly price"
                                    >
                                    @error('form.commercial_price_monthly') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Annual Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Annual Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model="form.commercial_price_annually"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Annual price"
                                    >
                                    @error('form.commercial_price_annually') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- More Commercial fields can be added as needed -->
                        </div>
                    </div>

                    <!-- Rental Fields Section (conditional) -->
                    <div x-show="$wire.form.listing_type === 'rent'">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Rental Property Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Daily Rental Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Daily Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="number"
                                        wire:model="form.rental_price_daily"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Daily rental price"
                                        min="0"
                                    >
                                    @error('form.rental_price_daily') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Monthly Rental Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monthly Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="number"
                                        wire:model="form.rental_price_monthly"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Monthly rental price"
                                        min="0"
                                    >
                                    @error('form.rental_price_monthly') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Furnished -->
                            <div class="flex items-center">
                                <div class="relative flex items-start pt-6">
                                    <div class="flex items-center h-5">
                                        <input 
                                            type="checkbox" 
                                            wire:model="form.is_furnished"
                                            class="h-5 w-5 rounded border-gray-300 dark:border-gray-600 text-[#02c9c2] focus:ring-[#02c9c2]"
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-gray-700 dark:text-gray-300">Furnished</label>
                                    </div>
                                </div>
                                @error('form.is_furnished') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Security Deposit -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Security Deposit</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="number"
                                        wire:model="form.security_deposit"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Security deposit amount"
                                        min="0"
                                    >
                                    @error('form.security_deposit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- More Rental fields can be added as needed -->
                        </div>
                    </div>

                    <!-- Airbnb Fields Section (conditional) -->
                    <div x-show="$wire.form.listing_type === 'airbnb'">
                        <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                            Airbnb Property Details
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Airbnb Nightly Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nightly Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="number"
                                        wire:model="form.airbnb_price_nightly"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Nightly price"
                                        min="0"
                                    >
                                    @error('form.airbnb_price_nightly') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Airbnb Weekly Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weekly Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="number"
                                        wire:model="form.airbnb_price_weekly"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Weekly price"
                                        min="0"
                                    >
                                    @error('form.airbnb_price_weekly') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Airbnb Monthly Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monthly Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="currency-dollar" class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="number"
                                        wire:model="form.airbnb_price_monthly"
                                        class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                        placeholder="Monthly price"
                                        min="0"
                                    >
                                    @error('form.airbnb_price_monthly') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- More Airbnb fields can be added as needed -->
                        </div>
                    </div>

                    <!-- Image Management Section -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white">Property Images</h4>
                        
                        <!-- View Mode Message -->
                        <div x-show="isViewMode()" class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <flux:icon name="information-circle" class="h-5 w-5 text-blue-400" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">View Mode</h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                                        <p>You are currently viewing property details. Images cannot be modified in this mode.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Image Upload Area -->
                        <div x-show="!isViewMode()" class="flex items-center justify-center w-full">
                            <label for="image-upload" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                        <span class="font-semibold">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG or JPEG (MAX. 5MB)</p>
                                </div>
                                <input 
                                    id="image-upload" 
                                    type="file" 
                                    class="hidden" 
                                    wire:model="imageUploads" 
                                    multiple 
                                    accept="image/*"
                                />
                            </label>
                        </div>

                        <!-- Preview of New Images -->
                        @if(count($temporaryImages))
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
                                @foreach($temporaryImages as $index => $image)
                                    <div class="relative group">
                                        <img src="{{ $image['url'] }}" class="w-full h-32 object-cover rounded-lg" alt="Preview">
                                        <div class="absolute inset-0 bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                            <button type="button" wire:click="removeTemporaryImage({{ $index }})" class="text-white p-2 hover:text-red-500 transition-colors">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Existing Images (Edit/View Mode) -->
                        @if(($modalMode === 'edit' || $modalMode === 'view') && $selectedProperty?->images)
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
                                @foreach($selectedProperty->images as $image)
                                    <div class="relative group">
                                        <img 
                                            src="{{ Storage::disk('property_images')->url($image->thumbnail_path) }}" 
                                            class="w-full h-32 object-cover rounded-lg {{ $image->is_featured ? 'ring-2 ring-blue-500' : '' }}"
                                            alt="{{ $image->alt_text }}"
                                        >
                                        <!-- Only show action buttons in edit mode -->
                                        @if($modalMode === 'edit')
                                        <div class="absolute inset-0 bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center space-x-2">
                                            <button 
                                                type="button" 
                                                wire:click="setFeaturedImage({{ $image->id }})"
                                                class="text-white p-2 hover:text-yellow-500 transition-colors"
                                                title="{{ $image->is_featured ? 'Featured Image' : 'Set as Featured' }}"
                                            >
                                                <svg class="w-6 h-6" fill="{{ $image->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                            </button>
                                            <button 
                                                type="button" 
                                                wire:click="confirmDeleteImage({{ $image->id }})"
                                                class="text-white p-2 hover:text-red-500 transition-colors"
                                            >
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                        @else
                                        <!-- View mode indicator for featured images -->
                                        @if($image->is_featured)
                                        <div class="absolute bottom-2 right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                            <span class="flex items-center">
                                                <flux:icon name="star" class="w-3 h-3 mr-1" />
                                                Featured
                                            </span>
                                        </div>
                                        @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                       </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex justify-end space-x-3">
                <flux:button variant="primary" wire:click="$toggle('showFormModal')">
                    @if($modalMode === 'view')
                        Close
                    @else
                        Cancel
                    @endif
                </flux:button>
                @if($modalMode !== 'view')
                <flux:button wire:click="save">
                    {{ $modalMode === 'create' ? 'Create Property' : 'Update Property' }}
                </flux:button>
                @endif
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
                <p class="text-gray-600 dark:text-gray-400">
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
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900"
                            wire:loading.attr="disabled">
                        <flux:icon wire:loading.remove wire:target="delete" name="trash" class="w-4 h-4 mr-1.5" />
                        <flux:icon wire:loading wire:target="delete" name="arrow-path" class="w-4 h-4 mr-1.5 animate-spin" />
                        Delete Property
                    </button>
                </div>
            </div>
        </div>
    </flux:modal>

    <!-- Image Delete Confirmation Modal -->
    <flux:modal wire:model="showImageDeleteModal" max-width="md" class="!p-0">
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
            <div class="bg-gradient-to-r from-red-500/20 to-red-600/20 dark:from-red-900/30 dark:to-red-700/30 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-circle" class="w-6 h-6 text-red-600" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Confirm Image Deletion
                    </h3>
                </div>
            </div>

            <div class="p-6">
                <p class="text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this image? This action cannot be undone.
                </p>

                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="$set('showImageDeleteModal', false)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteImage"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900"
                            wire:loading.attr="disabled">
                        <flux:icon wire:loading.remove wire:target="deleteImage" name="trash" class="w-4 h-4 mr-1.5" />
                        <flux:icon wire:loading wire:target="deleteImage" name="arrow-path" class="w-4 h-4 mr-1.5 animate-spin" />
                        Delete Image
                    </button>
                </div>
            </div>
        </div>
    </flux:modal>

    <!-- Enhanced Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10"></div>

    <!-- SweetAlert2 Toast Setup -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('showToast', (data) => {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: data.timer || 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: data.type,
                    title: data.message,
                    background: data.type === 'success' ? '#10B981' : '#EF4444',
                    color: '#ffffff',
                    iconColor: '#ffffff'
                });
            });
        });
    </script>
</div>
