<?php
use App\Models\Property;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        return [
            'properties' => Property::with(['propertyType', 'images'])
                ->latest()
                ->get()
        ];
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
            
            <a 
                href="{{ route('admin.properties.manage') }}" 
                wire:navigate
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
            >
                <flux:icon wire:loading.remove name="plus" class="w-5 h-5 mr-2" />
                <flux:icon wire:loading name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                Add New Property
            </a>
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
                                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <flux:icon name="pencil" class="w-4 h-4 mr-2" />
                                            Edit Details
                                        </a>
                                        <a href="#" class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
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
    </div>

    <!-- Enhanced Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10"></div>
</div>
