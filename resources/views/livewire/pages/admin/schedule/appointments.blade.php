<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\ViewingAppointment;
use App\Models\Property;
use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    #[State]
    public $search = '';

    #[State]
    public $sortField = 'scheduled_at';

    #[State]
    public $sortDirection = 'desc';

    #[State]
    public $showFilters = false;

    #[State]
    public $showAppointmentModal = false;

    #[State]
    public $selectedAppointment = null;

    #[State]
    public $filters = [
        'status' => '',
        'property_type' => '',
        'date_range' => '',
    ];

    public function mount(): void
    {
        $this->authorize('view_appointments');
    }

    public function getAppointmentsProperty()
    {
        return ViewingAppointment::query()
            ->with(['property', 'client'])
            ->when($this->filters['status'], fn($query) => 
                $query->where('status', $this->filters['status'])
            )
            ->when($this->filters['property_type'], fn($query) => 
                $query->whereHas('property', function($q) {
                    $q->where('type', $this->filters['property_type']);
                })
            )
            ->when($this->filters['date_range'], function($query) {
                return match($this->filters['date_range']) {
                    'today' => $query->whereDate('scheduled_at', today()),
                    'tomorrow' => $query->whereDate('scheduled_at', today()->addDay()),
                    'week' => $query->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()]),
                    'month' => $query->whereMonth('scheduled_at', now()->month)->whereYear('scheduled_at', now()->year),
                    default => $query
                };
            })
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->whereHas('client', function($q) {
                        $q->where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%");
                    })
                    ->orWhereHas('property', function($q) {
                        $q->where('title', 'like', "%{$this->search}%")
                            ->orWhere('location', 'like', "%{$this->search}%");
                    });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
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
        $this->reset(['search', 'filters']);
    }

    public function viewDetails($id): void
    {
        $this->selectedAppointment = ViewingAppointment::with(['property', 'client'])->findOrFail($id);
        $this->showAppointmentModal = true;
    }

    public function updateStatus($id, $status): void
    {
        $appointment = ViewingAppointment::findOrFail($id);
        $appointment->update(['status' => $status]);
        $this->dispatch('notify', type: 'success', message: 'Appointment status updated successfully.');
        
        if ($this->showAppointmentModal) {
            $this->showAppointmentModal = false;
        }
    }
}; ?>

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
                    <flux:icon name="calendar" class="w-8 h-8 text-[#02c9c2]" />
                    Appointments List
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Manage and track all property viewing appointments
                </p>
            </div>
            
            <a href="{{ route('admin.schedule.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
            >
                <flux:icon name="calendar-days" class="w-5 h-5 mr-2" />
                Calendar View
            </a>
        </div>

        <!-- Search and Filters -->
        <div class="mt-8 space-y-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input with Animation -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <flux:icon wire:loading.remove wire:target="search" name="magnifying-glass" class="h-5 w-5 text-gray-400" />
                        <flux:icon wire:loading wire:target="search" name="arrow-path" class="h-5 w-5 text-[#02c9c2] animate-spin" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="search"
                        placeholder="Search by client name, email, property title or location..."
                        class="block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                    >
                </div>

                <!-- Filter Toggle Button with Badge -->
                <button wire:click="toggleFilters"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                >
                    <flux:icon name="funnel" class="w-5 h-5 mr-2" />
                    Filters
                    <span class="ml-2 text-xs bg-white/20 rounded-full px-2 py-0.5">
                        {{ count(array_filter($filters)) }}
                    </span>
                </button>
            </div>

            <!-- Enhanced Filters Panel with Transitions -->
            <div x-show="$wire.showFilters"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="p-6 bg-white/70 dark:bg-gray-800/70 rounded-lg border border-gray-200 dark:border-gray-700 backdrop-blur-xl shadow-sm"
            >
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <!-- Status Filter -->
                    <div class="space-y-2">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select wire:model.live="filters.status" id="status"
                                class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Status</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <!-- Property Type Filter -->
                    <div class="space-y-2">
                        <label for="property_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property Type</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="building-office" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select wire:model.live="filters.property_type" id="property_type"
                                class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Types</option>
                                <option value="residential">Residential</option>
                                <option value="commercial">Commercial</option>
                                <option value="industrial">Industrial</option>
                            </select>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="space-y-2">
                        <label for="date_range" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="calendar" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select wire:model.live="filters.date_range" id="date_range"
                                class="appearance-none block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-2.5 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="tomorrow">Tomorrow</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Reset Filters Button -->
                <div class="flex justify-end mt-6">
                    <button wire:click="resetFilters"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-150"
                    >
                        <flux:icon name="arrow-path" class="w-4 h-4 mr-2" />
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Appointments Table with Card-Based UI -->
    <div class="p-6">
        <!-- Status filters for quick access -->
        <div class="mb-6 flex flex-wrap gap-3">
            <button 
                wire:click="$set('filters.status', '')" 
                @class([
                    'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                    'bg-white dark:bg-gray-800 text-gray-700 dark:text-white shadow-sm ring-1 ring-gray-300 dark:ring-gray-700' => $filters['status'] === '',
                    'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' => $filters['status'] !== '',
                ])
            >
                <flux:icon name="calendar" class="w-5 h-5 mr-2" />
                All
            </button>
            <button 
                wire:click="$set('filters.status', 'scheduled')"
                @class([
                    'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                    'bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 shadow-sm ring-1 ring-blue-200 dark:ring-blue-800' => $filters['status'] === 'scheduled',
                    'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/30' => $filters['status'] !== 'scheduled',
                ])
            >
                <flux:icon name="clock" class="w-5 h-5 mr-2" />
                Scheduled
            </button>
            <button 
                wire:click="$set('filters.status', 'completed')"
                @class([
                    'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                    'bg-green-50 dark:bg-green-900/50 text-green-700 dark:text-green-300 shadow-sm ring-1 ring-green-200 dark:ring-green-800' => $filters['status'] === 'completed',
                    'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-green-900/30' => $filters['status'] !== 'completed',
                ])
            >
                <flux:icon name="check" class="w-5 h-5 mr-2" />
                Completed
            </button>
            <button 
                wire:click="$set('filters.status', 'cancelled')"
                @class([
                    'inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
                    'bg-red-50 dark:bg-red-900/50 text-red-700 dark:text-red-300 shadow-sm ring-1 ring-red-200 dark:ring-red-800' => $filters['status'] === 'cancelled',
                    'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/30' => $filters['status'] !== 'cancelled',
                ])
            >
                <flux:icon name="x-mark" class="w-5 h-5 mr-2" />
                Cancelled
            </button>
        </div>

        <!-- Responsive Table Design -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl">
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th wire:click="sort('scheduled_at')" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150">
                                <div class="flex items-center gap-2">
                                    Date & Time
                                    @if ($sortField === 'scheduled_at')
                                        <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4 text-[#02c9c2]" />
                                    @else
                                        <flux:icon name="arrows-up-down" class="w-4 h-4 opacity-50" />
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Property</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($this->appointments as $appointment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    <div class="flex flex-col">
                                        <span>{{ $appointment->scheduled_at->format('M d, Y') }}</span>
                                        <span class="text-gray-500 dark:text-gray-400 text-xs">
                                            {{ $appointment->scheduled_at->format('h:i A') }}
                                        </span>
                                        <span class="text-xs mt-1 text-gray-500 dark:text-gray-400">
                                            {{ $appointment->scheduled_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover border-2 border-white dark:border-gray-800 shadow-sm" src="{{ $appointment->client->profile_photo_url }}" alt="{{ $appointment->client->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $appointment->client->name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $appointment->client->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-md overflow-hidden">
                                            @if($appointment->property->featured_image_url)
                                                <img class="h-10 w-10 object-cover" src="{{ $appointment->property->featured_image_url }}" alt="">
                                            @else
                                                <div class="h-10 w-10 flex items-center justify-center">
                                                    <flux:icon name="building-office" class="h-5 w-5 text-gray-400" />
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $appointment->property->title }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $appointment->property->location }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' => $appointment->status === 'scheduled',
                                        'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' => $appointment->status === 'completed',
                                        'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' => $appointment->status === 'cancelled',
                                    ])>
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 
                                            {{ $appointment->status === 'scheduled' ? 'bg-blue-500 dark:bg-blue-400' : '' }}
                                            {{ $appointment->status === 'completed' ? 'bg-green-500 dark:bg-green-400' : '' }}
                                            {{ $appointment->status === 'cancelled' ? 'bg-red-500 dark:bg-red-400' : '' }}
                                        "></span>
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button wire:click="viewDetails({{ $appointment->id }})" 
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-[#02c9c2] hover:bg-[#02c9c2]/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] transition-colors duration-150"
                                            title="View Details"
                                        >
                                            <flux:icon name="eye" class="w-5 h-5" />
                                        </button>
                                        @if($appointment->status === 'scheduled')
                                            <button wire:click="updateStatus({{ $appointment->id }}, 'completed')"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150"
                                                title="Mark as Completed"
                                            >
                                                <flux:icon name="check-circle" class="w-5 h-5" />
                                            </button>
                                            <button wire:click="updateStatus({{ $appointment->id }}, 'cancelled')"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150"
                                                title="Cancel Appointment"
                                            >
                                                <flux:icon name="x-circle" class="w-5 h-5" />
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                            <flux:icon name="calendar" class="w-10 h-10 text-gray-400 dark:text-gray-600" />
                                        </div>
                                        <h3 class="mt-2 text-base font-medium text-gray-900 dark:text-white">No appointments found</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm text-center">
                                            {{ $search || count(array_filter($filters)) ? 'Try adjusting your search or filter criteria to find what you\'re looking for.' : 'No upcoming appointments are currently scheduled. Create a new appointment from the Calendar view.' }}
                                        </p>
                                        
                                        @if($search || count(array_filter($filters)))
                                            <button wire:click="resetFilters" class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-150">
                                                <flux:icon name="arrow-path" class="w-4 h-4 mr-2" />
                                                Clear Filters
                                            </button>
                                        @else
                                            <a href="{{ route('admin.schedule.index') }}" class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-[#02c9c2] to-[#012e2b] rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] transition-all duration-150 shadow-sm">
                                                <flux:icon name="calendar-days" class="w-5 h-5 mr-2" />
                                                Go to Calendar
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Enhanced Pagination -->
            @if($this->appointments->hasPages())
                <div class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                    {{ $this->appointments->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Enhanced Appointment Details Modal -->
    <flux:modal wire:model="showAppointmentModal" class="w-full max-w-4xl" @close="$wire.resetForm()">
        @if($selectedAppointment)
            <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
                <!-- Modal Header with Status-Based Gradient -->
                <div @class([
                    'bg-gradient-to-r px-6 py-4 border-b border-gray-200 dark:border-gray-700',
                    'from-blue-500/20 to-blue-600/20 dark:from-blue-900/30 dark:to-blue-700/30' => $selectedAppointment->status === 'scheduled',
                    'from-green-500/20 to-green-600/20 dark:from-green-900/30 dark:to-green-700/30' => $selectedAppointment->status === 'completed',
                    'from-red-500/20 to-red-600/20 dark:from-red-900/30 dark:to-red-700/30' => $selectedAppointment->status === 'cancelled',
                ])>
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <flux:icon name="calendar" class="w-5 h-5 text-[#02c9c2]" />
                            Appointment Details
                        </h3>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <span @class([
                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' => $selectedAppointment->status === 'scheduled',
                                'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' => $selectedAppointment->status === 'completed',
                                'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' => $selectedAppointment->status === 'cancelled',
                            ])>
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 
                                    {{ $selectedAppointment->status === 'scheduled' ? 'bg-blue-500 dark:bg-blue-400' : '' }}
                                    {{ $selectedAppointment->status === 'completed' ? 'bg-green-500 dark:bg-green-400' : '' }}
                                    {{ $selectedAppointment->status === 'cancelled' ? 'bg-red-500 dark:bg-red-400' : '' }}
                                "></span>
                                {{ ucfirst($selectedAppointment->status) }}
                            </span>
                        </div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">
                            ID: {{ $selectedAppointment->id }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <!-- Left Column - Details -->
                        <div class="lg:col-span-3 space-y-8">
                            <!-- Date & Time Section -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Date & Time</h4>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-[#02c9c2]/10 flex items-center justify-center">
                                            <flux:icon name="clock" class="w-5 h-5 text-[#02c9c2]" />
                                        </div>
                                        <div>
                                            <p class="text-base font-medium text-gray-900 dark:text-white">
                                                {{ $selectedAppointment->scheduled_at->format('F d, Y') }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $selectedAppointment->scheduled_at->format('h:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $selectedAppointment->scheduled_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Client Information -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Client Information</h4>
                                <div class="flex items-center gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                    <img class="h-16 w-16 rounded-full object-cover border-2 border-white dark:border-gray-800 shadow-sm" 
                                         src="{{ $selectedAppointment->client->profile_photo_url }}" 
                                         alt="{{ $selectedAppointment->client->name }}">
                                    <div>
                                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ $selectedAppointment->client->name }}</p>
                                        <div class="mt-1 space-y-1">
                                            <p class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                <flux:icon name="envelope" class="w-4 h-4 mr-2" />
                                                {{ $selectedAppointment->client->email }}
                                            </p>
                                            @if($selectedAppointment->client->phone)
                                                <p class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                    <flux:icon name="phone" class="w-4 h-4 mr-2" />
                                                    {{ $selectedAppointment->client->phone }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="mt-3">
                                            <a href="{{ route('admin.clients.view', $selectedAppointment->client->id) }}" class="inline-flex items-center text-sm text-[#02c9c2] hover:text-[#02c9c2]/80">
                                                <span>View Client Profile</span>
                                                <flux:icon name="arrow-right" class="w-4 h-4 ml-1" />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notes Section -->
                            @if($selectedAppointment->notes)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Notes</h4>
                                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                                        <p class="text-gray-900 dark:text-white whitespace-pre-line">{{ $selectedAppointment->notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Right Column - Property Information -->
                        <div class="lg:col-span-2">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Property Information</h4>
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
                                <div class="aspect-w-16 aspect-h-12 bg-gray-200 dark:bg-gray-700">
                                    @if($selectedAppointment->property->featured_image_url)
                                        <img 
                                            src="{{ $selectedAppointment->property->featured_image_url }}" 
                                            alt="{{ $selectedAppointment->property->title }}" 
                                            class="object-cover w-full h-full"
                                        >
                                    @else
                                        <div class="flex items-center justify-center w-full h-full">
                                            <flux:icon name="building-office" class="w-16 h-16 text-gray-400" />
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="p-4">
                                    <h5 class="text-base font-medium text-gray-900 dark:text-white">
                                        {{ $selectedAppointment->property->title }}
                                    </h5>
                                    <p class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                        <flux:icon name="map-pin" class="w-4 h-4 mr-1 flex-shrink-0" />
                                        <span>{{ $selectedAppointment->property->location }}</span>
                                    </p>
                                    
                                    <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center gap-1">
                                            <flux:icon name="home-modern" class="w-4 h-4" />
                                            <span>{{ $selectedAppointment->property->bedrooms }} bd</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <flux:icon name="beaker" class="w-4 h-4" />
                                            <span>{{ $selectedAppointment->property->bathrooms }} ba</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <flux:icon name="square-2-stack" class="w-4 h-4" />
                                            <span>{{ number_format($selectedAppointment->property->square_feet) }} sq ft</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <a href="{{ route('admin.properties.view', $selectedAppointment->property->id) }}" class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-[#02c9c2] bg-[#02c9c2]/10 rounded-lg hover:bg-[#02c9c2]/20 transition-colors duration-150">
                                            View Property Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-8 pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span>Created: {{ $selectedAppointment->created_at->format('M d, Y') }}</span>
                            @if($selectedAppointment->created_at != $selectedAppointment->updated_at)
                                <span class="mx-2">•</span>
                                <span>Updated: {{ $selectedAppointment->updated_at->format('M d, Y') }}</span>
                            @endif
                        </div>
                        
                        @if($selectedAppointment->status === 'scheduled')
                            <div class="flex flex-wrap gap-3">
                                <button wire:click="updateStatus({{ $selectedAppointment->id }}, 'completed')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150"
                                >
                                    <flux:icon name="check" class="w-4 h-4 mr-2" />
                                    Mark as Completed
                                </button>
                                <button wire:click="updateStatus({{ $selectedAppointment->id }}, 'cancelled')"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150"
                                >
                                    <flux:icon name="x-mark" class="w-4 h-4 mr-2" />
                                    Cancel Appointment
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </flux:modal>

    <!-- Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>

    <style>
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</div>
