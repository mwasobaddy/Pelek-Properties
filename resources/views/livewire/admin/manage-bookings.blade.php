<?php

use App\Models\Property;
use App\Models\PropertyBooking;
use Illuminate\Support\Collection;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;
use function Livewire\Volt\{state, mount, computed};

new class extends Component {
    #[Rule(['required', 'exists:properties,id'])]
    public $propertyId = '';

    #[Rule(['required', 'date', 'after:today'])]
    public $checkIn = '';

    #[Rule(['required', 'date', 'after:check_in'])]
    public $checkOut = '';

    #[Rule(['required', 'string', 'max:255'])]
    public $guestName = '';

    #[Rule(['nullable', 'string', 'max:255'])]
    public $guestPhone = '';

    #[Rule(['nullable', 'email', 'max:255'])]
    public $guestEmail = '';

    #[Rule(['nullable', 'string'])]
    public $notes = '';

    public $selectedBookingId = null;
    public $isEditing = false;

    public function mount()
    {
        $this->resetForm();
    }

    public function createBooking()
    {
        $this->validate();

        $property = Property::findOrFail($this->propertyId);

        // Check availability
        if (!$property->isAvailable($this->checkIn, $this->checkOut)) {
            $this->addError('checkIn', 'These dates are not available');
            return;
        }

        // Calculate total amount based on stay duration
        $checkIn = \Carbon\Carbon::parse($this->checkIn);
        $checkOut = \Carbon\Carbon::parse($this->checkOut);
        $nights = $checkIn->diffInDays($checkOut);

        $totalAmount = match($property->listing_type) {
            'airbnb' => $property->airbnb_price_nightly * $nights,
            'rent' => $property->rental_price_daily * $nights,
            default => 0,
        };

        PropertyBooking::create([
            'property_id' => $this->propertyId,
            'admin_id' => auth()->id(),
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'guest_name' => $this->guestName,
            'guest_phone' => $this->guestPhone,
            'guest_email' => $this->guestEmail,
            'notes' => $this->notes,
            'total_amount' => $totalAmount,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Booking created successfully!'
        ]);

        $this->resetForm();
    }

    public function editBooking($bookingId)
    {
        $this->isEditing = true;
        $this->selectedBookingId = $bookingId;
        $booking = PropertyBooking::findOrFail($bookingId);
        
        $this->propertyId = $booking->property_id;
        $this->checkIn = $booking->check_in->format('Y-m-d');
        $this->checkOut = $booking->check_out->format('Y-m-d');
        $this->guestName = $booking->guest_name;
        $this->guestPhone = $booking->guest_phone;
        $this->guestEmail = $booking->guest_email;
        $this->notes = $booking->notes;
    }

    public function updateBooking()
    {
        $this->validate();

        $booking = PropertyBooking::findOrFail($this->selectedBookingId);
        $property = Property::findOrFail($this->propertyId);

        // Check availability excluding current booking
        if (!$property->isAvailable($this->checkIn, $this->checkOut, $this->selectedBookingId)) {
            $this->addError('checkIn', 'These dates are not available');
            return;
        }

        // Calculate total amount
        $checkIn = \Carbon\Carbon::parse($this->checkIn);
        $checkOut = \Carbon\Carbon::parse($this->checkOut);
        $nights = $checkIn->diffInDays($checkOut);

        $totalAmount = match($property->listing_type) {
            'airbnb' => $property->airbnb_price_nightly * $nights,
            'rent' => $property->rental_price_daily * $nights,
            default => 0,
        };

        $booking->update([
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'guest_name' => $this->guestName,
            'guest_phone' => $this->guestPhone,
            'guest_email' => $this->guestEmail,
            'notes' => $this->notes,
            'total_amount' => $totalAmount,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Booking updated successfully!'
        ]);

        $this->resetForm();
    }

    public function cancelBooking($bookingId)
    {
        $booking = PropertyBooking::findOrFail($bookingId);
        $booking->update(['status' => 'cancelled']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Booking cancelled successfully!'
        ]);
    }

    public function resetForm()
    {
        $this->reset([
            'propertyId',
            'checkIn',
            'checkOut',
            'guestName',
            'guestPhone',
            'guestEmail',
            'notes',
            'selectedBookingId',
            'isEditing'
        ]);
    }

    #[computed]
    public function properties()
    {
        return Property::where('listing_type', 'airbnb')
            ->orWhere('listing_type', 'rent')
            ->orderBy('title')
            ->get();
    }

    #[computed]
    public function bookings()
    {
        return PropertyBooking::with(['property', 'admin'])
            ->latest()
            ->get();
    }
} ?>

<div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">
            {{ $isEditing ? 'Edit Booking' : 'Create New Booking' }}
        </h2>

        <form wire:submit.prevent="{{ $isEditing ? 'updateBooking' : 'createBooking' }}" class="space-y-4">
            <!-- Property Selection -->
            <div>
                <label for="propertyId" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Property</label>
                <select wire:model="propertyId" id="propertyId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <option value="">Select a property</option>
                    @foreach($this->properties as $property)
                        <option value="{{ $property->id }}">{{ $property->title }} ({{ ucfirst($property->listing_type) }})</option>
                    @endforeach
                </select>
                @error('propertyId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="checkIn" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Check In</label>
                    <input type="date" wire:model="checkIn" id="checkIn" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    @error('checkIn') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="checkOut" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Check Out</label>
                    <input type="date" wire:model="checkOut" id="checkOut" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    @error('checkOut') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Guest Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="guestName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Guest Name</label>
                    <input type="text" wire:model="guestName" id="guestName" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    @error('guestName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="guestPhone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Guest Phone</label>
                    <input type="text" wire:model="guestPhone" id="guestPhone" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    @error('guestPhone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="guestEmail" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Guest Email</label>
                <input type="email" wire:model="guestEmail" id="guestEmail" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                @error('guestEmail') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea wire:model="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
                @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end space-x-3">
                @if($isEditing)
                    <flux:button type="button" wire:click="resetForm" variant="secondary">
                        Cancel
                    </flux:button>
                @endif
                <flux:button type="submit">
                    {{ $isEditing ? 'Update Booking' : 'Create Booking' }}
                </flux:button>
            </div>
        </form>
    </div>

    <!-- Bookings List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                Bookings
            </h3>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-700">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($this->bookings as $booking)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $booking->property->title }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $booking->guest_name }} • {{ $booking->check_in->format('M d, Y') }} - {{ $booking->check_out->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($booking->status === 'confirmed')
                                    <flux:button size="sm" wire:click="editBooking({{ $booking->id }})">
                                        Edit
                                    </flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="cancelBooking({{ $booking->id }})">
                                        Cancel
                                    </flux:button>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        Cancelled
                                    </span>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
