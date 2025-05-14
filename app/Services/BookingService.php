<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Carbon;

class BookingService
{
    /**
     * Generate WhatsApp message for property inquiry
     */
    public function generateWhatsAppMessage(Property $property, array $inquiryData): string
    {
        $message = "Hello! I'm interested in your {$property->listing_type} property:\n\n";
        $message .= "🏠 {$property->title}\n";
        $message .= "📍 {$property->location}\n";
        
        if (!empty($inquiryData['dates'])) {
            $message .= "📅 Interested dates: {$inquiryData['dates']}\n";
        }

        if (!empty($inquiryData['guests'])) {
            $message .= "👥 Number of guests: {$inquiryData['guests']}\n";
        }

        if (!empty($inquiryData['message'])) {
            $message .= "\n💬 Message: {$inquiryData['message']}\n";
        }

        return urlencode($message);
    }

    /**
     * Get WhatsApp redirect URL
     */
    public function getWhatsAppUrl(Property $property, array $inquiryData): string
    {
        $message = $this->generateWhatsAppMessage($property, $inquiryData);
        $phone = str_replace(['+', ' ', '-'], '', $property->whatsapp_number);
        
        return "https://wa.me/{$phone}?text={$message}";
    }

    /**
     * Check property availability for given dates (Airbnb properties)
     */
    public function checkAvailability(Property $property, string $startDate, string $endDate): bool
    {
        if ($property->listing_type !== 'airbnb') {
            return false;
        }

        $calendar = $property->availability_calendar ?? [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        for ($date = $start; $date->lte($end); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            if (isset($calendar[$dateStr]) && $calendar[$dateStr] !== 'available') {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate total price for a booking period
     */
    public function calculatePrice(Property $property, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = $start->diffInDays($end);
        $weeks = floor($days / 7);
        $remainingDays = $days % 7;
        $months = floor($days / 30);
        $remainingWeeks = floor(($days % 30) / 7);
        $remainingDaysAfterWeeks = ($days % 30) % 7;

        $total = 0;
        $breakdown = [];

        if ($property->listing_type === 'airbnb') {
            // Calculate using best price breaks (monthly -> weekly -> nightly)
            if ($months > 0 && $property->airbnb_price_monthly) {
                $total += $months * $property->airbnb_price_monthly;
                $breakdown['monthly'] = [
                    'units' => $months,
                    'price' => $property->airbnb_price_monthly,
                    'total' => $months * $property->airbnb_price_monthly
                ];
            }
            
            if ($remainingWeeks > 0 && $property->airbnb_price_weekly) {
                $total += $remainingWeeks * $property->airbnb_price_weekly;
                $breakdown['weekly'] = [
                    'units' => $remainingWeeks,
                    'price' => $property->airbnb_price_weekly,
                    'total' => $remainingWeeks * $property->airbnb_price_weekly
                ];
            }

            if ($remainingDaysAfterWeeks > 0) {
                $total += $remainingDaysAfterWeeks * $property->airbnb_price_nightly;
                $breakdown['nightly'] = [
                    'units' => $remainingDaysAfterWeeks,
                    'price' => $property->airbnb_price_nightly,
                    'total' => $remainingDaysAfterWeeks * $property->airbnb_price_nightly
                ];
            }
        } else if ($property->listing_type === 'rent') {
            // Calculate rental price
            if ($days >= 30 && $property->rental_price_monthly) {
                $total = $months * $property->rental_price_monthly;
                if ($remainingDays > 0 && $property->rental_price_daily) {
                    $total += $remainingDays * $property->rental_price_daily;
                }
            } else {
                $total = $days * $property->rental_price_daily;
            }
        }

        return [
            'total' => $total,
            'breakdown' => $breakdown,
            'days' => $days
        ];
    }
}