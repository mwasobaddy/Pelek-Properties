<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Models\MarketAnalysis;
use App\Models\ValuationReport;

class ValuationSeeder extends Seeder
{
    public function run(): void
    {
        // Create a mix of valuation requests with different statuses
        $pendingRequests = ValuationRequest::factory()
            ->count(15)
            ->pending()
            ->create();

        $inProgressRequests = ValuationRequest::factory()
            ->count(10)
            ->inProgress()
            ->create();

        // Create completed requests with their associated market analyses and reports
        $completedRequests = ValuationRequest::factory()
            ->count(25)
            ->completed()
            ->create()
            ->each(function ($request) {
                // Create market analysis
                $marketAnalysis = MarketAnalysis::factory()->create([
                    'location' => $request->location,
                    'property_type' => $request->property_type,
                ]);

                // Create valuation report with varying confidence levels
                ValuationReport::factory()
                    ->create([
                        'valuation_request_id' => $request->id,
                        'market_analysis_id' => $marketAnalysis->id,
                        'confidence_level' => collect(['high', 'medium', 'low'])->random(),
                    ]);
            });
    }
}
