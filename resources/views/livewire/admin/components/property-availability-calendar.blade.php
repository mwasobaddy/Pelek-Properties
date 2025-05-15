<?php

use App\Models\Property;
use Carbon\CarbonPeriod;
use Livewire\Volt\Component;
use function Livewire\Volt\{computed, state};

new class extends Component {
    public Property $property;

    #[state]
    public $startDate;

    #[state]
    public $endDate;

    // New properties for bulk editing
    #[state]
    public $selectedDates = [];

    #[state]
    public $bulkStatus = 'available';

    #[state]
    public $bulkPrice = '';

    #[state]
    public $showBulkEditModal = false;

    #[state]
    public $isDragging = false;

    #[state]
    public $dragStartDate = null;

    public function mount(Property $property)
    {
        $this->property = $property;
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->addDays(30)->format('Y-m-d');
        $this->bulkPrice = $property->airbnb_price_nightly;
    }

    public function startDateSelection($date)
    {
        $this->isDragging = true;
        $this->dragStartDate = $date;
        $this->selectedDates = [$date];
    }

    public function continueDateSelection($date)
    {
        if (!$this->isDragging) return;

        $period = CarbonPeriod::create(
            min($this->dragStartDate, $date),
            max($this->dragStartDate, $date)
        );

        $this->selectedDates = $period->toArray();
    }

    public function endDateSelection()
    {
        $this->isDragging = false;
        $this->showBulkEditModal = true;
    }

    public function applyBulkEdit()
    {
        foreach ($this->selectedDates as $date) {
            if ($date instanceof \Carbon\Carbon) {
                $date = $date->format('Y-m-d');
            }
            
            $this->property->generateAvailabilityCalendar(
                $date,
                $date,
                $this->bulkStatus,
                $this->bulkPrice ?: null
            );
        }

        $this->selectedDates = [];
        $this->showBulkEditModal = false;
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Calendar updated successfully'
        ]);
    }

    public function cancelBulkEdit()
    {
        $this->selectedDates = [];
        $this->showBulkEditModal = false;
        $this->isDragging = false;
    }

    #[computed]
    public function availabilityCalendar()
    {
        $days = CarbonPeriod::create($this->startDate, $this->endDate);
        $availability = $this->property->availability()
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->get()
            ->keyBy(fn($item) => $item->date->format('Y-m-d'));

        $bookings = $this->property->bookings()
            ->where('status', 'confirmed')
            ->whereBetween('check_in', [$this->startDate, $this->endDate])
            ->orWhereBetween('check_out', [$this->startDate, $this->endDate])
            ->get();

        $calendar = collect();
        foreach ($days as $day) {
            $date = $day->format('Y-m-d');
            $dayInfo = $availability->get($date);

            $isBooked = $bookings->contains(function ($booking) use ($date) {
                return $date >= $booking->check_in->format('Y-m-d') &&
                       $date <= $booking->check_out->format('Y-m-d');
            });

            $calendar->push([
                'date' => $date,
                'status' => $isBooked ? 'booked' : ($dayInfo ? $dayInfo->status : 'available'),
                'price' => $dayInfo ? $dayInfo->custom_price : $this->property->airbnb_price_nightly,
                'isWeekend' => $day->isWeekend(),
                'isSelected' => in_array($date, array_map(function ($d) {
                    return $d instanceof \Carbon\Carbon ? $d->format('Y-m-d') : $d;
                }, $this->selectedDates)),
            ]);
        }

        return $calendar;
    }
} ?>

<div wire:poll.10s class="relative">
    <!-- Calendar Controls -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Availability Calendar
            </h3>
            <div class="flex space-x-4">
                <button 
                    class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md"
                    wire:click="$set('startDate', now()->subMonth()->format('Y-m-d'))"
                >
                    Previous Month
                </button>
                
                <input 
                    type="month" 
                    wire:model.live="startDate"
                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                
                <button 
                    class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md"
                    wire:click="$set('startDate', now()->addMonth()->format('Y-m-d'))"
                >
                    Next Month
                </button>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-1">
            <!-- Day labels -->
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="text-xs font-medium text-center text-gray-500 dark:text-gray-400 p-2">
                    {{ $day }}
                </div>
            @endforeach

            <!-- Calendar days -->
            @foreach($this->availabilityCalendar as $day)
                <div 
                    wire:mousedown="startDateSelection('{{ $day['date'] }}')"
                    wire:mouseover="continueDateSelection('{{ $day['date'] }}')"
                    wire:mouseup="endDateSelection"
                    @class([
                        'p-2 text-center rounded-md cursor-pointer transition-all duration-200',
                        'ring-2 ring-primary-500 ring-offset-2' => $day['isSelected'],
                        'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-800/40' => $day['status'] === 'available',
                        'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' => $day['status'] === 'booked',
                        'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 hover:bg-yellow-200 dark:hover:bg-yellow-800/40' => $day['status'] === 'blocked',
                        'opacity-75' => $day['isWeekend']
                    ])
                >
                    <div class="text-sm font-medium">
                        {{ \Carbon\Carbon::parse($day['date'])->format('j') }}
                    </div>
                    @if($day['price'])
                        <div class="text-xs mt-1 font-medium">
                            KES {{ number_format($day['price']) }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Bulk Edit Modal -->
        <div x-data="{ show: @entangle('showBulkEditModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg p-8 max-w-lg w-full shadow-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        Edit {{ count($selectedDates) }} Selected Days
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Status
                            </label>
                            <select 
                                wire:model="bulkStatus"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            >
                                <option value="available">Available</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Price per Night (KES)
                            </label>
                            <input 
                                type="number" 
                                wire:model="bulkPrice"
                                placeholder="{{ $property->airbnb_price_nightly }}"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            >
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button
                                wire:click="cancelBulkEdit"
                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md"
                            >
                                Cancel
                            </button>
                            <button
                                wire:click="applyBulkEdit"
                                class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-md"
                            >
                                Apply Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-4 flex items-center justify-end space-x-4 text-sm">
        <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-green-100 dark:bg-green-900/30 rounded"></div>
            <span class="text-gray-600 dark:text-gray-400">Available</span>
        </div>
        <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-yellow-100 dark:bg-yellow-900/30 rounded"></div>
            <span class="text-gray-600 dark:text-gray-400">Blocked</span>
        </div>
        <div class="flex items-center space-x-2">
            <div class="w-4 h-4 bg-red-100 dark:bg-red-900/30 rounded"></div>
            <span class="text-gray-600 dark:text-gray-400">Booked</span>
        </div>
    </div>
</div>
