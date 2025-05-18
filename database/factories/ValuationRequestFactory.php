<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class ValuationRequestFactory extends Factory
{
    protected $model = ValuationRequest::class;

    public function definition(): array
    {
        $propertyTypes = ['apartment', 'house', 'land', 'commercial', 'office'];
        $purposes = ['sale', 'rental', 'insurance'];
        $statuses = ['pending', 'in_progress', 'completed'];

        return [
            'user_id' => User::factory(),
            'property_type' => $this->faker->randomElement($propertyTypes),
            'location' => $this->faker->city(),
            'land_size' => $this->faker->randomFloat(2, 100, 10000),
            'bedrooms' => $this->faker->optional()->numberBetween(1, 6),
            'bathrooms' => $this->faker->optional()->numberBetween(1, 4),
            'description' => $this->faker->optional()->paragraph(),
            'purpose' => $this->faker->randomElement($purposes),
            'status' => $this->faker->randomElement($statuses),
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending'
        ]);
    }

    public function inProgress(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress'
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed'
        ]);
    }
}
