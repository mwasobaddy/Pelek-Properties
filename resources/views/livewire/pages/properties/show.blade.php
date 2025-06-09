<?php

use App\Models\Property;
use App\Services\SEOService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;
use function Livewire\Volt\{state};

state([
    'property' => null,
    'similarProperties' => null
]);

new #[Layout('components.layouts.guest')] class extends Component {
    public $property;
    public $similarProperties;
    
    public function mount(Property $property, SEOService $seoService)
    {
        $this->property = $property->load(['propertyType', 'amenities', 'images']);
        
        $this->similarProperties = Property::query()
            ->where('property_type_id', $this->property->property_type_id)
            ->where('id', '!=', $this->property->id)
            ->with(['propertyType', 'images'])
            ->take(4)
            ->get();
        
        $this->dispatch('seo-tags', ['title' => $this->property->title, 'description' => $this->property->description]);
    }

    public function contactViaWhatsapp()
    {
        $message = "Hi, I'm interested in the property: {$this->property->title} at {$this->property->location}. I would like to know more about it.";
        $phoneNumber = '254711614099';
        $url = "https://wa.me/{$phoneNumber}?text=" . urlencode($message);
        
        return redirect()->away($url);
    }
}
?>

<div class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Enhanced Image Gallery with Immersive Viewing Experience -->
        <div x-data="{ 
            showGallery: false, 
            currentImage: 0,
            isLoading: false,
            initGallery() {
                this.isLoading = true;
                setTimeout(() => this.isLoading = false, 500);
            }
        }" class="relative mb-8">
            <!-- Main Gallery Grid with Improved Layout -->
            <div x-data="{
                    currentIndex: 0,
                    images: {{ $this->property->images->pluck('image_path')->toJson() }},
                    autoSlideInterval: null,
                    isPaused: false,
                    nextSlide() {
                        this.currentIndex = this.currentIndex === this.images.length - 1 ? 0 : this.currentIndex + 1;
                    },
                    prevSlide() {
                        this.currentIndex = this.currentIndex === 0 ? this.images.length - 1 : this.currentIndex - 1;
                    },
                    goToSlide(index) {
                        this.currentIndex = index;
                    },
                    startAutoSlide() {
                        if (!this.isPaused) {
                            this.autoSlideInterval = setInterval(() => {
                                this.nextSlide();
                            }, 5000);
                        }
                    },
                    pauseAutoSlide() {
                        this.isPaused = true;
                        if (this.autoSlideInterval) clearInterval(this.autoSlideInterval);
                    },
                    resumeAutoSlide() {
                        this.isPaused = false;
                        this.startAutoSlide();
                    }
                }" 
                x-init="startAutoSlide()"
                class="grid md:grid-cols-3 gap-4"
                @mouseenter="pauseAutoSlide()"
                @mouseleave="resumeAutoSlide()"
            >
                <!-- Main Carousel -->
                <div class="md:col-span-2 relative h-[500px] group cursor-pointer overflow-hidden">
                    <div class="relative h-[400px] md:h-[500px] overflow-hidden rounded-2xl">
                        <!-- Images -->
                        <template x-for="(image, index) in images" :key="index">
                            <div x-show="currentIndex === index"
                                x-transition:enter="transition ease-out duration-500"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-300"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-95"
                                class="absolute inset-0">
                                <img :src="'{{ Storage::disk('property_images')->url('') }}' + image"
                                    :alt="`Property image ${index + 1}`"
                                    class="w-full h-full object-cover"
                                    @click="showGallery = true; currentImage = index; initGallery()">
                            </div>
                        </template>
                        
                        <!-- Carousel Controls -->
                        <button @click.stop="prevSlide" 
                                class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white p-2 rounded-full transition-colors">
                            <flux:icon name="chevron-left" class="w-6 h-6" />
                        </button>
                        
                        <button @click.stop="nextSlide" 
                                class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white p-2 rounded-full transition-colors">
                            <flux:icon name="chevron-right" class="w-6 h-6" />
                        </button>
                        
                        <!-- View All Photos Button -->
                        <button @click.stop="showGallery = true; currentImage = currentIndex; initGallery()" 
                                class="absolute right-4 bottom-4 bg-black/50 hover:bg-black/70 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                            <flux:icon name="photo" class="w-5 h-5" />
                            <span class="text-sm font-medium">View All Photos</span>
                        </button>
                    </div>
                    
                    <!-- Thumbnail Navigation -->
                    <div class="flex justify-center mt-4 space-x-2 overflow-x-auto pb-2">
                        <template x-for="(image, index) in images" :key="index">
                            <button @click="goToSlide(index)" 
                                    class="w-150px md:w-16 h-16 rounded-md overflow-hidden transition-all duration-300 focus:outline-none"
                                    :class="currentIndex === index ? 'ring-2 ring-[#02c9c2]' : 'opacity-70 hover:opacity-100'">
                                <img :src="'{{ Storage::disk('property_images')->url('') }}' + image" 
                                    :alt="`Thumbnail ${index + 1}`"
                                    class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Side Images with Hover Effects -->
                <div class="hidden md:grid grid-rows-2 gap-4">
                    @foreach($this->property->images->skip(1)->take(2) as $index => $image)
                        <div class="relative h-[242px] group cursor-pointer overflow-hidden" @click="showGallery = true; currentImage = {{ $index + 1 }}">
                            <img 
                                src="{{ Storage::disk('property_images')->url($image->image_path) }}" 
                                alt="{{ $this->property->title }}"
                                class="w-full h-full object-cover rounded-2xl shadow-lg transition-transform duration-300 group-hover:scale-[1.02]"
                            >
                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Immersive Full Screen Gallery Modal -->
            <div x-show="showGallery" 
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="fixed inset-0 z-[60] bg-black/95 backdrop-blur-sm flex items-center justify-center"
                @keydown.escape.window="showGallery = false"
                @click.self="showGallery = false">
                
                <!-- Gallery Controls -->
                <div class="relative w-full max-w-6xl px-4">
                    <!-- Close Button -->
                    <button type="button" @click.stop="showGallery = false" class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors z-20">
                        <flux:icon name="x-mark" class="w-8 h-8" />
                    </button>

                    <!-- Image Counter -->
                    <div class="absolute top-4 left-4 text-white/80 text-sm font-medium">
                        <span x-text="currentImage + 1"></span> / <span>{{ $this->property->images->count() }}</span>
                    </div>

                    <!-- Main Image -->
                    <div class="relative aspect-video">
                        <template x-for="(image, index) in {{ $this->property->images->pluck('image_path')->toJson() }}" :key="index">
                            <img :src="'{{ Storage::disk('property_images')->url('') }}' + image" 
                                :alt="'Property image ' + (index + 1)"
                                class="absolute inset-0 w-full h-full object-contain transition-opacity duration-300"
                                :class="currentImage === index ? 'opacity-100' : 'opacity-0'"
                            >
                        </template>
                    </div>

                    <!-- Navigation Arrows -->
                    <button @click="currentImage = (currentImage - 1 + {{ $this->property->images->count() }}) % {{ $this->property->images->count() }}"
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 transition-colors">
                        <flux:icon name="chevron-left" class="w-10 h-10" />
                    </button>
                    <button @click="currentImage = (currentImage + 1) % {{ $this->property->images->count() }}"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 transition-colors">
                        <flux:icon name="chevron-right" class="w-10 h-10" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid md:grid-cols-3 gap-8">
            <!-- Left Column - Main Content -->
            <div class="col-span-3 md:col-span-2 space-y-8">
                <!-- Property Header -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1 text-sm bg-[#02c9c2] text-white rounded-full shadow-md">
                            {{ $this->property->propertyType->name }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Overview Section -->
                    <div class="p-6 space-y-6">
                        <div class="flex justify-between items-start flex-col md:flex-row space-y-4 md:space-y-0">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                    {{ $this->property->title }}
                                </h1>
                                <p class="text-gray-600 dark:text-gray-400 flex items-center">
                                    <flux:icon name="map-pin" class="w-5 h-5 mr-1 text-[#02c9c2]"/>
                                    {{ $this->property->location }}
                                </p>
                            </div>
                            <div class="text-right w-full md:w-[unset]">
                                <p class="text-3xl font-bold text-[#02c9c2]">
                                    @if($this->property->listing_type === 'airbnb')
                                        KSH {{ number_format($this->property->airbnb_price_nightly) }} <p class="text-sm text-gray-600 dark:text-gray-400">per night</p>
                                        <br>
                                        KSH {{ number_format($this->property->airbnb_price_weekly) }} <p class="text-sm text-gray-600 dark:text-gray-400">per week</p>
                                        <br>
                                        KSH {{ number_format($this->property->airbnb_price_monthly) }} <p class="text-sm text-gray-600 dark:text-gray-400">per month</p>
                                    @elseif($this->property->listing_type === 'rent')
                                        KSH {{ number_format($this->property->rental_price_daily) }} <p class="text-sm text-gray-600 dark:text-gray-400">per day</p>
                                        <br>
                                        KSH {{ number_format($this->property->rental_price_weekly) }} <p class="text-sm text-gray-600 dark:text-gray-400">per week</p>
                                        <br>
                                        KSH {{ number_format($this->property->rental_price_monthly) }} <p class="text-sm text-gray-600 dark:text-gray-400">per month</p>
                                    @elseif($this->property->listing_type === 'commercial')
                                        KSH {{ number_format($this->property->commercial_price_monthly) }} <p class="text-sm text-gray-600 dark:text-gray-400">per month</p>
                                        <br>
                                        KSH {{ number_format($this->property->commercial_price_annually) }} <p class="text-sm text-gray-600 dark:text-gray-400">per year</p>
                                    @else
                                        KSH {{ number_format($this->property->price) }} <p class="text-sm text-gray-600 dark:text-gray-400">per month</p>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Enhanced Overview Grid with Visual Icons -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 p-8 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800/50 dark:to-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-700/50 backdrop-blur-sm">
                            <div class="relative group">
                                <div class="absolute inset-0 bg-[#02c9c2]/5 dark:bg-[#02c9c2]/10 rounded-xl transition-transform duration-300 group-hover:scale-105"></div>
                                <div class="relative p-4 text-center">
                                    <div class="mb-2 inline-flex items-center justify-center w-12 h-12 rounded-xl bg-[#02c9c2]/10 dark:bg-[#02c9c2]/20">
                                        <flux:icon name="building-office-2" class="w-6 h-6 text-[#02c9c2]"/>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">Category</p>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $this->property->propertyType->name }}</p>
                                </div>
                            </div>
                            <div class="relative group">
                                <div class="absolute inset-0 bg-[#02c9c2]/5 dark:bg-[#02c9c2]/10 rounded-xl transition-transform duration-300 group-hover:scale-105"></div>
                                <div class="relative p-4 text-center">
                                    <div class="mb-2 inline-flex items-center justify-center w-12 h-12 rounded-xl bg-[#02c9c2]/10 dark:bg-[#02c9c2]/20">
                                        <flux:icon name="square-2-stack" class="w-6 h-6 text-[#02c9c2]"/>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">Square</p>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ number_format($this->property->size) }} m²</p>
                                </div>
                            </div>
                            <div class="relative group">
                                <div class="absolute inset-0 bg-[#02c9c2]/5 dark:bg-[#02c9c2]/10 rounded-xl transition-transform duration-300 group-hover:scale-105"></div>
                                <div class="relative p-4 text-center">
                                    <div class="mb-2 inline-flex items-center justify-center w-12 h-12 rounded-xl bg-[#02c9c2]/10 dark:bg-[#02c9c2]/20">
                                        <flux:icon name="home" class="w-6 h-6 text-[#02c9c2]"/>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">Bedrooms</p>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $this->property->bedrooms }}</p>
                                </div>
                            </div>
                            <div class="relative group">
                                <div class="absolute inset-0 bg-[#02c9c2]/5 dark:bg-[#02c9c2]/10 rounded-xl transition-transform duration-300 group-hover:scale-105"></div>
                                <div class="relative p-4 text-center">
                                    <div class="mb-2 inline-flex items-center justify-center w-12 h-12 rounded-xl bg-[#02c9c2]/10 dark:bg-[#02c9c2]/20">
                                        <flux:icon name="beaker" class="w-6 h-6 text-[#02c9c2]"/>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">Bathrooms</p>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $this->property->bathrooms }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Description Section -->
                        <div class="space-y-4">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                <flux:icon name="document-text" class="w-6 h-6 mr-2 text-[#02c9c2]"/>
                                Description
                            </h2>
                            <div class="prose dark:prose-invert max-w-none">
                                {{ $this->property->description }}
                            </div>
                        </div>

                    <!-- Enhanced Features Section with Interactive Cards -->
                    @if($this->property->amenities->isNotEmpty())
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                    <flux:icon name="sparkles" class="w-6 h-6 mr-2 text-[#02c9c2]"/>
                                    Property Features
                                </h2>
                                <span class="text-sm text-[#02c9c2]">{{ $this->property->amenities->count() }} amenities</span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($this->property->amenities as $amenity)
                                    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-gray-50 to-white dark:from-gray-800/50 dark:to-gray-700/50 p-4 border border-gray-100 dark:border-gray-700/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                                        <div class="absolute inset-0 bg-[#02c9c2]/5 dark:bg-[#02c9c2]/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <div class="relative flex items-center gap-3">
                                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#02c9c2]/10 dark:bg-[#02c9c2]/20 flex items-center justify-center">
                                                <flux:icon name="{{ $amenity->icon ?? 'check-circle' }}" class="w-5 h-5 text-[#02c9c2]"/>
                                            </div>
                                            <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $amenity->name }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-span-3 md:col-span-1 space-y-8">
                <!-- Enhanced Contact Card with Modern Design -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700/50">
                    <div class="relative overflow-hidden">
                        <!-- Decorative Top Pattern -->
                        <div class="absolute top-0 inset-x-0 h-24 bg-gradient-to-br from-[#02c9c2]/20 to-[#02a8a2]/20 dark:from-[#02c9c2]/30 dark:to-[#02a8a2]/30"></div>
                        
                        <div class="relative p-6 space-y-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <flux:icon name="user-circle" class="w-6 h-6 text-[#02c9c2]"/>
                                    Contact Agent
                                </h3>
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-[#02c9c2]/10 text-[#02c9c2]">Available</span>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <div class="h-16 w-16 rounded-xl bg-gradient-to-br from-[#02c9c2] to-[#02a8a2] flex items-center justify-center transform rotate-3 transition-transform group-hover:rotate-6">
                                        <flux:icon name="user" class="w-8 h-8 text-white"/>
                                    </div>
                                    <div class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Property Agent</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Real Estate Expert</p>
                                    <p class="text-xs text-[#02c9c2] mt-1">Usually responds within 30 minutes</p>
                                </div>
                            </div>
                        
                        <div class="space-y-4 pt-4">
                            <!-- WhatsApp Button with Enhanced Interaction -->
                            <button
                                wire:click="contactViaWhatsapp"
                                class="w-full group relative overflow-hidden rounded-xl bg-gradient-to-r from-[#02c9c2] to-[#02a8a2] p-4 text-white shadow-md hover:shadow-xl transition-all duration-500 flex items-center justify-between"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-[#028c87] to-[#025956] translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
                                <div class="relative flex items-center space-x-3">
                                    <div class="bg-white/20 rounded-lg p-2">
                                        <flux:icon name="chat-bubble-left-ellipsis" class="w-5 h-5"/>
                                    </div>
                                    <span class="font-medium">WhatsApp Chat</span>
                                </div>
                                <flux:icon name="arrow-right" class="w-5 h-5 relative transform group-hover:translate-x-1 transition-transform"/>
                            </button>
                            
                            <!-- Phone Call Button with Enhanced Interaction -->
                            <a href="tel:+254711614099" 
                            class="w-full group relative overflow-hidden rounded-xl bg-white dark:bg-gray-700/50 p-4 border border-gray-200 dark:border-gray-600/50 text-gray-700 dark:text-gray-300 hover:border-[#02c9c2] dark:hover:border-[#02c9c2] shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-gray-100 dark:bg-gray-600 rounded-lg p-2 group-hover:bg-[#02c9c2]/10 dark:group-hover:bg-[#02c9c2]/20 transition-colors">
                                        <flux:icon name="phone" class="w-5 h-5 group-hover:text-[#02c9c2] transition-colors"/>
                                    </div>
                                    <span class="font-medium group-hover:text-[#02c9c2] transition-colors">Call Agent</span>
                                </div>
                                <flux:icon name="arrow-right" class="w-5 h-5 transform group-hover:translate-x-1 group-hover:text-[#02c9c2] transition-all"/>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Similar Properties -->
                @if($this->similarProperties->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <flux:icon name="squares-2x2" class="w-5 h-5 mr-2 text-[#02c9c2]"/>
                            Similar Properties
                        </h3>
                        <div class="space-y-4">
                            @foreach($this->similarProperties as $similarProperty)
                                <a href="{{ route('properties.show', $similarProperty) }}" 
                                wire:navigate
                                class="group block hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-colors">
                                    <div class="flex space-x-4">
                                        <div class="flex-shrink-0 w-20 h-20">
                                            @if($similarProperty->images->isNotEmpty())
                                                <img 
                                                    src="{{ Storage::disk('property_images')->url($similarProperty->images->first()->image_path) }}" 
                                                    alt="{{ $similarProperty->title }}"
                                                    class="w-full h-full object-cover rounded-lg"
                                                >
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-[#02c9c2] transition-colors">
                                                {{ $similarProperty->title }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $similarProperty->location }}
                                            </p>
                                            <p class="text-sm font-semibold text-[#02c9c2]">
                                                @if($similarProperty->listing_type === 'airbnb')
                                                    KSH {{ number_format($similarProperty->airbnb_price_nightly) }} <span class="text-xs text-gray-500 dark:text-gray-400">per night</span>
                                                @elseif($similarProperty->listing_type === 'rent')
                                                    KSH {{ number_format($similarProperty->rental_price_monthly) }} <span class="text-xs text-gray-500 dark:text-gray-400">per month</span>
                                                @elseif($similarProperty->listing_type === 'commercial')
                                                    KSH {{ number_format($similarProperty->commercial_price_monthly) }} <span class="text-xs text-gray-500 dark:text-gray-400">per month</span>
                                                @else
                                                    KSH {{ number_format($similarProperty->price) }} <span class="text-xs text-gray-500 dark:text-gray-400">per month</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
        <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    </div>
</div>