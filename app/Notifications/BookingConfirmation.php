<?php

namespace App\Notifications;

use App\Models\PropertyBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WhatsApp\WhatsAppChannel;
use NotificationChannels\WhatsApp\WhatsAppMessage;

class BookingConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PropertyBooking $booking
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', WhatsAppChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $property = $this->booking->property;
        
        return (new MailMessage)
            ->subject('Booking Confirmation - ' . $property->title)
            ->greeting('Hello ' . $this->booking->guest_name . '!')
            ->line('Your booking has been confirmed.')
            ->line('Booking Details:')
            ->line('Property: ' . $property->title)
            ->line('Check-in: ' . $this->booking->check_in->format('D, M d, Y'))
            ->line('Check-out: ' . $this->booking->check_out->format('D, M d, Y'))
            ->line('Total Amount: KES ' . number_format($this->booking->total_amount, 2))
            ->action('View Booking Details', url('/bookings/' . $this->booking->id))
            ->line('Thank you for choosing Pelek Properties!');
    }

    public function toWhatsApp(object $notifiable): WhatsAppMessage
    {
        $property = $this->booking->property;
        
        return WhatsAppMessage::create()
            ->to($this->booking->guest_phone)
            ->content("*Booking Confirmation - {$property->title}*\n\n" .
                "Hello {$this->booking->guest_name}!\n\n" .
                "Your booking has been confirmed.\n\n" .
                "*Booking Details:*\n" .
                "Property: {$property->title}\n" .
                "Check-in: {$this->booking->check_in->format('D, M d, Y')}\n" .
                "Check-out: {$this->booking->check_out->format('D, M d, Y')}\n" .
                "Total Amount: KES " . number_format($this->booking->total_amount, 2) . "\n\n" .
                "For any questions, please contact us at {$property->whatsapp_number}\n\n" .
                "Thank you for choosing Pelek Properties!");
    }
}
