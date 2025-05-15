<?php

namespace App\Livewire;

use App\Models\Property;
use App\Models\PropertyBooking;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Livewire\Volt\Component;

class PropertyBookingForm extends Component
{
    public Property $property;

    #[Rule(['required', 'date', 'after:today'])]
    public $checkIn = '';

    #[Rule(['required', 'date', 'after:check_in'])]
    public $checkOut = '';

    #[Rule(['required', 'string', 'max:255'])]
    public $guestName = '';

    #[Rule(['required', 'string', 'max:255'])]
    public $guestPhone = '';

    #[Rule(['required', 'email', 'max:255'])]
    public $guestEmail = '';

    #[Rule(['nullable', 'string'])]
    public $notes = '';

    public $totalAmount = 0;
    public $isDateRangeValid = false;

    public function mount(Property $property)
    {
        $this->property = $property;
    }

    public function updatedCheckIn()
    {
        $this->validateDateRange();
        $this->calculateTotal();
    }

    public function updatedCheckOut()
    {
        $this->validateDateRange();
        $this->calculateTotal();
    }

    protected function validateDateRange()
    {
        $this->isDateRangeValid = false;
        
        if (!$this->checkIn || !$this->checkOut) {
            return;
        }

        $checkIn = Carbon::parse($this->checkIn);
        $checkOut = Carbon::parse($this->checkOut);

        if ($checkOut->lte($checkIn)) {
            $this->addError('checkOut', 'Check-out date must be after check-in date');
            return;
        }

        if (!$this->property->isAvailable($this->checkIn, $this->checkOut)) {
            $this->addError('checkIn', 'Selected dates are not available');
            return;
        }

        $this->isDateRangeValid = true;
    }

    protected function calculateTotal()
    {
        if (!$this->isDateRangeValid) {
            $this->totalAmount = 0;
            return;
        }

        $checkIn = Carbon::parse($this->checkIn);
        $checkOut = Carbon::parse($this->checkOut);
        $nights = $checkIn->diffInDays($checkOut);

        // Get price from availability calendar or default property pricing
        $pricePerNight = $this->property->availability()
            ->whereBetween('date', [$checkIn, $checkOut])
            ->avg('custom_price') ?? match($this->property->listing_type) {
                'airbnb' => $this->property->airbnb_price_nightly,
                'rent' => $this->property->rental_price_daily,
                default => 0,
            };

        $this->totalAmount = $pricePerNight * $nights;
    }

    public function whatsAppContact()
    {
        $message = "Hi! I'm interested in booking *{$this->property->title}*";
        if ($this->checkIn && $this->checkOut) {
            $message .= " from {$this->checkIn} to {$this->checkOut}";
        }
        $message .= ". Can you help me?";

        $phone = $this->property->whatsapp_number;
        $encodedMessage = urlencode($message);
        
        return redirect()->away("https://wa.me/{$phone}?text={$encodedMessage}");
    }

    public function createBooking()
    {
        if (!$this->isDateRangeValid) {
            return;
        }

        $this->validate();

        $booking = PropertyBooking::create([
            'property_id' => $this->property->id,
            'admin_id' => 1, // We'll update this after user authentication is implemented
            'check_in' => $this->checkIn,
            'check_out' => $this->checkOut,
            'guest_name' => $this->guestName,
            'guest_phone' => $this->guestPhone,
            'guest_email' => $this->guestEmail,
            'notes' => $this->notes,
            'total_amount' => $this->totalAmount,
            'status' => 'confirmed',
        ]);

        $this->dispatch('booking-created', bookingId: $booking->id);
        $this->reset(['checkIn', 'checkOut', 'guestName', 'guestPhone', 'guestEmail', 'notes']);
    }
}
