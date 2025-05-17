<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyOffer;
use App\Models\ViewingAppointment;
use App\Models\FinancialRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    protected $financialService;
    protected $propertyService;

    public function __construct(
        FinancialService $financialService,
        CommercialPropertyService $propertyService
    ) {
        $this->financialService = $financialService;
        $this->propertyService = $propertyService;
    }

    public function getPropertyMarketTrends(): array
    {
        return [
            'average_price' => Property::avg('price'),
            'total_properties' => Property::count(),
            'properties_by_type' => Property::groupBy('type')
                ->select('type', DB::raw('count(*) as count'))
                ->pluck('count', 'type'),
            'average_days_on_market' => Property::where('status', 'active')
                ->avg(DB::raw('DATEDIFF(NOW(), created_at)'))
        ];
    }

    public function getRevenueAnalytics(): array
    {
        $currentMonth = now()->format('Y-m');
        
        return [
            'monthly_revenue' => $this->financialService->getCurrentMonthRevenue(),
            'revenue_growth' => $this->calculateRevenueGrowth(),
            'projected_revenue' => $this->calculateProjectedRevenue(),
            'revenue_by_property_type' => $this->getRevenueByPropertyType()
        ];
    }

    public function getOccupancyRates(): array
    {
        $properties = Property::all();
        $totalProperties = $properties->count();
        $occupiedProperties = $properties->where('status', 'occupied')->count();
        
        return [
            'overall_rate' => $totalProperties > 0 ? ($occupiedProperties / $totalProperties) * 100 : 0,
            'by_property_type' => $this->getOccupancyByPropertyType(),
            'historical_rates' => $this->getHistoricalOccupancyRates()
        ];
    }

    public function getConversionMetrics(): array
    {
        $viewings = ViewingAppointment::count();
        $offers = PropertyOffer::count();
        
        return [
            'viewing_to_offer_rate' => $viewings > 0 ? ($offers / $viewings) * 100 : 0,
            'monthly_viewings' => $this->getMonthlyViewings(),
            'offer_success_rate' => $this->calculateOfferSuccessRate()
        ];
    }

    protected function calculateRevenueGrowth(): float
    {
        $lastMonth = $this->financialService->getLastMonthRevenue();
        $currentMonth = $this->financialService->getCurrentMonthRevenue();
        
        return $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;
    }

    protected function getOccupancyByPropertyType(): Collection
    {
        return Property::groupBy('type')
            ->select(
                'type',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "occupied" THEN 1 ELSE 0 END) as occupied')
            )
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->type => [
                        'rate' => ($item->total > 0) ? ($item->occupied / $item->total) * 100 : 0,
                        'total' => $item->total,
                        'occupied' => $item->occupied
                    ]
                ];
            });
    }

    protected function getRevenueByPropertyType(): Collection
    {
        return FinancialRecord::join('properties', 'financial_records.property_id', '=', 'properties.id')
            ->groupBy('properties.type')
            ->select(
                'properties.type',
                DB::raw('SUM(amount) as total_revenue')
            )
            ->where('financial_records.type', 'income')
            ->where('financial_records.created_at', '>=', now()->startOfMonth())
            ->get()
            ->pluck('total_revenue', 'type');
    }
}
