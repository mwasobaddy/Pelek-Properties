<?php

namespace Database\Factories;

use App\Models\FinancialRecord;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialRecordFactory extends Factory
{
    protected $model = FinancialRecord::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'transaction_type' => $this->faker->randomElement(['income', 'expense']),
            'category' => $this->faker->randomElement([
                'rent',
                'maintenance',
                'utilities',
                'management_fee',
                'repairs',
                'marketing',
                'insurance',
                'taxes'
            ]),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'mpesa', 'cheque']),
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
            'description' => $this->faker->sentence(),
            'reference_number' => $this->faker->unique()->bothify('TR-####-????'),
            'recorded_by' => User::factory(),
        ];
    }

    public function income(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'transaction_type' => 'income',
                'category' => $this->faker->randomElement(['rent', 'security_deposit', 'management_fee']),
            ];
        });
    }

    public function expense(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'transaction_type' => 'expense',
                'category' => $this->faker->randomElement(['maintenance', 'utilities', 'repairs']),
            ];
        });
    }

    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
            ];
        });
    }
}
