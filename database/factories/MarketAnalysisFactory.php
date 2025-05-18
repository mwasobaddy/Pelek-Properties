<?php

namespace Database\Factories;

use App\Models\MarketAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketAnalysisFactory extends Factory
{
    protected $model = MarketAnalysis::class;

    public function definition(): array
    {
        $propertyTypes = ['apartment', 'house', 'land', 'commercial', 'office'];

        return [
            'location' => $this->faker->city(),
            'property_type' => $this->faker->randomElement($propertyTypes),
            'average_price' => $this->faker->randomFloat(2, 100000, 5000000),
            'price_per_sqft' => $this->faker->randomFloat(2, 50, 1000),
            'total_listings' => $this->faker->numberBetween(10, 500),
            'days_on_market' => $this->faker->numberBetween(7, 180),
            'price_trends' => [
                'last_month' => $this->faker->randomFloat(2, -5, 5),
                'last_quarter' => $this->faker->randomFloat(2, -10, 10),
                'last_year' => $this->faker->randomFloat(2, -15, 15),
            ],
        ];
    }
}
