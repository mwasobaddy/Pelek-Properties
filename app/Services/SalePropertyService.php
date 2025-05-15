<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\ViewingAppointment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SalePropertyService
{
    /**
     * Get all properties for sale with optional filters
     */
    public function getSaleProperties(array $filters = []): Collection
    {
        return Property::query()
            ->forSale()
            ->when(isset($filters['price_min']), fn($q) => $q->where('sale_price', '>=', $filters['price_min']))
            ->when(isset($filters['price_max']), fn($q) => $q->where('sale_price', '<=', $filters['price_max']))
            ->when(isset($filters['development_status']), fn($q) => $q->where('development_status', $filters['development_status']))
            ->when(isset($filters['ownership_type']), fn($q) => $q->where('ownership_type', $filters['ownership_type']))
            ->when(isset($filters['mortgage_available']), fn($q) => $q->where('mortgage_available', true))
            ->when(isset($filters['has_title_deed']), fn($q) => $q->where('has_title_deed', true))
            ->with(['images', 'amenities'])
            ->latest()
            ->get();
    }

    /**
     * Schedule a viewing appointment
     */
    public function scheduleViewing(
        Property $property,
        array $appointmentData
    ): ViewingAppointment {
        return $property->viewingAppointments()->create([
            'admin_id' => auth()->id(),
            'client_name' => $appointmentData['client_name'],
            'client_phone' => $appointmentData['client_phone'],
            'client_email' => $appointmentData['client_email'] ?? null,
            'appointment_date' => $appointmentData['appointment_date'],
            'notes' => $appointmentData['notes'] ?? null,
        ]);
    }

    /**
     * Record a property offer
     */
    public function recordOffer(
        Property $property,
        array $offerData
    ): PropertyOffer {
        return $property->offers()->create([
            'admin_id' => auth()->id(),
            'client_name' => $offerData['client_name'],
            'client_phone' => $offerData['client_phone'],
            'client_email' => $offerData['client_email'] ?? null,
            'offer_amount' => $offerData['amount'],
            'payment_method' => $offerData['payment_method'],
            'terms_conditions' => $offerData['terms_conditions'] ?? null,
            'valid_until' => isset($offerData['valid_days']) 
                ? now()->addDays($offerData['valid_days']) 
                : null,
            'notes' => $offerData['notes'] ?? null,
        ]);
    }

    /**
     * Update offer status
     */
    public function updateOfferStatus(
        PropertyOffer $offer,
        string $status,
        ?string $notes = null
    ): void {
        $offer->update([
            'status' => $status,
            'notes' => $notes ?? $offer->notes,
        ]);

        // If an offer is accepted, reject all other pending offers
        if ($status === 'accepted') {
            $offer->property->offers()
                ->where('id', '!=', $offer->id)
                ->pending()
                ->update(['status' => 'rejected']);
        }
    }

    /**
     * Get viewing appointments dashboard data
     */
    public function getViewingsDashboard(): array
    {
        $appointments = ViewingAppointment::with(['property', 'admin'])
            ->upcoming()
            ->get();

        return [
            'today' => $appointments->filter(fn($apt) => 
                $apt->appointment_date->isToday()
            ),
            'this_week' => $appointments->filter(fn($apt) => 
                $apt->appointment_date->isCurrentWeek()
            ),
            'pending' => $appointments->where('status', 'pending')->count(),
            'total_scheduled' => $appointments->count(),
        ];
    }

    /**
     * Get offers dashboard data
     */
    public function getOffersDashboard(): array
    {
        $offers = PropertyOffer::with(['property', 'admin'])
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->get();

        return [
            'active_offers' => $offers->where('status', 'pending')->count(),
            'accepted_offers' => $offers->where('status', 'accepted')->count(),
            'total_value' => $offers->where('status', 'pending')->sum('offer_amount'),
            'recent_offers' => $offers->take(5),
        ];
    }

    /**
     * Get property sale analytics
     */
    public function getPropertyAnalytics(Property $property): array
    {
        $viewings = $property->viewingAppointments;
        $offers = $property->offers;

        return [
            'total_viewings' => $viewings->count(),
            'upcoming_viewings' => $viewings->where('appointment_date', '>=', now())->count(),
            'total_offers' => $offers->count(),
            'active_offers' => $offers->where('status', 'pending')->count(),
            'highest_offer' => $offers->max('offer_amount'),
            'average_offer' => $offers->avg('offer_amount'),
            'offer_history' => $offers->map(fn($offer) => [
                'date' => $offer->created_at->format('Y-m-d'),
                'amount' => $offer->offer_amount,
                'status' => $offer->status,
            ]),
        ];
    }
}
