<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Livewire\Volt\{state, computed};
use App\Models\ViewingAppointment;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;

new class extends Component {
    use WithPagination;

    #[State]
    public $selectedDate = '';
    
    #[State]
    public $selectedProperty = null;
    
    #[State]
    public $showScheduleModal = false;
    
    #[State]
    public $showAppointmentDetailsModal = false;
    
    #[State]
    public $selectedAppointment = null;
    
    #[State]
    public $isLoading = false;
    
    #[State]
    public $calendarMonth;
    
    #[State]
    public $calendarYear;
    
    #[State]
    public $calendarDays = [];
    
    #[State]
    public $availableProperties = [];
    
    #[State]
    public $clients = [];
    
    #[State]
    public $form = [
        'property_id' => '',
        'client_id' => '',
        'scheduled_at' => '',
        'notes' => '',
        'status' => 'scheduled'
    ];

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->calendarMonth = now()->month;
        $this->calendarYear = now()->year;
        $this->authorize('schedule_appointments');
    }

    #[Computed]
    public function dailyAppointments()
    {
        $this->isLoading = true;
        
        try {
            return ViewingAppointment::query()
                ->with(['property', 'client'])
                ->when($this->selectedProperty, function($query) {
                    return $query->where('property_id', $this->selectedProperty);
                })
                ->whereDate('scheduled_at', $this->selectedDate)
                ->orderBy('scheduled_at')
                ->get()
                ->groupBy(function($appointment) {
                    return $appointment->scheduled_at->format('H:i');
                });
        } finally {
            $this->isLoading = false;
        }
    }

    #[Computed]
    public function availableProperties()
    {
        return Property::where('status', 'active')->get();
    }
    
    #[Computed]
    public function clients()
    {
        return User::role('client')->orderBy('name')->get();
    }
    
    #[Computed]
    public function calendarDays()
    {
        // Generate calendar days for mini calendar
        $firstDay = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        
        $days = [];
        $currentDay = $firstDay->copy()->startOfWeek();
        
        while ($currentDay <= $lastDay->copy()->endOfWeek()) {
            $days[] = [
                'date' => $currentDay->format('Y-m-d'),
                'day' => $currentDay->format('j'),
                'isCurrentMonth' => $currentDay->month === (int)$this->calendarMonth,
                'isToday' => $currentDay->isToday(),
                'isSelected' => $currentDay->format('Y-m-d') === $this->selectedDate,
                'hasAppointments' => ViewingAppointment::whereDate('scheduled_at', $currentDay->format('Y-m-d'))
                    ->when($this->selectedProperty, function($query) {
                        return $query->where('property_id', $this->selectedProperty);
                    })
                    ->exists()
            ];
            
            $currentDay->addDay();
        }
        
        return $days;
    }

    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarMonth = $date->month;
        $this->calendarYear = $date->year;
    }
    
    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarMonth = $date->month;
        $this->calendarYear = $date->year;
    }

    public function selectDate($date): void
    {
        $this->selectedDate = $date;
    }

    public function viewAppointmentDetails($id): void
    {
        $this->selectedAppointment = ViewingAppointment::with(['property', 'client'])->findOrFail($id);
        $this->showAppointmentDetailsModal = true;
    }

    public function createAppointment(): void
    {
        $this->showScheduleModal = true;
        $this->form = [
            'property_id' => $this->selectedProperty ?: '',
            'client_id' => '',
            'scheduled_at' => now()->format('Y-m-d\TH:i'),
            'notes' => '',
            'status' => 'scheduled'
        ];
    }
    
    public function updateAppointmentStatus($id, $status): void
    {
        $appointment = ViewingAppointment::findOrFail($id);
        $appointment->update(['status' => $status]);
        
        $this->dispatch('notify', type: 'success', message: 'Appointment status updated.');
        
        if ($this->showAppointmentDetailsModal) {
            $this->showAppointmentDetailsModal = false;
        }
    }

    public function saveAppointment(): void
    {
        $this->validate([
            'form.property_id' => 'required|exists:properties,id',
            'form.client_id' => 'required|exists:users,id',
            'form.scheduled_at' => 'required|date',
            'form.notes' => 'nullable|string|max:500',
            'form.status' => 'required|in:scheduled,completed,cancelled'
        ]);

        ViewingAppointment::create($this->form);
        
        $this->showScheduleModal = false;
        $this->dispatch('notify', type: 'success', message: 'Appointment scheduled successfully.');
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
                    Property Viewing Schedule
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Manage appointments and property viewings
                </p>
            </div>
            
            <button 
                wire:click="createAppointment"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                wire:loading.attr="disabled"
            >
                <flux:icon wire:loading.remove name="plus" class="w-5 h-5 mr-2" />
                <flux:icon wire:loading name="arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                New Appointment
            </button>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row h-[calc(100vh-14rem)]">
        <!-- Calendar Sidebar -->
        <div class="lg:w-80 border-r border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-900/50 p-6 overflow-y-auto">
            <!-- Mini Calendar Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-900 dark:text-white">
                        {{ Carbon::createFromDate($calendarYear, $calendarMonth, 1)->format('F Y') }}
                    </h3>
                    <div class="flex space-x-1">
                        <button wire:click="previousMonth" class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                            <flux:icon name="chevron-left" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        </button>
                        <button wire:click="nextMonth" class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                            <flux:icon name="chevron-right" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        </button>
                    </div>
                </div>
                
                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 text-center mb-1">
                    @foreach(['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $day)
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $day }}</div>
                    @endforeach
                </div>
                
                <div class="grid grid-cols-7 gap-1">
                    @foreach($this->calendarDays as $calendarDay)
                        <button 
                            wire:key="calendar-day-{{ $calendarDay['date'] }}"
                            wire:click="selectDate('{{ $calendarDay['date'] }}')"
                            @class([
                                'w-9 h-9 flex items-center justify-center text-sm rounded-full relative',
                                'font-bold' => $calendarDay['isToday'],
                                'text-[#02c9c2] bg-[#02c9c2]/10 ring-2 ring-[#02c9c2]' => $calendarDay['isSelected'],
                                'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700' => !$calendarDay['isSelected'] && $calendarDay['isCurrentMonth'],
                                'text-gray-400 dark:text-gray-600' => !$calendarDay['isCurrentMonth'],
                            ])
                        >
                            {{ $calendarDay['day'] }}
                            
                            @if($calendarDay['hasAppointments'])
                                <span class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full {{ $calendarDay['isSelected'] ? 'bg-white' : 'bg-[#02c9c2]' }}"></span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
            
            <!-- Today's Date Card -->
            <div class="bg-gradient-to-r from-[#02c9c2]/20 to-[#012e2b]/20 dark:from-[#02c9c2]/30 dark:to-[#012e2b]/30 backdrop-blur-sm rounded-xl p-4 mb-6">
                <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">SELECTED DATE</h3>
                <div class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ Carbon::parse($selectedDate)->format('F d, Y') }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    {{ Carbon::parse($selectedDate)->format('l') }}
                </div>
                
                <div class="mt-3 flex gap-2">
                    <button 
                        wire:click="selectDate('{{ now()->subDay()->format('Y-m-d') }}')"
                        class="flex-1 inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium rounded-md bg-white/50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-800 transition-colors duration-150"
                    >
                        Previous
                    </button>
                    <button 
                        wire:click="selectDate('{{ now()->format('Y-m-d') }}')"
                        class="flex-1 inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium rounded-md bg-[#02c9c2]/20 text-[#02c9c2] hover:bg-[#02c9c2]/30 transition-colors duration-150"
                    >
                        Today
                    </button>
                    <button 
                        wire:click="selectDate('{{ now()->addDay()->format('Y-m-d') }}')"
                        class="flex-1 inline-flex justify-center items-center px-3 py-1.5 text-xs font-medium rounded-md bg-white/50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-800 transition-colors duration-150"
                    >
                        Next
                    </button>
                </div>
            </div>

            <!-- Properties Filter -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-medium text-gray-500 dark:text-gray-400">FILTER BY PROPERTIES</h3>
                    @if($selectedProperty)
                        <button 
                            wire:click="$set('selectedProperty', null)"
                            class="text-xs text-[#02c9c2] hover:underline"
                        >
                            Clear
                        </button>
                    @endif
                </div>
                
                <div class="space-y-1 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($this->availableProperties as $property)
                        <button 
                            wire:click="$set('selectedProperty', {{ $property->id }})"
                            wire:key="property-{{ $property->id }}"
                            @class([
                                'w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg transition-all duration-150',
                                'bg-[#02c9c2]/10 text-[#02c9c2] font-medium' => $selectedProperty === $property->id,
                                'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800/50' => $selectedProperty !== $property->id,
                            ])
                        >
                            <div class="flex items-center gap-2 truncate">
                                <div class="w-6 h-6 bg-gray-100 dark:bg-gray-700 rounded-md flex items-center justify-center flex-shrink-0">
                                    <flux:icon name="building-office" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                </div>
                                <span class="truncate">{{ $property->title }}</span>
                            </div>
                        </button>
                    @empty
                        <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                            No properties available
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Schedule Grid -->
        <div class="flex-1 overflow-y-auto p-6 relative">
            <!-- Loading Overlay -->
            <div wire:loading.delay wire:target="selectDate, selectedProperty" class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="flex items-center space-x-4">
                    <flux:icon name="arrow-path" class="w-8 h-8 text-[#02c9c2] animate-spin" />
                    <span class="text-gray-600 dark:text-gray-300 font-medium">Loading schedule...</span>
                </div>
            </div>
            
            <!-- Schedule Header -->
            <div class="bg-white dark:bg-gray-800 rounded-t-xl shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ Carbon::parse($selectedDate)->format('l, F d') }} Schedule
                    </h3>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        @if($selectedProperty)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#02c9c2]/10 text-[#02c9c2]">
                                Filtered: {{ $this->availableProperties->where('id', $selectedProperty)->first()->title ?? 'Unknown Property' }}
                            </span>
                        @else
                            <span>All Properties</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Time Slots -->
            <div class="bg-white dark:bg-gray-800 rounded-b-xl shadow overflow-hidden mb-6">
                @php
                    $startHour = 8;
                    $endHour = 20;
                    $hasDailyAppointments = false;
                @endphp

                @for($hour = $startHour; $hour <= $endHour; $hour++)
                    @php
                        $hourFormatted = sprintf('%02d:00', $hour);
                        $hourAppointments = $this->dailyAppointments[$hourFormatted] ?? [];
                        if(count($hourAppointments) > 0) $hasDailyAppointments = true;
                    @endphp
                    
                    <div @class([
                        'flex border-b border-gray-200 dark:border-gray-700 last:border-b-0',
                        'bg-gray-50 dark:bg-gray-800/80' => $hour % 2 === 0
                    ])>
                        <!-- Time Column -->
                        <div class="w-20 flex-shrink-0 py-4 px-4 text-right text-sm text-gray-500 dark:text-gray-400 font-medium border-r border-gray-200 dark:border-gray-700">
                            {{ date('g:i A', strtotime("$hour:00")) }}
                        </div>

                        <!-- Appointments Column -->
                        <div class="flex-1 min-h-[80px] p-2 relative">
                            @if(count($hourAppointments) > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2 h-full">
                                    @foreach($hourAppointments as $appointment)
                                        <div 
                                            wire:key="appointment-{{ $appointment->id }}"
                                            wire:click="viewAppointmentDetails({{ $appointment->id }})"
                                            @class([
                                                'cursor-pointer p-3 rounded-lg border border-transparent hover:border-gray-300 dark:hover:border-gray-600 shadow-sm flex flex-col justify-between transition-all duration-150 h-full',
                                                'bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50' => $appointment->status === 'scheduled',
                                                'bg-green-50 dark:bg-green-900/30 hover:bg-green-100 dark:hover:bg-green-900/50' => $appointment->status === 'completed',
                                                'bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50' => $appointment->status === 'cancelled',
                                            ])
                                        >
                                            <div>
                                                <div class="flex items-center justify-between">
                                                    <span @class([
                                                        'inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full',
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $appointment->status === 'scheduled',
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $appointment->status === 'completed',
                                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $appointment->status === 'cancelled'
                                                    ])>
                                                        {{ ucfirst($appointment->status) }}
                                                    </span>
                                                    <span class="text-sm text-gray-600 dark:text-gray-300 font-medium">
                                                        {{ $appointment->scheduled_at->format('g:i A') }}
                                                    </span>
                                                </div>
                                                <div class="mt-2 font-medium text-gray-900 dark:text-white">
                                                    {{ $appointment->property->title }}
                                                </div>
                                                <div class="flex items-center gap-1 mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    <flux:icon name="user" class="w-4 h-4" />
                                                    <span>{{ $appointment->client->name }}</span>
                                                </div>
                                            </div>
                                            
                                            @if($appointment->notes)
                                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                                                    {{ $appointment->notes }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="h-full flex items-center justify-center">
                                    <div class="text-gray-400 dark:text-gray-600 text-sm">No appointments</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor
                
                @if(!$hasDailyAppointments)
                    <div class="py-16 flex flex-col items-center justify-center text-center px-4">
                        <div class="h-24 w-24 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                            <flux:icon name="calendar" class="w-12 h-12 text-gray-400 dark:text-gray-600" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No appointments scheduled</h3>
                        <p class="text-gray-500 dark:text-gray-400 max-w-md">
                            There are no appointments scheduled for {{ Carbon::parse($selectedDate)->format('F d, Y') }}. 
                            Click the "New Appointment" button to schedule one.
                        </p>
                        <button 
                            wire:click="createAppointment"
                            class="mt-4 inline-flex items-center px-4 py-2 bg-[#02c9c2] text-white font-medium rounded-lg text-sm hover:bg-[#02c9c2]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] transition-all duration-150"
                        >
                            <flux:icon name="plus" class="w-5 h-5 mr-2" />
                            New Appointment
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Enhanced Schedule Modal -->
    <flux:modal wire:model="showScheduleModal" class="w-full max-w-4xl !p-0" @close="$wire.resetForm()">
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
            <div class="bg-gradient-to-r from-[#02c9c2]/20 to-[#012e2b]/20 dark:from-[#02c9c2]/30 dark:to-[#012e2b]/30 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="calendar-days" class="w-5 h-5 text-[#02c9c2]" />
                        Schedule New Appointment
                    </h3>
                </div>
            </div>

            <div class="p-6">
                <form wire:submit="saveAppointment" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Property Selection -->
                        <div>
                            <label for="property" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Property</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="building-office" class="h-5 w-5 text-gray-400" />
                                </div>
                                <select
                                    wire:model="form.property_id"
                                    id="property"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                >
                                    <option value="">Select Property</option>
                                    @foreach($this->availableProperties as $property)
                                        <option value="{{ $property->id }}">{{ $property->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('form.property_id') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Client Selection -->
                        <div>
                            <label for="client" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="user" class="h-5 w-5 text-gray-400" />
                                </div>
                                <select
                                    wire:model="form.client_id"
                                    id="client"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                >
                                    <option value="">Select Client</option>
                                    @foreach($this->clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('form.client_id') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Date & Time -->
                        <div>
                            <label for="scheduled_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date & Time</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="clock" class="h-5 w-5 text-gray-400" />
                                </div>
                                <input 
                                    type="datetime-local" 
                                    wire:model="form.scheduled_at" 
                                    id="scheduled_at"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                >
                            </div>
                            @error('form.scheduled_at') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                                </div>
                                <select
                                    wire:model="form.status"
                                    id="status"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                >
                                    <option value="scheduled">Scheduled</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            @error('form.status') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <div class="relative">
                            <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                                <flux:icon name="document-text" class="h-5 w-5 text-gray-400" />
                            </div>
                            <textarea
                                wire:model="form.notes"
                                id="notes"
                                rows="3"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                placeholder="Add any important notes about this appointment..."
                            ></textarea>
                        </div>
                        @error('form.notes') 
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" wire:click="$set('showScheduleModal', false)"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white rounded-lg text-sm font-medium hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] shadow-sm">
                            <flux:icon wire:loading wire:target="saveAppointment" name="arrow-path" class="w-4 h-4 mr-2 animate-spin" />
                            Schedule Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </flux:modal>
    
    <!-- Appointment Details Modal -->
    <flux:modal wire:model="showAppointmentDetailsModal" class="w-full max-w-4xl !p-0" @close="$wire.resetForm()">
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
            <div @class([
                'bg-gradient-to-r px-6 py-4 border-b border-gray-200 dark:border-gray-700',
                'from-blue-500/20 to-blue-600/20 dark:from-blue-900/30 dark:to-blue-700/30' => $selectedAppointment?->status === 'scheduled',
                'from-green-500/20 to-green-600/20 dark:from-green-900/30 dark:to-green-700/30' => $selectedAppointment?->status === 'completed',
                'from-red-500/20 to-red-600/20 dark:from-red-900/30 dark:to-red-700/30' => $selectedAppointment?->status === 'cancelled',
            ])>
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="eye" class="w-5 h-5 text-[#02c9c2]" />
                        Appointment Details
                    </h3>
                </div>
            </div>

            <div class="p-6">
                @if($selectedAppointment)
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <span @class([
                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' => $selectedAppointment->status === 'scheduled',
                                'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' => $selectedAppointment->status === 'completed',
                                'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' => $selectedAppointment->status === 'cancelled',
                            ])>
                                {{ ucfirst($selectedAppointment->status) }}
                            </span>
                        </div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">
                            ID: {{ $selectedAppointment->id }}
                        </div>
                    </div>
                
                    <dl class="space-y-6">
                        <div class="flex">
                            <dt class="w-32 flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Property:</dt>
                            <dd class="flex-1 text-sm text-gray-900 dark:text-white">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-md flex items-center justify-center">
                                        <flux:icon name="building-office" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                    </div>
                                    <span class="font-medium">{{ $selectedAppointment->property->title }}</span>
                                </div>
                            </dd>
                        </div>
                        
                        <div class="flex">
                            <dt class="w-32 flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Client:</dt>
                            <dd class="flex-1 text-sm text-gray-900 dark:text-white">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                        <flux:icon name="user" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                    </div>
                                    <span class="font-medium">{{ $selectedAppointment->client->name }}</span>
                                </div>
                            </dd>
                        </div>
                        
                        <div class="flex">
                            <dt class="w-32 flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Date & Time:</dt>
                            <dd class="flex-1 text-sm text-gray-900 dark:text-white">
                                <div class="space-y-1">
                                    <div class="font-medium">{{ $selectedAppointment->scheduled_at->format('F d, Y') }}</div>
                                    <div>{{ $selectedAppointment->scheduled_at->format('g:i A') }}</div>
                                </div>
                            </dd>
                        </div>
                        
                        @if($selectedAppointment->notes)
                            <div class="flex">
                                <dt class="w-32 flex-shrink-0 text-sm font-medium text-gray-500 dark:text-gray-400">Notes:</dt>
                                <dd class="flex-1 text-sm text-gray-900 dark:text-white">
                                    {{ $selectedAppointment->notes }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                    
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Created: {{ $selectedAppointment->created_at->format('M d, Y') }}
                            </div>
                            <div class="flex gap-2">
                                @if($selectedAppointment->status !== 'completed')
                                    <button
                                        wire:click="updateAppointmentStatus({{ $selectedAppointment->id }}, 'completed')"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    >
                                        <flux:icon name="check" class="w-4 h-4 mr-1" />
                                        Mark Completed
                                    </button>
                                @endif
                                
                                @if($selectedAppointment->status !== 'cancelled')
                                    <button
                                        wire:click="updateAppointmentStatus({{ $selectedAppointment->id }}, 'cancelled')"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                    >
                                        <flux:icon name="x-mark" class="w-4 h-4 mr-1" />
                                        Cancel
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </flux:modal>

    <!-- Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(2, 201, 194, 0.3);
            border-radius: 999px;
        }
        
        @media (prefers-color-scheme: dark) {
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background-color: rgba(2, 201, 194, 0.5);
            }
        }
    </style>
</div>
