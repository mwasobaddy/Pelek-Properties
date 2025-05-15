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

    public function mount(Property $property)
    {
        $this->property = $property;
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->addDays(30)->format('Y-m-d');
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
                'price' => $dayInfo ? $dayInfo->custom_price : null,
                'isWeekend' => $day->isWeekend(),
            ]);
        }

        return $calendar;
    }
} ?>

<div>
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Availability Calendar
            </h3>
            <div class="flex space-x-4">
                <input 
                    type="date" 
                    wire:model.live="startDate"
                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                <input 
                    type="date" 
                    wire:model.live="endDate"
                    class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
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
                <div @class([
                    'p-2 text-center rounded-md',
                    'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200' => $day['status'] === 'available',
                    'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' => $day['status'] === 'booked',
                    'opacity-75' => $day['isWeekend']
                ])>
                    <div class="text-sm font-medium">{{ \Carbon\Carbon::parse($day['date'])->format('j') }}</div>
                    @if($day['price'])
                        <div class="text-xs mt-1">
                            KES {{ number_format($day['price']) }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
