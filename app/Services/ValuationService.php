<?php

namespace App\Services;

use App\Models\ValuationRequest;
use App\Models\MarketAnalysis;
use App\Models\ValuationReport;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ValuationService
{
    public function createRequest(array $data): ValuationRequest
    {
        return ValuationRequest::create($data);
    }

    public function getMarketAnalysis(string $location, string $propertyType): MarketAnalysis
    {
        return MarketAnalysis::firstOrCreate(
            ['location' => $location, 'property_type' => $propertyType],
            [
                'average_price' => 0,
                'price_per_sqft' => 0,
                'total_listings' => 0,
                'days_on_market' => 0,
                'price_trends' => []
            ]
        );
    }

    public function generateReport(ValuationRequest $request): ValuationReport
    {
        $marketAnalysis = $this->getMarketAnalysis($request->location, $request->property_type);
        
        // Calculate estimated value based on market analysis and property details
        $estimatedValue = $this->calculateEstimatedValue($request, $marketAnalysis);
        
        // Get comparable properties
        $comparableProperties = $this->findComparableProperties($request);
        
        // Generate valuation factors
        $valuationFactors = $this->generateValuationFactors($request, $marketAnalysis);
        
        return ValuationReport::create([
            'valuation_request_id' => $request->id,
            'market_analysis_id' => $marketAnalysis->id,
            'estimated_value' => $estimatedValue,
            'justification' => $this->generateJustification($request, $marketAnalysis, $estimatedValue),
            'comparable_properties' => $comparableProperties,
            'valuation_factors' => $valuationFactors,
            'confidence_level' => $this->determineConfidenceLevel($request, $marketAnalysis),
            'valid_until' => Carbon::now()->addMonths(3),
        ]);
    }

    private function calculateEstimatedValue(ValuationRequest $request, MarketAnalysis $marketAnalysis): float
    {
        $baseValue = $marketAnalysis->average_price;
        
        // Adjust based on land size
        if ($request->land_size) {
            $baseValue += ($request->land_size * $marketAnalysis->price_per_sqft);
        }
        
        // Adjust based on bedrooms and bathrooms
        if ($request->bedrooms) {
            $baseValue += ($request->bedrooms * 50000);
        }
        if ($request->bathrooms) {
            $baseValue += ($request->bathrooms * 25000);
        }
        
        return round($baseValue, 2);
    }

    private function findComparableProperties(ValuationRequest $request): array
    {
        // This would typically query your property database for similar properties
        // For now, returning a mock array
        return [
            [
                'location' => $request->location,
                'price' => 1000000,
                'bedrooms' => $request->bedrooms,
                'bathrooms' => $request->bathrooms,
                'land_size' => $request->land_size,
            ]
        ];
    }

    private function generateValuationFactors(ValuationRequest $request, MarketAnalysis $marketAnalysis): array
    {
        return [
            'location_rating' => 'prime',
            'market_trend' => $marketAnalysis->getMarketTrend(),
            'property_condition' => 'good',
            'development_potential' => 'high',
        ];
    }

    private function generateJustification(ValuationRequest $request, MarketAnalysis $marketAnalysis, float $estimatedValue): string
    {
        return "Based on current market analysis of {$request->location}, properties of this type are valued at an average of {$marketAnalysis->average_price}. " .
               "Considering the property's specific features and market conditions, we estimate the value at {$estimatedValue}.";
    }

    private function determineConfidenceLevel(ValuationRequest $request, MarketAnalysis $marketAnalysis): string
    {
        if ($marketAnalysis->total_listings > 10) {
            return 'high';
        } elseif ($marketAnalysis->total_listings > 5) {
            return 'medium';
        }
        return 'low';
    }
}
