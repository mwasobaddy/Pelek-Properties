<?php

namespace Database\Factories;

use App\Models\ManagementContract;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManagementContractFactory extends Factory
{
    protected $model = ManagementContract::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'admin_id' => User::factory(),
            'contract_type' => $this->faker->randomElement(['full_service', 'maintenance_only', 'financial_only']),
            'management_fee_percentage' => $this->faker->randomFloat(2, 5, 15),
            'base_fee' => $this->faker->randomFloat(2, 5000, 20000),
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'payment_schedule' => $this->faker->randomElement(['monthly', 'quarterly', 'yearly']),
            'services_included' => $this->faker->randomElements([
                'tenant_management',
                'maintenance',
                'financial_reporting',
                'marketing'
            ], $this->faker->numberBetween(1, 4)),
            'special_terms' => $this->faker->optional()->text(),
            'status' => 'active',
        ];
    }

    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'start_date' => now()->subYear(),
                'end_date' => now()->subMonth(),
                'status' => 'expired',
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'start_date' => now()->addMonth(),
                'status' => 'pending',
            ];
        });
    }

    public function maintenance(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'contract_type' => 'maintenance_only',
                'services_included' => ['maintenance'],
            ];
        });
    }

    public function financial(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'contract_type' => 'financial_only',
                'services_included' => ['financial_reporting'],
            ];
        });
    }
}
