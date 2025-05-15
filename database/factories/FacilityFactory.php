<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    public function definition(): array
    {
        $types = ['HVAC', 'Security', 'Utilities', 'Amenities', 'Network', 'Safety'];
        
        return [
            'name' => $this->faker->words(2, true),
            'type' => $this->faker->randomElement($types),
            'description' => $this->faker->sentence(),
            'specifications' => [
                'brand' => $this->faker->company(),
                'model' => $this->faker->bothify('???-###'),
                'capacity' => $this->faker->numberBetween(1, 100),
                'year' => $this->faker->year(),
            ],
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    public function hvac(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'HVAC',
                'name' => $this->faker->randomElement(['Central AC', 'Heat Pump', 'Ventilation System']),
            ];
        });
    }

    public function security(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'Security',
                'name' => $this->faker->randomElement(['CCTV System', 'Access Control', 'Security Gates']),
            ];
        });
    }
}
