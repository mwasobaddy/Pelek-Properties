<?php

namespace Database\Factories;

use App\Models\ValuationReport;
use App\Models\ValuationRequest;
use App\Models\MarketAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;

class ValuationReportFactory extends Factory
{
    protected $model = ValuationReport::class;

    public function definition(): array
    {
        $confidenceLevels = ['high', 'medium', 'low'];

        return [
            'valuation_request_id' => ValuationRequest::factory(),
            'market_analysis_id' => MarketAnalysis::factory(),
            'estimated_value' => $this->faker->randomFloat(2, 100000, 5000000),
            'justification' => $this->faker->paragraphs(3, true),
            'comparable_properties' => [
                [
                    'address' => $this->faker->address(),
                    'sale_price' => $this->faker->randomFloat(2, 100000, 5000000),
                    'sale_date' => $this->faker->date(),
                    'similarity_score' => $this->faker->randomFloat(2, 0.5, 1),
                ],
                [
                    'address' => $this->faker->address(),
                    'sale_price' => $this->faker->randomFloat(2, 100000, 5000000),
                    'sale_date' => $this->faker->date(),
                    'similarity_score' => $this->faker->randomFloat(2, 0.5, 1),
                ],
                [
                    'address' => $this->faker->address(),
                    'sale_price' => $this->faker->randomFloat(2, 100000, 5000000),
                    'sale_date' => $this->faker->date(),
                    'similarity_score' => $this->faker->randomFloat(2, 0.5, 1),
                ],
            ],
            'valuation_factors' => [
                'location_score' => $this->faker->randomFloat(2, 0.1, 1),
                'condition_score' => $this->faker->randomFloat(2, 0.1, 1),
                'market_demand' => $this->faker->randomFloat(2, 0.1, 1),
                'amenities_score' => $this->faker->randomFloat(2, 0.1, 1),
            ],
            'confidence_level' => $this->faker->randomElement($confidenceLevels),
            'valid_until' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
        ];
    }

    public function highConfidence(): self
    {
        return $this->state(fn (array $attributes) => [
            'confidence_level' => 'high'
        ]);
    }

    public function mediumConfidence(): self
    {
        return $this->state(fn (array $attributes) => [
            'confidence_level' => 'medium'
        ]);
    }

    public function lowConfidence(): self
    {
        return $this->state(fn (array $attributes) => [
            'confidence_level' => 'low'
        ]);
    }
}
