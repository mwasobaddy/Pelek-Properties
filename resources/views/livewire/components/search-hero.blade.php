<?php

use App\Models\PropertyType;
use App\Services\PropertySearchService;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, computed};

new class extends Component {
    #[Url]
    public string $search = '';

    #[Url]
    public ?string $type = '';

    #[Url]
    public ?string $city = '';

    public string $listingType = 'all';

    public function search()
    {
        $params = array_filter([
            'search' => $this->search,
            'type' => $this->type,
            'city' => $this->city,
            'listing_type' => $this->listingType !== 'all' ? $this->listingType : null,
        ]);

        $this->redirect(route('properties.search', $params));
    }

    public function with(): array
    {
        return [
            'propertyTypes' => computed(function () {
                return PropertyType::orderBy('name')->get();
            }),
            'cities' => computed(function () {
                return app(PropertySearchService::class)->getAvailableCities();
            }),
            'propertyStats' => computed(function () {
                return app(PropertySearchService::class)->getPropertyCountsByType();
            }),
        ];
    }
} ?>

<div class="relative isolate overflow-hidden">
    {{-- Background with gradient overlay --}}
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-black/50"></div>
        <img 
            src="{{ asset('images/hero-background.jpg') }}" 
            alt="Luxury Property in Nairobi"
            class="h-full w-full object-cover"
        />
    </div>

    <div class="relative mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8">
        {{-- Hero Content --}}
        <div class="max-w-2xl">
            <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl mb-4">
                Find Your Perfect Property in Nairobi
            </h1>
            <p class="mt-6 text-lg leading-8 text-gray-300">
                Explore premium properties for sale, rent, or short stays in Nairobi's most desirable neighborhoods.
            </p>
        </div>

        {{-- Search Form --}}
        <div class="mt-8 max-w-3xl">
            <div class="rounded-2xl bg-white/10 backdrop-blur-lg p-4 shadow-lg">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {{-- Property Type Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-white">Property Type</label>
                        <select 
                            wire:model.live="type"
                            class="mt-1 block w-full rounded-md border-0 bg-white/80 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-indigo-500 sm:text-sm sm:leading-6"
                        >
                            <option value="">Any Type</option>
                            @foreach($this->propertyTypes as $propertyType)
                                <option value="{{ $propertyType->id }}">{{ $propertyType->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Location Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-white">Location</label>
                        <select 
                            wire:model.live="city"
                            class="mt-1 block w-full rounded-md border-0 bg-white/80 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-indigo-500 sm:text-sm sm:leading-6"
                        >
                            <option value="">Any Location</option>
                            @foreach($this->cities as $availableCity)
                                <option value="{{ $availableCity }}">{{ $availableCity }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Listing Type Tabs --}}
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-white mb-2">I'm looking to</label>
                        <div class="flex rounded-lg bg-white/5 p-0.5">
                            @foreach(['all' => 'All', 'sale' => 'Buy', 'rent' => 'Rent', 'airbnb' => 'Book'] as $value => $label)
                                <button
                                    wire:click="$set('listingType', '{{ $value }}')"
                                    type="button"
                                    @class([
                                        'flex-1 rounded-md px-2.5 py-1.5 text-sm font-medium',
                                        'bg-white text-gray-900' => $listingType === $value,
                                        'text-white hover:bg-white/10' => $listingType !== $value,
                                    ])
                                >
                                    {{ $label }}
                                    @if(isset($this->propertyStats[$value]))
                                        ({{ $this->propertyStats[$value] }})
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Search Input --}}
                    <div class="lg:col-span-3">
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search by location, title or description..."
                                class="block w-full rounded-md border-0 bg-white/80 py-2 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-white/10 placeholder:text-gray-500 focus:ring-2 focus:ring-indigo-500 sm:text-sm sm:leading-6"
                            >
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <x-flux-icon name="magnifying-glass" class="h-5 w-5 text-gray-500" />
                            </div>
                        </div>
                    </div>

                    {{-- Search Button --}}
                    <div>
                        <x-flux-button
                            wire:click="search"
                            class="w-full justify-center bg-indigo-600 hover:bg-indigo-500"
                        >
                            Search Properties
                        </x-flux-button>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <dl class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-3">
                @foreach(['sale' => 'Properties for Sale', 'rent' => 'Rental Properties', 'airbnb' => 'Airbnb Properties'] as $type => $label)
                    <div class="flex flex-col rounded-lg bg-white/5 px-4 py-3">
                        <dt class="text-sm font-medium text-gray-300">{{ $label }}</dt>
                        <dd class="mt-1">
                            <div class="text-2xl font-semibold text-white">
                                {{ $this->propertyStats[$type] ?? 0 }}
                            </div>
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</div>
