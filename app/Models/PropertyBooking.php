<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\WhatsAppService;
use App\Notifications\BookingConfirmation;

class PropertyBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'admin_id',
        'check_in',
        'check_out',
        'guest_name',
        'guest_phone',
        'guest_email',
        'notes',
        'total_amount',
        'status'
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_amount' => 'decimal:2'
    ];

    protected static function booted()
    {
        static::created(function ($booking) {
            try {
                // Send email notification
                $booking->notify(new BookingConfirmation($booking));

                // Send WhatsApp message
                if ($booking->guest_phone) {
                    app(WhatsAppService::class)->sendMessage(
                        $booking->guest_phone,
                        "✨ Booking Confirmed ✨\n\n" .
                        "Hello {$booking->guest_name}!\n\n" .
                        "Your booking for {$booking->property->title} has been confirmed.\n\n" .
                        "Check-in: {$booking->check_in->format('D, M d, Y')}\n" .
                        "Check-out: {$booking->check_out->format('D, M d, Y')}\n" .
                        "Total Amount: KES " . number_format($booking->total_amount, 2) . "\n\n" .
                        "For any questions, please contact us!"
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Booking notification failed', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage()
                ]);
            }
        });

        // When a booking is created or status changes to confirmed
        static::saved(function ($booking) {
            if ($booking->status === 'confirmed') {
                $booking->property->blockDatesForBooking($booking);
            }
        });

        // When a booking is cancelled, make dates available again
        static::updated(function ($booking) {
            if ($booking->isDirty('status') && $booking->status === 'cancelled') {
                // Regenerate availability for the booking period
                $booking->property->generateAvailabilityCalendar(
                    $booking->check_in,
                    $booking->check_out
                );
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Scope to get active bookings
    public function scopeActive($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Check if dates are available, considering both availability calendar and existing bookings
     */
    public static function areDatesAvailable($propertyId, $checkIn, $checkOut, $excludeBookingId = null)
    {
        $query = self::where('property_id', $propertyId)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                    });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->count() === 0;
    }
}
