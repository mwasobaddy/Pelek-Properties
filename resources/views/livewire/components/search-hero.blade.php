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

<div class="relative isolate overflow-hidden bg-gradient-to-br from-zinc-900 to-[#012e2b] dark:from-zinc-950 dark:to-[#012e2b]">
    {{-- Modern hero background with enhanced quality and overlay --}}
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-zinc-900/85 via-[#012e2b]/75 to-[#02c9c2]/30 dark:from-zinc-950/90 dark:via-[#012e2b]/80 dark:to-[#02c9c2]/20 backdrop-blur-sm"></div>
        <img 
            src="{{ asset('images/placeholder.webp') }}" 
            alt="Luxury Property in Nairobi"
            class="h-full w-full object-cover object-center transition-all duration-700 filter"
            loading="eager"
        />
    </div>
    
    {{-- Modern decorative elements with branded colors --}}
    <div aria-hidden="true" class="absolute inset-0 overflow-hidden">
        <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-[#02c9c2]/20 dark:bg-[#02c9c2]/15 blur-3xl"></div>
        <div class="absolute right-0 bottom-0 h-80 w-80 rounded-full bg-[#02c9c2]/15 dark:bg-[#02c9c2]/10 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/3 h-64 w-64 rounded-full bg-[#02c9c2]/10 dark:bg-[#02c9c2]/5 blur-2xl"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-28">
        {{-- Hero Content with enhanced typography --}}
        <div class="max-w-2xl animate-fade-in">
            <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4">Premier Property Search</span>
            <h1 class="font-display text-4xl font-bold tracking-tight text-white dark:text-white sm:text-5xl lg:text-6xl">
                Find Your <span class="text-[#02c9c2] dark:text-[#02c9c2]">Perfect Property</span> in Nairobi
            </h1>
            <p class="mt-5 text-lg leading-relaxed text-zinc-300 dark:text-zinc-200 sm:text-xl max-w-xl">
                Explore premium properties for sale, rent, or short stays in Nairobi's most desirable neighborhoods.
            </p>
        </div>

        {{-- Enhanced Search Form with glassmorphism design --}}
        <div class="mt-10 max-w-4xl">
            <div class="rounded-2xl bg-white/8 dark:bg-white/5 backdrop-blur-xl p-6 shadow-2xl ring-1 ring-white/20 dark:ring-white/10 transition-all duration-300">
                {{-- Listing Type Tabs with modern design --}}
                <div class="mb-6">
                    <label class="text-sm font-medium text-white/90 dark:text-white/80">I'm looking to:</label>
                    <div class="mt-2.5 flex flex-wrap gap-2 rounded-xl bg-white/5 dark:bg-zinc-800/30 p-1.5">
                        @foreach(['all' => 'All Properties', 'sale' => 'Buy', 'rent' => 'Rent', 'airbnb' => 'Book Stay'] as $value => $label)
                            <button
                                wire:click="$set('listingType', '{{ $value }}')"
                                type="button"
                                @class([
                                    'rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-300',
                                    'bg-[#02c9c2] text-zinc-900 shadow-lg shadow-[#02c9c2]/20' => $listingType === $value,
                                    'text-white/80 hover:bg-white/15 hover:text-white dark:text-white/70 dark:hover:bg-white/10' => $listingType !== $value,
                                ])
                            >
                                {{ $label }}
                                @if(isset($this->propertyStats[$value]))
                                    <span class="ml-1.5 rounded-full bg-zinc-900/30 dark:bg-white/20 px-2 py-0.5 text-xs">{{ $this->propertyStats[$value] }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
                
                <div class="grid gap-5 sm:gap-6 md:grid-cols-2 lg:grid-cols-4">
                    {{-- Property Type Selection with modernized dropdown --}}
                    <div>
                        <label class="block text-sm font-medium text-white/90 dark:text-white/80 mb-1.5">Property Type</label>
                        <div class="relative">
                            <select 
                                wire:model.live="type"
                                class="w-full appearance-none rounded-lg border-0 bg-white/10 dark:bg-zinc-800/50 py-3 pl-4 pr-10 text-white ring-1 ring-white/20 dark:ring-white/10 transition-all duration-200 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:bg-white/15 dark:focus:bg-zinc-800/80 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">Any Type</option>
                                @foreach($propertyTypes as $propertyType)
                                    <option value="{{ $propertyType->id }}">{{ $propertyType->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Location Selection with modernized dropdown --}}
                    <div>
                        <label class="block text-sm font-medium text-white/90 dark:text-white/80 mb-1.5">Location</label>
                        <div class="relative">
                            <select 
                                wire:model.live="city"
                                class="w-full appearance-none rounded-lg border-0 bg-white/10 dark:bg-zinc-800/50 py-3 pl-4 pr-10 text-white ring-1 ring-white/20 dark:ring-white/10 transition-all duration-200 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:bg-white/15 dark:focus:bg-zinc-800/80 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">Any Location</option>
                                @foreach($cities as $availableCity)
                                    <option value="{{ $availableCity }}">{{ $availableCity }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Search Input with modern styling --}}
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-white/90 dark:text-white/80 mb-1.5">Keywords</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <svg class="h-4 w-4 text-zinc-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search by location, title or features..."
                                class="block w-full rounded-lg border-0 bg-white/10 dark:bg-zinc-800/50 py-3 pl-10 pr-3 text-white ring-1 ring-white/20 dark:ring-white/10 transition-all duration-200 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:bg-white/15 dark:focus:bg-zinc-800/80 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                        </div>
                    </div>
                </div>
                
                {{-- Modern Search Button with animation --}}
                <div class="mt-6">
                    <button
                        wire:click="search"
                        class="group w-full rounded-lg bg-gradient-to-r from-[#02c9c2] to-[#02c9c2]/80 dark:from-[#02c9c2] dark:to-[#02c9c2]/90 px-4 py-3 font-medium text-zinc-900 dark:text-zinc-900 shadow-lg shadow-[#02c9c2]/20 transition-all duration-300 hover:shadow-xl hover:shadow-[#02c9c2]/30 focus:outline-none focus:ring-2 focus:ring-[#02c9c2] focus:ring-offset-2 focus:ring-offset-zinc-900"
                    >
                        <span class="flex items-center justify-center">
                            <svg class="mr-2.5 h-5 w-5 transition-transform duration-300 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-base">Find Your Perfect Property</span>
                        </span>
                    </button>
                </div>
            </div>

            {{-- Property Stats with enhanced card design --}}
            <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach(['sale' => ['For Sale', 'shopping-bag'], 'rent' => ['For Rent', 'home'], 'airbnb' => ['Short Stays', 'calendar']] as $type => $data)
                    <div class="group flex items-center gap-5 rounded-xl bg-white/8 dark:bg-white/5 backdrop-blur-md p-5 transition-all duration-300 hover:bg-white/12 dark:hover:bg-white/8 hover:shadow-xl hover:shadow-[#02c9c2]/5">
                        <div class="rounded-xl bg-[#02c9c2]/10 dark:bg-[#02c9c2]/15 p-3.5 text-[#02c9c2] dark:text-[#02c9c2] transition-all duration-300 group-hover:bg-[#02c9c2]/20 dark:group-hover:bg-[#02c9c2]/20">
                            @if($data[1] === 'shopping-bag')
                                <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 004.25 22.5h15.5a1.875 1.875 0 001.865-2.071l-1.263-12a1.875 1.875 0 00-1.865-1.679H16.5V6a4.5 4.5 0 10-9 0zM12 3a3 3 0 00-3 3v.75h6V6a3 3 0 00-3-3zm-3 8.25a3 3 0 106 0v-.75a.75.75 0 011.5 0v.75a4.5 4.5 0 11-9 0v-.75a.75.75 0 011.5 0v.75z" clip-rule="evenodd" />
                                </svg>
                            @elseif($data[1] === 'home')
                                <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                                    <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198c.03-.028.061-.056.091-.086L12 5.43z" />
                                </svg>
                            @else
                                <svg class="h-7 w-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12.75 12.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM7.5 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM8.25 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM9.75 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM10.5 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM12.75 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM14.25 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM15 17.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM16.5 15.75a.75.75 0 100-1.5.75.75 0 000 1.5zM15 12.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM16.5 13.5a.75.75 0 100-1.5.75.75 0 000 1.5z" />
                                    <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                        <div>
                            <div class="text-sm font-medium text-zinc-300 dark:text-zinc-300">{{ $data[0] }}</div>
                            <div class="mt-1 text-2xl font-bold text-white dark:text-white transition-all duration-300 group-hover:text-[#02c9c2] dark:group-hover:text-[#02c9c2]">
                                {{ number_format($this->propertyStats[$type] ?? 0) }}
                                <span class="text-sm font-normal text-zinc-400 dark:text-zinc-400">properties</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
